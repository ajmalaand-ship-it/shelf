<?php

namespace App\Support\WordImport;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use Illuminate\Validation\ValidationException;
use ZipArchive;

/** Reads text only; never normalizes Unicode, repairs XML, or rewrites source files. */
class DocxReader
{
    public const MAX_BYTES = 20 * 1024 * 1024;

    private const WORD_NAMESPACES = [
        'http://schemas.openxmlformats.org/wordprocessingml/2006/main',
        'http://purl.oclc.org/ooxml/wordprocessingml/main',
    ];

    public function read(string $path): array
    {
        if (! is_file($path) || filesize($path) > self::MAX_BYTES) {
            $this->fail('Choose one Word .docx file no larger than 20 MB.');
        }
        $zip = new ZipArchive;
        if ($zip->open($path) !== true) {
            $this->fail('This file is not a readable Word .docx document.');
        }
        try {
            $expanded = 0;
            $names = [];
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $entry = $zip->statIndex($i);
                $expanded += $entry['size'];
                $names[] = $entry['name'];
                if ($expanded > 64 * 1024 * 1024 || $zip->numFiles > 10000) {
                    $this->fail('This document is too complex to preview safely. Split it into smaller Word files.');
                }
            }
            $types = $this->xml($zip->getFromName('[Content_Types].xml'));
            $typesPath = new DOMXPath($types);
            if (! $typesPath->query('//*[local-name()="Override" and @PartName="/word/document.xml" and @ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"]')->length) {
                $this->fail('Choose a standard Word .docx document (not a macro-enabled document).');
            }
            $document = $this->xml($zip->getFromName('word/document.xml'));
            $namespace = $document->documentElement->namespaceURI;
            if (! in_array($namespace, self::WORD_NAMESPACES, true)) {
                $this->fail('This Word document uses an unsupported text format.');
            }
            $xpath = new DOMXPath($document);
            $xpath->registerNamespace('w', $namespace);
            $warnings = [];
            foreach ([
                'Images' => '//w:drawing | //w:pict',
                'Tables' => '//w:tbl',
                'Footnotes' => '//w:footnoteReference',
                'Endnotes' => '//w:endnoteReference',
                'Text boxes' => '//w:txbxContent | //*[local-name()="textbox"]',
                'Comments' => '//w:commentRangeStart | //w:commentReference',
                'Tracked changes' => '//w:ins | //w:del | //w:moveFrom | //w:moveTo | //w:rPrChange | //w:pPrChange | //w:sectPrChange',
                'Embedded objects / alternate content' => '//w:object | //w:altChunk | //*[local-name()="AlternateContent"]',
            ] as $label => $query) {
                if ($xpath->query($query)->length) {
                    $warnings[$label] = $label.' found and NOT imported.';
                }
            }
            foreach ($names as $name) {
                $label = match (true) {
                    str_starts_with($name, 'word/media/') => 'Images',
                    $name === 'word/footnotes.xml' => 'Footnotes',
                    $name === 'word/endnotes.xml' => 'Endnotes',
                    $name === 'word/comments.xml' => 'Comments',
                    (bool) preg_match('~^word/(header|footer)\d*\.xml$~', $name) => 'Headers / footers',
                    default => null,
                };
                if ($label) {
                    $warnings[$label] = $label.' found and NOT imported.';
                }
            }
            $omissions = array_values($warnings);
            $headingStyles = ['Heading1'];
            if (($stylesXml = $zip->getFromName('word/styles.xml')) !== false) {
                $stylesPath = new DOMXPath($this->xml($stylesXml));
                $stylesPath->registerNamespace('w', $namespace);
                foreach ($stylesPath->query('//w:style[@w:type="paragraph"]') as $style) {
                    if (strcasecmp($stylesPath->evaluate('string(w:name/@w:val)', $style), 'heading 1') === 0) {
                        $headingStyles[] = $style->getAttributeNS($namespace, 'styleId');
                    }
                }
            }
            $body = $xpath->query('/w:document/w:body')->item(0);
            if (! $body) {
                $this->fail('The document has no readable main body.');
            }
            $items = [];
            $title = null;
            $lines = [];
            $started = false;
            $flush = function () use (&$items, &$title, &$lines, &$started): void {
                // Only truly empty boundary lines are removed; spaces are source text.
                while ($lines && $lines[0] === '') {
                    array_shift($lines);
                }
                while ($lines && end($lines) === '') {
                    array_pop($lines);
                }
                if ($started || $lines) {
                    $text = implode("\n", $lines);
                    $items[] = [
                        'title' => $title, 'body' => $text,
                        'first_lines' => implode("\n", array_slice($lines, 0, 2)),
                        'word_count' => preg_match_all('/[^\s\p{Z}]+/u', $text),
                    ];
                }
                $title = null;
                $lines = [];
            };
            foreach ($this->paragraphs($body) as $paragraph) {
                $text = $this->text($paragraph);
                $style = $xpath->evaluate('string(w:pPr/w:pStyle/@w:val)', $paragraph);
                if (in_array($style, $headingStyles, true)) {
                    $flush();
                    if (mb_strlen($text) > 255) {
                        $this->fail('A Heading 1 title exceeds 255 characters. Nothing was imported; change its style in Word to keep it as body text.');
                    }
                    $title = $text;
                    $started = true;

                    continue;
                }
                foreach (explode("\n", $text) as $line) {
                    if ($line === '***') {
                        $flush();
                        $started = true;
                    } else {
                        $lines[] = $line;
                    }
                }
            }
            $flush();
            foreach ($items as $index => $item) {
                if ($item['word_count'] === 0) {
                    $warnings[] = 'Item '.($index + 1).' is empty. It will be imported as an empty draft.';
                }
            }
            if (! $items) {
                $warnings[] = 'No items were found. There is nothing to import.';
            }

            return ['items' => $items, 'omissions' => $omissions, 'warnings' => array_values($warnings), 'sha256' => hash_file('sha256', $path)];
        } finally {
            $zip->close();
        }
    }

    private function xml(string|false $xml): DOMDocument
    {
        if ($xml === false || stripos($xml, '<!DOCTYPE') !== false || stripos($xml, '<!ENTITY') !== false) {
            $this->fail('The document contains missing or unsupported XML. Nothing was imported.');
        }
        $previous = libxml_use_internal_errors(true);
        try {
            $document = new DOMDocument;
            $document->preserveWhiteSpace = true;
            if (! $document->loadXML($xml, LIBXML_NONET) || $document->doctype) {
                $this->fail('The document contains invalid XML. Nothing was imported; source text was not repaired.');
            }

            return $document;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }

    private function paragraphs(DOMNode $node): iterable
    {
        foreach ($node->childNodes as $child) {
            if (! $child instanceof DOMElement || $this->excluded($child)) {
                continue;
            }
            if ($child->localName === 'p' && in_array($child->namespaceURI, self::WORD_NAMESPACES, true)) {
                yield $child;
            } else {
                yield from $this->paragraphs($child);
            }
        }
    }

    private function excluded(DOMElement $node): bool
    {
        return $node->localName === 'AlternateContent' || (in_array($node->namespaceURI, self::WORD_NAMESPACES, true)
            && in_array($node->localName, ['txbxContent', 'tbl', 'drawing', 'pict', 'object', 'altChunk', 'ins', 'del', 'moveFrom', 'moveTo', 'pPr', 'rPr', 'sdtPr', 'sectPr'], true));
    }

    private function text(DOMNode $node): string
    {
        $text = '';
        foreach ($node->childNodes as $child) {
            if (! $child instanceof DOMElement || $this->excluded($child)) {
                continue;
            }
            if (in_array($child->namespaceURI, self::WORD_NAMESPACES, true)) {
                $value = match ($child->localName) {
                    't' => $child->textContent,
                    'tab' => "\t",
                    'br', 'cr' => "\n",
                    'noBreakHyphen' => "\u{2011}",
                    'softHyphen' => "\u{00AD}",
                    'instrText', 'delText', 'footnoteReference', 'endnoteReference', 'commentReference' => '',
                    'sym' => $this->fail('A font-specific symbol cannot be converted exactly. Nothing was imported.'),
                    default => null,
                };
                if ($value !== null) {
                    $text .= $value;

                    continue;
                }
            }
            $text .= $this->text($child);
        }

        return $text;
    }

    private function fail(string $message): never
    {
        throw ValidationException::withMessages(['document' => $message]);
    }
}

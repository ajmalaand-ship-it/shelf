<?php

namespace App\Support;

use DOMDocument;
use DOMNode;

final class PoetryEditorDocument
{
    public static function toEditorHtml(?string $body): string
    {
        $body ??= '';

        return implode('', array_map(
            static fn (string $paragraph): string => '<p>'.str_replace("\n", '<br>', htmlspecialchars($paragraph, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')).'</p>',
            explode("\n\n", $body),
        ));
    }

    public static function toPlainText(?string $document): string
    {
        $document ??= '';
        if (! str_contains($document, '<')) {
            return $document;
        }

        $dom = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8"><div id="poetry-editor-root">'.$document.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $dom->getElementById('poetry-editor-root');
        if (! $root) {
            return strip_tags($document);
        }

        $paragraphs = [];
        foreach ($root->childNodes as $node) {
            if ($node->nodeType === XML_TEXT_NODE && trim($node->textContent) === '') {
                continue;
            }
            $paragraphs[] = self::nodeText($node);
        }

        return implode("\n\n", $paragraphs);
    }

    private static function nodeText(DOMNode $node): string
    {
        if ($node->nodeName === 'br') {
            return "\n";
        }
        if ($node->nodeType === XML_TEXT_NODE) {
            return $node->nodeValue ?? '';
        }

        $text = '';
        foreach ($node->childNodes as $child) {
            $text .= self::nodeText($child);
        }

        return $text;
    }
}

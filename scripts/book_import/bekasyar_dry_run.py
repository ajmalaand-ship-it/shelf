#!/usr/bin/env python3
"""Read-only source analysis. Never connects to Laravel or writes book content."""
import argparse
import collections
import difflib
import hashlib
import io
import json
from pathlib import Path
import re
import xml.etree.ElementTree as ET
import zipfile

NS = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main'
W = '{' + NS + '}'
FILES = ['A.Bekasyar...1.docx', 'A.Bekasyar...2.docx', 'A.Bekasyar...3.docx', 'Nazmona.docx']
FRONT = 'Pushtai aw kitab pezhandana.docx'

def sha(data):
    return hashlib.sha256(data).hexdigest()

def text(node):
    out = []
    for e in node.iter():
        if e.tag == W+'t': out.append(e.text or '')
        elif e.tag == W+'tab': out.append('\t')
        elif e.tag in (W+'br', W+'cr'): out.append('\n')
        elif e.tag == W+'noBreakHyphen': out.append('\u2011')
        elif e.tag == W+'softHyphen': out.append('\u00ad')
    return ''.join(out)

def note_locations(paragraph):
    line = 1
    result = []
    for e in paragraph.iter():
        if e.tag in (W+'br', W+'cr'): line += 1
        elif e.tag == W+'t': line += (e.text or '').count('\n')
        elif e.tag == W+'footnoteReference': result.append({'id': e.get(W+'id'), 'logical_line': line})
    return result

def read_doc(data, filename):
    with zipfile.ZipFile(io.BytesIO(data)) as z:
        raw = z.read('word/document.xml')
        if b'<!DOCTYPE' in raw or b'<!ENTITY' in raw: raise ValueError('Unsupported XML declarations')
        root = ET.fromstring(raw)
        body = root.find(W+'body')
        if body is None: raise ValueError('No Word body')
        paragraphs = []
        tables = []
        # Main-body positions include paragraphs inside cells, once, in XML order.
        # Cell order is archival ONLY; table reading order is explicitly held.
        for index, p in enumerate(body.iter(W+'p'), 1):
            paragraphs.append({'paragraph': index, 'text': text(p),
                'footnote_references': note_locations(p),
                'styles': [s.get(W+'val') for s in p.iter(W+'pStyle')]})
        for index, table in enumerate(body.iter(W+'tbl'), 1):
            rows = []
            for row in table.findall(W+'tr'):
                rows.append([{'paragraphs': [text(p) for p in c.findall(W+'p')],
                              'properties_xml': ET.tostring(c.find(W+'tcPr'), encoding='unicode') if c.find(W+'tcPr') is not None else None}
                             for c in row.findall(W+'tc')])
            tables.append({'table': index, 'rows': rows,
                           'properties_xml': ET.tostring(table.find(W+'tblPr'), encoding='unicode') if table.find(W+'tblPr') is not None else None})
        notes = []
        if 'word/footnotes.xml' in z.namelist():
            for note in ET.fromstring(z.read('word/footnotes.xml')):
                if note.get(W+'type') is None:
                    notes.append({'id': note.get(W+'id'), 'paragraphs': [text(p) for p in note.iter(W+'p')]})
        auxiliary = {}
        for name in z.namelist():
            if re.match(r'word/(header|footer)\d*\.xml$', name):
                auxiliary[name] = [text(p) for p in ET.fromstring(z.read(name)).iter(W+'p')]
        special = collections.Counter(e.tag.rsplit('}', 1)[-1] for e in body.iter()
            if e.tag.rsplit('}', 1)[-1] in ['sym','drawing','pict','txbxContent','object','altChunk','AlternateContent','ins','del','moveFrom','moveTo','fldSimple','instrText','lastRenderedPageBreak'])
        lines = []
        for p in paragraphs:
            for i, line in enumerate(p['text'].split('\n'), 1):
                lines.append({'paragraph': p['paragraph'], 'logical_line': i, 'text': line})
        chunks, separators, ambiguous = [], [], []
        current = []
        for line in lines:
            if re.fullmatch(r'[ \t]*\*\*\*[ \t]*', line['text']):
                chunks.append(current); current = []; separators.append(line)
            else:
                current.append(line)
                if '***' in line['text']: ambiguous.append(line)
        chunks.append(current)
        poems, empty = [], []
        for index, chunk in enumerate(chunks, 1):
            if not any(x['text'].strip() for x in chunk):
                empty.append({'segment': index, 'lines': chunk})
                continue
            poem = {'file': filename, 'file_sequence': len(poems)+1, 'segment': index,
                    'title': None, 'lines': chunk, 'body': '\n'.join(x['text'] for x in chunk)}
            poem['sha256'] = sha(poem['body'].encode())
            poem['first_nonempty_line'] = next(x['text'] for x in chunk if x['text'].strip())
            poem['footnote_ids'] = [r['id'] for p in paragraphs for r in p['footnote_references'] if any(x['paragraph'] == p['paragraph'] and x['logical_line'] == r['logical_line'] for x in chunk)]
            poems.append(poem)
        # Verify every paragraph/logical line occurs once in source order; nothing trimmed.
        reconstructed = []
        for index, chunk in enumerate(chunks):
            reconstructed.extend(chunk)
            if index < len(separators): reconstructed.append(separators[index])
        assert reconstructed == lines
        all_text_nodes = ''.join(e.text or '' for e in body.iter(W+'t'))
        paragraph_text_nodes = ''.join(e.text or '' for p in body.iter(W+'p') for e in p.iter(W+'t'))
        return {'filename': filename, 'sha256': sha(data), 'paragraph_count': len(paragraphs),
                'paragraphs': paragraphs, 'tables': tables, 'footnotes': notes, 'headers_footers': auxiliary,
                'special_elements': dict(special), 'poems': poems, 'separator_count': len(separators),
                'separators': separators, 'empty_segments': empty, 'ambiguous_star_lines': ambiguous,
                'line_reconstruction_exact': reconstructed == lines,
                'text_node_accounting_exact': all_text_nodes == paragraph_text_nodes,
                'text_node_characters': len(all_text_nodes)}

def main():
    parser = argparse.ArgumentParser()
    parser.add_argument('archive', type=Path)
    parser.add_argument('snapshot', type=Path)
    parser.add_argument('output', type=Path)
    a = parser.parse_args()
    data = a.archive.read_bytes()
    snapshot = json.loads(a.snapshot.read_text())
    with zipfile.ZipFile(io.BytesIO(data)) as z:
        assert sorted(z.namelist()) == sorted(FILES+[FRONT]), 'Unexpected archive members'
        documents = [read_doc(z.read(n), n) for n in FILES]
        front = read_doc(z.read(FRONT), FRONT)
    poems = [p for d in documents for p in d['poems']]
    for i,p in enumerate(poems,1): p['sequence'] = i
    exact = collections.defaultdict(list)
    whitespace = collections.defaultdict(list)
    # Whitespace folding is ONLY a comparison key. Stored source strings never change.
    key = lambda s: re.sub(r'\s+', ' ', s).strip()
    for p in poems:
        exact[p['body']].append(p['sequence'])
        whitespace[key(p['body'])].append(p['sequence'])
    near = []
    for i, p in enumerate(poems):
        x = ''.join(p['body'].split())
        for other in poems[i+1:]:
            y = ''.join(other['body'].split())
            if min(len(x),len(y))/max(len(x),len(y)) < .75: continue
            score = difflib.SequenceMatcher(None,x,y,autojunk=False).ratio()
            if score >= .8: near.append({'sequences':[p['sequence'],other['sequence']], 'similarity':score})
    existing = []
    for old in snapshot['poems']:
        candidates = [p['sequence'] for p in poems if key(old['body']) == key(p['body'])]
        first = next((l.strip() for l in old['body'].splitlines() if l.strip()), '')
        first_candidates = [p['sequence'] for p in poems if p['first_nonempty_line'].strip() == first]
        existing.append({'id': old['id'], 'deleted_at': old['deleted_at'],
                         'whitespace_only_matches': candidates, 'first_line_candidates': first_candidates,
                         'exact_matches': [p['sequence'] for p in poems if p['body']==old['body']]})
    result = {'archive': str(a.archive), 'archive_sha256': sha(data), 'draft': snapshot['book'],
        'credits': snapshot['credits'], 'snapshot_sha256': sha(a.snapshot.read_bytes()),
        'total_poems': len(poems), 'documents': documents, 'front_matter': front,
        'near_duplicate_candidates': near,
        'exact_duplicate_groups': [v for v in exact.values() if len(v)>1],
        'whitespace_duplicate_groups': [v for v in whitespace.values() if len(v)>1],
        'existing_work_candidates': existing, 'safe_to_import': False,
        'warning': 'Review archive only. Tables retain cell structure; footnotes retain reference IDs. No literary titles inferred. No apply command.'}
    a.output.mkdir(mode=0o700, parents=True, exist_ok=True)
    (a.output/'dry-run.json').write_text(json.dumps(result,ensure_ascii=False,indent=2)+'\n')
    # Full review text includes separators/blank lines and table structure in JSON.
    review=[]
    for p in poems:
        review.append(f"=== Internal sequence {p['sequence']:03d}; {p['file']}; segment {p['segment']} ===\n"+p['body'])
    (a.output/'poems-review.txt').write_text('\n\n'.join(review)+'\n')
    print(json.dumps({k:v for k,v in result.items() if k not in ['documents','front_matter']},ensure_ascii=False,indent=2))
    for d in documents:
        print(d['filename'],len(d['poems']),'poems;', d['separator_count'],'separators;',len(d['empty_segments']),'empty segments;',len(d['tables']),'tables;',len(d['footnotes']),'footnotes')

if __name__=='__main__': main()

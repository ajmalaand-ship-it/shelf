#!/usr/bin/env python3
"""Finalize the reviewed source mapping; source ZIP and live data are read only."""
import argparse, collections, copy, hashlib, io, json, re, uuid, zipfile
from pathlib import Path
import xml.etree.ElementTree as ET
from bekasyar_dry_run import FILES, W, text, read_doc, sha

ARCHIVE_SHA = '76b49b2c0ae5d21be7b5dff760925c76df67500654a6881e243709e95de2678f'

def visual_table(table, table_number=None):
    assert table.find(W+'tblPr/'+W+'bidiVisual').get(W+'val') == 'true'
    result=[]
    for row in table.findall(W+'tr'):
        columns=[]
        for cell in row.findall(W+'tc'):
            lines=[line for p in cell.findall(W+'p') for line in text(p).split('\n')]
            if table_number == 9 and len(columns) == 2:
                # The original left cell wraps as two hemistichs aligned with
                # the two right-cell lines. Preserve its separating space too.
                original = 'د وخت په چار ناچار يم بس کړى مې تړون دى'
                assert lines == [original, '']
                lines = ['د وخت په چار ناچار يم ', 'بس کړى مې تړون دى', '']
            columns.append(lines)
        # Logical cell zero is physically rightmost because bidiVisual=true.
        # Retain every source line, including empty lines, while reading across
        # each explicit line from right to left before moving downward.
        for line in range(max(map(len,columns))):
            for column in columns:
                if line < len(column): result.append(column[line])
    return result

def prepare(archive, baseline, output):
    data=archive.read_bytes(); assert sha(data)==ARCHIVE_SHA
    prior=json.loads((output.parent/'review/dry-run.json').read_text())
    baseline_data=json.loads(baseline.read_text())
    documents=[]; table_map=[]; totals=[]; sequence=0
    with zipfile.ZipFile(io.BytesIO(data)) as z:
        for file_index,name in enumerate(FILES):
            original=z.read(name); audit=read_doc(original,name)
            root=ET.fromstring(zipfile.ZipFile(io.BytesIO(original)).read('word/document.xml'))
            body=root.find(W+'body')
            raw_ps=list(body.iter(W+'p')); number={id(p):i+1 for i,p in enumerate(raw_ps)}
            replacements={}; skip=set()
            for ti,table in enumerate(body.iter(W+'tbl'),1):
                ps=list(table.iter(W+'p')); first=number[id(ps[0])]
                lines=visual_table(table,ti)
                replacements[first]=lines
                skip.update(number[id(p)] for p in ps[1:])
                table_map.append({'file':name,'table':ti,'first_paragraph':first,'visual_lines':lines})
            # Keep the already-reviewed separator intervals exactly. Only table
            # line ordering changes inside a segment; no boundaries are inferred.
            items=[]
            reviewed=[]
            for original_poem in audit['poems']:
                if name == 'Nazmona.docx' and original_poem['file_sequence'] == 4:
                    # Direct source correction: paragraph 104 is a centered
                    # 20-point poem title, matching the other named poems.
                    heading=raw_ps[103]
                    assert text(heading)=='ژوند'
                    assert heading.find(W+'pPr/'+W+'jc').get(W+'val')=='center'
                    assert heading.find(W+'pPr/'+W+'rPr/'+W+'sz').get(W+'val')=='40'
                    split=next(i for i,l in enumerate(original_poem['lines']) if l['paragraph']==104)
                    for part,part_lines in enumerate([original_poem['lines'][:split],original_poem['lines'][split:]],1):
                        item=copy.deepcopy(original_poem);item['lines']=part_lines
                        item['first_nonempty_line']=next(l['text'] for l in part_lines if l['text'].strip())
                        item['boundary_part']=part;reviewed.append(item)
                else: reviewed.append(original_poem)
            for p in reviewed:
                sequence+=1; lines=[]; emitted=set()
                for line in p['lines']:
                    n=line['paragraph']
                    if n in skip: continue
                    if n in replacements:
                        if n not in emitted:lines.extend(replacements[n]);emitted.add(n)
                    else: lines.append(line['text'])
                # Original title-like heading is retained verbatim in the body;
                # title metadata points to the same supplied text, never invented.
                title=None if file_index<3 else p['first_nonempty_line']
                body_text='\n'.join(lines)
                before=collections.Counter(''.join(l['text'] for l in p['lines']))
                after=collections.Counter(''.join(lines))
                assert before==after, (name, sequence, 'source line loss')
                notes=[n for n in audit['footnotes'] if n['id'] in p['footnote_ids']]
                note_text='\n\n'.join('\n'.join(n['paragraphs']) for n in notes)
                # Notes are appended exactly, outside the poetry block, so existing
                # reader code displays them rather than hiding source_note metadata.
                final_body=body_text+('\n\n'+note_text if notes else '')
                items.append({'sequence':sequence,'title':title,'body':final_body,
                    'body_sha256':sha(final_body.encode()),'poetry_sha256':sha(body_text.encode()),
                    'notes':notes,'source_segment':p['segment'],'source_boundary_part':p.get('boundary_part'),
                    'first_lines':'\n'.join(lines[:2]),'word_count':len(final_body.split())})
            assert len(items)==[48,47,77,11][file_index]
            warnings=['Original table/footnote mapping retained in the approved import manifest.']
            if file_index==0: warnings.append('Editorial flag: sequence 12, footnote ID 3 is exactly "("; preserve as supplied pending later authorized correction.')
            documents.append({'filename':name,'sha256':sha(original),
                'id':str(uuid.uuid5(uuid.NAMESPACE_URL,ARCHIVE_SHA+'/'+name)),
                'items':items,'warnings':warnings})
            totals.append(len(items))
    assert sequence==183
    overlaps=[]
    old=json.loads((output.parent/'draft-snapshot.json').read_text())
    flat=[p for d in prior['documents'] for p in d['poems']]
    key=lambda s:re.sub(r'\s+',' ',s).strip()
    for p in old['poems']:
        pieces=[s for s in re.split(r'(?m)^[ \t]*\*\*\*[ \t]*$',p['body']) if s.strip()]
        matches=[]
        for piece in pieces:
            found=[s['sequence'] for s in flat if key(s['body'])==key(piece)]
            assert len(found)==1;matches.extend(found)
        overlaps.append({'bin_id':p['id'],'source_sequences':matches,'comparison':'exact characters; whitespace-only differences; separators split for comparison only'})
    manifest={'schema':1,'book_id':13,'archive_sha256':ARCHIVE_SHA,
              'baseline':baseline_data,'documents':documents,'total_poems':183,
              'tables':table_map,'bin_overlaps':overlaps,
              'boundary_evidence':{'file':'Nazmona.docx','paragraph':104,'title':'ژوند','centered':True,'font_size_half_points':40,'old_segment':176,'new_sequences':[176,177]},
              'boundary_policy':'182 separator-defined segments plus one source-evidenced boundary at Nazmona paragraph 104: centered 20-point title ژوند. No other boundary changes.',
              'footnote_policy':'Append exact footnote paragraphs, in reference order, to their own source segment; no invented labels or characters.'}
    output.mkdir(parents=True,exist_ok=True,mode=0o700)
    raw=(json.dumps(manifest,ensure_ascii=False,indent=2)+'\n').encode()
    (output/'manifest.json').write_bytes(raw)
    (output/'poems-final.txt').write_text('\n\n'.join(f"=== {p['sequence']:03} ===\n"+p['body'] for d in documents for p in d['items'])+'\n')
    print(json.dumps({'poems':sequence,'per_file':totals,'manifest_sha256':sha(raw),'overlaps':overlaps},ensure_ascii=False))

if __name__=='__main__':
    p=argparse.ArgumentParser();p.add_argument('archive',type=Path);p.add_argument('baseline',type=Path);p.add_argument('output',type=Path)
    a=p.parse_args();prepare(a.archive,a.baseline,a.output)

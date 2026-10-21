<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class BekasyarDryRunTest extends TestCase
{
    public function test_read_only_analyser_preserves_logical_lines_tables_and_note_locations(): void
    {
        $script = <<<'PY'
import importlib.util, io, zipfile
spec=importlib.util.spec_from_file_location('dryrun', 'scripts/book_import/bekasyar_dry_run.py')
m=importlib.util.module_from_spec(spec); spec.loader.exec_module(m)
def doc(body):
    data=io.BytesIO()
    with zipfile.ZipFile(data,'w') as z:
        z.writestr('word/document.xml', '<w:document xmlns:w="'+m.NS+'"><w:body>'+body+'</w:body></w:document>')
    return m.read_doc(data.getvalue(), 'synthetic.docx')
r=doc('<w:p><w:r><w:t>ځ\u0652 ي  </w:t><w:br/><w:t> *** </w:t><w:br/><w:t>ب***</w:t></w:r></w:p>')
assert len(r['poems'])==2 and r['separator_count']==1
assert r['poems'][0]['body']=='ځ\u0652 ي  '
assert r['poems'][1]['body']=='ب***' and len(r['ambiguous_star_lines'])==1
assert r['line_reconstruction_exact'] and r['text_node_accounting_exact']
r=doc('<w:p><w:r><w:t>الف</w:t><w:footnoteReference w:id="2"/><w:br/><w:t>***</w:t><w:br/><w:t>ب</w:t></w:r></w:p>')
assert r['poems'][0]['footnote_ids']==['2'] and r['poems'][1]['footnote_ids']==[]
r=doc('<w:tbl><w:tr><w:tc><w:p><w:r><w:t>ښي</w:t><w:br/><w:t>دويم</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>کيڼ</w:t></w:r></w:p></w:tc></w:tr></w:tbl>')
assert r['tables'][0]['rows'][0][0]['paragraphs']==['ښي\nدويم']
assert r['tables'][0]['rows'][0][1]['paragraphs']==['کيڼ']
assert r['text_node_accounting_exact']
r=doc('<w:p><w:r><w:t>الف</w:t><w:br/><w:t>***</w:t><w:br/><w:t>***</w:t></w:r></w:p>')
assert len(r['poems'])==1 and len(r['empty_segments'])==2
import sys
sys.path.insert(0, 'scripts/book_import')
from prepare_bekasyar import visual_table
import xml.etree.ElementTree as ET
xml='<w:tbl xmlns:w="'+m.NS+'"><w:tblPr><w:bidiVisual w:val="true"/></w:tblPr><w:tr>'
for value in ['R1\nR2', '', 'L1\nL2']:
    xml+='<w:tc><w:p><w:r>'+''.join('<w:t>'+x+'</w:t><w:br/>' for x in value.split('\n'))+'</w:r></w:p></w:tc>'
xml+='</w:tr></w:tbl>'
assert visual_table(ET.fromstring(xml))[:4]==['R1', '', 'L1', 'R2']
assert [x for x in visual_table(ET.fromstring(xml)) if x]==['R1','L1','R2','L2']
print('Logical separators, exact Unicode/whitespace, table cells, reference locations and empty segments passed.')
PY;
        $process = new Process(['python3', '-B', '-c', $script], dirname(__DIR__, 2));
        $process->run();
        $this->assertSame(0, $process->getExitCode(), $process->getOutput().$process->getErrorOutput());
    }
}

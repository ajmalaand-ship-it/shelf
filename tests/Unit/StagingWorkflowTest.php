<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class StagingWorkflowTest extends TestCase
{
    public function test_promotion_gate_rejects_missing_failed_or_different_commit(): void
    {
        $process=new Process(['python3','-B','-c', <<<'PY'
import importlib.util
spec=importlib.util.spec_from_file_location('workflow','scripts/staging/workflow.py')
w=importlib.util.module_from_spec(spec);spec.loader.exec_module(w)
assert not w.promotion_allowed({},'a','a')
for state in [dict(staging_commit='b',checks=dict(commit='a',passed=True)),dict(staging_commit='a',checks=dict(commit='b',passed=True)),dict(staging_commit='a',checks=dict(commit='a',passed=False))]:
    assert not w.promotion_allowed(state,'a','a')
state=dict(staging_commit='a',checks=dict(commit='a',passed=True))
assert not w.promotion_allowed(state,'a','b')
assert w.promotion_allowed(state,'a','a')
print('PASS')
PY], dirname(__DIR__,2));
        $process->mustRun();$this->assertSame("PASS\n",$process->getOutput());
    }
}

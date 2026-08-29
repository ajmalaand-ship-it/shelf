<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class AudioDurationProbe
{
    public function seconds(?string $audioPath): ?int
    {
        if (! $audioPath || ! Storage::disk('audio')->exists($audioPath)) {
            return null;
        }

        $process = new Process([
            '/usr/bin/ffprobe', '-v', 'error', '-show_entries', 'format=duration',
            '-of', 'default=noprint_wrappers=1:nokey=1', Storage::disk('audio')->path($audioPath),
        ]);
        $process->setTimeout(15);
        $process->run();
        $output = trim($process->getOutput());

        if (! $process->isSuccessful() || ! is_numeric($output)) {
            return null;
        }

        return max(0, (int) round((float) $output));
    }
}

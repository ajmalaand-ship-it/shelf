<?php

namespace Tests\Feature;

use App\Support\AudioDurationProbe;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AudioDurationProbeTest extends TestCase
{
    public function test_duration_is_detected_from_audio_in_private_storage(): void
    {
        Storage::fake('audio');
        Storage::disk('audio')->put('test-only.wav', $this->oneSecondWav());

        $this->assertSame(1, app(AudioDurationProbe::class)->seconds('test-only.wav'));
        $this->assertNull(app(AudioDurationProbe::class)->seconds('missing.wav'));
    }

    private function oneSecondWav(): string
    {
        $sampleRate = 8000;
        $data = str_repeat("\0", $sampleRate * 2);

        return 'RIFF'.pack('V', 36 + strlen($data)).'WAVEfmt '
            .pack('VvvVVvv', 16, 1, 1, $sampleRate, $sampleRate * 2, 2, 16)
            .'data'.pack('V', strlen($data)).$data;
    }
}

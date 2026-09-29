<?php

namespace App\Console\Commands;

use App\Models\Collection;
use App\Models\Poem;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Symfony\Component\Process\Process;

class CheckSamples extends Command
{
    protected $signature = 'shelf:check-samples {--after-reset : Verify every legacy and new sample flag is cleared}';

    protected $description = 'Read-only sample inventory and direct HTTPS checks; never print source text or signed URLs.';

    public function handle(): int
    {
        $base = rtrim(config('app.url'), '/');
        if (parse_url($base, PHP_URL_SCHEME) !== 'https') {
            $this->error('APP_URL must use HTTPS.');

            return self::FAILURE;
        }
        $counts = DB::table('poems')->selectRaw('sample_mode, count(*) as count')->groupBy('sample_mode')->pluck('count', 'sample_mode')->all();
        $legacy = DB::table('poems')->where('is_free_sample', true)->count();
        $this->line('Sample modes (including bin): '.json_encode($counts));
        $this->line('Legacy free flags still enabled: '.$legacy);
        if ($this->option('after-reset') && ($legacy !== 0 || DB::table('poems')->where('sample_mode', '!=', 'none')->exists())) {
            $this->error('FAIL sample reset is incomplete.');

            return self::FAILURE;
        }
        $targets = collect([
            Poem::where('sample_mode', 'none')->orderByDesc('is_active')->first(),
            Poem::where('sample_mode', 'none')->whereNotNull('artwork_path')->first(),
            Poem::where('sample_mode', 'none')->whereNotNull('audio_path')->first(),
        ])->filter()->unique('id');
        if ($targets->isEmpty()) {
            $this->error('FAIL no non-sample item exists for direct checks.');

            return self::FAILURE;
        }
        $publishedTarget = Poem::where('sample_mode', 'none')->where('is_active', true)
            ->whereHas('collection', fn ($query) => $query->where('status', 'published'))->first();
        if ($publishedTarget) {
            $targets->push($publishedTarget);
            $targets = $targets->unique('id');
        } else {
            $this->line('No published non-sample target: live requests prove unpublished denial; published sample-only access is covered by automated tests.');
        }
        foreach ($targets as $item) {
            $public = $item->is_active && $item->collection?->isPublished();
            $expected = $public ? 200 : 404;
            foreach ([null, 'unlock_all', 'pitswal_unlock_all_v1', 'default'] as $identity) {
                $headers = $identity === null ? [] : ['X-RC-User-Id: '.$identity, 'X-RC-Refresh: 1', 'X-Entitlement: '.$identity,
                    'X-Product-Id: '.$identity, 'X-Unlock-All: true', 'Authorization: Bearer '.$identity];
                if (! $this->probe($base.'/api/poems/'.$item->id.'?access=paid&unlock_all=1', $expected, $headers,
                    $public ? fn ($json) => ($json['data']['locked'] ?? false) && empty($json['data']['body']) && empty($json['data']['excerpt']) && empty($json['data']['artwork']['url']) && ($json['data']['audio']['locked'] ?? false) : null)) {
                    return self::FAILURE;
                }
            }
            if (! $this->probe($base.'/api/poems/'.$item->id.'/audio', $expected, [],
                $public ? fn ($json) => ($json['locked'] ?? false) && empty($json['url']) && empty($json['excerpt']) : null)) {
                return self::FAILURE;
            }
            foreach (['poems.audio.stream', 'poems.artwork.stream'] as $route) {
                if (! $this->probe(route($route, $item), 403)) {
                    return self::FAILURE;
                }
                // Reproduce an old valid paid signature, without printing its value.
                if (! $this->probe(URL::temporarySignedRoute($route, now()->addMinutes(2), ['poem' => $item, 'access' => 'paid']), 404)) {
                    return self::FAILURE;
                }
            }
        }
        foreach (['unlock_all', 'pitswal_unlock_all_v1', 'default'] as $path) {
            if (! $this->probe($base.'/api/'.$path, 404)) {
                return self::FAILURE;
            }
        }
        $expectedIds = Collection::where('status', 'published')->orderBy('sort_order')->orderBy('id')->limit(50)->pluck('id')->all();
        if (! $this->probe($base.'/api/collections', 200, [], fn ($json) => array_column($json['data'] ?? [], 'id') === $expectedIds)) {
            return self::FAILURE;
        }
        $this->info('PASS all sample-access HTTPS checks. No data changed.');

        return self::SUCCESS;
    }

    private function probe(string $url, int $expected, array $headers = [], ?callable $validate = null): bool
    {
        $args = ['curl', '--silent', '--show-error', '--max-time', '20', '--header', 'Accept: application/json', '--write-out', "\n%{http_code}"];
        foreach ($headers as $header) {
            $args[] = '--header';
            $args[] = $header;
        }
        $args[] = $url;
        $curl = new Process($args);
        $curl->run();
        $output = $curl->getOutput();
        $position = strrpos($output, "\n");
        $actual = $position === false ? 0 : (int) substr($output, $position + 1);
        $pass = $curl->isSuccessful() && $actual === $expected;
        if ($pass && $validate) {
            $json = json_decode(substr($output, 0, $position), true);
            $pass = is_array($json) && $validate($json);
        }
        // Bodies, bearer values and signed query strings remain in memory only.
        $this->line(($pass ? 'PASS' : 'FAIL').' GET '.parse_url($url, PHP_URL_PATH)
            .' headers='.($headers ? 'legacy-forged' : 'none').' expected='.$expected.' actual='.$actual);

        return $pass;
    }
}

<?php

// Read-only inventory and HTTPS probes. Never print source text, credentials or URLs with signatures.
use App\Models\AppSetting;
use App\Models\Collection;
use App\Models\User;
use App\Services\OwnerPreviewTokenService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
$preflight = ($argv[1] ?? '') === '--preflight';
$ids = [3, 4, 5, 6, 7, 8];
$db = DB::connection();

function check(bool $condition, string $description): void
{
    if (! $condition) {
        throw new RuntimeException($description);
    }
    echo 'PASS '.$description.PHP_EOL;
}

function probe(string $path, int $status = 200, ?string $token = null, array $headers = []): array
{
    $request = Http::acceptJson()->timeout(20)->withOptions(['allow_redirects' => false])->withHeaders($headers);
    if ($token !== null) {
        $request = $request->withToken($token);
    }
    $response = $request->get(rtrim(config('app.url'), '/').$path);
    check($response->status() === $status, 'HTTPS '.strtok($path, '?').' status '.$status);

    return $response->json() ?? [];
}

try {
    $db->statement('START TRANSACTION READ ONLY');
    check(parse_url(config('app.url'), PHP_URL_HOST) === 'shelf.services'
        && parse_url(config('app.url'), PHP_URL_SCHEME) === 'https', 'Shelf HTTPS server');
    check(! config('purchases.enabled') && ! config('play_sync.enabled'), 'Purchases and Play sync remain disabled');
    check(User::where('is_owner', true)->count() === 1, 'Exactly one owner');
    $books = Collection::whereIn('id', $ids)->orderBy('id')->get();
    check($books->pluck('id')->all() === $ids, 'All six books exist outside the bin');
    $snapshot = $preflight ? null : json_decode(AppSetting::where('key', 'shelf_internal_test_catalogue_20260930')
        ->firstOrFail()->value, true, 512, JSON_THROW_ON_ERROR);
    foreach ($books as $book) {
        $items = $book->poems()->orderBy('id')->get();
        check($book->language === 'ps' && $book->credits()->where('role', 'author')->exists()
            && $book->cover_image && Collection::safeCoverPath($book->cover_image)
            && Storage::disk('covers')->exists($book->cover_image)
            && $book->price_usd === '2.99' && $book->product_id === 'shelf_book_'.$book->id,
            'Book '.$book->id.' author/language/cover/USD 2.99/product requirements');
        check($items->count() >= 3 && ! $items->take(2)->contains(fn ($item) => blank($item->body)),
            'Book '.$book->id.' has two non-empty first items and a locked remainder');
        if ($preflight) {
            check($book->status === 'draft' && ! $items->contains(fn ($item) => $item->sample_mode !== 'none'),
                'Book '.$book->id.' expected Draft/no-sample state');
            echo json_encode(['book_id' => $book->id, 'items' => $items->count(),
                'already_visible' => $items->where('is_active', true)->count(),
                'temporary_sample_ids' => $items->take(2)->pluck('id')->all()], JSON_THROW_ON_ERROR).PHP_EOL;
            continue;
        }
        $saved = collect($snapshot['books'])->firstWhere('id', $book->id);
        $hash = hash('sha256', json_encode(DB::table('poems')->where('collection_id', $book->id)->orderBy('id')
            ->get(['id', 'title', 'body', 'excerpt', 'original_author', 'translator', 'source_date_place',
                'source_note', 'layout_mode', 'sort_order', 'deleted_at'])->all(), JSON_THROW_ON_ERROR));
        check($hash === $saved['source_sha256'], 'Book '.$book->id.' source and order unchanged');
        $book->assertPublishable();
        check($book->isPublished() && $items->every(fn ($item) => $item->is_active)
            && $items->where('sample_mode', 'full')->pluck('id')->all() === $saved['sample_ids']
            && $saved['sample_ids'] === $items->take(2)->pluck('id')->all(),
            'Book '.$book->id.' published/all visible/exactly first two full samples');
        check($book->status_changed_by === $snapshot['owner_id']
            && DB::table('book_status_changes')->where('id', $saved['status_change_id'])
                ->where('changed_by', $snapshot['owner_id'])->where('to_status', 'published')->exists(),
            'Book '.$book->id.' owner/time/status history recorded');
        foreach ($items->take(2) as $sample) {
            $json = probe('/api/poems/'.$sample->id);
            check(($json['data']['body'] ?? null) === $sample->body && ($json['data']['locked'] ?? true) === false,
                'Sample '.$sample->id.' returns exact full source');
        }
        $locked = $items->firstWhere('sample_mode', 'none');
        $json = probe('/api/poems/'.$locked->id);
        check(($json['data']['locked'] ?? false) === true && ($json['data']['body'] ?? null) === null
            && empty($json['data']['excerpt']) && empty($json['data']['artwork']['url'])
            && ($json['data']['audio']['locked'] ?? false), 'Non-sample '.$locked->id.' text/media locked');
        probe('/media/covers/'.$book->id);
        $listedIds = [];
        $lastPage = 1;
        for ($page = 1; $page <= $lastPage; $page++) {
            $json = probe('/api/collections/'.rawurlencode($book->slug).'/poems?page='.$page);
            $lastPage = (int) ($json['meta']['last_page'] ?? 1);
            check($lastPage >= 1 && $lastPage <= 10, 'Book '.$book->id.' valid content pagination');
            $safe = true;
            foreach ($json['data'] ?? [] as $summary) {
                $listedIds[] = $summary['id'];
                $safe = $safe && ! array_key_exists('body', $summary)
                    && (($summary['sample_mode'] ?? 'none') !== 'none' || empty($summary['excerpt']));
            }
            check($safe, 'Book '.$book->id.' page '.$page.' summaries omit bodies and non-sample excerpts');
        }
        sort($listedIds);
        $expectedIds = $items->pluck('id')->sort()->values()->all();
        check($listedIds === $expectedIds, 'Book '.$book->id.' all visible items listed across pages');
        echo json_encode(['book_id' => $book->id, 'visible_items' => $items->count(),
            'temporary_sample_ids' => $saved['sample_ids'], 'locked_item_checked' => $locked->id], JSON_THROW_ON_ERROR).PHP_EOL;
    }
    if (! $preflight) {
        $catalogue = probe('/api/collections');
        $publicIds = array_column($catalogue['data'] ?? [], 'id');
        sort($publicIds);
        check($publicIds === $ids && ($catalogue['meta']['total'] ?? null) === 6, 'Public catalogue lists exactly six books');
        $purchaseConfig = probe('/api/purchases/config');
        check(($purchaseConfig['enabled'] ?? true) === false, 'Public purchase configuration disabled');
        probe('/api/owner-preview/collections', 401);
        // Signing is stateless; the token stays only in memory and is never displayed.
        $token = app(OwnerPreviewTokenService::class)->issue(1)['token'];
        $preview = probe('/api/owner-preview/collections', 200, $token);
        check(count($preview['data'] ?? []) >= 6, 'Protected owner preview still lists books');
        $locked = $books->first()->poems()->where('sample_mode', 'none')->firstOrFail();
        $preview = probe('/api/owner-preview/poems/'.$locked->id, 200, $token);
        check(($preview['data']['body'] ?? null) === $locked->body, 'Protected owner preview still reads non-sample source');
        unset($token);
        $forged = probe('/api/poems/'.$locked->id.'?access=paid&unlock_all=1', 200, null,
            ['X-RC-User-Id' => 'unlock_all', 'X-Entitlement' => 'unlock_all', 'X-Unlock-All' => 'true']);
        check(($forged['data']['locked'] ?? false) === true && empty($forged['data']['body']), 'Legacy forged unlock cannot reveal text');
    }
    $db->statement('ROLLBACK');
    echo $preflight ? "PASS read-only preflight complete.\n" : "PASS read-only live verification complete.\n";
} catch (Throwable $error) {
    try {
        $db->statement('ROLLBACK');
    } catch (Throwable) {
    }
    // Only our fixed validation labels may be printed; HTTP/database exceptions can contain secrets.
    fwrite(STDERR, 'FAIL '.($error instanceof RuntimeException && get_class($error) === RuntimeException::class
        ? $error->getMessage() : 'Verification failed ('.get_class($error).'); inspect privately.').PHP_EOL);
    exit(1);
}

<?php

use App\Models\AppSetting;
use App\Models\Collection;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const IDS = [3, 4, 5, 6, 7, 8];

    private const SNAPSHOT = 'shelf_internal_test_catalogue_20260930';

    public function up(): void
    {
        // Empty fresh installations have no legacy books to publish.
        if (! Collection::withTrashed()->whereIn('id', self::IDS)->exists()) {
            return;
        }
        $this->assertPaymentsDisabled();
        $this->asOwner(function (User $owner): void {
            DB::transaction(function () use ($owner): void {
                if (AppSetting::where('key', self::SNAPSHOT)->exists()) {
                    throw new RuntimeException('Internal-test publication snapshot already exists.');
                }
                $books = Collection::whereIn('id', self::IDS)->orderBy('id')->lockForUpdate()->get();
                if ($books->pluck('id')->all() !== self::IDS) {
                    throw new RuntimeException('Expected all six non-deleted books (IDs 3–8).');
                }
                $snapshot = ['owner_id' => $owner->id, 'actor' => 'Ajmal Aand: approval 30 September 2026',
                    'changed_at' => now()->toIso8601String(), 'books' => []];
                foreach ($books as $book) {
                    if ($book->status !== 'draft' || $book->price_usd !== '2.99'
                        || $book->product_id !== 'shelf_book_'.$book->id) {
                        throw new RuntimeException('Book '.$book->id.' has unexpected status, price or product ID.');
                    }
                    $items = $book->poems()->orderBy('id')->lockForUpdate()->get()
                        ->sortBy(fn ($item) => sprintf('%010d-%010d', $item->sort_order, $item->id))->values();
                    if ($items->count() < 2 || $items->take(2)->contains(fn ($item) => blank($item->body))) {
                        throw new RuntimeException('Book '.$book->id.' needs two non-empty items for temporary samples.');
                    }
                    // Do not silently replace samples the owner has selected since preflight.
                    if ($items->contains(fn ($item) => $item->sample_mode !== 'none')) {
                        throw new RuntimeException('Book '.$book->id.' already has sample choices; review before rollout.');
                    }
                    $before = $items->map(fn ($item) => [$item->id, $item->is_active,
                        $item->sample_mode, $item->sample_unit, $item->sample_count])->all();
                    $sourceHash = $this->sourceHash($book->id);
                    foreach ($items as $position => $item) {
                        $values = ['is_active' => true];
                        if ($position < 2) {
                            $values += ['sample_mode' => 'full', 'sample_unit' => null, 'sample_count' => null];
                        }
                        $item->fill($values);
                        if ($item->isDirty()) {
                            $item->save();
                        }
                    }
                    // Same guard, actor/time and history as the existing Filament Publish action.
                    $book->changeStatus('published');
                    if ($this->sourceHash($book->id) !== $sourceHash) {
                        throw new RuntimeException('Source changed unexpectedly; publication transaction aborted.');
                    }
                    $snapshot['books'][] = ['id' => $book->id, 'status' => 'draft', 'items' => $before,
                        'sample_ids' => $items->take(2)->pluck('id')->all(), 'source_sha256' => $sourceHash,
                        'applied_state_sha256' => $this->stateHash($book->id),
                        'status_change_id' => DB::table('book_status_changes')->where('collection_id', $book->id)->max('id')];
                }
                AppSetting::create(['key' => self::SNAPSHOT, 'value' => json_encode($snapshot, JSON_THROW_ON_ERROR)]);
            });
        });
    }

    public function down(): void
    {
        if (! AppSetting::where('key', self::SNAPSHOT)->exists()) {
            return;
        }
        $this->assertPaymentsDisabled();
        $this->asOwner(function (): void {
            DB::transaction(function (): void {
                $setting = AppSetting::where('key', self::SNAPSHOT)->lockForUpdate()->firstOrFail();
                $snapshot = json_decode($setting->value, true, 512, JSON_THROW_ON_ERROR);
                // Validate every book before any rollback writes; never overwrite newer owner work.
                foreach ($snapshot['books'] as $saved) {
                    $book = Collection::whereKey($saved['id'])->lockForUpdate()->firstOrFail();
                    $book->poems()->lockForUpdate()->get();
                    if ($book->status !== 'published' || $this->stateHash($book->id) !== $saved['applied_state_sha256']
                        || $this->sourceHash($book->id) !== $saved['source_sha256']
                        || DB::table('book_status_changes')->where('collection_id', $book->id)->max('id') !== $saved['status_change_id']) {
                        throw new RuntimeException('Book '.$book->id.' changed since publication; refusing rollback over owner work.');
                    }
                }
                foreach ($snapshot['books'] as $saved) {
                    $book = Collection::findOrFail($saved['id']);
                    $book->changeStatus($saved['status']);
                    foreach ($saved['items'] as [$id, $active, $mode, $unit, $count]) {
                        $item = $book->poems()->findOrFail($id);
                        $item->fill(['is_active' => $active, 'sample_mode' => $mode,
                            'sample_unit' => $unit, 'sample_count' => $count]);
                        if ($item->isDirty()) {
                            $item->save();
                        }
                    }
                }
                // Status history remains; rollback adds new entries rather than deleting history.
                $setting->delete();
            });
        });
    }

    private function assertPaymentsDisabled(): void
    {
        if (config('purchases.enabled') || config('play_sync.enabled')) {
            throw new RuntimeException('Internal-test publication requires purchases and Play sync disabled.');
        }
    }

    private function asOwner(callable $action): void
    {
        $owners = User::where('is_owner', true)->get();
        if ($owners->count() !== 1) {
            throw new RuntimeException('Expected exactly one owner to record publication actor.');
        }
        $guard = auth()->guard();
        $previous = $guard->user();
        $guard->setUser($owners->first());
        try {
            $action($owners->first());
        } finally {
            $previous ? $guard->setUser($previous) : $guard->forgetUser();
        }
    }

    private function sourceHash(int $bookId): string
    {
        return hash('sha256', json_encode(DB::table('poems')->where('collection_id', $bookId)->orderBy('id')
            ->get(['id', 'title', 'body', 'excerpt', 'original_author', 'translator', 'source_date_place',
                'source_note', 'layout_mode', 'sort_order', 'deleted_at'])->all(), JSON_THROW_ON_ERROR));
    }

    private function stateHash(int $bookId): string
    {
        return hash('sha256', json_encode([
            DB::table('collections')->where('id', $bookId)->first(),
            DB::table('poems')->where('collection_id', $bookId)->orderBy('id')->get()->all(),
        ], JSON_THROW_ON_ERROR));
    }
};

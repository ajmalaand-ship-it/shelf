<?php

use Brick\Math\BigDecimal;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const REASON = 'Step 4 Task 2b: owner-approved USD 2.99 test price';

    public function up(): void
    {
        DB::transaction(function (): void {
            $ownerId = DB::table('users')->where('is_owner', true)->orderBy('id')->value('id');
            foreach (DB::table('collections')->whereIn('id', [3, 4, 5, 6, 7, 8])->orderBy('id')->get() as $book) {
                DB::table('book_price_changes')->insert(['collection_id' => $book->id,
                    'old_price_usd' => $book->price_usd, 'new_price_usd' => '2.99',
                    'changed_by' => $ownerId, 'actor' => 'Ajmal Aand (approval 29 September 2026; migration)',
                    'reason' => self::REASON, 'created_at' => now()]);
                DB::table('collections')->where('id', $book->id)->update([
                    'price_usd' => '2.99', 'updated_by' => $ownerId, 'updated_at' => now()]);
                DB::table('play_product_syncs')->insert(['collection_id' => $book->id, 'revision' => 1,
                    'status' => 'pending', 'message' => 'Waiting for Google Play setup.',
                    'created_at' => now(), 'updated_at' => now()]);
            }
        });
    }

    public function down(): void
    {
        DB::transaction(function (): void {
            foreach (DB::table('book_price_changes')->where('reason', self::REASON)->get() as $change) {
                $book = DB::table('collections')->where('id', $change->collection_id)->first();
                if (! $book) {
                    continue;
                }
                if ($book->price_usd === null || ! BigDecimal::of((string) $book->price_usd)->isEqualTo('2.99')
                    || DB::table('book_price_changes')->where('collection_id', $book->id)->where('id', '>', $change->id)
                        ->where('reason', 'Admin price change')->exists()) {
                    throw new RuntimeException('Price changed since rollout; refusing to overwrite owner work.');
                }
                DB::table('collections')->where('id', $book->id)->update(['price_usd' => $change->old_price_usd]);
                DB::table('book_price_changes')->insert(['collection_id' => $book->id,
                    'old_price_usd' => '2.99', 'new_price_usd' => $change->old_price_usd,
                    'changed_by' => null, 'actor' => 'Migration rollback', 'reason' => 'Reverse '.self::REASON, 'created_at' => now()]);
                DB::table('play_product_syncs')->where('collection_id', $book->id)->update([
                    'revision' => DB::raw('revision + 1'), 'status' => 'pending', 'message' => 'Rollback awaiting Google Play sync.',
                    'next_attempt_at' => null, 'queued_at' => null, 'updated_at' => now()]);
            }
        });
    }
};

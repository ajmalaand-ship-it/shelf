<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const KEY = 'owner_d10_20261001';

    public function up(): void
    {
        if (! DB::table('collections')->whereIn('id', range(3, 8))->exists()) { return; }
        DB::transaction(function (): void {
            $owners = DB::table('users')->where('is_owner', true)->get();
            $authors = DB::table('authors')->where('name', 'اجمل اند')->get();
            if ($owners->count() !== 1 || $authors->count() !== 1
                || DB::table('collections')->whereIn('id', range(3, 8))->count() !== 6) {
                throw new RuntimeException('Expected one owner, Ajmal author record and all six books.');
            }
            $ids = [];
            foreach (range(3, 8) as $id) {
                $ids[] = DB::table('author_share_agreements')->insertGetId([
                    'collection_id' => $id, 'contributors' => json_encode([['author_id' => $authors[0]->id, 'percentage' => 100]], JSON_UNESCAPED_UNICODE),
                    'basis' => 'net', 'deductions' => 'Store fees and taxes already withheld before the net amount is received; no further deductions.',
                    'sharing_terms' => $id === 6
                        ? 'Owner decision 1 October 2026: 100% to اجمل اند as translator/publisher. Author پروین پژواک permits the owner to keep all income; written permission retained by the owner.'
                        : 'Owner decision 1 October 2026: 100% of the net amount received to اجمل اند.',
                    'starts_at' => '2026-10-01 00:00:00', 'created_by' => $owners[0]->id, 'created_at' => now(),
                ]);
            }
            DB::table('app_settings')->insert(['key' => self::KEY, 'value' => json_encode([
                'agreement_ids' => $ids, 'owner_id' => $owners[0]->id, 'recorded_at' => now()->toIso8601String(),
            ]), 'created_at' => now(), 'updated_at' => now()]);
        });
    }

    public function down(): void
    {
        DB::transaction(function (): void {
            $setting = DB::table('app_settings')->where('key', self::KEY)->first();
            if (! $setting) { return; }
            $ids = json_decode($setting->value, true, 512, JSON_THROW_ON_ERROR)['agreement_ids'];
            foreach (DB::table('sales_ledger')->whereNotNull('agreement_snapshot')->cursor() as $entry) {
                if (in_array(json_decode($entry->agreement_snapshot, true)['id'] ?? null, $ids, true)) {
                    throw new RuntimeException('These agreements have financial history; retain them and add a correction.');
                }
            }
            DB::table('author_share_agreements')->whereIn('id', $ids)->delete();
            DB::table('app_settings')->where('key', self::KEY)->delete();
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const SNAPSHOT_KEY = '_migration_20260927_shelf_branding_previous';

    private const BRANDING = [
        'public_app_name' => 'Shelf',
        'public_slogan' => 'کتاب مو ژوند بدلوي',
    ];

    public function up(): void
    {
        DB::transaction(function () {
            if (DB::table('app_settings')->where('key', self::SNAPSHOT_KEY)->exists()) {
                throw new RuntimeException('Branding rollback snapshot already exists.');
            }

            $previous = [];
            foreach (self::BRANDING as $key => $value) {
                $row = DB::table('app_settings')->where('key', $key)
                    ->first(['value', 'created_at', 'updated_at']);
                $previous[$key] = $row === null ? null : (array) $row;
            }
            // Persist the actual previous values across separate migrate/rollback runs.
            DB::table('app_settings')->insert([
                'key' => self::SNAPSHOT_KEY,
                'value' => json_encode($previous, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach (self::BRANDING as $key => $value) {
                DB::table('app_settings')->upsert([
                    ['key' => $key, 'value' => $value, 'created_at' => now(), 'updated_at' => now()],
                ], ['key'], ['value', 'updated_at']);
            }
        });
    }

    public function down(): void
    {
        DB::transaction(function () {
            $snapshot = DB::table('app_settings')->where('key', self::SNAPSHOT_KEY)->value('value');
            if ($snapshot === null) {
                throw new RuntimeException('Branding rollback snapshot is missing; refusing to guess.');
            }
            $previous = json_decode($snapshot, true, flags: JSON_THROW_ON_ERROR);
            foreach (self::BRANDING as $key => $value) {
                if (! is_array($previous) || ! array_key_exists($key, $previous)) {
                    throw new RuntimeException('Branding rollback snapshot is incomplete.');
                }
                $row = $previous[$key];
                if ($row === null) {
                    DB::table('app_settings')->where('key', $key)->delete();
                } else {
                    if (! is_array($row) || ! isset($row['value'])
                        || ! array_key_exists('created_at', $row) || ! array_key_exists('updated_at', $row)) {
                        throw new RuntimeException('Branding rollback snapshot is invalid.');
                    }
                    DB::table('app_settings')->updateOrInsert(['key' => $key], [
                        'value' => $row['value'],
                        'created_at' => $row['created_at'],
                        'updated_at' => $row['updated_at'],
                    ]);
                }
            }
            DB::table('app_settings')->where('key', self::SNAPSHOT_KEY)->delete();
        });
    }
};

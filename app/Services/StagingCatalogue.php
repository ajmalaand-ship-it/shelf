<?php

namespace App\Services;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;

class StagingCatalogue
{
    public const TABLES = ['users', 'authors', 'categories', 'collections', 'collection_author', 'category_collection', 'poems', 'app_settings'];
    public const SETTINGS = ['public_app_name', 'public_slogan', 'content_version', 'min_app_version', 'languages'];

    public function capture(ConnectionInterface $source, bool $allowEmpty = false): array
    {
        $data = [];
        foreach (self::TABLES as $table) {
            $query = $source->table($table);
            if ($table === 'users') { $query->where('is_owner', true); }
            if ($table === 'app_settings') { $query->whereIn('key', self::SETTINGS); }
            $data[$table] = $query->get()->map(fn ($row) => (array) $row)->all();
        }
        if (count($data['users']) !== 1 && ! ($allowEmpty && $data['users'] === [] && $data['collections'] === [])) { throw new \RuntimeException('Expected exactly one owner account.'); }
        foreach ($data['users'] as &$owner) {
            $owner['remember_token'] = null;
            $owner['app_authentication_secret'] = null;
            $owner['app_authentication_recovery_codes'] = null;
        }
        return $data;
    }

    public function apply(array $data): void
    {
        if (! \App\Support\Staging::active() || (! app()->runningUnitTests()
            && DB::connection()->getDatabaseName() !== 'shelf_staging')) {
            throw new \RuntimeException('Catalogue refresh is allowed only on the separate staging database.');
        }
        $empty = $data['users'] === [] && $data['collections'] === [];
        if (array_keys($data) !== self::TABLES || (! $empty && (count($data['users']) !== 1 || ! $data['users'][0]['is_owner']))) {
            throw new \RuntimeException('Unexpected catalogue/owner payload.');
        }
        foreach ($data['app_settings'] as $row) {
            if (! in_array($row['key'], self::SETTINGS, true)) { throw new \RuntimeException('Non-catalogue setting refused.'); }
        }
        // Existing test money/access history must never be destroyed by a refresh.
        foreach (['purchases', 'purchase_events', 'sales_ledger', 'book_entitlements', 'purchase_consents'] as $table) {
            if (DB::table($table)->exists()) { throw new \RuntimeException('Staging test purchase history exists; refresh requires a preservation plan.'); }
        }
        DB::transaction(function () use ($data): void {
            // Keep agreement versions; refuse if a book being removed has one.
            $ids = array_column($data['collections'], 'id');
            if (DB::table('author_share_agreements')->whereNotIn('collection_id', $ids)->exists()) {
                throw new \RuntimeException('Refresh would remove a book with an agreement.');
            }
            foreach (['category_collection', 'collection_author', 'poems'] as $table) { DB::table($table)->delete(); }
            foreach (['users', 'authors', 'categories', 'collections'] as $table) {
                foreach ($data[$table] as $row) { DB::table($table)->updateOrInsert(['id' => $row['id']], $row); }
            }
            DB::table('collections')->whereNotIn('id', $ids)->delete();
            foreach (['users', 'authors', 'categories'] as $table) {
                DB::table($table)->whereNotIn('id', array_column($data[$table], 'id'))->delete();
            }
            foreach (['collection_author', 'category_collection', 'poems'] as $table) {
                foreach (array_chunk($data[$table], 50) as $rows) { DB::table($table)->insert($rows); }
            }
            DB::table('app_settings')->whereIn('key', self::SETTINGS)->delete();
            foreach ($data['app_settings'] as $row) {
                unset($row['id']);
                DB::table('app_settings')->insert($row);
            }
        });
    }
}

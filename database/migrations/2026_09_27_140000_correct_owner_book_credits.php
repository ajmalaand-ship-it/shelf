<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const SNAPSHOT = '_migration_20260927_credit_correction';

    private const BOOKS = [3, 4, 5, 6, 7, 8];

    public function up(): void
    {
        DB::transaction(function (): void {
            if (DB::table('app_settings')->where('key', self::SNAPSHOT)->exists()) {
                throw new RuntimeException('Credit correction snapshot already exists.');
            }
            $snapshot = ['languages' => [], 'credits' => [], 'poems' => []];
            // Fresh installations have no legacy catalogue to correct.
            if (DB::table('collections')->exists()) {
                $books = DB::table('collections')->orderBy('id')->get();
                if ($books->pluck('id')->all() !== self::BOOKS) {
                    throw new RuntimeException('Expected exactly books 3, 4, 5, 6, 7, 8; no changes made.');
                }
                $authors = DB::table('authors')->get();
                $ajmal = $authors->whereStrict('name', 'اجمل اند');
                $parwin = $authors->whereStrict('name', 'پروین پژواک');
                if ($ajmal->count() !== 1 || $parwin->count() !== 1) {
                    throw new RuntimeException('Expected one exact author record for each approved name.');
                }
                $poems = DB::table('poems')->where('collection_id', 4)->orderBy('id')->get()
                    ->whereStrict('original_author', 'پروین پژواک');
                if ($poems->count() !== 4) {
                    throw new RuntimeException('Expected four matching poems in book 4; no changes made.');
                }
                $snapshot['languages'] = $books->map(fn ($book) => [
                    'id' => $book->id, 'language' => $book->language,
                ])->all();
                $snapshot['credits'] = DB::table('collection_author')->whereIn('collection_id', self::BOOKS)
                    ->orderBy('id')->get()->map(fn ($row) => (array) $row)->all();
                $snapshot['poems'] = $poems->map(fn ($poem) => [
                    'id' => $poem->id, 'work_type' => $poem->work_type,
                    'original_author' => $poem->original_author, 'translator' => $poem->translator,
                ])->values()->all();
                DB::table('collection_author')->whereIn('collection_id', self::BOOKS)->delete();
                foreach (self::BOOKS as $id) {
                    $credits = $id === 6
                        ? [[$parwin->first()->id, 'author'], [$ajmal->first()->id, 'translator']]
                        : [[$ajmal->first()->id, 'author']];
                    foreach ($credits as $position => [$authorId, $role]) {
                        DB::table('collection_author')->insert([
                            'collection_id' => $id, 'author_id' => $authorId, 'role' => $role,
                            'position' => $position + 1, 'created_at' => now(), 'updated_at' => now(),
                        ]);
                    }
                }
                DB::table('collections')->whereIn('id', self::BOOKS)->update(['language' => 'ps']);
                DB::table('poems')->whereIn('id', $poems->pluck('id'))->update([
                    'work_type' => 'ORIGINAL', 'original_author' => 'اجمل اند', 'translator' => null,
                ]);
            }
            DB::table('app_settings')->insert([
                'key' => self::SNAPSHOT, 'value' => json_encode($snapshot, JSON_THROW_ON_ERROR),
                'created_at' => now(), 'updated_at' => now(),
            ]);
            DB::table('app_settings')->where('key', 'content_version')->increment('value');
        });
    }

    public function down(): void
    {
        DB::transaction(function (): void {
            $value = DB::table('app_settings')->where('key', self::SNAPSHOT)->value('value');
            if ($value === null) {
                throw new RuntimeException('Credit correction snapshot missing; refusing to guess.');
            }
            $snapshot = json_decode($value, true, flags: JSON_THROW_ON_ERROR);
            $ids = array_column($snapshot['languages'], 'id');
            DB::table('collection_author')->whereIn('collection_id', $ids)->delete();
            if ($snapshot['credits']) {
                DB::table('collection_author')->insert($snapshot['credits']);
            }
            foreach ($snapshot['languages'] as $book) {
                DB::table('collections')->where('id', $book['id'])->update(['language' => $book['language']]);
            }
            foreach ($snapshot['poems'] as $poem) {
                $id = $poem['id'];
                unset($poem['id']);
                DB::table('poems')->where('id', $id)->update($poem);
            }
            DB::table('app_settings')->where('key', self::SNAPSHOT)->delete();
            // Cache versions must remain monotonic, including on rollback.
            DB::table('app_settings')->where('key', 'content_version')->increment('value');
        });
    }
};

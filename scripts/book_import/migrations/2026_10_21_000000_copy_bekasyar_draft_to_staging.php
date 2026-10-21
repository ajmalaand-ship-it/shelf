<?php

use App\Models\{Author, BookCredit, Collection, Poem};
use App\Support\Staging;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Add only the absent staging Draft; never refresh or replace another book. */
return new class extends Migration
{
    private function fixture(): array
    {
        if (! Staging::active() || DB::connection()->getDatabaseName() !== 'shelf_staging') {
            throw new RuntimeException('Draft fixture is restricted to the separate staging database.');
        }
        return json_decode(file_get_contents('/home/shelf/tmp/bekasyar-import-20261020/staging-fixture.json'), true, flags: JSON_THROW_ON_ERROR);
    }

    public function up(): void
    {
        $d = $this->fixture();
        DB::transaction(function () use ($d) {
            if (Collection::withTrashed()->whereKey(13)->exists() || Author::whereKey(3)->exists()
                || BookCredit::whereKey(18)->exists() || Poem::withTrashed()->whereIn('id', range(350,355))->exists()) {
                throw new RuntimeException('Staging fixture ID already exists; nothing replaced.');
            }
            Model::withoutEvents(function () use ($d) {
                foreach ([[Author::class, $d['author']], [Collection::class, $d['baseline']['book']]] as [$class,$attributes]) {
                    $record = new $class; $record->timestamps = false;
                    $record->forceFill($attributes)->save();
                }
                foreach ($d['baseline']['credits'] as $attributes) {
                    $record = new BookCredit; $record->timestamps = false;
                    $record->forceFill($attributes)->save();
                }
                Collection::findOrFail(13)->categories()->attach($d['baseline']['categories']);
                foreach ($d['baseline']['poems'] as $attributes) {
                    $record = new Poem; $record->timestamps = false;
                    $record->forceFill($attributes)->save();
                }
            });
        });
    }

    public function down(): void
    {
        $d = $this->fixture();
        DB::transaction(function () use ($d) {
            $book = Collection::withTrashed()->findOrFail(13);
            if ($book->getAttributes() != $d['baseline']['book']
                || $book->poems()->withTrashed()->orderBy('id')->get()->map->getAttributes()->all() != $d['baseline']['poems']
                || Author::findOrFail(3)->getAttributes() != $d['author']
                || Author::findOrFail(3)->credits()->where('collection_id','!=',13)->exists()) {
                throw new RuntimeException('Staging fixture changed or imported content exists; refusing removal.');
            }
            Model::withoutEvents(function () use ($book) {
                foreach ($book->poems()->withTrashed()->get() as $p) { $p->forceDelete(); }
                $book->categories()->detach();
                foreach ($book->credits as $credit) { $credit->delete(); }
                $book->forceDelete();
                Author::findOrFail(3)->delete();
            });
        });
    }
};

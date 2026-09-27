<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    private const SNAPSHOT = '_migration_20260927_book_credits';

    private function canonical(?string $name): ?string
    {
        $name = trim($name ?? '');
        if ($name === '' || $name === 'نوم نه دی ښودل شوی') {
            return null;
        }

        return $name === 'Ajmal Aand' ? 'اجمل اند' : $name;
    }

    public function up(): void
    {
        DB::transaction(function (): void {
            if (DB::table('app_settings')->where('key', self::SNAPSHOT)->exists()) {
                throw new RuntimeException('Credit conversion snapshot already exists.');
            }
            $snapshot = ['authors' => [], 'credits' => [], 'languages' => []];
            // Compare exact strings in PHP, not the database's case-insensitive collation.
            $authors = [];
            foreach (DB::table('authors')->orderBy('id')->get() as $author) {
                $authors[$author->name] ??= $author->id;
            }
            $resolve = function (?string $raw) use (&$authors, &$snapshot): ?int {
                $name = $this->canonical($raw);
                if ($name === null) {
                    return null;
                }
                if (! isset($authors[$name])) {
                    $authors[$name] = DB::table('authors')->insertGetId([
                        'slug' => 'author-'.strtolower((string) Str::ulid()),
                        'name' => $name,
                        'name_latin' => $name === 'اجمل اند' ? 'Ajmal Aand' : null,
                        'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
                    ]);
                    $snapshot['authors'][] = $authors[$name];
                }

                return $authors[$name];
            };
            // Preserve every known work-level name without altering any poem fields.
            foreach (DB::table('poems')->orderBy('id')->cursor() as $poem) {
                $resolve($poem->original_author);
                $resolve($poem->translator);
            }
            foreach (DB::table('collections')->orderBy('id')->get() as $book) {
                $resolve($book->author);
                $credits = [];
                $add = function (?string $name, string $role) use (&$credits, $resolve): void {
                    $id = $resolve($name);
                    if ($id !== null) {
                        $credits[$id.':'.$role] = ['author_id' => $id, 'role' => $role];
                    }
                };
                $works = DB::table('poems')->where('collection_id', $book->id)->orderBy('sort_order')->orderBy('id')->get();
                if ($book->id === 6) {
                    // Explicit owner decision; do not infer authorship from translator text.
                    $add('پروین پژواک', 'author');
                    $add('اجمل اند', 'translator');
                } else {
                    $add($book->author, 'author');
                    foreach ($works as $work) {
                        $add($work->original_author, 'author');
                    }
                    $translations = $works->where('work_type', 'TRANSLATION');
                    $translators = $translations->map(fn ($work) => $this->canonical($work->translator))->uniqueStrict();
                    if ($translations->isNotEmpty() && $translators->count() === 1 && $translators->first() !== null) {
                        $add($translators->first(), 'translator');
                    }
                }
                $position = (int) DB::table('collection_author')->where('collection_id', $book->id)->max('position');
                foreach ($credits as $credit) {
                    $identity = ['collection_id' => $book->id] + $credit;
                    if (! DB::table('collection_author')->where($identity)->exists()) {
                        $snapshot['credits'][] = DB::table('collection_author')->insertGetId($identity + [
                            'position' => ++$position, 'created_at' => now(), 'updated_at' => now(),
                        ]);
                    }
                }
                // Only the six existing book IDs have an owner-approved language.
                if (in_array($book->id, [1, 2, 3, 4, 5, 6], true)) {
                    $snapshot['languages'][(string) $book->id] = $book->language;
                    DB::table('collections')->where('id', $book->id)->update(['language' => 'ps']);
                }
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
                throw new RuntimeException('Credit conversion snapshot missing; refusing to guess.');
            }
            $snapshot = json_decode($value, true, flags: JSON_THROW_ON_ERROR);
            // Refuse to destroy authors that have gained new credits since conversion.
            if (DB::table('collection_author')->whereIn('author_id', $snapshot['authors'])
                ->whereNotIn('id', $snapshot['credits'])->exists()) {
                throw new RuntimeException('Converted authors have new credits; review before rollback.');
            }
            DB::table('collection_author')->whereIn('id', $snapshot['credits'])->delete();
            DB::table('authors')->whereIn('id', $snapshot['authors'])->delete();
            foreach ($snapshot['languages'] as $id => $language) {
                DB::table('collections')->where('id', $id)->update(['language' => $language]);
            }
            DB::table('app_settings')->where('key', self::SNAPSHOT)->delete();
            DB::table('app_settings')->where('key', 'content_version')->increment('value');
        });
    }
};

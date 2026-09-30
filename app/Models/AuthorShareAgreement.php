<?php

namespace App\Models;

use Illuminate\Validation\ValidationException;

class AuthorShareAgreement extends ImmutableRecord
{
    protected function casts(): array { return ['contributors' => 'array', 'starts_at' => 'datetime']; }
    public function book() { return $this->belongsTo(Collection::class, 'collection_id'); }

    protected static function booted(): void
    {
        parent::booted();
        static::creating(function (self $agreement): void {
            $total = 0;
            $seen = [];
            foreach ($agreement->contributors ?? [] as $contributor) {
                $id = $contributor['author_id'] ?? null;
                $percent = $contributor['percentage'] ?? null;
                if (! Author::whereKey($id)->exists() || isset($seen[$id]) || ! is_numeric($percent) || $percent <= 0 || $percent > 100) {
                    throw ValidationException::withMessages(['contributors' => 'Choose each contributor once with a percentage above 0 and at most 100.']);
                }
                $seen[$id] = true;
                $total += (int) round((float) $percent * 10000);
            }
            if (! $seen || $total > 1000000 || ! in_array($agreement->basis, ['gross', 'net'], true)
                || blank($agreement->deductions) || blank($agreement->sharing_terms) || ! $agreement->starts_at) {
                throw ValidationException::withMessages(['contributors' => 'Complete the agreement; combined percentages cannot exceed 100%.']);
            }
            $agreement->created_by = auth()->id();
        });
    }
}

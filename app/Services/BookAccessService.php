<?php

namespace App\Services;

use App\Models\Collection;
use App\Models\User;

class BookAccessService
{
    public function ownsBook(?User $reader, Collection $book): bool
    {
        // Step 4: only verified purchases for this exact book may grant ownership.
        // No header, global entitlement, store offering or legacy cache is ownership.
        return false;
    }
}

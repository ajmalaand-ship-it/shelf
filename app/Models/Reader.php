<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Reader extends Authenticatable
{
    use HasApiTokens;

    protected $fillable = ['email', 'name'];

    protected $hidden = ['password', 'google_subject_hash', 'avatar_path'];

    protected function casts(): array
    {
        return ['password' => 'hashed', 'email_verified_at' => 'datetime', 'buying_blocked' => 'boolean'];
    }

    public function getIsOwnerAttribute(): bool
    {
        return false;
    }

    public function purchases() { return $this->hasMany(Purchase::class); }

    public function profile(): array
    {
        return ['id' => $this->id, 'email' => $this->email, 'name' => $this->name,
            'has_avatar' => \App\Services\Accounts\ReaderAvatar::safe($this->avatar_path, $this->id),
            'sign_in_method' => $this->sign_in_method, 'email_verified' => $this->email_verified_at !== null];
    }
}

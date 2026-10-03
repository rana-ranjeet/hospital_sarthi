<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LedgerAccount extends Model
{
    protected $fillable = ['account_key', 'account_type', 'user_id', 'guide_profile_id', 'currency', 'balance_minor'];

    public function entries(): HasMany
    {
        return $this->hasMany(LedgerEntry::class);
    }
}
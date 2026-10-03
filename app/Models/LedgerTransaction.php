<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class LedgerTransaction extends Model
{
    protected $fillable = [
        'reference', 'idempotency_key', 'type', 'description', 'source_type', 'source_id', 'metadata', 'posted_at',
    ];

    protected $casts = ['metadata' => 'array', 'posted_at' => 'datetime'];

    public function entries(): HasMany
    {
        return $this->hasMany(LedgerEntry::class);
    }

    public function source(): MorphTo
    {
        return $this->morphTo();
    }
}
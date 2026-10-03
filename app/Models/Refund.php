<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Refund extends Model
{
    protected $fillable = [
        'payment_id', 'amount_minor', 'status', 'reason', 'idempotency_key', 'provider_reference',
        'ledger_transaction_id', 'processed_by', 'provider_metadata', 'processed_at',
    ];

    protected $casts = ['provider_metadata' => 'array', 'processed_at' => 'datetime'];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
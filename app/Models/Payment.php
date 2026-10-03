<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'booking_id', 'amount', 'currency', 'status', 'provider', 'transaction_reference', 'paid_at',
        'gateway_order_id', 'gateway_payment_id', 'idempotency_key', 'gateway_fee_minor',
        'commission_minor', 'guide_earning_minor', 'reward_minor', 'refund_minor', 'verified_at', 'gateway_metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2', 'paid_at' => 'datetime', 'verified_at' => 'datetime',
        'gateway_metadata' => 'array',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function refunds()
    {
        return $this->hasMany(Refund::class);
    }
}
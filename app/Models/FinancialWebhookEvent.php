<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialWebhookEvent extends Model
{
    protected $fillable = [
        'provider', 'event_id', 'signature_verified', 'payload', 'processing_status', 'error', 'processed_at',
    ];

    protected $casts = ['signature_verified' => 'boolean', 'processed_at' => 'datetime'];
}
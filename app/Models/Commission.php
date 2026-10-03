<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    protected $fillable = [
        'percentage_rate', 'fixed_amount', 'reward_percentage_rate',
        'gateway_fee_percentage', 'gateway_fee_fixed_amount', 'is_active', 'effective_at',
    ];

    protected $casts = [
        'percentage_rate' => 'decimal:2',
        'fixed_amount' => 'decimal:2',
        'reward_percentage_rate' => 'decimal:2',
        'gateway_fee_percentage' => 'decimal:2',
        'gateway_fee_fixed_amount' => 'decimal:2',
        'is_active' => 'boolean',
        'effective_at' => 'datetime',
    ];
}
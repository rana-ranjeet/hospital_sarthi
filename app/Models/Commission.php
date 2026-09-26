<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    protected $fillable = ['percentage_rate', 'fixed_amount', 'is_active', 'effective_at'];

    protected $casts = [
        'percentage_rate' => 'decimal:2',
        'fixed_amount' => 'decimal:2',
        'is_active' => 'boolean',
        'effective_at' => 'datetime',
    ];
}
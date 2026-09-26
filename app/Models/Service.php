<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Service extends Model
{
    protected $fillable = ['name', 'description', 'base_price', 'is_active'];

    protected $casts = ['base_price' => 'decimal:2', 'is_active' => 'boolean'];

    public function bookings(): BelongsToMany
    {
        return $this->belongsToMany(Booking::class, 'booking_services')->withTimestamps();
    }
}
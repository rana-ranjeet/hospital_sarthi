<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Hospital extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'city', 'type', 'address', 'phone', 'image_url', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function guides(): BelongsToMany
    {
        return $this->belongsToMany(GuideProfile::class, 'hospital_guide')->withTimestamps();
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
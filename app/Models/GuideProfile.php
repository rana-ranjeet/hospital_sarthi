<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GuideProfile extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'city', 'bio', 'languages', 'years_experience', 'specialization', 'hourly_rate', 'is_verified', 'is_available', 'status', 'document_path'];

    protected $casts = [
        'languages' => 'array',
        'is_verified' => 'boolean',
        'is_available' => 'boolean',
        'hourly_rate' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function hospitals(): BelongsToMany
    {
        return $this->belongsToMany(Hospital::class, 'hospital_guide')->withTimestamps();
    }

    public function availabilities(): HasMany
    {
        return $this->hasMany(GuideAvailability::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
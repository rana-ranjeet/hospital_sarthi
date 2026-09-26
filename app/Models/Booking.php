<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = ['patient_id', 'guide_profile_id', 'hospital_id', 'service', 'visit_date', 'start_time', 'message', 'status'];

    protected $casts = ['visit_date' => 'date'];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function guideProfile(): BelongsTo
    {
        return $this->belongsTo(GuideProfile::class);
    }

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'booking_services')->withTimestamps();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
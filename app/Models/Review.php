<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use HasFactory;

    protected $fillable = ['booking_id', 'rating', 'comment', 'is_visible'];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
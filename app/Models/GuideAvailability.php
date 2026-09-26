<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuideAvailability extends Model
{
    protected $fillable = ['guide_profile_id', 'weekday', 'start_time', 'end_time'];

    public function guideProfile(): BelongsTo
    {
        return $this->belongsTo(GuideProfile::class);
    }
}
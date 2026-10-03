<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GuideBankAccount extends Model
{
    protected $fillable = [
        'guide_profile_id', 'account_holder_name', 'account_number_encrypted', 'ifsc_encrypted',
        'account_last_four', 'ifsc_masked', 'bank_name', 'status', 'verified_by', 'verified_at',
    ];

    protected $hidden = ['account_number_encrypted', 'ifsc_encrypted'];

    protected $casts = [
        'account_number_encrypted' => 'encrypted',
        'ifsc_encrypted' => 'encrypted',
        'verified_at' => 'datetime',
    ];

    public function guideProfile(): BelongsTo
    {
        return $this->belongsTo(GuideProfile::class);
    }

    public function payouts(): HasMany
    {
        return $this->hasMany(GuidePayout::class);
    }
}
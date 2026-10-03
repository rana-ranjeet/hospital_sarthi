<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class FinancialAuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = ['user_id', 'action', 'subject_type', 'subject_id', 'metadata', 'ip_address', 'request_id', 'created_at'];

    protected $casts = ['metadata' => 'array', 'created_at' => 'datetime'];

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}
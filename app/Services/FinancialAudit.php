<?php

namespace App\Services;

use App\Models\FinancialAuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class FinancialAudit
{
    public function record(string $action, ?Model $subject = null, array $metadata = [], ?Request $request = null): void
    {
        FinancialAuditLog::create([
            'user_id' => $request?->user()?->id,
            'action' => $action,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'metadata' => $metadata,
            'ip_address' => $request?->ip(),
            'request_id' => $request?->headers->get('X-Request-ID'),
            'created_at' => now(),
        ]);
    }
}
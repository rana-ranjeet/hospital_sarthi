<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuidePayout extends Model
{
    protected $fillable = [
        'guide_profile_id', 'guide_bank_account_id', 'amount_minor', 'fee_minor', 'currency', 'status',
        'idempotency_key', 'provider', 'provider_reference', 'ledger_transaction_id', 'processed_by',
        'failure_reason', 'provider_metadata', 'processed_at',
    ];

    protected $casts = ['provider_metadata' => 'array', 'processed_at' => 'datetime'];

    public function guideProfile(): BelongsTo
    {
        return $this->belongsTo(GuideProfile::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(GuideBankAccount::class, 'guide_bank_account_id');
    }

    public function ledgerTransaction(): BelongsTo
    {
        return $this->belongsTo(LedgerTransaction::class);
    }
}
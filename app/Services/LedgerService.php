<?php

namespace App\Services;

use App\Models\LedgerAccount;
use App\Models\LedgerTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class LedgerService
{
    public function post(
        string $type,
        string $description,
        array $entries,
        string $reference,
        ?string $idempotencyKey = null,
        ?object $source = null,
        array $metadata = [],
    ): LedgerTransaction {
        $debits = array_sum(array_map(fn ($entry) => $entry['side'] === 'debit' ? (int) $entry['amount_minor'] : 0, $entries));
        $credits = array_sum(array_map(fn ($entry) => $entry['side'] === 'credit' ? (int) $entry['amount_minor'] : 0, $entries));
        if ($debits <= 0 || $debits !== $credits) {
            throw new InvalidArgumentException('Ledger transactions must have equal positive debits and credits.');
        }

        return DB::transaction(function () use ($type, $description, $entries, $reference, $idempotencyKey, $source, $metadata) {
            if ($idempotencyKey) {
                $existing = LedgerTransaction::query()->where('idempotency_key', $idempotencyKey)->first();
                if ($existing) {
                    return $existing;
                }
            }

            $accountKeys = collect($entries)->pluck('account_key')->unique()->sort()->values();
            foreach ($accountKeys as $accountKey) {
                LedgerAccount::query()->firstOrCreate(
                    ['account_key' => $accountKey],
                    ['account_type' => $this->accountType($accountKey), 'currency' => 'INR'],
                );
            }

            $accounts = LedgerAccount::query()
                ->whereIn('account_key', $accountKeys)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('account_key');

            foreach ($entries as $entry) {
                if (! in_array($entry['side'], ['debit', 'credit'], true) || (int) $entry['amount_minor'] <= 0) {
                    throw new InvalidArgumentException('Ledger entries require a valid side and positive amount.');
                }
                $account = $accounts->get($entry['account_key']);
                $normalSide = in_array($account->account_type, ['gateway_clearing', 'expense'], true) ? 'debit' : 'credit';
                $change = $entry['side'] === $normalSide ? 1 : -1;
                $nextBalance = (int) $account->balance_minor + ($change * (int) $entry['amount_minor']);
                if ($account->account_type === 'guide_wallet' && $nextBalance < 0) {
                    throw ValidationException::withMessages(['amount' => 'The payout exceeds your available guide balance.']);
                }
                $account->balance_minor = $nextBalance;
                $account->save();
            }

            $transaction = LedgerTransaction::create([
                'reference' => $reference,
                'idempotency_key' => $idempotencyKey,
                'type' => $type,
                'description' => $description,
                'source_type' => $source ? $source::class : null,
                'source_id' => $source?->getKey(),
                'metadata' => $metadata,
                'posted_at' => now(),
            ]);

            foreach ($entries as $entry) {
                $transaction->entries()->create([
                    'ledger_account_id' => $accounts->get($entry['account_key'])->id,
                    'side' => $entry['side'],
                    'amount_minor' => $entry['amount_minor'],
                    'currency' => $entry['currency'] ?? 'INR',
                    'created_at' => now(),
                ]);
            }

            return $transaction;
        });
    }

    public function balance(string $accountKey): int
    {
        return (int) LedgerAccount::query()->where('account_key', $accountKey)->value('balance_minor');
    }

    private function accountType(string $accountKey): string
    {
        return match (true) {
            str_starts_with($accountKey, 'guide:') => 'guide_wallet',
            str_starts_with($accountKey, 'customer:') => 'customer_rewards',
            str_contains($accountKey, 'revenue') => 'platform_revenue',
            str_contains($accountKey, 'expense') => 'expense',
            str_contains($accountKey, 'clearing') => 'gateway_clearing',
            str_contains($accountKey, 'reserve') => 'payout_reserve',
            default => 'liability',
        };
    }
}
<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Commission;
use Illuminate\Validation\ValidationException;

class FinanceRules
{
    public function activeCommission(): Commission
    {
        return Commission::query()->where('is_active', true)->latest('effective_at')->latest('id')->first()
            ?? new Commission([
                'percentage_rate' => 0,
                'fixed_amount' => 0,
                'reward_percentage_rate' => 0,
                'gateway_fee_percentage' => 0,
                'gateway_fee_fixed_amount' => 0,
            ]);
    }

    public function allocation(Booking $booking): array
    {
        $commission = $this->activeCommission();
        $gross = self::toMinor($booking->amount);
        $commissionMinor = min($gross, self::percentage($gross, $commission->percentage_rate) + self::toMinor($commission->fixed_amount));
        $gatewayFeeMinor = self::percentage($gross, $commission->gateway_fee_percentage)
            + self::toMinor($commission->gateway_fee_fixed_amount);
        if ($commissionMinor + $gatewayFeeMinor > $gross) {
            throw ValidationException::withMessages(['payment' => 'Configured commission and gateway fees exceed the booking amount.']);
        }

        return [
            'gross_minor' => $gross,
            'commission_minor' => $commissionMinor,
            'gateway_fee_minor' => $gatewayFeeMinor,
            'guide_earning_minor' => $gross - $commissionMinor,
            'reward_minor' => self::percentage($gross, $commission->reward_percentage_rate),
            'currency' => 'INR',
            'commission_id' => $commission->exists ? $commission->id : null,
        ];
    }

    public static function toMinor(int|float|string|null $amount): int
    {
        $value = trim((string) ($amount ?? '0'));
        if (! preg_match('/^(\d+)(?:\.(\d+))?$/', $value, $matches)) {
            return 0;
        }

        $fraction = str_pad(substr($matches[2] ?? '', 0, 2), 2, '0');
        $minor = ((int) $matches[1] * 100) + (int) $fraction;
        if (isset($matches[2][2]) && (int) $matches[2][2] >= 5) {
            $minor++;
        }

        return $minor;
    }

    private static function percentage(int $amountMinor, int|float|string|null $rate): int
    {
        $rateParts = explode('.', (string) ($rate ?? '0'), 2);
        $basisPoints = ((int) $rateParts[0] * 100) + (int) str_pad(substr($rateParts[1] ?? '', 0, 2), 2, '0');

        return intdiv(($amountMinor * $basisPoints) + 5000, 10000);
    }
}
<?php

namespace App\Services;

class FdCalculator
{
    public const PAYOUTS_PER_YEAR = [
        'monthly' => 12,
        'quarterly' => 4,
    ];

    public static function toDays(int $value, string $unit): int
    {
        return match ($unit) {
            'years' => $value * 365,
            'months' => (int) round($value * 365 / 12),
            default => $value,
        };
    }

    /**
     * Cumulative FDs are compounded quarterly. Deposits shorter than
     * 6 months get simple interest, which is how most banks do it.
     * Non-cumulative FDs pay simple interest out every month/quarter.
     */
    public function calculate(float $amount, int $days, float $rate, string $payout): array
    {
        $years = $days / 365;

        if ($payout === 'cumulative') {
            if ($days < 180) {
                $maturity = $amount + ($amount * $rate * $years / 100);
            } else {
                $maturity = $amount * pow(1 + $rate / 400, 4 * $years);
            }

            return [
                'principal' => round($amount, 2),
                'interest' => round($maturity - $amount, 2),
                'maturity' => round($maturity, 2),
                'payout_amount' => null,
            ];
        }

        $perYear = self::PAYOUTS_PER_YEAR[$payout];
        $interest = $amount * $rate * $years / 100;

        return [
            'principal' => round($amount, 2),
            'interest' => round($interest, 2),
            'maturity' => round($amount, 2),
            'payout_amount' => round($amount * $rate / 100 / $perYear, 2),
        ];
    }
}

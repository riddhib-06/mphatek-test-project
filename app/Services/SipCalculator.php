<?php

namespace App\Services;

class SipCalculator
{
    public const INSTALMENTS_PER_YEAR = [
        'monthly' => 12,
        'quarterly' => 4,
        'yearly' => 1,
    ];

    /**
     * Future value of an SIP (instalment paid at the start of each period):
     * FV = P * [((1 + i)^n - 1) / i] * (1 + i)
     */
    public function calculate(float $amount, string $frequency, int $months, float $annualRate): array
    {
        $perYear = self::INSTALMENTS_PER_YEAR[$frequency];
        $instalments = intdiv($months, 12 / $perYear);
        $i = $annualRate / 100 / $perYear;

        if ($i == 0) {
            $futureValue = $amount * $instalments;
        } else {
            $futureValue = $amount * ((pow(1 + $i, $instalments) - 1) / $i) * (1 + $i);
        }

        $invested = $amount * $instalments;

        return [
            'instalments' => $instalments,
            'invested' => round($invested, 2),
            'returns' => round($futureValue - $invested, 2),
            'future_value' => round($futureValue, 2),
        ];
    }
}

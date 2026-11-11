<?php

namespace Database\Seeders;

use App\Models\FdRate;
use Illuminate\Database\Seeder;

class FdRateSeeder extends Seeder
{
    public function run(): void
    {
        FdRate::truncate();

        $slabs = [
            [7, 45, 3.50, 4.00],
            [46, 179, 4.75, 5.25],
            [180, 364, 6.00, 6.50],
            [365, 729, 6.80, 7.30],
            [730, 1094, 7.00, 7.50],
            [1095, 1824, 6.75, 7.25],
            [1825, 3650, 6.50, 7.25],
        ];

        foreach ($slabs as [$min, $max, $general, $senior]) {
            FdRate::create([
                'min_days' => $min,
                'max_days' => $max,
                'general_rate' => $general,
                'senior_rate' => $senior,
            ]);
        }
    }
}

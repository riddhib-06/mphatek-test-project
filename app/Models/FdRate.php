<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FdRate extends Model
{
    protected $fillable = ['min_days', 'max_days', 'general_rate', 'senior_rate'];

    public static function forTenure(int $days, bool $senior = false): ?float
    {
        $slab = static::where('min_days', '<=', $days)
            ->where('max_days', '>=', $days)
            ->first();

        if (! $slab) {
            return null;
        }

        return (float) ($senior ? $slab->senior_rate : $slab->general_rate);
    }
}

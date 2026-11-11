<?php

namespace App\Http\Controllers;

use App\Models\FdRate;
use App\Services\FdCalculator;
use App\Services\SipCalculator;
use Illuminate\Http\Request;

class CalculatorController extends Controller
{
    public function fd()
    {
        return view('calculators.fd', ['rates' => FdRate::orderBy('min_days')->get()]);
    }

    public function sip()
    {
        return view('calculators.sip');
    }

    public function calculateFd(Request $request, FdCalculator $calculator)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:1000|max:100000000',
            'tenure' => 'required|integer|min:1',
            'tenure_unit' => 'required|in:days,months,years',
            'customer_type' => 'required|in:general,senior',
            'rate_mode' => 'required|in:auto,manual',
            'rate' => 'required_if:rate_mode,manual|nullable|numeric|min:0.1|max:20',
            'payout' => 'required|in:cumulative,monthly,quarterly',
        ]);

        $days = FdCalculator::toDays($data['tenure'], $data['tenure_unit']);

        if ($days < 7 || $days > 3650) {
            return $this->error('tenure', 'Tenure must be between 7 days and 10 years.');
        }

        if ($data['payout'] === 'monthly' && $days < 30) {
            return $this->error('payout', 'Monthly payout needs a tenure of at least 1 month.');
        }

        if ($data['payout'] === 'quarterly' && $days < 90) {
            return $this->error('payout', 'Quarterly payout needs a tenure of at least 3 months.');
        }

        $rate = $data['rate_mode'] === 'auto'
            ? FdRate::forTenure($days, $data['customer_type'] === 'senior')
            : (float) $data['rate'];

        if ($rate === null) {
            return $this->error('rate', 'No FD rate is available for this tenure.');
        }

        $result = $calculator->calculate($data['amount'], $days, $rate, $data['payout']);

        return response()->json($result + ['rate' => $rate, 'days' => $days]);
    }

    public function calculateSip(Request $request, SipCalculator $calculator)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:100|max:10000000',
            'frequency' => 'required|in:monthly,quarterly,yearly',
            'duration' => 'required|integer|min:1|max:480',
            'duration_unit' => 'required|in:years,months',
            'rate' => 'required|numeric|min:0|max:50',
        ]);

        $months = $data['duration_unit'] === 'years' ? $data['duration'] * 12 : $data['duration'];

        if ($months > 480) {
            return $this->error('duration', 'Duration can be at most 40 years.');
        }

        $result = $calculator->calculate($data['amount'], $data['frequency'], $months, $data['rate']);

        if ($result['instalments'] === 0) {
            return $this->error('duration', 'Duration is too short for the selected frequency.');
        }

        return response()->json($result);
    }

    private function error(string $field, string $message)
    {
        return response()->json(['message' => $message, 'errors' => [$field => [$message]]], 422);
    }
}

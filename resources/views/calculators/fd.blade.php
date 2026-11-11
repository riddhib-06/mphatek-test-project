@extends('layouts.app')

@section('title', 'FD Calculator | '.config('app.name'))

@section('meta')
    <meta name="description" content="Calculate the maturity amount and interest earned on your fixed deposit.">
    <link rel="canonical" href="{{ route('calculators.fd') }}">
@endsection

@section('content')
    <h1>FD Calculator</h1>

    <div class="calculator">
        <form class="box" id="fd-form" data-calculator="fd" action="{{ route('calculators.fd.calculate') }}">
            <label>
                Deposit Amount (₹)
                <input type="number" name="amount" value="100000" min="1000" step="1000">
            </label>

            <label>FD Tenure</label>
            <div class="row">
                <input type="number" name="tenure" value="1" min="1">
                <select name="tenure_unit">
                    <option value="days">Days</option>
                    <option value="months">Months</option>
                    <option value="years" selected>Years</option>
                </select>
            </div>

            <label>Customer Type</label>
            <div class="options">
                <label><input type="radio" name="customer_type" value="general" checked> General Citizen</label>
                <label><input type="radio" name="customer_type" value="senior"> Senior Citizen</label>
            </div>

            <label>Interest Rate (%)</label>
            <div class="options">
                <label><input type="radio" name="rate_mode" value="auto" checked> Current bank rate</label>
                <label><input type="radio" name="rate_mode" value="manual"> Enter manually</label>
            </div>
            <input type="number" name="rate" step="0.01" min="0.1" max="20" readonly>

            <label>
                Interest Payout
                <select name="payout">
                    <option value="cumulative">Cumulative (paid on maturity)</option>
                    <option value="monthly">Non-Cumulative - Monthly</option>
                    <option value="quarterly">Non-Cumulative - Quarterly</option>
                </select>
            </label>
        </form>

        <div class="box result">
            <p class="error" data-error></p>
            <dl>
                <dt>Principal Amount</dt><dd data-result="principal" data-money>-</dd>
                <dt>Interest Rate</dt><dd data-result="rate" data-suffix="%">-</dd>
                <dt>Total Interest</dt><dd data-result="interest" data-money>-</dd>
                <dt class="payout-row">Interest per Payout</dt><dd class="payout-row" data-result="payout_amount" data-money>-</dd>
                <dt>Maturity Amount</dt><dd class="big" data-result="maturity" data-money>-</dd>
            </dl>
        </div>
    </div>

    @if ($rates->isNotEmpty())
        <section class="box">
            <h2>Current FD Interest Rates</h2>
            <table>
                <tr><th>Tenure</th><th class="num">General</th><th>Senior Citizen</th></tr>
                @foreach ($rates as $rate)
                    <tr>
                        <td>{{ $rate->min_days }} - {{ $rate->max_days }} days</td>
                        <td class="num">{{ $rate->general_rate }}%</td>
                        <td>{{ $rate->senior_rate }}%</td>
                    </tr>
                @endforeach
            </table>
        </section>
    @endif
@endsection

@push('scripts')
    <script src="{{ asset('js/calculator.js') }}"></script>
    <script>
        (function () {
            var form = document.getElementById('fd-form');
            var rate = form.elements.rate;

            function toggleRate() {
                var manual = form.elements.rate_mode.value === 'manual';
                rate.readOnly = !manual;
                if (manual && !rate.value) rate.value = 7;
            }

            function togglePayoutRow() {
                var show = form.elements.payout.value !== 'cumulative';
                document.querySelectorAll('.payout-row').forEach(function (el) {
                    el.style.display = show ? '' : 'none';
                });
            }

            form.addEventListener('change', function () {
                toggleRate();
                togglePayoutRow();
            });

            // show the rate that was picked up from the rate table
            form.addEventListener('calculated', function (e) {
                if (form.elements.rate_mode.value === 'auto') {
                    rate.value = e.detail.rate;
                }
            });

            toggleRate();
            togglePayoutRow();
        })();
    </script>
@endpush

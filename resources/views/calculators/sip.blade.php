@extends('layouts.app')

@section('title', 'SIP Calculator | '.config('app.name'))

@section('meta')
    <meta name="description" content="Estimate the future value of your monthly, quarterly or yearly SIP.">
    <link rel="canonical" href="{{ route('calculators.sip') }}">
@endsection

@section('content')
    <h1>SIP Calculator</h1>

    <div class="calculator">
        <form class="box" data-calculator="sip" action="{{ route('calculators.sip.calculate') }}">
            <label>
                Investment Amount (₹)
                <input type="number" name="amount" value="5000" min="100" step="500">
            </label>

            <label>
                Investment Frequency
                <select name="frequency">
                    <option value="monthly">Monthly SIP</option>
                    <option value="quarterly">Quarterly SIP</option>
                    <option value="yearly">Yearly SIP</option>
                </select>
            </label>

            <label>Investment Duration</label>
            <div class="row">
                <input type="number" name="duration" value="10" min="1">
                <select name="duration_unit">
                    <option value="years">Years</option>
                    <option value="months">Months</option>
                </select>
            </div>

            <label>
                Expected Annual Return (%)
                <input type="number" name="rate" value="12" min="0" max="50" step="0.1">
            </label>
        </form>

        <div class="box result">
            <p class="error" data-error></p>
            <dl>
                <dt>No. of Instalments</dt><dd data-result="instalments">-</dd>
                <dt>Amount Invested</dt><dd data-result="invested" data-money>-</dd>
                <dt>Estimated Returns</dt><dd data-result="returns" data-money>-</dd>
                <dt>Total Value</dt><dd class="big" data-result="future_value" data-money>-</dd>
            </dl>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="{{ asset('js/calculator.js') }}"></script>
@endpush

@extends('layouts.app')

@section('title', 'Lead Report | Admin | '.config('app.name'))
@section('body_class', 'admin')

@section('meta')
    <meta name="robots" content="noindex, nofollow">
@endsection

@section('content')
    @include('admin.nav')

    <h1>Lead Report</h1>

    <div class="grid-2">
        <section class="box">
            <h2>Leads by Product</h2>
            <table>
                <tr><th>Product</th><th>Leads</th></tr>
                @forelse ($byProduct as $row)
                    <tr><td>{{ $row->product }}</td><td>{{ $row->total }}</td></tr>
                @empty
                    <tr><td colspan="2">No leads yet.</td></tr>
                @endforelse
            </table>
        </section>

        <section class="box">
            <h2>Leads by Source</h2>
            <table>
                <tr><th>Source</th><th>Medium</th><th>Leads</th></tr>
                @forelse ($bySource as $row)
                    <tr><td>{{ $row->source }}</td><td>{{ $row->medium ?? '-' }}</td><td>{{ $row->total }}</td></tr>
                @empty
                    <tr><td colspan="3">No leads yet.</td></tr>
                @endforelse
            </table>
        </section>
    </div>

    <section class="box">
        <h2>Product vs Source</h2>
        <table>
            <tr><th>Product</th><th>Source</th><th>Leads</th></tr>
            @forelse ($byProductAndSource as $row)
                <tr><td>{{ $row->product }}</td><td>{{ $row->source }}</td><td>{{ $row->total }}</td></tr>
            @empty
                <tr><td colspan="3">No leads yet.</td></tr>
            @endforelse
        </table>
    </section>
@endsection

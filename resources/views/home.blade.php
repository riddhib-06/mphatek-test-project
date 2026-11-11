@extends('layouts.app')

@section('content')
    <section class="hero">
        <h1>Banking made simple</h1>
        <p>Plan your savings with our calculators or explore our products.</p>
    </section>

    @php
        $sections = [
            'product' => 'Products',
            'calculator' => 'Calculators',
        ];
    @endphp

    @foreach ($sections as $type => $heading)
        @if (isset($pages[$type]))
            <section class="list-section">
                <h2>{{ $heading }}</h2>
                <div class="cards">
                    @foreach ($pages[$type]->take(3) as $item)
                        <a class="card" href="{{ $item->url() }}">
                            <strong>{{ $item->title }}</strong>
                            @if ($item->summary)
                                <span>{{ $item->summary }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    @endforeach
@endsection

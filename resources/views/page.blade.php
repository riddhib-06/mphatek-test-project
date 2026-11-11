@extends('layouts.app')

@section('title', $page->title.' | '.config('app.name'))

@section('meta')
    @if ($page->summary)
        <meta name="description" content="{{ $page->summary }}">
    @endif
    @if ($page->noindex)
        <meta name="robots" content="noindex, follow">
    @endif
    <link rel="canonical" href="{{ $page->canonical() }}">
@endsection

@section('content')
    <article class="page">
        <h1>{{ $page->title }}</h1>
        @if ($page->summary)
            <p class="lead">{{ $page->summary }}</p>
        @endif

        <div class="content">{!! $page->content !!}</div>
    </article>

    @if ($page->type === 'product')
        @include('partials.enquiry-form', ['product' => $page])
    @endif
@endsection

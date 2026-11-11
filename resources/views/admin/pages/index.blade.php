@extends('layouts.app')

@section('title', 'Pages | Admin | '.config('app.name'))
@section('body_class', 'admin')

@section('meta')
    <meta name="robots" content="noindex, nofollow">
@endsection

@section('content')
    @include('admin.nav')

    @php($excluded = $pages->filter(fn ($p) => $p->sitemapExclusion())->count())

    <div class="page-head">
        <div>
            <h1>Pages</h1>
            <p class="muted">
                {{ $pages->count() }} {{ Str::plural('page', $pages->count()) }}
                &middot; {{ $pages->count() - $excluded }} in the
                <a href="{{ route('sitemap') }}" target="_blank" rel="noopener">sitemap</a>
                &middot; {{ $excluded }} left out
            </p>
        </div>
        <a class="button" href="{{ route('admin.pages.create', ['type' => $type]) }}">Add page</a>
    </div>

    <nav class="tabs">
        <a href="{{ route('admin.pages.index') }}" @class(['active' => ! $type])>All <small>{{ $counts->sum() }}</small></a>
        @foreach ($types as $key => $label)
            <a href="{{ route('admin.pages.index', ['type' => $key]) }}" @class(['active' => $type === $key])>
                {{ $label }} <small>{{ $counts[$key] ?? 0 }}</small>
            </a>
        @endforeach
    </nav>

    <div class="box table-wrap">
        <table class="list">
            <thead>
                <tr>
                    <th>Page</th>
                    @unless ($type) <th>Type</th> @endunless
                    <th>Sitemap</th>
                    <th>Updated</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pages as $page)
                    @php($reason = $page->sitemapExclusion())
                    @php($path = parse_url($page->url(), PHP_URL_PATH) ?: '/')
                    <tr>
                        <td>
                            <a class="title" href="{{ route('admin.pages.edit', $page) }}">{{ $page->title }}</a>
                            <a class="path" href="{{ $page->url() }}" target="_blank" rel="noopener" title="Open page">{{ $path }}</a>
                        </td>
                        @unless ($type) <td class="muted">{{ $types[$page->type] ?? $page->type }}</td> @endunless
                        <td class="nowrap">
                            @if ($reason)
                                <span class="status off">{{ $reason }}</span>
                            @else
                                <span class="status on">Included</span>
                            @endif
                        </td>
                        <td class="nowrap muted">{{ $page->updated_at->format('j M Y') }}</td>
                        <td class="nowrap actions-cell">
                            <a href="{{ route('admin.pages.edit', $page) }}">Edit</a>
                            <form method="POST" action="{{ route('admin.pages.destroy', $page) }}" class="inline"
                                  onsubmit="return confirm('Delete &quot;{{ addslashes($page->title) }}&quot;? It will also be removed from the sitemap.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="link danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="muted">No pages here yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection

@extends('layouts.app')

@section('title', ($page->exists ? 'Edit page' : 'Add page').' | Admin | '.config('app.name'))
@section('body_class', 'admin')

@section('meta')
    <meta name="robots" content="noindex, nofollow">
@endsection

@section('content')
    @include('admin.nav')

    <h1>{{ $page->exists ? 'Edit: '.$page->title : 'Add page' }}</h1>
    @if ($page->exists)
        <p class="lead">
            <a href="{{ $page->url() }}" target="_blank" rel="noopener" class="mono">{{ $page->url() }}</a>
            &middot; {{ $page->sitemapStatus() }}
        </p>
    @endif
    <form method="POST" class="admin-form"
          action="{{ $page->exists ? route('admin.pages.update', $page) : route('admin.pages.store') }}">
        @csrf
        @if ($page->exists) @method('PUT') @endif

        <div class="calculator">
            <section class="box">
                <h2>Page</h2>

                <label>
                    Type
                    <select name="type" id="type">
                        @foreach ($types as $key => $label)
                            <option value="{{ $key }}" @selected(old('type', $page->type) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                @error('type') <p class="error">{{ $message }}</p> @enderror

                <label>
                    Title
                    <input type="text" name="title" id="title" value="{{ old('title', $page->title) }}" required>
                </label>
                @error('title') <p class="error">{{ $message }}</p> @enderror

                <label id="slug-text">
                    URL Slug <span class="note">(automatic - made from the title, change only if needed)</span>
                    <input type="text" name="slug" id="slug" value="{{ old('slug', $page->slug) }}"
                           placeholder="made from the title" data-touched="{{ $page->exists ? '1' : '' }}">
                </label>
                <label id="slug-calc" hidden>
                    Calculator
                    <select name="slug" id="slug-select" disabled>
                        <option value="">Choose</option>
                        @foreach ($calculatorSlugs as $calc)
                            <option value="{{ $calc }}" @selected(old('slug', $page->slug) === $calc)>{{ Str::headline($calc) }}</option>
                        @endforeach
                    </select>
                </label>
                <p class="note">URL: <span class="mono" id="url-preview"></span></p>
                @if ($page->exists)
                    <p class="note">Changing the slug changes the page's URL. Old links will stop working unless you add a redirect.</p>
                @endif
                @error('slug') <p class="error">{{ $message }}</p> @enderror

                <label>
                    Category <span class="note">(optional, e.g. Loans, Deposits)</span>
                    <input type="text" name="category" value="{{ old('category', $page->category) }}">
                </label>
                @error('category') <p class="error">{{ $message }}</p> @enderror

                <label>
                    Summary <span class="note">(shown under the title and as meta description)</span>
                    <textarea name="summary" rows="2" class="plain">{{ old('summary', $page->summary) }}</textarea>
                </label>
                @error('summary') <p class="error">{{ $message }}</p> @enderror

                <label>
                    Content <span class="note">(HTML: p, a, strong, ul, li, h2, table...)</span>
                    <textarea name="content" rows="8" class="plain">{{ old('content', $page->content) }}</textarea>
                </label>
                @error('content') <p class="error">{{ $message }}</p> @enderror
            </section>

            <section class="box">
                <h2>SEO &amp; sitemap</h2>

                <label class="check">
                    <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $page->is_published))>
                    Published
                    <span class="note">Unpublished pages return 404 and are left out of the sitemap.</span>
                </label>

                <label class="check">
                    <input type="checkbox" name="noindex" value="1" @checked(old('noindex', $page->noindex))>
                    Noindex
                    <span class="note">Page stays visible, but search engines are told not to index it. Left out of the sitemap.</span>
                </label>

                <label>
                    Canonical URL <span class="note">(only for duplicates)</span>
                    <input type="text" name="canonical_url" value="{{ old('canonical_url', $page->canonical_url) }}" placeholder="leave empty if not a duplicate">
                </label>
                <p class="note">If this page is a copy of another page, enter the original's URL, e.g. /products/fixed-deposit. The copy is left out of the sitemap.</p>
                @error('canonical_url') <p class="error">{{ $message }}</p> @enderror

                <label>
                    Redirect to <span class="note">(optional)</span>
                    <input type="text" name="redirect_url" value="{{ old('redirect_url', $page->redirect_url) }}" placeholder="leave empty - only for moved pages">
                </label>
                @error('redirect_url') <p class="error">{{ $message }}</p> @enderror

                <div class="options">
                    <label><input type="radio" name="redirect_code" value="301" @checked((int) old('redirect_code', $page->redirect_code) === 301)> 301 permanent</label>
                    <label><input type="radio" name="redirect_code" value="302" @checked((int) old('redirect_code', $page->redirect_code) === 302)> 302 temporary</label>
                </div>
                <p class="note">e.g. /products/personal-loan. Redirected pages are left out of the sitemap.</p>
            </section>
        </div>

        <div class="row actions">
            <button type="submit">{{ $page->exists ? 'Save changes' : 'Add page' }}</button>
            <a href="{{ route('admin.pages.index') }}">Cancel</a>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        (function () {
            var prefixes = {product: '/products/', blog: '/blog/', branch: '/branches/', calculator: '/calculators/', static: '/'};
            var type = document.getElementById('type');
            var title = document.getElementById('title');
            var slug = document.getElementById('slug');
            var preview = document.getElementById('url-preview');
            var calcSelect = document.getElementById('slug-select');

            function slugify(text) {
                return text.toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
            }

            function update() {
                // calculators: pick from the list, other types: text field filled from the title
                var isCalc = type.value === 'calculator';
                document.getElementById('slug-text').hidden = isCalc;
                document.getElementById('slug-calc').hidden = !isCalc;
                slug.disabled = isCalc;
                calcSelect.disabled = !isCalc;

                var value = isCalc ? calcSelect.value : (slug.value || slugify(title.value));
                preview.textContent = location.origin + prefixes[type.value] + (value || '...');
            }

            calcSelect.addEventListener('change', update);

            // fill the slug from the title until the slug is edited by hand
            title.addEventListener('input', function () {
                if (!slug.dataset.touched) slug.value = slugify(title.value);
                update();
            });
            slug.addEventListener('input', function () { slug.dataset.touched = '1'; update(); });
            type.addEventListener('change', update);

            update();
        })();
    </script>
@endpush

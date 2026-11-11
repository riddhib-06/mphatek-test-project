{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach ($sitemaps as $name => $lastUpdated)
    <sitemap>
        <loc>{{ route('sitemap.show', $name) }}</loc>
        <lastmod>{{ \Illuminate\Support\Carbon::parse($lastUpdated)->toAtomString() }}</lastmod>
    </sitemap>
@endforeach
</sitemapindex>

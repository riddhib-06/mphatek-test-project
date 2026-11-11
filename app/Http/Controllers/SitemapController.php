<?php

namespace App\Http\Controllers;

use App\Models\Page;

class SitemapController extends Controller
{
    // sitemap name in the url => page type in the database
    public const SITEMAPS = [
        'products' => 'product',
        'blogs' => 'blog',
        'branches' => 'branch',
        'static' => 'static',
        'calculators' => 'calculator',
    ];

    public function index()
    {
        $lastUpdated = Page::indexable()
            ->selectRaw('type, max(updated_at) as last_updated')
            ->groupBy('type')
            ->pluck('last_updated', 'type');

        $sitemaps = [];
        foreach (self::SITEMAPS as $name => $type) {
            if (isset($lastUpdated[$type])) {
                $sitemaps[$name] = $lastUpdated[$type];
            }
        }

        return response()
            ->view('sitemap.index', ['sitemaps' => $sitemaps])
            ->header('Content-Type', 'application/xml');
    }

    public function show(string $name)
    {
        if (! isset(self::SITEMAPS[$name])) {
            abort(404);
        }

        $pages = Page::indexable()
            ->where('type', self::SITEMAPS[$name])
            ->orderBy('slug')
            ->get()
            ->filter(fn (Page $page) => $page->isCanonical())
            ->unique(fn (Page $page) => $page->url());

        return response()
            ->view('sitemap.urls', ['pages' => $pages])
            ->header('Content-Type', 'application/xml');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function home()
    {
        // same rules as the sitemap: no redirected, noindex or duplicate (canonical elsewhere) pages
        $pages = Page::indexable()
            ->where('slug', '!=', 'home')
            ->orderBy('title')
            ->get()
            ->filter(fn (Page $page) => $page->isCanonical())
            ->groupBy('type');

        return view('home', ['pages' => $pages]);
    }

    // The page type comes from the route definition, see routes/web.php
    public function show(Request $request, string $slug)
    {
        $page = Page::where('type', $request->route('type'))->where('slug', $slug)->first();

        if (! $page || ! $page->is_published) {
            abort(404);
        }

        // avoid having the home page on two urls
        if ($page->type === 'static' && $page->slug === 'home') {
            return redirect()->route('home', [], 301);
        }

        if ($page->redirect_url) {
            return redirect($page->redirect_url, $page->redirect_code);
        }

        return view('page', ['page' => $page]);
    }
}

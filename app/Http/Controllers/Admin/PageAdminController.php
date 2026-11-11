<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Small CMS: add / edit / delete pages.
 * Every change shows up in the sitemap straight away, because the sitemap
 * is built from the pages table on each request.
 */
class PageAdminController extends Controller
{
    public const TYPE_LABELS = [
        'product' => 'Product',
        'blog' => 'Blog',
        'branch' => 'Branch',
        'static' => 'Static',
        'calculator' => 'Calculator',
    ];

    // Calculator pages need code (a route + view), so only the built ones can be listed.
    public const CALCULATOR_SLUGS = ['fd-calculator', 'sip-calculator'];

    // Static pages live at /{slug}, so these would clash with real routes.
    public const RESERVED_SLUGS = ['admin', 'products', 'blog', 'branches', 'calculators', 'enquiry', 'up'];

    public function index(Request $request)
    {
        $type = $request->query('type');

        $pages = Page::when(isset(self::TYPE_LABELS[$type]), fn ($q) => $q->where('type', $type))
            ->orderBy('type')
            ->orderBy('title')
            ->get();

        return view('admin.pages.index', [
            'pages' => $pages,
            'type' => $type,
            'types' => self::TYPE_LABELS,
            'counts' => Page::selectRaw('type, count(*) as total')->groupBy('type')->pluck('total', 'type'),
        ]);
    }

    public function create(Request $request)
    {
        $page = new Page([
            'type' => isset(self::TYPE_LABELS[$request->query('type')]) ? $request->query('type') : 'product',
            'is_published' => true,
            'redirect_code' => 301,
        ]);

        return $this->form($page);
    }

    public function store(Request $request)
    {
        $page = Page::create($this->validated($request));

        return redirect()->route('admin.pages.index')
            ->with('success', '"'.$page->title.'" was added. '.$page->sitemapStatus().'.');
    }

    public function edit(Page $page)
    {
        return $this->form($page);
    }

    public function update(Request $request, Page $page)
    {
        $page->update($this->validated($request, $page));

        return redirect()->route('admin.pages.index')
            ->with('success', '"'.$page->title.'" was saved. '.$page->sitemapStatus().'.');
    }

    public function destroy(Page $page)
    {
        $page->delete();

        return redirect()->route('admin.pages.index')
            ->with('success', '"'.$page->title.'" was deleted and removed from the sitemap.');
    }

    private function form(Page $page)
    {
        return view('admin.pages.form', [
            'page' => $page,
            'types' => self::TYPE_LABELS,
            'calculatorSlugs' => self::CALCULATOR_SLUGS,
        ]);
    }

    /**
     * Slug is managed automatically:
     * - left empty on a new page -> made from the title ("Car Loan" -> car-loan),
     *   with -2, -3... added if that URL is already taken
     * - left empty on an existing page -> the current slug is kept, so a live URL never changes by accident
     * - calculators -> picked from a list in the form
     */
    private function prepareSlug(Request $request, ?Page $page): void
    {
        $type = $request->input('type');
        $slug = Str::slug((string) $request->input('slug'));

        if ($slug === '' && $page && $page->type === $type) {
            $slug = $page->slug;
        }

        if ($slug === '' && $type !== 'calculator') {
            $base = Str::slug((string) $request->input('title')) ?: 'page';
            $slug = $base;

            for ($i = 2; $this->slugTaken($type, $slug, $page) || ($type === 'static' && in_array($slug, self::RESERVED_SLUGS)); $i++) {
                $slug = $base.'-'.$i;
            }
        }

        $request->merge(['slug' => $slug]);
    }

    private function slugTaken(?string $type, string $slug, ?Page $page): bool
    {
        return Page::where('type', $type)->where('slug', $slug)
            ->when($page, fn ($q) => $q->where('id', '!=', $page->id))
            ->exists();
    }

    private function validated(Request $request, ?Page $page = null): array
    {
        $this->prepareSlug($request, $page);
        $type = $request->input('type');

        $slugRules = [
            'required', 'max:100', 'regex:/^[a-z0-9]+(-[a-z0-9]+)*$/',
            // one URL per page: slug must be unique within its type
            Rule::unique('pages', 'slug')->where('type', $type)->ignore($page?->id),
        ];

        if ($type === 'calculator') {
            $slugRules[] = Rule::in(self::CALCULATOR_SLUGS);
        }

        if ($type === 'static') {
            $slugRules[] = Rule::notIn(self::RESERVED_SLUGS);
        }

        // a path on this site (/products/x) or a full URL
        $urlRule = ['nullable', 'max:255', 'regex:#^(/|https?://)#'];

        $data = $request->validate([
            'type' => ['required', Rule::in(array_keys(self::TYPE_LABELS))],
            'title' => 'required|string|max:150',
            'slug' => $slugRules,
            'category' => 'nullable|string|max:100',
            'summary' => 'nullable|string|max:500',
            'content' => 'nullable|string|max:65000',
            'canonical_url' => $urlRule,
            'redirect_url' => $urlRule,
            'redirect_code' => 'required|in:301,302',
        ], [
            'slug.required' => 'Please pick which calculator this page is for.',
            'slug.regex' => 'Use lowercase letters, numbers and dashes only, e.g. car-loan.',
            'slug.unique' => 'Another page of this type already uses this slug.',
            'slug.in' => 'Calculator pages must be one of: '.implode(', ', self::CALCULATOR_SLUGS).'.',
            'slug.not_in' => 'This slug is used by the site itself. Please pick another.',
            'canonical_url.regex' => 'Start with / (e.g. /products/home-loan) or https://',
            'redirect_url.regex' => 'Start with / (e.g. /products/home-loan) or https://',
        ]);

        $data['is_published'] = $request->boolean('is_published');
        $data['noindex'] = $request->boolean('noindex');
        $data['content'] = $this->cleanHtml($data['content'] ?? null);

        return $data;
    }

    // Basic HTML clean-up so page content can't run scripts on the site.
    private function cleanHtml(?string $html): ?string
    {
        if ($html === null) {
            return null;
        }

        $html = strip_tags($html, '<p><br><a><strong><b><em><i><ul><ol><li><h2><h3><h4><table><thead><tbody><tr><th><td><img><blockquote>');
        $html = preg_replace('/\s+on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html);

        return preg_replace('/(href|src)\s*=\s*(["\']?)\s*javascript:[^"\'>]*\2/i', '$1="#"', $html);
    }
}

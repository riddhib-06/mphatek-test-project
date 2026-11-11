<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    public const TYPES = ['product', 'blog', 'branch', 'static', 'calculator'];

    protected $fillable = [
        'type', 'title', 'slug', 'category', 'summary', 'content',
        'is_published', 'noindex', 'canonical_url', 'redirect_url', 'redirect_code',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'noindex' => 'boolean',
    ];

    public function url(): string
    {
        return match ($this->type) {
            'product' => url('products/'.$this->slug),
            'blog' => url('blog/'.$this->slug),
            'branch' => url('branches/'.$this->slug),
            'calculator' => url('calculators/'.$this->slug),
            default => $this->slug === 'home' ? url('/') : url($this->slug),
        };
    }

    // Pages that should not appear in the sitemap are filtered out here:
    // unpublished (404), redirected and noindex pages.
    public function scopeIndexable(Builder $query): void
    {
        $query->where('is_published', true)
            ->where('noindex', false)
            ->whereNull('redirect_url');
    }

    // Full canonical URL. canonical_url can be a path (/products/x) or a full URL.
    public function canonical(): string
    {
        if (empty($this->canonical_url)) {
            return $this->url();
        }

        return str_starts_with($this->canonical_url, '/') ? url($this->canonical_url) : $this->canonical_url;
    }

    // Why a page is (or isn't) in the sitemap - shown in the admin page list.
    // Same rules as scopeIndexable() + isCanonical().
    public function sitemapStatus(): string
    {
        $reason = $this->sitemapExclusion();

        return $reason ? 'Not in sitemap: '.lcfirst($reason) : 'In sitemap';
    }

    // Short reason why the page is left out of the sitemap, or null if it is included.
    public function sitemapExclusion(): ?string
    {
        return match (true) {
            ! $this->is_published => 'Unpublished (404)',
            (bool) $this->redirect_url => $this->redirect_code.' redirect',
            $this->noindex => 'Noindex',
            ! $this->isCanonical() => 'Duplicate (canonical)',
            default => null,
        };
    }

    // A page whose canonical points somewhere else is a duplicate.
    public function isCanonical(): bool
    {
        return rtrim($this->canonical(), '/') === rtrim($this->url(), '/');
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CaptureLeadSource
{
    /**
     * Remembers where the visitor came from so it can be saved with the lead.
     * UTM tags always win. Without them the first outside referrer is used,
     * otherwise the visit is counted as direct.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethod('get') || $request->ajax()) {
            return $next($request);
        }

        $session = $request->session();

        if ($request->filled('utm_source')) {
            $session->put('lead_source', [
                'source' => $this->tag($request->query('utm_source')),
                'medium' => $this->tag($request->query('utm_medium')),
                'campaign' => $this->tag($request->query('utm_campaign')),
                'referrer' => $this->cut($request->headers->get('referer')),
                'landing_page' => $this->cut($request->fullUrl()),
            ]);
        } elseif (! $session->has('lead_source')) {
            $referrer = $request->headers->get('referer');
            $host = $referrer ? parse_url($referrer, PHP_URL_HOST) : null;
            $isExternal = $host && $host !== $request->getHost();

            $session->put('lead_source', [
                'source' => $isExternal ? $this->tag($host) : 'direct',
                'medium' => $isExternal ? 'referral' : null,
                'campaign' => null,
                'referrer' => $isExternal ? $this->cut($referrer) : null,
                'landing_page' => $this->cut($request->fullUrl()),
            ]);
        }

        return $next($request);
    }

    // "  Facebook " and "facebook" are the same source. Max 100 chars so it always fits the column.
    private function tag(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        return mb_substr(mb_strtolower(trim($value)), 0, 100);
    }

    // URLs are stored in 255-char columns
    private function cut(?string $value): ?string
    {
        return $value === null ? null : mb_substr($value, 0, 255);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CanonicalUrl
{
    /**
     * Every page has exactly one URL: lowercase, no trailing slash.
     * /products/HOME-LOAN or /products/home-loan/ get a 301 to /products/home-loan
     * (query string such as utm tags is kept).
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethod('get')) {
            return $next($request);
        }

        $path = $request->getPathInfo();
        $clean = rtrim(strtolower($path), '/') ?: '/';

        if ($clean !== $path) {
            $query = $request->getQueryString();

            return redirect()->to($clean.($query ? '?'.$query : ''), 301);
        }

        return $next($request);
    }
}

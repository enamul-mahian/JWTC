<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EmailStudioResponseHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $response->headers->set('Cache-Control', 'private, no-store, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'no-referrer');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        // No remote scripts/assets, no XHR, and no framing by other sites.
        // JS for WYSIWYG is served as an owned, versioned static asset.
        $response->headers->set('Content-Security-Policy',
            "default-src 'none'; script-src 'self'; style-src 'self' 'unsafe-inline'; " .
            "img-src 'self' blob: data:; font-src 'self'; frame-src 'self' blob:; " .
            "form-action 'self'; base-uri 'none'; frame-ancestors 'none'; " .
            "connect-src 'none'; object-src 'none'"
        );
        return $response;
    }
}

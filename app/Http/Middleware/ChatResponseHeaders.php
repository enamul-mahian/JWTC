<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ChatResponseHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $response->headers->set('Cache-Control', 'private, no-store, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Referrer-Policy', 'no-referrer');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Content-Security-Policy',
            "default-src 'none'; style-src 'self' 'unsafe-inline'; " .
            "script-src 'self'; connect-src 'self'; img-src 'self' data:; " .
            "form-action 'self'; base-uri 'none'; frame-ancestors 'none'; object-src 'none'");
        return $response;
    }
}

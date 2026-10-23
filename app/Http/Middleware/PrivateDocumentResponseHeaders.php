<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PrivateDocumentResponseHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        // Applies only to the private document web UI and downloads. No third-
        // party scripts or remote document previews are permitted on this page.
        $response->headers->set('Cache-Control', 'private, no-store, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Referrer-Policy', 'no-referrer');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $html = str_starts_with(strtolower($response->headers->get('Content-Type', '')), 'text/html');
        // Only our own UI assets may load in HTML workspaces. Downloads keep
        // their original deny-by-default policy and cannot execute scripts.
        $policy = $html
            ? "default-src 'none'; style-src 'self' 'unsafe-inline'; script-src 'self'; font-src 'self'; img-src 'self' data:; form-action 'self'; base-uri 'none'; frame-ancestors 'none'; object-src 'none'"
            : "default-src 'none'; style-src 'unsafe-inline'; img-src 'self' data:; form-action 'self'; base-uri 'none'; frame-ancestors 'none'";
        $response->headers->set('Content-Security-Policy', $policy);


        return $response;
    }
}

<?php

namespace App\Http\Middleware;

use App\Enum\UserType;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminuserMiddleware
{
    /**
     * Legacy databases can contain "Admin", while newer API accounts use
     * UserType::ADMIN ("admin"). Reject non-admin roles consistently.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $type = strtolower((string) ($user?->user_type ?? ''));

        abort_unless($user && in_array($type, [
            UserType::ADMIN,
            UserType::SUPER_ADMIN,
        ], true), 403);

        return $next($request);
    }
}

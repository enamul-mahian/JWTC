<?php
namespace App\Http\Middleware;
use App\Enum\UserType;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class RequireApprovedEmployer
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user && $request->is('api/*') && $request->bearerToken()) $user = Auth::guard('sanctum')->user();
        if (strtolower((string)$user?->user_type) !== UserType::EMPLOYER) return $next($request);
        // Only the holding page and logout are available while pending/revoked.
        if ($request->routeIs('company.approval', 'logout') || $request->is('api/logout')) return $next($request);
        if (!$user->employer_approved_at || (int) $user->status !== 1) {
            if ($request->expectsJson() || $request->is('api/*')) return response()->json(['message' => 'Company account requires administrator approval.', 'approval_required' => true], 403);
            return redirect()->route('company.approval');
        }
        return $next($request);
    }
}

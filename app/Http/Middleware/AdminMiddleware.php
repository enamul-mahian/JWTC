<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            if ($user->type === 'employee') {
                return redirect('/employee/dashboard');
            } elseif ($user->type === 'job_seeker') {
                return redirect('/job_seeker/dashboard');
            } elseif ($user->type === 'admin') {
                return redirect('/admin/dashboard');
            }
        }
        return $next($request);
    }
}
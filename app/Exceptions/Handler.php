<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Response;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'passport_number', 'hcaptcha_secret',
        'current_password',
        'password',
        'password_confirmation',
        'otp',
        'h-captcha-response',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function render($request, Throwable $exception)
    {
        // An expired browser tab can submit an old CSRF token when the user
        // clicks Sign out. Never bypass CSRF verification for state-changing
        // requests: offer a fresh confirmation form with a new session token.
        if ($exception instanceof TokenMismatchException &&
            $request->isMethod('POST') && $request->is('logout') &&
            !$request->expectsJson()) {
            return redirect()->route('logout.confirm');
        }
        // API/Sanctum callers must receive JSON, not a missing HTML error view.
        // Some production deployments use custom templates which are not
        // present in the tracked source; fall back to Laravel's safe default.
        if (!$request->expectsJson() && $this->isHttpException($exception)) {
            $code = $exception->getStatusCode();
            if (in_array($code, [404, 500], true) &&
                \Illuminate\Support\Facades\View::exists('errors.' . $code)) {
                return response()->view('errors.' . $code, [], $code);
            }
        }

        return parent::render($request, $exception);
    }
}

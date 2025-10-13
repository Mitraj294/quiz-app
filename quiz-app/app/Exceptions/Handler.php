<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $_) {
            //
        });
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        // If this is an Inertia request, render the login page directly so the client
        // receives a proper Inertia response and displays the UI instead of a plain JSON message.
        if ($request->header('X-Inertia')) {
            // Match props returned by the AuthenticatedSessionController::create
            $props = [
                'canResetPassword' => \Illuminate\Support\Facades\Route::has('password.request'),
                'status' => session('status'),
            ];

            // Return an Inertia response for the login page. Some middleware may set an
            // X-Inertia-Location header which causes a 409; remove it and force 200 so
            // the client receives a valid Inertia JSON payload to render the UI.
            $response = Inertia::render('Auth/Login', $props)->toResponse($request);

            if ($response->headers->has('X-Inertia-Location')) {
                $response->headers->remove('X-Inertia-Location');
            }

            $response->setStatusCode(200);

            return $response;
        }

        // If the client expects JSON (but not Inertia), return the standard JSON response.
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Please log in.'], 401);
        }

        return redirect()->guest(route('login'));
    }
}

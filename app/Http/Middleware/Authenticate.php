<?php

namespace App\Http\Middleware;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    // Login page for each guard in config/auth.php
    private const LOGIN_ROUTES = [
        'web' => 'employee.login',
        'faculty' => 'faculty.login',
        'kioskstudent' => 'student.login',
    ];

    /**
     * Send guests to the login page of the guard the route requires (e.g. auth:web => employee login).
     */
    protected function unauthenticated($request, array $guards)
    {
        $guard = $guards[0] ?? config('auth.defaults.guard');

        if (!$request->expectsJson()) {
            // Shown as a toastr notice on the login page (master_main)
            $request->session()->flash('error', 'Your session has ended. Please sign in again.');
        }

        throw new AuthenticationException(
            'Unauthenticated.',
            $guards,
            $request->expectsJson() ? null : route(self::LOGIN_ROUTES[$guard] ?? 'home')
        );
    }

    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : route('home');
    }
}

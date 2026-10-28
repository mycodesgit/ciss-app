<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    // Where a logged-in user of each guard belongs (guards without a dashboard yet go home)
    private const HOME_ROUTES = [
        'web' => 'employee.dashboard',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $guard = $guard ?? config('auth.defaults.guard');

                return redirect()->route(self::HOME_ROUTES[$guard] ?? 'home');
            }
        }

        return $next($request);
    }
}

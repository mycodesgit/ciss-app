<?php

namespace App\Http\Middleware;

use App\Http\Middleware\Concerns\DeniesAccessWithModal;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * A Scheduling page: only the roles / users of config ciss.scheduling.pages.{page} (User::canOpenSchedulingPage).
 * Usage: ->middleware('scheduling:rooms')
 */
class EnsureSchedulingPage
{
    use DeniesAccessWithModal;

    public function handle(Request $request, Closure $next, string $page): Response
    {
        if (!Auth::guard('web')->user()?->canOpenSchedulingPage($page)) {
            return $this->denyAccess($request, 'this page', 'Your account has not been given access to this Scheduling page.');
        }

        return $next($request);
    }
}

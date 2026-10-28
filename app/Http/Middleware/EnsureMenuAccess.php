<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks an employee module unless the user may open its menu (see User::canAccessMenu).
 * Usage: ->middleware('menu.access:setting-url')
 */
class EnsureMenuAccess
{
    public function handle(Request $request, Closure $next, string $menuKey): Response
    {
        abort_unless(Auth::guard('web')->user()?->canAccessMenu($menuKey), 403, 'You do not have access to this module.');

        return $next($request);
    }
}

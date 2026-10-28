<?php

namespace App\Http\Middleware;

use App\Http\Middleware\Concerns\DeniesAccessWithModal;
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
    use DeniesAccessWithModal;

    public function handle(Request $request, Closure $next, string $menuKey): Response
    {
        if (!Auth::guard('web')->user()?->canAccessMenu($menuKey)) {
            $module = config("ciss.employee_menu.{$menuKey}.label", 'this module');

            return $this->denyAccess($request, $module, 'Your account has not been given access to this module.');
        }

        return $next($request);
    }
}

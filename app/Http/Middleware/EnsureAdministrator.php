<?php

namespace App\Http\Middleware;

use App\Http\Middleware\Concerns\DeniesAccessWithModal;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Only administrators (role 0) may continue. Usage: ->middleware('admin')
 */
class EnsureAdministrator
{
    use DeniesAccessWithModal;

    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('web')->user()?->isAdministrator()) {
            return $this->denyAccess($request, 'this page', 'This page is only available to administrators.');
        }

        return $next($request);
    }
}

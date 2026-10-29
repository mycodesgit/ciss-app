<?php

namespace App\Http\Middleware;

use App\Http\Middleware\Concerns\DeniesAccessWithModal;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Only Administrators, the Registrar and Guidance (User::canManageStudentInfo) may continue.
 * Usage: ->middleware('student-info')
 */
class EnsureStudentInfoAccess
{
    use DeniesAccessWithModal;

    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('web')->user()?->canManageStudentInfo()) {
            return $this->denyAccess($request, 'this page', 'This page is only available to Administrators, the Registrar and Guidance.');
        }

        return $next($request);
    }
}

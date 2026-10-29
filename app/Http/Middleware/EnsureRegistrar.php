<?php

namespace App\Http\Middleware;

use App\Http\Middleware\Concerns\DeniesAccessWithModal;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Only the Registrar and Administrators (User::canCorrectGrades) may continue.
 * Usage: ->middleware('registrar')
 */
class EnsureRegistrar
{
    use DeniesAccessWithModal;

    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('web')->user()?->canCorrectGrades()) {
            return $this->denyAccess($request, 'Correction of Grades', 'This page is only available to the Registrar and Administrators.');
        }

        return $next($request);
    }
}

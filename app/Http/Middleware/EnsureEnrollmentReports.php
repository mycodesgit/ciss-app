<?php

namespace App\Http\Middleware;

use App\Http\Middleware\Concerns\DeniesAccessWithModal;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Enrollment reports: everyone but the Encoder (User::canViewEnrollmentReports) may continue.
 * Usage: ->middleware('enrollment-reports')
 */
class EnsureEnrollmentReports
{
    use DeniesAccessWithModal;

    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('web')->user()?->canViewEnrollmentReports()) {
            return $this->denyAccess($request, 'this page', 'This report is not available to Encoders.');
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use App\Http\Middleware\Concerns\DeniesAccessWithModal;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Only the roles of User::REPORT_CARD_ROLES (User::canPrintReportCards) may continue.
 * Usage: ->middleware('report-card')
 */
class EnsureReportCardAccess
{
    use DeniesAccessWithModal;

    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('web')->user()?->canPrintReportCards()) {
            return $this->denyAccess($request, 'this page', 'The Student Report Card is only available to Administrators, the Registrar, MIS and the Dean of Instruction.');
        }

        return $next($request);
    }
}

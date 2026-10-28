<?php

namespace App\Http\Middleware\Concerns;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Instead of Laravel's 403 page, send the user back to the page they came from
 * (or the dashboard) and flash "access_denied" so the employee layout opens a modal.
 */
trait DeniesAccessWithModal
{
    protected function denyAccess(Request $request, string $module, string $reason): Response
    {
        // AJAX / JSON callers (DataTables, forms) get a normal 403 with the message
        if ($request->expectsJson()) {
            abort(403, $reason);
        }

        // Back to the previous employee page (it has the modal); otherwise the dashboard
        $previous = url()->previous();
        $isEmployeePage = str_starts_with($previous, url('employee') . '/') && $previous !== $request->fullUrl();
        $target = $isEmployeePage ? $previous : route('employee.dashboard');

        return redirect()->to($target)->with('access_denied', [
            'module' => $module,
            'reason' => $reason,
        ]);
    }
}

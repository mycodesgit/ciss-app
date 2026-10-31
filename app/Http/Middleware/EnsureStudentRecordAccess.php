<?php

namespace App\Http\Middleware;

use App\Http\Middleware\Concerns\DeniesAccessWithModal;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Only the roles of User::STUDENT_RECORD_ROLES (User::canViewStudentRecords) may continue.
 * Used by Student Record and Gradesheet Logbook. Usage: ->middleware('student-record')
 */
class EnsureStudentRecordAccess
{
    use DeniesAccessWithModal;

    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('web')->user()?->canViewStudentRecords()) {
            return $this->denyAccess($request, 'this page', 'This page is only available to Administrators, the Registrar and Registrar Staff, College Staff, MIS, Graduate School Staff, Encoders and the Dean of Instruction.');
        }

        return $next($request);
    }
}

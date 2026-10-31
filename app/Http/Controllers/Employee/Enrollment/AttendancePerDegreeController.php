<?php

namespace App\Http\Controllers\Employee\Enrollment;

/**
 * Enrollment > Attendance Per Degree: the same page, search, lists and access as Student Per Degree
 * (StudentPerDegreeController), but its PDF has a Signature column so the printed list is signed by the students.
 */
class AttendancePerDegreeController extends StudentPerDegreeController
{
    protected string $routeName = 'employee.enrollment.attendance-per-degree';
    protected string $pageTitle = 'Attendance Per Degree';
    protected string $pageDescription = 'Printable attendance sheet of the enrolled students per program, year and section.';
    protected bool $withSignature = true;
}

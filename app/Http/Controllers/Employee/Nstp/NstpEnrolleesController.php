<?php

namespace App\Http\Controllers\Employee\Nstp;

use App\Http\Controllers\Employee\Enrollment\NstpEnrolleesController as EnrollmentNstpEnrolleesController;

/**
 * NSTP > CWTS/LTS/ROTC: the Enrollment page of the same name (students per component in a campus and term),
 * in the NSTP layout.
 */
class NstpEnrolleesController extends EnrollmentNstpEnrolleesController
{
    protected string $routeName = 'employee.nstp.enrollees';
    protected array $module = ['name' => 'NSTP', 'layout' => 'layouts.master_empnstp'];
}

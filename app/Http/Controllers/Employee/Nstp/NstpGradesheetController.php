<?php

namespace App\Http\Controllers\Employee\Nstp;

use App\Http\Controllers\Employee\Enrollment\GradesheetController;

use App\Models\ScheduleDB\SubjectOffered;
use App\Services\Enrollment\NstpEnrollees;

/**
 * NSTP > Grade Sheet: the Enrollment Gradesheet (encode, submit, unlock with the grades password) for the
 * NSTP subjects offered in the user's campus only (config ciss.nstp_components), in the NSTP layout.
 */
class NstpGradesheetController extends GradesheetController
{
    protected string $routeName = 'employee.nstp.gradesheet';
    protected array $module = ['name' => 'NSTP', 'layout' => 'layouts.master_empnstp', 'description' => 'Encode and view student grades in NSTP subjects.'];

    // NSTP subjects of the user's campus (for every role, Graduate School staff included)
    protected function allowedSubjects()
    {
        return SubjectOffered::join('subjects', 'sub_offered.subCode', '=', 'subjects.sub_code')
            ->whereIn('sub_offered.campus', $this->campuses())
            ->whereIn('sub_offered.subCode', collect(NstpEnrollees::components())->pluck('codes')->flatten()->all());
    }
}

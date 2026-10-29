<?php

namespace App\Http\Controllers\Employee\Admission;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Admission\Concerns\ConfiguresAdmission;

use App\Models\AdmissionDB\Year;

/**
 * Admission > Configure Admission: one page with a tab per setting. Each tab's table and forms are
 * served by its own controller in Employee/Admission/Config (public/js/employee/admission/config.js).
 */
class AdmissionConfigController extends Controller
{
    use ConfiguresAdmission;

    public function index()
    {
        return view('pages.employee.admission.configure.admsnconfig', [
            'years' => Year::orderByDesc('adyear')->pluck('adyear'),
            'currentYear' => Year::current(),
            'canManageYears' => $this->user()->canViewAllCampuses(),
        ]);
    }
}

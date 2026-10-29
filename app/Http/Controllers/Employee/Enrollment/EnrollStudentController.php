<?php

namespace App\Http\Controllers\Employee\Enrollment;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Enrollment\Concerns\ChecksEnrollment;

use Illuminate\Http\Request;

use App\Models\SettingDB\QueueCounter;
use App\Models\SettingDB\QueueCustomer;
use App\Models\SettingDB\QueueMode;

/**
 * Enrollment > Enroll Student, search page.
 *  - index():       Student ID + school year + semester, queue panel and pre-enrolled list
 *  - findStudent(): AJAX check of the search; returns the enrollment form's URL when the student can enroll
 * The form is EnrollmentFormController; the queue panel's actions are EnrollmentQueueController.
 * (public/js/employee/enrollment/enroll.js)
 */
class EnrollStudentController extends Controller
{
    use ChecksEnrollment;

    public function index()
    {
        $terms = $this->openTerms();
        $queueCounter = QueueCounter::where('useridlog', $this->user()->id)->first();

        return view('pages.employee.enrollment.studenroll.enroll', [
            'schoolYears' => $terms->pluck('schlyear')->unique()->values(),
            'semesters' => $terms->pluck('semester')->unique()->sort()->values(),
            'queueOn' => QueueMode::value('statusqueue') !== 'Off',
            'queueCounter' => $queueCounter,
            // Number the counter is serving now (shown in Current Serving)
            'servingNumber' => $queueCounter?->currentid ? QueueCustomer::whereKey($queueCounter->currentid)->value('queue_number') : null,
            'hasQueueCounter' => $this->user()->campus === 'MC', // only Main Campus has queue counters
        ]);
    }

    public function findStudent(Request $request)
    {
        [$student, $term] = $this->enrollableStudent($request);

        return response()->json([
            'url' => route('employee.enrollment.enroll.create', [
                'student' => $student->stud_id,
                'schlyear' => $term->schlyear,
                'semester' => $term->semester,
            ]),
        ]);
    }
}

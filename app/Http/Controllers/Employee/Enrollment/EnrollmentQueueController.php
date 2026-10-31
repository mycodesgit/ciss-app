<?php

namespace App\Http\Controllers\Employee\Enrollment;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

use App\Models\EnrollmentDB\StudEnrolmentHistory;
use App\Models\SettingDB\ConfigureCurrent;
use App\Models\SettingDB\QueueCounter;
use App\Models\SettingDB\QueueCustomer;

/**
 * Queue panel of Enroll Student (Main Campus): the logged-in user's own counter (counters.useridlog).
 *  - updateStatus():   Closed / Open / Hold
 *  - updateCategory(): the transaction the counter serves (Enrollment, Processing, ...)
 *  - next():           serve the next waiting number of that transaction
 *  - call():           call the number being served again (the queue monitor announces counters.callid)
 *  - preEnrolled():    List of Pre-Enrolled Students of the active term
 * (public/js/employee/enrollment/enroll.js)
 */
class EnrollmentQueueController extends Controller
{
    public const STATUSES = [1 => 'Closed', 2 => 'Open', 3 => 'Hold'];
    public const CATEGORIES = ['Enrollment', 'Processing', 'Pre-register', 'Evaluation', 'Printing'];

    // program_en_history.status shown in the List of Pre-Enrolled Students
    public const PRE_ENROLLED_STATUSES = [1 => 'For Checking', 3 => 'For Printing'];

    private const STATUS_OPEN = 2;

    public function updateStatus(Request $request)
    {
        $data = $request->validate(['counterstatus' => ['required', Rule::in(array_keys(self::STATUSES))]]);

        $counter = $this->counter();
        $counter->update(['counterstatus' => (int) $data['counterstatus']]);

        return $this->ok('Queue control is now ' . self::STATUSES[(int) $data['counterstatus']] . '.');
    }

    public function updateCategory(Request $request)
    {
        $data = $request->validate(['category' => ['required', Rule::in(self::CATEGORIES)]], [], ['category' => 'transaction']);

        $this->counter()->update(['category' => $data['category']]);

        return $this->ok('Transaction saved: ' . $data['category'] . '.');
    }

    // Take the oldest waiting number of the counter's transaction (campus) and serve it
    public function next()
    {
        $counter = $this->counter();

        if ((int) $counter->counterstatus !== self::STATUS_OPEN) {
            return $this->fail('Open the queue control first.');
        }

        // Locked so two counters pressing Next at the same time never get the same number
        $customer = DB::connection('settings')->transaction(function () use ($counter) {
            $customer = QueueCustomer::where('status', 'waiting')
                ->where('catname', $counter->category)
                ->where('campus', $this->user()->campus)
                ->orderBy('id')
                ->lockForUpdate()
                ->first();

            if ($customer) {
                // The number the window was serving is done (Queue > Dashboard counts served numbers)
                if ((int) $counter->currentid) {
                    QueueCustomer::whereKey((int) $counter->currentid)->where('status', 'serving')->update(['status' => 'served']);
                }

                // customers.counter_id holds the counter's window name (read that way by the queue screens)
                $customer->update(['status' => 'serving', 'counter_id' => $counter->windowname]);
                $counter->update(['activeidnumber' => $customer->id, 'currentid' => $customer->id]);
            }

            return $customer;
        });

        if (! $customer) {
            return $this->fail("No more waiting numbers for {$counter->category}.");
        }

        return response()->json(['success' => true, 'queue_number' => $customer->queue_number, 'message' => "Now serving {$customer->queue_number}."]);
    }

    // Call the number being served again: the monitor announces the one counter whose callid is set
    public function call()
    {
        $counter = $this->counter();
        $customer = $counter->activeidnumber ? QueueCustomer::find($counter->activeidnumber) : null;

        if (! $customer) {
            return $this->fail('No number to call. Press Next first.');
        }

        DB::connection('settings')->transaction(function () use ($counter, $customer) {
            QueueCounter::whereKeyNot($counter->id)->where('callid', '!=', 0)->update(['callid' => 0]);
            $counter->update(['callid' => $customer->id]);
            $customer->update(['status' => 'serving']);
        });

        return response()->json(['success' => true, 'queue_number' => $customer->queue_number, 'message' => "Calling {$customer->queue_number}."]);
    }

    // List of Pre-Enrolled Students: active term, user's campus, status 1 or 3, oldest first
    public function preEnrolled()
    {
        $term = ConfigureCurrent::active();

        if (! $term) {
            return response()->json(['data' => []]);
        }

        $rows = StudEnrolmentHistory::join('students', 'program_en_history.studentID', '=', 'students.stud_id')
            ->where('program_en_history.schlyear', $term->schlyear)
            ->where('program_en_history.semester', $term->semester)
            ->whereCampusIn('program_en_history.campus', $this->campuses())
            ->whereIn('program_en_history.status', array_keys(self::PRE_ENROLLED_STATUSES))
            ->orderBy('program_en_history.created_at')
            ->get([
                'program_en_history.id', 'program_en_history.studentID', 'program_en_history.campus',
                'program_en_history.course', 'program_en_history.status', 'program_en_history.created_at',
                'students.lname', 'students.fname', 'students.mname', 'students.ext',
            ]);

        return response()->json([
            'data' => $rows->map(fn ($row) => [
                'id' => $row->id,
                'date' => $row->created_at?->format('M d, Y h:i A'),
                'date_sort' => $row->created_at?->timestamp,
                'student_id' => $row->studentID,
                // First M. Last Ext
                'name' => trim(preg_replace('/\s+/', ' ', $row->fname . ' ' . ($row->mname ? mb_substr($row->mname, 0, 1) . '.' : '') . ' ' . $row->lname . ' ' . ($row->ext !== 'N/A' ? $row->ext : ''))),
                'course' => $row->course,
                'campus' => str_replace(' Campus', '', config('ciss.campuses')[$row->campus] ?? $row->campus),
                'status' => (int) $row->status,
                'status_label' => self::PRE_ENROLLED_STATUSES[(int) $row->status] ?? (string) $row->status,
                // Action: open the enrollment in Edit Enrollment
                'edit_url' => route('employee.enrollment.edit.show', ['student' => $row->studentID, 'schlyear' => $term->schlyear, 'semester' => $term->semester]),
            ]),
        ]);
    }

    // The user's queue counter (404 when none is assigned)
    private function counter(): QueueCounter
    {
        $counter = QueueCounter::where('useridlog', $this->user()->id)->first();
        abort_unless($counter, 404, 'You have no queue counter assigned.');

        return $counter;
    }

    private function user()
    {
        return Auth::guard('web')->user();
    }

    private function campuses(): array
    {
        return array_values(array_filter(array_map('trim', explode(',', (string) $this->user()->campus))));
    }

    private function ok(string $message)
    {
        return response()->json(['success' => true, 'message' => $message]);
    }

    private function fail(string $message)
    {
        return response()->json(['success' => false, 'message' => $message], 422);
    }
}

<?php

namespace App\Http\Controllers\Employee\Queue;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Enrollment\Concerns\ChecksEnrollment;
use App\Http\Controllers\Employee\Enrollment\Concerns\FindsEnrollment;
use App\Http\Controllers\Employee\Queue\Concerns\PicksQueueCampus;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\SettingDB\QueueCounter;
use App\Models\SettingDB\QueueCustomer;
use App\Models\SettingDB\QueueMode;
use App\Services\Queue\QueueBoard;

/**
 * Queue > Settings:
 *  - toggle(): Queueing On / Off (queuemode.statusqueue, all campuses: the same switch as Settings > Control Status;
 *              Off hides the queue panel of Enroll Student)
 *  - reset():  starts the campus's queue over: every number back to waiting, every window serving nothing.
 *              The old system reset every campus; this resets the picked campus only. (queue/settings.js)
 */
class QueueSettingController extends Controller
{
    use ChecksEnrollment, FindsEnrollment, PicksQueueCampus;

    public function index(Request $request)
    {
        $campus = $this->queueCampus($request);

        return view('pages.employee.queue.settings.index', $this->campusOptions($campus) + ['queueOn' => QueueBoard::isOn()]);
    }

    public function toggle(Request $request)
    {
        $on = $request->validate(['status' => ['required', 'boolean']])['status'];

        QueueMode::firstOrCreate([], ['statusqueue' => 'Off'])->update(['statusqueue' => $on ? 'On' : 'Off']);

        return response()->json(['success' => true, 'is_on' => (bool) $on, 'message' => 'Queueing is now ' . ($on ? 'On.' : 'Off.')]);
    }

    public function reset(Request $request)
    {
        $campus = $this->queueCampus($request);

        $numbers = DB::connection('settings')->transaction(function () use ($campus) {
            QueueCounter::where('campus', $campus)->update(['activeidnumber' => 0, 'currentid' => 0, 'callid' => 0]);

            return QueueCustomer::where('campus', $campus)->where('status', '!=', QueueBoard::STATUS_WAITING)
                ->update(['status' => QueueBoard::STATUS_WAITING]);
        });

        $campusName = str_replace(' Campus', '', config('ciss.campuses')[$campus] ?? $campus);

        return response()->json(['success' => true, 'message' => "Queue of {$campusName} reset: {$numbers} number(s) are waiting again."]);
    }
}

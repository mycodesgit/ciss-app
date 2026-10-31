<?php

namespace App\Http\Controllers\Employee\Queue;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Enrollment\Concerns\ChecksEnrollment;
use App\Http\Controllers\Employee\Enrollment\Concerns\FindsEnrollment;
use App\Http\Controllers\Employee\Queue\Concerns\PicksQueueCampus;

use Illuminate\Http\Request;

use App\Services\Queue\QueueBoard;

/**
 * Queue > Dashboard: the live state of a campus's queue (QueueBoard::snapshot): totals, every counter with its
 * personnel and the number it serves, and the next number of each category.
 *  - index(): the page (campus select, link to the public Queue Monitor)
 *  - data():  AJAX, the snapshot; polled every few seconds by queue/dashboard.js
 */
class QueueDashboardController extends Controller
{
    use ChecksEnrollment, FindsEnrollment, PicksQueueCampus;

    public function index(Request $request)
    {
        $campus = $this->queueCampus($request);

        return view('pages.employee.queue.index', $this->campusOptions($campus));
    }

    public function data(Request $request)
    {
        return response()->json(QueueBoard::snapshot($this->queueCampus($request)));
    }
}

<?php

namespace App\Http\Controllers\Main;

use App\Http\Controllers\Controller;

use App\Services\Queue\QueueBoard;

/**
 * The public Queue Monitor (TV screen at the Registrar), no login: /queueing/{campus}.
 *  - show(): the full-screen page; queue-monitor.js polls data() every 2 seconds and announces new numbers
 *            (chime + voice) after someone clicks the start button (browsers block sound until a click)
 *  - data(): the campus's snapshot without the personnel's names (QueueBoard::snapshot)
 */
class QueueMonitorController extends Controller
{
    public function show(string $campus = 'MC')
    {
        return view('pages.queue-monitor', [
            'campus' => $campus,
            'campusName' => config('ciss.campuses')[$campus],
        ]);
    }

    public function data(string $campus)
    {
        $snapshot = QueueBoard::snapshot($campus, false);

        return response()->json([
            'on' => $snapshot['on'],
            'counters' => $snapshot['counters']->map(fn ($counter) => [
                'window' => $counter['window'],
                'category' => $counter['category'],
                'status' => $counter['status'],
                'status_label' => $counter['status_label'],
                'serving' => $counter['serving'],
            ]),
            'nowServing' => $snapshot['nowServing'],
            'calling' => $snapshot['calling'],
            'nextInLine' => $snapshot['nextInLine'],
        ]);
    }
}

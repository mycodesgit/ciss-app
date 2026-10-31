<?php

namespace App\Http\Controllers\Employee\Queue;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Enrollment\Concerns\ChecksEnrollment;
use App\Http\Controllers\Employee\Enrollment\Concerns\FindsEnrollment;
use App\Http\Controllers\Employee\Enrollment\EnrollmentQueueController;
use App\Http\Controllers\Employee\Queue\Concerns\PicksQueueCampus;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

use App\Models\SettingDB\QueueCustomer;

/**
 * Queue > Numbers: the queue numbers (tickets) of a campus.
 *  - index():   the page (campus select, Generate form, summary, list)
 *  - summary(): AJAX, per category: how many numbers, waiting / serving / served, first and last number
 *  - list():    AJAX, the numbers of one category (DataTable; a category has a few thousand at most)
 *  - store():   generates Start..End of a category, e.g. 1..1000 of Enrollment -> ENRO-0001 .. ENRO-1000
 * queue_number is unique in the whole table, so numbers that already exist are skipped (and counted).
 * (queue/numbers.js)
 */
class QueueNumberController extends Controller
{
    use ChecksEnrollment, FindsEnrollment, PicksQueueCampus;

    private const MAX_PER_GENERATE = 5000;

    public function index(Request $request)
    {
        $campus = $this->queueCampus($request);

        return view('pages.employee.queue.numbers.index', $this->campusOptions($campus) + [
            'categories' => EnrollmentQueueController::CATEGORIES,
        ]);
    }

    public function summary(Request $request)
    {
        $rows = QueueCustomer::where('campus', $this->queueCampus($request))
            ->groupBy('catname')
            ->orderBy('catname')
            ->get([
                'catname',
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(status = 'waiting') as waiting"),
                DB::raw("SUM(status = 'serving') as serving"),
                DB::raw("SUM(status = 'served') as served"),
                DB::raw('MIN(queue_number) as first_number'),
                DB::raw('MAX(queue_number) as last_number'),
            ]);

        return response()->json([
            'data' => $rows->map(fn ($row) => [
                'category' => $row->catname,
                'total' => (int) $row->total,
                'waiting' => (int) $row->waiting,
                'serving' => (int) $row->serving,
                'served' => (int) $row->served,
                'range' => "{$row->first_number} - {$row->last_number}",
            ]),
        ]);
    }

    // Empty until a category is picked
    public function list(Request $request)
    {
        if (! $request->filled('category')) {
            return response()->json(['data' => []]);
        }

        $campus = $this->queueCampus($request);
        $category = $request->validate(['category' => ['required', Rule::in(EnrollmentQueueController::CATEGORIES)]])['category'];

        return response()->json([
            'data' => QueueCustomer::where('campus', $campus)->where('catname', $category)->orderBy('id')
                ->get(['id', 'queue_number', 'status', 'counter_id', 'updated_at'])
                ->map(fn ($number) => [
                    'id' => $number->id,
                    'queue_number' => $number->queue_number,
                    'status' => $number->status,
                    'window' => $number->status === 'waiting' ? null : $number->counter_id,
                    'updated_at' => $number->status === 'waiting' ? null : $number->updated_at?->format('M d, Y h:i A'),
                ]),
        ]);
    }

    public function store(Request $request)
    {
        $campus = $this->queueCampus($request);
        $data = $request->validate([
            'catname' => ['required', Rule::in(EnrollmentQueueController::CATEGORIES)],
            'start' => ['required', 'integer', 'min:1', 'max:9999'],
            'end' => ['required', 'integer', 'min:1', 'max:9999', 'gte:start', 'lte:' . ((int) $request->input('start') + self::MAX_PER_GENERATE - 1)],
        ], ['end.lte' => 'Generate at most ' . self::MAX_PER_GENERATE . ' numbers at a time.'], ['catname' => 'category']);

        $prefix = self::prefix($data['catname'], $campus);
        $now = now();
        $rows = collect(range((int) $data['start'], (int) $data['end']))->map(fn ($i) => [
            'queue_number' => sprintf('%s%04d', $prefix, $i),
            'catname' => $data['catname'],
            'status' => 'waiting',
            'campus' => $campus,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // insertOrIgnore skips the numbers that already exist (queue_number is unique)
        $added = $rows->chunk(500)->sum(fn ($chunk) => DB::connection('settings')->table('customers')->insertOrIgnore($chunk->values()->all()));
        $skipped = $rows->count() - $added;

        return response()->json([
            'success' => true,
            'message' => "{$added} number(s) added ({$prefix}" . sprintf('%04d', $data['start']) . " - {$prefix}" . sprintf('%04d', $data['end']) . ')'
                . ($skipped ? ", {$skipped} already existed." : '.'),
        ]);
    }

    // The old system's prefixes: ENRO-0001, PRE-0001, MCP-0001 (Processing, per campus), ...
    public static function prefix(string $category, string $campus): string
    {
        return [
            'Processing' => strtoupper($campus) . 'P-',
            'Pre-register' => 'PRE-',
            'Enrollment' => 'ENRO-',
            'Printing' => 'PRINT-',
            'Evaluation' => 'EVAL-',
        ][$category] ?? strtoupper($campus) . '-';
    }
}

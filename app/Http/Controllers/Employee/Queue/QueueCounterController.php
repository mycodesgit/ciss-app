<?php

namespace App\Http\Controllers\Employee\Queue;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Enrollment\Concerns\ChecksEnrollment;
use App\Http\Controllers\Employee\Enrollment\Concerns\FindsEnrollment;
use App\Http\Controllers\Employee\Enrollment\EnrollmentQueueController;
use App\Http\Controllers\Employee\Queue\Concerns\PicksQueueCampus;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

use App\Models\AdmissionDB\User;
use App\Models\SettingDB\QueueCounter;

/**
 * Queue > Counters: the windows of a campus, each with its personnel and the transaction (category) it serves.
 *  - index() / list(): the page and its table
 *  - store() / update(): add a window / change its personnel or category; destroy(): remove it
 *  - status(): Closed / Open / Hold (the personnel can also change it in Enroll Student's queue panel)
 * A personnel member has at most one counter (Enroll Student finds "my counter" by counters.useridlog).
 * Personnel: Registrar Office and Graduate School Registrar staff of the campus. (queue/counters.js)
 */
class QueueCounterController extends Controller
{
    use ChecksEnrollment, FindsEnrollment, PicksQueueCampus;

    // users.dept of the staff that can be given a counter (config ciss.departments spells the second one "Registar")
    private const PERSONNEL_DEPARTMENTS = ['Registrar Office', 'Graduate School Registrar', 'Graduate School Registar'];

    public function index(Request $request)
    {
        $campus = $this->queueCampus($request);

        return view('pages.employee.queue.counters.index', $this->campusOptions($campus) + [
            'categories' => EnrollmentQueueController::CATEGORIES,
            'statuses' => EnrollmentQueueController::STATUSES,
        ]);
    }

    public function list(Request $request)
    {
        $campus = $this->queueCampus($request);
        $counters = QueueCounter::where('campus', $campus)->orderBy('windowname')->get();
        $staff = User::whereIn('id', $counters->pluck('useridlog')->filter()->unique()->values()->all() ?: [0])->get(['id', 'fname', 'lname'])->keyBy('id');

        return response()->json([
            'data' => $counters->map(fn (QueueCounter $counter) => [
                'id' => $counter->id,
                'windowname' => $counter->windowname,
                'category' => $counter->category,
                'useridlog' => $counter->useridlog,
                'staff' => isset($staff[$counter->useridlog]) ? trim($staff[$counter->useridlog]->fname . ' ' . $staff[$counter->useridlog]->lname) : null,
                'counterstatus' => (int) $counter->counterstatus,
                'status_label' => EnrollmentQueueController::STATUSES[(int) $counter->counterstatus] ?? 'Closed',
            ]),
            // The personnel select of the add / edit modals (the campus can change without a page reload)
            'personnel' => $this->personnel($campus),
        ]);
    }

    public function store(Request $request)
    {
        $campus = $this->queueCampus($request);
        $data = $this->validated($request, $campus);

        if (QueueCounter::where('campus', $campus)->where('windowname', $data['windowname'])->exists()) {
            throw ValidationException::withMessages(['windowname' => "Window {$data['windowname']} already exists in this campus."]);
        }
        $this->ensureFreePersonnel((int) $data['useridlog']);

        QueueCounter::create($data + ['campus' => $campus, 'counterstatus' => 1]);

        return response()->json(['success' => true, 'message' => "Window {$data['windowname']} added."]);
    }

    public function update(Request $request, QueueCounter $counter)
    {
        $this->authorizeCounter($counter);
        $data = $this->validated($request, $counter->campus, false);
        $this->ensureFreePersonnel((int) $data['useridlog'], $counter->id);

        $counter->update($data);

        return response()->json(['success' => true, 'message' => "Window {$counter->windowname} saved."]);
    }

    public function status(Request $request, QueueCounter $counter)
    {
        $this->authorizeCounter($counter);
        $status = (int) $request->validate(['counterstatus' => ['required', Rule::in(array_keys(EnrollmentQueueController::STATUSES))]])['counterstatus'];

        $counter->update(['counterstatus' => $status]);

        return response()->json(['success' => true, 'message' => "Window {$counter->windowname} is now " . EnrollmentQueueController::STATUSES[$status] . '.']);
    }

    public function destroy(QueueCounter $counter)
    {
        $this->authorizeCounter($counter);
        $counter->delete();

        return response()->json(['success' => true, 'message' => "Window {$counter->windowname} removed."]);
    }

    // ---------- Helpers ----------

    private function validated(Request $request, string $campus, bool $withWindow = true): array
    {
        return $request->validate(array_filter([
            'windowname' => $withWindow ? ['required', 'integer', 'min:1', 'max:999'] : null,
            'useridlog' => ['required', 'integer', Rule::in($this->personnel($campus)->pluck('id')->all())],
            'category' => ['required', Rule::in(EnrollmentQueueController::CATEGORIES)],
        ]), ['useridlog.in' => 'Pick a Registrar personnel of this campus.'], ['windowname' => 'window number', 'useridlog' => 'personnel']);
    }

    private function ensureFreePersonnel(int $userId, ?int $exceptCounter = null): void
    {
        $other = QueueCounter::where('useridlog', $userId)->when($exceptCounter, fn ($query) => $query->whereKeyNot($exceptCounter))->first();

        if ($other) {
            throw ValidationException::withMessages(['useridlog' => "This personnel already has window {$other->windowname} ({$other->campus})."]);
        }
    }

    private function personnel(string $campus)
    {
        return User::whereIn('dept', self::PERSONNEL_DEPARTMENTS)
            ->whereCampusIn('campus', [$campus])
            ->orderBy('lname')->orderBy('fname')
            ->get(['id', 'fname', 'lname'])
            ->map(fn (User $user) => ['id' => $user->id, 'name' => trim($user->lname . ', ' . $user->fname)])
            ->values();
    }

    // Only counters of a campus the user may manage
    private function authorizeCounter(QueueCounter $counter): void
    {
        abort_unless(array_key_exists($counter->campus, $this->searchableCampuses()), 404, 'Counter not found in your campus.');
    }
}

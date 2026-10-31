<?php

namespace App\Services\Queue;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Employee\Enrollment\EnrollmentQueueController;
use App\Models\AdmissionDB\User;
use App\Models\SettingDB\QueueCounter;
use App\Models\SettingDB\QueueCustomer;
use App\Models\SettingDB\QueueMode;

/**
 * The live state of a campus's queue, for Queue > Dashboard and the public Queue Monitor.
 *
 * Tables (settings connection): counters = the windows, customers = the queue numbers.
 *  - counters.currentid: the number the window is serving (set by Next in Enroll Student)
 *  - counters.callid:    the number being called again (Call); one counter at a time, the monitor announces it
 *  - customers.counter_id holds the counter's window number (windowname), not counters.id
 *  - customers.status: waiting -> serving (Next) -> served (the counter's next Next)
 */
class QueueBoard
{
    public const STATUS_WAITING = 'waiting';
    public const STATUS_SERVING = 'serving';
    public const STATUS_SERVED = 'served';

    public static function isOn(): bool
    {
        return QueueMode::value('statusqueue') !== 'Off';
    }

    /**
     * @param bool $withStaff include the personnel of each counter (employees only, never the public monitor)
     */
    public static function snapshot(string $campus, bool $withStaff = true): array
    {
        $counters = QueueCounter::where('campus', $campus)->orderBy('windowname')->get();
        $numbers = QueueCustomer::whereIn('id', $counters->pluck('currentid')->merge($counters->pluck('callid'))->filter()->map(fn ($id) => (int) $id)->unique()->values()->all() ?: [0])
            ->pluck('queue_number', 'id');
        $staff = $withStaff
            ? User::whereIn('id', $counters->pluck('useridlog')->filter()->unique()->values()->all() ?: [0])->get(['id', 'fname', 'lname'])->keyBy('id')
            : collect();

        // Numbers each window took today (served, or serving now)
        $handledToday = QueueCustomer::where('campus', $campus)
            ->whereIn('status', [self::STATUS_SERVING, self::STATUS_SERVED])
            ->whereDate('updated_at', today())
            ->groupBy('counter_id')
            ->pluck(DB::raw('COUNT(*)'), 'counter_id');

        $rows = $counters->map(function (QueueCounter $counter) use ($numbers, $staff, $handledToday, $withStaff) {
            $status = (int) $counter->counterstatus;

            return [
                'id' => $counter->id,
                'window' => $counter->windowname,
                'category' => $counter->category,
                'status' => $status,
                'status_label' => EnrollmentQueueController::STATUSES[$status] ?? 'Closed',
                'serving' => $numbers[(int) $counter->currentid] ?? null,
                'calling' => (int) $counter->callid ? ($numbers[(int) $counter->callid] ?? null) : null,
                'handled_today' => (int) ($handledToday[$counter->windowname] ?? 0),
                'updated_at' => $counter->updated_at?->timestamp,
            ] + ($withStaff ? ['staff' => self::staffName($staff[$counter->useridlog] ?? null)] : []);
        });

        $nowServing = $rows->filter(fn ($row) => $row['serving'])->sortByDesc('updated_at')->first();
        $calling = $rows->first(fn ($row) => $row['calling']);

        return [
            'on' => self::isOn(),
            'counters' => $rows->values(),
            'nowServing' => $nowServing ? ['window' => $nowServing['window'], 'number' => $nowServing['serving']] : null,
            'calling' => $calling ? ['window' => $calling['window'], 'number' => $calling['calling']] : null,
            'nextInLine' => self::nextInLine($campus),
            'totals' => [
                'waiting' => QueueCustomer::where('campus', $campus)->where('status', self::STATUS_WAITING)->count(),
                'serving' => $rows->filter(fn ($row) => $row['serving'])->count(),
                'handledToday' => (int) $handledToday->sum(),
                'open' => $rows->where('status', 2)->count(),
            ],
            'time' => now()->format('h:i:s A'),
        ];
    }

    // The next waiting number and how many wait, per category (Enrollment, Processing, ...)
    private static function nextInLine(string $campus): Collection
    {
        $waiting = QueueCustomer::where('campus', $campus)
            ->where('status', self::STATUS_WAITING)
            ->groupBy('catname')
            ->get(['catname', DB::raw('MIN(id) as next_id'), DB::raw('COUNT(*) as waiting')]);

        $numbers = QueueCustomer::whereIn('id', $waiting->pluck('next_id')->all() ?: [0])->pluck('queue_number', 'id');

        return $waiting->map(fn ($row) => [
            'category' => $row->catname,
            'number' => $numbers[$row->next_id] ?? null,
            'waiting' => (int) $row->waiting,
        ])->sortBy('category')->values();
    }

    private static function staffName(?User $user): ?string
    {
        return $user ? trim($user->fname . ' ' . $user->lname) : null;
    }
}

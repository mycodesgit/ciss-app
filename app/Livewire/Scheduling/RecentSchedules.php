<?php

namespace App\Livewire\Scheduling;

use Livewire\Component;

use Illuminate\Support\Facades\Auth;

use App\Models\ScheduleDB\SetClassSchedule;
use App\Models\SettingDB\ConfigureCurrent;
use App\Support\CampusList;

/**
 * Scheduling > Dashboard: the latest class schedules plotted in a campus in the active term. Polls every
 * 60 seconds while visible; administrators can switch campus. Livewire's update requests do not run the route's
 * menu.access middleware, so boot() checks the module access on every request, and the campus is checked
 * whenever it changes (a Livewire property can be set by the browser).
 */
class RecentSchedules extends Component
{
    private const LIMIT = 8;

    public string $campus = '';

    public function boot(): void
    {
        abort_unless(Auth::guard('web')->user()?->canAccessMenu('scheduler-url'), 403);
    }

    public function mount(): void
    {
        $this->campus = CampusList::split(Auth::guard('web')->user()->campus)[0] ?? '';
    }

    public function updatedCampus(): void
    {
        $this->validate(['campus' => ['required', 'in:' . implode(',', array_keys($this->campuses()))]]);
    }

    public function render()
    {
        $term = ConfigureCurrent::active();
        $campuses = $this->campuses();

        $schedules = $term && isset($campuses[$this->campus])
            ? SetClassSchedule::join('sub_offered', 'scheduleclass.subject_id', '=', 'sub_offered.id')
                ->join('subjects', 'sub_offered.subCode', '=', 'subjects.sub_code')
                ->leftJoin('faculty', 'scheduleclass.faculty_id', '=', 'faculty.id')
                ->leftJoin('rooms', 'scheduleclass.room_id', '=', 'rooms.id')
                ->where('scheduleclass.schlyear', $term->schlyear)
                ->where('scheduleclass.semester', $term->semester)
                ->where('scheduleclass.campus', $this->campus)
                ->latest('scheduleclass.id')
                ->limit(self::LIMIT)
                ->get(['scheduleclass.id', 'scheduleclass.schedday', 'scheduleclass.start_time', 'scheduleclass.end_time',
                    'scheduleclass.postedBy', 'sub_offered.subSec', 'subjects.sub_name', 'faculty.lname', 'faculty.fname', 'rooms.room_name'])
            : collect();

        return view('livewire.scheduling.recent-schedules', [
            'term' => $term,
            'campuses' => $campuses,
            'schedules' => $schedules,
        ]);
    }

    // Administrators: every campus; everyone else: their own
    private function campuses(): array
    {
        $user = Auth::guard('web')->user();
        $all = config('ciss.campuses');

        return $user->isAdministrator() ? $all : array_intersect_key($all, array_flip(CampusList::split($user->campus)));
    }
}

<?php

namespace App\Http\Controllers\Employee\Scheduling;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Scheduling\Concerns\ScopesScheduling;
use App\Http\Controllers\Employee\Enrollment\Concerns\ChecksEnrollment;
use App\Http\Controllers\Employee\Enrollment\Concerns\FindsEnrollment;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

use App\Models\ScheduleDB\ClassEnroll;
use App\Models\ScheduleDB\College;
use App\Models\ScheduleDB\EnPrograms;
use App\Models\ScheduleDB\FacDesignation;
use App\Models\ScheduleDB\Room;
use App\Models\ScheduleDB\SubjectOffered;
use App\Models\SettingDB\ConfigureCurrent;

/**
 * Scheduling > Dashboard: counts of colleges, programs (undergraduate / graduate) and the user's campus rooms,
 * the active term's setup in the user's campus (sections, subjects offered, designations, and the sections that
 * have no subject offered yet), and a month calendar. Everything is cached 5 minutes; no polling.
 */
class SchedulingDashboardController extends Controller
{
    use ChecksEnrollment, FindsEnrollment, ScopesScheduling;

    private const CACHE_SECONDS = 300;
    private const SECTIONS_SHOWN = 10;

    public function index()
    {
        $graduate = '%' . config('ciss.scheduling.graduate_program_code') . '%';
        $campus = $this->campuses()[0] ?? '';
        $term = ConfigureCurrent::active();

        $counts = Cache::remember("scheduling_dashboard_counts_{$campus}", self::CACHE_SECONDS, fn () => [
            'colleges' => College::whereIn('id', config('ciss.scheduling.college_ids'))->count(),
            'undergraduate' => EnPrograms::where('progCod', 'not like', $graduate)->count(),
            'graduate' => EnPrograms::where('progCod', 'like', $graduate)->count(),
            'rooms' => Room::where('campus', $campus)->count(),
        ]);

        // Graduate School Staff see their own programs, so they get their own cache entry
        $setupKey = "scheduling_dashboard_setup_{$campus}_{$term?->id}_" . (int) $this->user()->isGraduateSchoolStaff();
        $setup = $term && $campus
            ? Cache::remember($setupKey, self::CACHE_SECONDS, fn () => $this->termSetup($campus, $term))
            : null;

        return view('pages.employee.scheduling.index', [
            'counts' => $counts,
            'term' => $term,
            'campusName' => config("ciss.campuses.{$campus}", $campus),
            'setup' => $setup,
            'sectionsShown' => self::SECTIONS_SHOWN,
            'canOffer' => $this->user()->canOpenSchedulingPage('offerings'),
            'month' => now()->startOfMonth(),
        ]);
    }

    // The term's sections, subjects offered per section, designations, and the sections without subjects
    private function termSetup(string $campus, ConfigureCurrent $term): array
    {
        $where = ['campus' => $campus, 'schlyear' => $term->schlyear, 'semester' => $term->semester];
        $acronyms = $this->programsOf($campus)->pluck('progAcronym', 'progCod');

        $sections = ClassEnroll::where($where)->whereIn('progCode', $acronyms->keys())
            ->get(['progCode', 'classSection'])
            ->map(fn (ClassEnroll $class) => "{$acronyms[$class->progCode]} {$class->classSection}")
            ->unique()->sort()->values();

        // Subjects offered per section name (sub_offered.subSec)
        $offered = SubjectOffered::where($where)
            ->when($this->user()->isGraduateSchoolStaff(), fn ($query) => $query->where('subCode', 'like', '%' . config('ciss.scheduling.graduate_program_code') . '%'))
            ->groupBy('subSec')
            ->pluck(DB::raw('COUNT(*)'), 'subSec');

        $withoutSubjects = $sections->reject(fn ($name) => isset($offered[$name]))->values();

        return [
            'sections' => $sections->count(),
            'offerings' => (int) $offered->sum(),
            'ready' => $sections->count() - $withoutSubjects->count(),
            'designations' => FacDesignation::where($where)->count(),
            'withoutSubjects' => $withoutSubjects->all(),
        ];
    }
}

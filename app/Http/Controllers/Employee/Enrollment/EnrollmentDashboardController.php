<?php

namespace App\Http\Controllers\Employee\Enrollment;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use PDF;

use App\Models\EnrollmentDB\StudEnrolmentHistory;
use App\Models\SettingDB\ConfigureCurrent;

/**
 * Enrollment > Dashboard for the active school year & semester (settings_conf):
 *  - undergraduate staff: student counts (cards), charts per college / year level / program / campus,
 *    and the Regular, Irregular, Transferee and Returnee student lists as PDF;
 *  - graduate school staff (role 15): new vs old students and per program charts.
 * The page shows the cards; the charts load by AJAX from charts() (public/js/employee/enrollment/dashboard.js).
 */
class EnrollmentDashboardController extends Controller
{
    private const GRADUATE_ROLE = 15;
    private const ENROLLED_STATUSES = [2, 3];          // program_en_history.status counted as enrolled
    private const UNDERGRAD_COLLEGES = [2, 3, 4, 5, 6, 7, 8];
    private const CACHE_SECONDS = 1000;

    // Student list PDFs: group => [title, program_en_history column, value]
    public const STUDENT_GROUPS = [
        'regular' => ['Regular Students', 'studStatus', 1],
        'irregular' => ['Irregular Students', 'studStatus', 2],
        'transferee' => ['Transferee Students', 'transferee', 1],
        'returnee' => ['Returnee Students', 'studType', 3],
    ];

    public function index()
    {
        $term = ConfigureCurrent::active();

        return view('pages.employee.enrollment.index', [
            'term' => $term,
            'isGraduate' => $this->isGraduateStaff(),
            'stats' => $term && ! $this->isGraduateStaff() ? $this->studentStats($term) : null,
        ]);
    }

    // Chart data of the active term and the one before it
    public function charts()
    {
        $term = ConfigureCurrent::active();
        abort_unless($term, 404, 'No active school year found.');

        $previous = $term->previous() ?? $term;

        $data = $this->isGraduateStaff()
            ? $this->graduateCharts($term, $previous)
            : $this->undergraduateCharts($term, $previous);

        return response()->json($data + [
            'terms' => ['current' => $term->label, 'previous' => $previous->label],
        ]);
    }

    // Regular / Irregular / Transferee / Returnee students of the active term, the src of the PDF modal's iframe
    public function studentsPdf(string $group)
    {
        $term = ConfigureCurrent::active();
        abort_unless($term, 404, 'No active school year found.');

        [$title, $column, $value] = self::STUDENT_GROUPS[$group];

        $students = $this->undergraduates($term)
            ->where('program_en_history.campus', $this->campus())
            ->where("program_en_history.{$column}", $value)
            ->leftJoin('students', 'program_en_history.studentID', '=', 'students.stud_id')
            ->orderBy('students.lname')->orderBy('students.fname')
            ->get(['program_en_history.studentID', 'students.lname', 'students.fname', 'students.mname', 'students.ext']);

        return PDF::loadView('pdf.enrollment-students', [
            'title' => $title,
            'term' => $term,
            'campusName' => config('ciss.campuses')[$this->campus()] ?? $this->campus(),
            'students' => $students,
        ])
            ->setPaper('a4', 'portrait')
            ->stream($group . '-students.pdf');
    }

    // ---------- Undergraduate ----------

    // Counts for the cards (one query)
    private function studentStats(ConfigureCurrent $term)
    {
        return $this->remember($term, 'stats', fn () => $this->undergraduates($term)
            ->where('program_en_history.campus', $this->campus())
            ->selectRaw("
                SUM(studYear = '1') as year1,
                SUM(studYear = '2') as year2,
                SUM(studYear = '3') as year3,
                SUM(studYear = '4') as year4,
                SUM(transferee = '1') as transferee,
                SUM(transferee = '2') as shiftee,
                SUM(studType = '2') as `continuing`,
                SUM(studType = '3') as `returning`,
                SUM(studStatus = '1') as `regular`,
                SUM(studStatus = '2') as `irregular`
            ")
            ->first()
            ->toArray());
    }

    private function undergraduateCharts(ConfigureCurrent $term, ConfigureCurrent $previous): array
    {
        return [
            'graduate' => false,
            'colleges' => [
                'previous' => $this->collegeCounts($previous),
                'current' => $this->collegeCounts($term),
            ],
            'yearLevels' => [
                'labels' => ['1st Year', '2nd Year', '3rd Year', '4th Year'],
                'previous' => $this->yearLevelCounts($this->undergraduates($previous), [1, 2, 3, 4]),
                'current' => $this->yearLevelCounts($this->undergraduates($term), [1, 2, 3, 4]),
            ],
            'programs' => $this->programCounts($term, $this->undergraduates($term)),
            'campuses' => $this->campusCounts($term),
        ];
    }

    // Students per college of the user's campus: [{label, color, count}]
    private function collegeCounts(ConfigureCurrent $term): array
    {
        $college = $this->tableOf('schedule', 'college');

        return $this->remember($term, 'colleges', fn () => $this->enrolled($term)
            ->where('program_en_history.campus', $this->campus())
            ->join("{$college} as college", 'college.college_abbr', '=', DB::raw("SUBSTRING_INDEX(program_en_history.progCod, '-', 1)"))
            ->whereIn('college.id', self::UNDERGRAD_COLLEGES)
            ->where('college.campus', 'LIKE', '%' . $this->campus() . '%')
            ->groupBy('college.id', 'college.college_abbr', 'college.colcolor', 'college.college_name')
            ->orderBy('college.college_name')
            ->get(['college.college_abbr', 'college.colcolor', DB::raw('COUNT(DISTINCT program_en_history.studentID) as total')])
            ->map(fn ($row) => ['label' => $row->college_abbr, 'color' => $row->colcolor, 'count' => (int) $row->total])
            ->all());
    }

    // Students per campus (all campuses) of the term
    private function campusCounts(ConfigureCurrent $term): array
    {
        $counts = Cache::remember("enrollment_campuses_{$term->id}", self::CACHE_SECONDS, fn () => $this->undergraduates($term)
            ->groupBy('program_en_history.campus')
            ->selectRaw('program_en_history.campus, COUNT(*) as total')
            ->pluck('total', 'campus')
            ->all());

        $campuses = collect(config('ciss.campuses'));

        return [
            'labels' => $campuses->map(fn ($name) => str_replace(' Campus', '', $name))->values(),
            'counts' => $campuses->keys()->map(fn ($code) => (int) ($counts[$code] ?? 0)),
        ];
    }

    // ---------- Graduate school ----------

    private function graduateCharts(ConfigureCurrent $term, ConfigureCurrent $previous): array
    {
        return [
            'graduate' => true,
            'yearLevels' => [
                'labels' => ['New Students', 'Old Students'],
                'previous' => $this->yearLevelCounts($this->graduates($previous), [1, 2]),
                'current' => $this->yearLevelCounts($this->graduates($term), [1, 2]),
            ],
            'programs' => $this->programCounts($term, $this->graduates($term)),
        ];
    }

    // ---------- Shared queries ----------

    // Enrolled records (program_en_history) of a term
    private function enrolled(ConfigureCurrent $term)
    {
        return StudEnrolmentHistory::query()
            ->where('program_en_history.schlyear', $term->schlyear)
            ->where('program_en_history.semester', $term->semester)
            ->whereIn('program_en_history.status', self::ENROLLED_STATUSES);
    }

    // Undergraduate students: Student IDs without "-G"
    private function undergraduates(ConfigureCurrent $term)
    {
        return $this->enrolled($term)->where('program_en_history.studentID', 'NOT LIKE', '%-G%');
    }

    // Graduate students: Student IDs with "G" (outside Main Campus also with "N")
    private function graduates(ConfigureCurrent $term)
    {
        return $this->enrolled($term)
            ->where('program_en_history.studentID', 'LIKE', '%G%')
            ->when($this->campus() !== 'MC', fn ($query) => $query->where('program_en_history.studentID', 'LIKE', '%N%'));
    }

    // Students per year level of the user's campus, in the order of $levels
    private function yearLevelCounts($query, array $levels): array
    {
        $counts = $query->where('program_en_history.campus', $this->campus())
            ->whereIn('program_en_history.studYear', $levels)
            ->groupBy('program_en_history.studYear')
            ->selectRaw('program_en_history.studYear, COUNT(*) as total')
            ->pluck('total', 'studYear');

        return array_map(fn ($level) => (int) ($counts[$level] ?? 0), $levels);
    }

    // Students per program (acronym) of the user's campus
    private function programCounts(ConfigureCurrent $term, $query): array
    {
        $programs = $this->tableOf('schedule', 'programs');

        $rows = $this->remember($term, 'programs_' . ($this->isGraduateStaff() ? 'grad' : 'undergrad'), fn () => $query
            ->where('program_en_history.campus', $this->campus())
            ->join("{$programs} as programs", 'program_en_history.progCod', '=', 'programs.progCod')
            ->groupBy('programs.progAcronym')
            ->orderBy('programs.progAcronym')
            ->selectRaw('programs.progAcronym as label, COUNT(*) as total')
            ->get()
            ->toArray());

        return [
            'labels' => array_column($rows, 'label'),
            'counts' => array_map('intval', array_column($rows, 'total')),
        ];
    }

    // ---------- Helpers ----------

    private function user()
    {
        return Auth::guard('web')->user();
    }

    private function campus(): string
    {
        return $this->user()->campus;
    }

    private function isGraduateStaff(): bool
    {
        return (int) $this->user()->role === self::GRADUATE_ROLE;
    }

    // "database.table" of another connection, for joins across databases
    private function tableOf(string $connection, string $table): string
    {
        return config("database.connections.{$connection}.database") . '.' . $table;
    }

    // Cached per campus and term
    private function remember(ConfigureCurrent $term, string $key, callable $callback)
    {
        return Cache::remember("enrollment_{$key}_{$this->campus()}_{$term->id}", self::CACHE_SECONDS, $callback);
    }
}

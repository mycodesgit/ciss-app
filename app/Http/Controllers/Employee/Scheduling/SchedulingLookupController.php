<?php

namespace App\Http\Controllers\Employee\Scheduling;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use App\Models\ScheduleDB\College;
use App\Models\ScheduleDB\Department;
use App\Models\ScheduleDB\Subject;
use App\Services\Scheduling\SubjectFees;

/**
 * Scheduling: lookups shared by several pages (scheduling/common.js)
 *  - subjects(): the subject pickers (Select2 with AJAX) of Curriculum Programs and Subject Offered, instead of
 *    putting all 3,000+ subjects in every page. Active subjects only; codes of config
 *    ciss.scheduling.hidden_subject_prefix are never listed
 *  - fees(): the units and fees the server will save for a subject
 *  - departments(): the departments of a college (Programs, Faculty List)
 */
class SchedulingLookupController extends Controller
{
    private const MAX_RESULTS = 30;

    public function departments(Request $request)
    {
        $colleges = College::whereIn('id', config('ciss.scheduling.college_ids'))->pluck('college_abbr')->all();
        $college = $request->validate(['college' => ['required', Rule::in($colleges)]])['college'];

        return response()->json(Department::where('collegeCod', $college)->orderBy('deptName')->get(['deptCod', 'deptName']));
    }

    public function subjects(Request $request)
    {
        $search = trim((string) $request->validate(['q' => ['nullable', 'string', 'max:100']])['q']);
        $like = '%' . addcslashes($search, '%_\\') . '%';

        $subjects = self::offerable()
            ->when($search !== '', fn ($query) => $query->where(fn ($where) => $where->where('sub_code', 'like', $like)
                ->orWhere('sub_name', 'like', $like)
                ->orWhere('sub_title', 'like', $like)))
            ->orderBy('sub_name')
            ->limit(self::MAX_RESULTS)
            ->get(['sub_code', 'sub_name', 'sub_title', 'sublecredit', 'sublabcredit']);

        return response()->json([
            'results' => $subjects->map(fn (Subject $subject) => [
                'id' => $subject->sub_code,
                'text' => "{$subject->sub_name} - {$subject->sub_title} ({$subject->sub_code})",
                'graduate' => SubjectFees::isGraduate($subject->sub_code),
            ]),
        ]);
    }

    // The units and fees the server will save for a subject (shown read-only in the Add / Edit forms)
    public function fees(Request $request, string $subjectCode)
    {
        $subject = self::offerable()->where('sub_code', $subjectCode)->firstOrFail();
        $data = $request->validate([
            'isOJT' => ['nullable', 'string', 'in:' . implode(',', array_keys(SubjectFees::ojtOptions(3, true)))],
            'lecFee' => ['nullable', 'numeric', 'min:0', 'max:999999'],
        ]);

        return response()->json(SubjectFees::compute($subject, $data['isOJT'] ?? SubjectFees::NO, $data['lecFee'] ?? 0) + [
            'graduate' => SubjectFees::isGraduate($subject->sub_code),
            'fund' => SubjectFees::suggestedFund($subject),
        ]);
    }

    // Subjects that can be put in a curriculum or offered
    public static function offerable()
    {
        return Subject::where('subjstatus', Subject::ACTIVE)
            ->where('sub_code', 'not like', addcslashes(config('ciss.scheduling.hidden_subject_prefix'), '%_\\') . '%');
    }
}

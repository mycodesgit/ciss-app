<?php

namespace App\Livewire\Portal;

use Livewire\Attributes\Locked;
use Livewire\Attributes\Validate;
use Livewire\Component;

use Carbon\Carbon;
use App\Models\AdmissionDB\Applicant;
use App\Models\AdmissionDB\Year;

class TrackAdmission extends Component
{
    private const CANCELLED_STATUS = 7;
    private const RESULTS_RELEASE = '04-01'; // month-day the exam results are shown

    private const CAMPUSES = [
        'MC' => 'Main Campus',
        'VC' => 'Victorias Campus',
        'SCC' => 'San Carlos Campus',
        'MP' => 'Moises Padilla Campus',
        'HC' => 'Hinigaran Campus',
        'IC' => 'Ilog Campus',
        'CA' => 'Candoni Campus',
        'CC' => 'Cauayan Campus',
        'SC' => 'Sipalay Campus',
        'HinC' => 'Hinobaan Campus',
    ];

    // Raw score upper bound => stanine
    private const STANINES = [12 => 1, 18 => 2, 23 => 3, 29 => 4, 36 => 5, 43 => 6, 49 => 7, 56 => 8, 72 => 9];

    #[Validate('required|string', as: 'last name')]
    public $lname = '';

    #[Validate('required|string', as: 'first name')]
    public $fname = '';

    #[Locked]
    public $applicant = null;

    #[Locked]
    public $notFound = false;

    public function search()
    {
        $this->validate();

        $year = Year::where('status', 'On')->value('adyear');

        $record = Applicant::join('ad_examinee_result', 'ad_applicant_admission.id', '=', 'ad_examinee_result.app_id')
            ->join('ad_applicant_dept_rating', 'ad_applicant_admission.id', '=', 'ad_applicant_dept_rating.app_id')
            ->where('ad_applicant_admission.lname', trim($this->lname))
            ->where('ad_applicant_admission.fname', trim($this->fname))
            ->where('ad_applicant_admission.p_status', '!=', self::CANCELLED_STATUS)
            ->where('ad_applicant_admission.year', $year)
            ->select(
                'ad_applicant_admission.admission_id',
                'ad_applicant_admission.p_status',
                'ad_applicant_admission.campus',
                'ad_applicant_admission.fname',
                'ad_applicant_admission.mname',
                'ad_applicant_admission.lname',
                'ad_applicant_admission.bday',
                'ad_applicant_admission.email',
                'ad_applicant_admission.contact',
                'ad_applicant_admission.address',
                'ad_applicant_admission.d_admission',
                'ad_applicant_admission.time as adtime',
                'ad_applicant_admission.venue',
                'ad_applicant_admission.created_at',
                'ad_examinee_result.raw_score',
                'ad_examinee_result.percentile',
                'ad_applicant_dept_rating.remarks',
                'ad_applicant_dept_rating.course',
            )
            ->first();

        $this->applicant = $record ? $this->present($record, $year) : null;
        $this->notFound = $record === null;
    }

    public function render()
    {
        return view('livewire.portal.track-admission');
    }

    // Everything the view needs, already formatted (Livewire keeps it as a plain array)
    private function present($record, $year)
    {
        $status = (int) $record->p_status;
        $screened = in_array($status, [4, 5, 6, 7]);

        return [
            'admission_id' => $record->admission_id,
            'name' => trim($record->fname . ' ' . substr($record->mname, 0, 2) . ' ' . $record->lname),
            'campus' => self::CAMPUSES[$record->campus] ?? $record->campus,
            'bday' => $record->bday ? Carbon::parse($record->bday)->format('F d, Y') : null,
            'email' => $record->email,
            'contact' => $record->contact,
            'address' => $record->address,

            'submitted_date' => $record->created_at?->format('M. d, Y'),
            'submitted_time' => $record->created_at?->format('h:i A'),

            'exam_date' => $record->d_admission ? Carbon::parse($record->d_admission)->format('M. d, Y') : null,
            'exam_time' => $record->adtime ? Carbon::parse($record->adtime)->format('h:i A') : null,
            'exam_venue' => $record->venue ?: null,

            'results_released' => Carbon::now()->format('m-d') >= self::RESULTS_RELEASE,
            'results_release_label' => Carbon::createFromFormat('Y-m-d', ($year ?: Carbon::now()->year) . '-' . self::RESULTS_RELEASE)->format('F j, Y'),
            'stanine' => $this->stanine($record->raw_score),
            'percentile' => $record->percentile ?: null,

            'screened' => $screened,
            'screening_remark' => $screened && !empty($record->remarks) ? ($record->remarks == 1 ? 'Passed' : 'Failed') : null,
            'accepted' => in_array($status, [5, 6, 7]),
            'accepted_course' => !empty($record->remarks) ? $record->course : null,
            'enrolled' => $status === 7,
        ];
    }

    private function stanine($rawScore)
    {
        if (!is_numeric($rawScore) || (int) $rawScore < 1) {
            return null;
        }

        foreach (self::STANINES as $maxScore => $stanine) {
            if ((int) $rawScore <= $maxScore) {
                return $stanine;
            }
        }

        return null;
    }
}

<?php

namespace App\Http\Controllers\Main;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

use Carbon\Carbon;
use App\Models\AdmissionDB\Applicant;
use App\Models\AdmissionDB\ApplicantDocs;
use App\Models\AdmissionDB\ExamineeResult;
use App\Models\AdmissionDB\DeptRating;
use App\Models\AdmissionDB\AdReupload;
use App\Models\AdmissionDB\Programs;
use App\Models\AdmissionDB\Strands;
use App\Models\AdmissionDB\Time;
use App\Models\AdmissionDB\Year;

use App\Models\SettingDB\Region;
use App\Models\SettingDB\AdmissionMode;
use App\Models\SettingDB\Campus;

class PortalApplyController extends Controller
{
    private const DAILY_LIMIT = 3000;
    private const OPEN_HOUR = 8;
    private const CLOSE_HOUR = 17;
    private const CANCELLED_STATUS = 7;

    // Optional uploads => storage folder (inside the admission year folder)
    private const OPTIONAL_UPLOADS = [
        'grade12File' => 'applcntrequirement',
        'shsFile' => 'applcntrequirement',
        'transfereeFile' => 'applcntrequirement',
        'alsFile' => 'applcntrequirement',
        'lifelongFile' => 'applcntrequirement',
    ];

    public function create()
    {
        return view('pages.portal.apply', [
            'isAdmissionOpen' => AdmissionMode::first()?->statusadmission === 'On',
            'isWithinOfficeHours' => $this->isWithinOfficeHours(),
            'isDailyLimitReached' => $this->todayRegistrations() >= self::DAILY_LIMIT,
            'programs' => Programs::orderBy('id', 'asc')->get(),
            'strands' => Strands::orderBy('code', 'asc')->get(),
            'campuses' => Campus::all(),
            'regions' => Region::all(),
        ]);
    }

    public function store(Request $request)
    {
        $year = $this->currentYear();

        $validator = Validator::make($request->all(), [
            'type' => 'required',
            'campus' => 'required',
            'lastname' => 'required',
            'firstname' => 'required',
            'email' => [
                'required',
                'max:191',
                Rule::unique('ad_applicant_admission', 'email')->where('year', $year),
            ],
            'gender' => 'required',
            'age' => 'required',
            'contact' => 'required|numeric|min:11',
            'preference_1' => 'required',
            'preference_2' => 'required',
            'proofdoc_image' => 'required',
            'studiddoc_image' => 'required',
        ]);

        $alreadyRegistered = Applicant::where('lname', $request->input('lastname'))
            ->where('fname', $request->input('firstname'))
            ->where('year', $year)
            ->exists();

        if ($alreadyRegistered) {
            return $this->failed('Error: You have already Registered this year.', ['existing_applicant' => 'Error: You have already Registered this year.']);
        }

        if ($validator->fails()) {
            return $this->failed('Error in saving applicant data. Your Email already exist. Please check the inputs!', $validator);
        }

        if ($this->todayRegistrations() >= self::DAILY_LIMIT) {
            return $this->failed('Error: Daily registration limit reached!');
        }

        try {
            $admissionId = DB::transaction(function () use ($request, $year) {
                $dateId = $request->input('dateID');

                if ($dateId) {
                    $schedule = Time::lockForUpdate()->find($dateId);

                    if (!$schedule || $this->remainingSlots($schedule) <= 0) {
                        return null;
                    }
                }

                $admissionId = $this->nextAdmissionId($request->input('campus'), $year);
                $now = Carbon::now();

                $applicant = new Applicant;
                $applicant->studagree = $request->input('studagree');
                $applicant->year = $year;
                $applicant->campus = $request->input('campus');
                $applicant->admission_id = $admissionId;
                $applicant->type = $request->input('type');
                $applicant->lname = $request->input('lastname');
                $applicant->fname = $request->input('firstname');
                $applicant->mname = $request->input('mname');
                $applicant->ext = $request->input('ext');
                $applicant->gender = $request->input('gender');
                $applicant->address = $request->input('address');
                $applicant->hnum = $request->input('hnum');
                $applicant->brgy = $request->input('brgy');
                $applicant->city = $request->input('city');
                $applicant->province = $request->input('province');
                $applicant->region = $request->input('region');
                $applicant->zcode = $request->input('zcode');
                $applicant->bday = $request->input('bday');
                $applicant->age = $request->input('age');
                $applicant->contact = $request->input('contact');
                $applicant->email = $request->input('email');
                $applicant->civil_status = $request->input('civil_status');
                $applicant->religion = $request->input('religion');
                $applicant->monthly_income = $request->input('monthly_income');
                $applicant->strand = $request->input('strand');
                $applicant->lstsch_attended = $request->input('lstsch_attended');
                $applicant->suc_lst_attended = $request->input('suc_lst_attended');
                $applicant->course = $request->input('course');
                $applicant->preference_1 = $request->input('preference_1');
                $applicant->preference_2 = $request->input('preference_2');
                $applicant->dateID = $request->input('dateID');
                $applicant->d_admission = $request->input('d_admission');
                $applicant->time = $request->input('time');
                $applicant->created_at = $now;
                $applicant->save();

                $fileName = $request->input('lastname') . '_' . $request->input('firstname') . '_' . $admissionId;

                $docs = new ApplicantDocs;
                $docs->app_id = $applicant->id;
                $docs->camp = $applicant->campus;
                $docs->admission_id = $admissionId;
                $docs->r_card = $request->input('r_card');
                $docs->g_moral = $request->input('g_moral');
                $docs->t_record = $request->input('t_record');
                $docs->b_cert = $request->input('b_cert');
                $docs->h_dismissal = $request->input('h_dismissal');
                $docs->m_cert = $request->input('m_cert');
                $docs->qstion1 = $request->input('qstion1');
                $docs->qstion2 = $request->input('qstion2');
                $docs->typefileproofupload = $request->input('typefileproofupload');
                $docs->created_at = $now;
                $docs->studiddoc_image = $this->storeUpload($request, 'studiddoc_image', $year . '/studentIDfolder', $fileName);
                $docs->proofdoc_image = $this->storeUpload($request, 'proofdoc_image', $year . '/prooffolder', $fileName);
                foreach (self::OPTIONAL_UPLOADS as $field => $folder) {
                    $docs->{$field} = $this->storeUpload($request, $field, $year . '/' . $folder, $fileName);
                }
                $docs->save();

                $result = new ExamineeResult;
                $result->app_id = $applicant->id;
                $result->camp = $applicant->campus;
                $result->admission_id = $admissionId;
                $result->raw_score = $request->input('raw_score');
                $result->percentile = $request->input('percentile');
                $result->created_at = $now;
                $result->save();

                $rating = new DeptRating;
                $rating->app_id = $applicant->id;
                $rating->camp = $applicant->campus;
                $rating->admission_id = $admissionId;
                $rating->created_at = $now;
                $rating->save();

                $reupload = new AdReupload;
                $reupload->appid = $applicant->id;
                $reupload->camp = $applicant->campus;
                $reupload->save();

                return $admissionId;
            });
        } catch (\Exception $e) {
            report($e);
            return $this->failed('Error: Could not process your application. Please try again.');
        }

        if ($admissionId === null) {
            return $this->failed('Error: Selected exam schedule is no longer available. Please choose another schedule.');
        }

        $this->sendConfirmationEmail($request->input('email'), $year);

        return redirect()->route('portal.apply.submitted')
            ->with('success', 'Application was successfully submitted. Check status in the (Track) Admission Page.')
            ->with('admission_id', $admissionId)
            ->with('email', $request->input('email'));
    }

    public function submitted()
    {
        if (!session()->has('admission_id')) {
            return redirect()->route('portal.apply.create');
        }

        return view('pages.portal.apply-submitted', [
            'year' => $this->currentYear(),
            'email' => session('email'),
            'admissionId' => session('admission_id'),
        ]);
    }

    public function programs(Request $request)
    {
        $programs = Programs::whereRaw("FIND_IN_SET(?, REPLACE(campus, ' ', '')) > 0", [$request->input('campus')])
            ->get(['code', 'program']);

        return response()->json(['programs' => $programs]);
    }

    public function schedules(Request $request)
    {
        $schedules = Time::where('campus', $request->input('campus'))
            ->whereYear('date', $this->currentYear())
            ->get(['date', 'time', 'slots', 'id']);

        // One count query for all schedules instead of one per schedule
        $taken = Applicant::whereIn('dateID', $schedules->pluck('id'))
            ->where('p_status', '!=', self::CANCELLED_STATUS)
            ->groupBy('dateID')
            ->selectRaw('dateID, COUNT(*) as total')
            ->pluck('total', 'dateID');

        $schedules->each(function ($schedule) use ($taken) {
            $schedule->slots = max($schedule->slots - ($taken[$schedule->id] ?? 0), 0);
        });

        return response()->json(['schedtest' => $schedules]);
    }

    private function currentYear()
    {
        return Year::where('status', 'On')->value('adyear');
    }

    private function todayRegistrations()
    {
        return Applicant::whereDate('created_at', Carbon::today())->count();
    }

    private function isWithinOfficeHours()
    {
        $now = Carbon::now();

        return $now->isWeekday() && $now->hour >= self::OPEN_HOUR && $now->hour < self::CLOSE_HOUR;
    }

    private function remainingSlots(Time $schedule)
    {
        $taken = Applicant::where('dateID', $schedule->id)
            ->where('p_status', '!=', self::CANCELLED_STATUS)
            ->count();

        return max($schedule->slots - $taken, 0);
    }

    private function nextAdmissionId($campus, $year)
    {
        $latestApplicant = Applicant::where('campus', $campus)
            ->where('admission_id', 'like', $year . '%')
            ->orderByDesc('admission_id')
            ->first();

        $nextNumber = $latestApplicant ? (int) substr($latestApplicant->admission_id, -4) + 1 : 1;
        $admissionId = $year . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        $exists = Applicant::where('admission_id', $admissionId)->where('campus', $campus)->exists();

        return $exists ? $admissionId + 1 : $admissionId;
    }

    private function storeUpload(Request $request, $field, $folder, $fileName)
    {
        if (!$request->hasFile($field)) {
            return null;
        }

        $file = $request->file($field);

        return $file->storeAs($folder, $fileName . '.' . $file->getClientOriginalExtension(), 'public');
    }

    private function sendConfirmationEmail($email, $year)
    {
        try {
            Mail::raw("
                Dear Applicant,

                Congratulations! You have successfully registered for the {$year} Admission Test.

                Please note that your application is currently under review. You will receive a confirmation email once it has been approved.

                Thank you for your interest and patience.

                This is a system-generated message. Please do not reply.
                ", function ($message) use ($email) {
                    $message->to($email)
                            ->subject('Admission Test Application - System Notification');
            });
        } catch (\Exception $e) {
            // The application is already saved; a mail failure should not undo it
            report($e);
        }
    }

    private function failed($message, $errors = [])
    {
        return redirect()->route('portal.apply.create')
            ->withErrors($errors)
            ->withInput()
            ->with('fail', $message);
    }
}

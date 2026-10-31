<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\StudentLoginController;
use App\Http\Controllers\Auth\FacultyLoginController;
use App\Http\Controllers\Auth\EmployeeLoginController;
use App\Http\Controllers\Employee\DashboardController;
use App\Http\Controllers\Employee\Settings\SettingController;
use App\Http\Controllers\Employee\Settings\SettingUsersController;
use App\Http\Controllers\Employee\Settings\SettingAcademicTermController;
use App\Http\Controllers\Employee\Settings\SettingGradePassController;
use App\Http\Controllers\Employee\Settings\SettingControlStatusController;
use App\Http\Controllers\Employee\Settings\SettingCampusController;
use App\Http\Controllers\Employee\Admission\AdmissionDashboardController;
use App\Http\Controllers\Employee\Admission\AdmissionApplicantController;
use App\Http\Controllers\Employee\Admission\AdmissionExamineeController;
use App\Http\Controllers\Employee\Admission\AdmissionResultController;
use App\Http\Controllers\Employee\Admission\AdmissionConfirmController;
use App\Http\Controllers\Employee\Admission\AdmissionPreEnrollmentController;
use App\Http\Controllers\Employee\Admission\AdmissionAcceptedController;
use App\Http\Controllers\Employee\Admission\AdmissionSlotsController;
use App\Http\Controllers\Employee\Admission\AdmissionApplicantReportController;
use App\Http\Controllers\Employee\Admission\AdmissionSchoolReportController;
use App\Http\Controllers\Employee\Admission\AdmissionScheduleReportController;
use App\Http\Controllers\Employee\Admission\AdmissionNoScheduleReportController;
use App\Http\Controllers\Employee\Admission\AdmissionResultReportController;
use App\Http\Controllers\Employee\Admission\AdmissionConfirmedReportController;
use App\Http\Controllers\Employee\Admission\AdmissionAcceptedReportController;
use App\Http\Controllers\Employee\Admission\AdmissionConfigController;
use App\Http\Controllers\Employee\Admission\Config\ConfigProgramController;
use App\Http\Controllers\Employee\Admission\Config\ConfigStrandController;
use App\Http\Controllers\Employee\Admission\Config\ConfigDateController;
use App\Http\Controllers\Employee\Admission\Config\ConfigTimeController;
use App\Http\Controllers\Employee\Admission\Config\ConfigVenueController;
use App\Http\Controllers\Employee\Admission\Config\ConfigYearController;
use App\Http\Controllers\Employee\Enrollment\EnrollmentDashboardController;
use App\Http\Controllers\Employee\Enrollment\EnrollStudentController;
use App\Http\Controllers\Employee\Enrollment\EnrollmentFormController;
use App\Http\Controllers\Employee\Enrollment\RegistrationFormController;
use App\Http\Controllers\Employee\Enrollment\EditEnrollmentController;
use App\Http\Controllers\Employee\Enrollment\DuplicateFeesController;
use App\Http\Controllers\Employee\Enrollment\EnrollmentHistoryController;
use App\Http\Controllers\Employee\Enrollment\GradesheetController;
use App\Http\Controllers\Employee\Enrollment\GradeCorrectionController;
use App\Http\Controllers\Employee\Enrollment\StudentTransferController;
use App\Http\Controllers\Employee\Enrollment\SubjectController;
use App\Http\Controllers\Employee\Enrollment\StudentInformationController;
use App\Http\Controllers\Employee\Enrollment\PrintRegistrationFormController;
use App\Http\Controllers\Employee\Enrollment\StudentPerDegreeController;
use App\Http\Controllers\Employee\Enrollment\AttendancePerDegreeController;
use App\Http\Controllers\Employee\Enrollment\ClassAttendanceController;
use App\Http\Controllers\Employee\Enrollment\StudentGradesController;
use App\Http\Controllers\Employee\Enrollment\EnrollmentQueueController;
use App\Http\Controllers\Main\PortalController;
use App\Http\Controllers\Main\PortalApplyController;
use App\Http\Controllers\Main\PortalLocationController;
use App\Http\Controllers\Main\PortalTrackController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Public
Route::get('/', [PortalController::class, 'home'])->name('home');

Route::prefix('portal')->name('portal.')->group(function () {
    Route::get('/admission', [PortalController::class, 'admission'])->name('admission');

    Route::prefix('admission/apply')->name('apply.')->controller(PortalApplyController::class)->group(function () {
        Route::get('/', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/submitted', 'submitted')->name('submitted');
        Route::get('/programs', 'programs')->name('programs');
        Route::get('/schedules', 'schedules')->name('schedules');
    });

    Route::get('/admission/track', [PortalTrackController::class, 'index'])->name('track.index');

    Route::prefix('locations')->name('locations.')->controller(PortalLocationController::class)->group(function () {
        Route::get('/provinces/{regionId}', 'provinces')->name('provinces');
        Route::get('/cities/{provinceId}', 'cities')->name('cities');
        Route::get('/barangays/{cityId}', 'barangays')->name('barangays');
    });
});

// Guest only (users already logged in on that guard are redirected to their home)
Route::get('/student/login', [StudentLoginController::class, 'index'])->middleware('guest:kioskstudent')->name('student.login');
Route::get('/faculty/login', [FacultyLoginController::class, 'index'])->middleware('guest:faculty')->name('faculty.login');

// Employee login: URL only, no button on the main page. POST is limited to 10 requests/minute per IP
Route::middleware('guest:web')->controller(EmployeeLoginController::class)->group(function () {
    Route::get('/employee', 'index')->name('employee.login');
    Route::post('/employee', 'login')->middleware('throttle:10,1')->name('employee.login.attempt');
});

// Employee area (logged in on the "web" guard; pages are never cached so Back after logout can't show them)
Route::prefix('employee')->name('employee.')->middleware(['auth:web', 'prevent-back-history'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [EmployeeLoginController::class, 'logout'])->name('logout');

    // Address lookups for employee forms (same data as the portal's apply form; the portal routes stay unchanged)
    Route::prefix('locations')->name('locations.')->controller(PortalLocationController::class)->group(function () {
        Route::get('/provinces/{regionId}', 'provinces')->name('provinces');
        Route::get('/cities/{provinceId}', 'cities')->name('cities');
        Route::get('/barangays/{cityId}', 'barangays')->name('barangays');
    });

    // Admission module (menu key "admission-url")
    Route::prefix('admission')->name('admission.')->middleware('menu.access:admission-url')->group(function () {
        Route::get('/', [AdmissionDashboardController::class, 'index'])->name('dashboard');
        Route::get('/chart', [AdmissionDashboardController::class, 'chart'])->name('dashboard.chart');

        // List of Applicant
        Route::prefix('applicants')->name('applicants.')->controller(AdmissionApplicantController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/list', 'list')->name('list');
            Route::put('/{applicant}', 'update')->name('update');
            Route::get('/{applicant}/reupload', 'showReupload')->name('reupload.show');
            Route::put('/{applicant}/reupload', 'updateReupload')->name('reupload.update');
            Route::put('/{applicant}/schedule', 'updateSchedule')->name('schedule');
            Route::put('/{applicant}/push', 'pushToExaminees')->name('push');
            Route::put('/{applicant}/cancel', 'cancel')->name('cancel');
        });

        // Pre-Enrollment Form PDF (Examination Results, Confirmed Applicants)
        Route::get('/applicants/{applicant}/pre-enrollment', [AdmissionPreEnrollmentController::class, 'show'])->name('applicants.pre-enrollment');

        // List of Examinee (View Data / Schedule / Delete use the applicants routes above)
        Route::prefix('examinees')->name('examinees.')->controller(AdmissionExamineeController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/list', 'list')->name('list');
            Route::put('/{applicant}/result', 'saveResult')->name('result');
            Route::put('/{applicant}/push', 'pushToResults')->name('push');
        });

        // Examination Results (View Data uses the applicants route above)
        Route::prefix('results')->name('results.')->controller(AdmissionResultController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/list', 'list')->name('list');
            Route::put('/{applicant}/result', 'updateResult')->name('result');
            Route::put('/{applicant}/push', 'pushToConfirmation')->name('push');
        });

        // Confirmed Applicants (View Data uses the applicants route above)
        Route::prefix('confirmed')->name('confirmed.')->controller(AdmissionConfirmController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/list', 'list')->name('list');
            Route::put('/{applicant}/interview', 'saveInterview')->name('interview');
            Route::put('/{applicant}/push', 'pushToAccepted')->name('push');
        });

        // Accepted Applicants
        Route::prefix('accepted')->name('accepted.')->controller(AdmissionAcceptedController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/list', 'list')->name('list');
            Route::put('/{applicant}/push', 'pushToEnrollment')->name('push');
        });

        // Reports: Applicants (View Data uses the applicants route above)
        Route::prefix('reports')->name('reports.')->controller(AdmissionApplicantReportController::class)->group(function () {
            Route::get('/applicants', 'applicants')->name('applicants');
            Route::get('/applicants/list', 'applicantsList')->name('applicants.list');
            Route::get('/applicants/admission-slips', 'admissionSlipsZip')->name('applicants.slips');
            Route::get('/applicants/{applicant}/admission-slip', 'admissionSlip')->name('applicants.slip');
        });

        // Reports: Admission Schedules
        Route::prefix('reports/schedules')->name('reports.schedules.')->controller(AdmissionScheduleReportController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/options', 'schedules')->name('options');
            Route::get('/list', 'list')->name('list');
            Route::get('/pdf', 'pdf')->name('pdf');
        });

        // Reports: Applicants No Sched
        Route::prefix('reports/no-schedule')->name('reports.no-schedule.')->controller(AdmissionNoScheduleReportController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/list', 'list')->name('list');
            Route::get('/pdf', 'pdf')->name('pdf');
        });

        // Reports: Examination Results
        Route::prefix('reports/results')->name('reports.results.')->controller(AdmissionResultReportController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/list', 'list')->name('list');
            Route::get('/pdf', 'pdf')->name('pdf');
        });

        // Reports: Confirmed Applicants
        Route::prefix('reports/confirmed')->name('reports.confirmed.')->controller(AdmissionConfirmedReportController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/list', 'list')->name('list');
            Route::get('/pdf', 'pdf')->name('pdf');
        });

        // Reports: Accepted Applicants
        Route::prefix('reports/accepted')->name('reports.accepted.')->controller(AdmissionAcceptedReportController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/list', 'list')->name('list');
            Route::get('/pdf', 'pdf')->name('pdf');
        });

        // Reports: Applicants per School
        Route::prefix('reports/schools')->name('reports.schools.')->controller(AdmissionSchoolReportController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/list', 'list')->name('list');
        });

        // Configuration: Availability Slots
        Route::prefix('slots')->name('slots.')->controller(AdmissionSlotsController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/list', 'list')->name('list');
        });

        // Configuration: Configure Admission (one page, a tab per setting; each tab has list / store / update / destroy)
        Route::prefix('config')->name('config.')->group(function () {
            Route::get('/', [AdmissionConfigController::class, 'index'])->name('index');
            Route::get('/dates/options', [ConfigDateController::class, 'options'])->name('dates.options');

            $sections = [
                'programs' => [ConfigProgramController::class, 'program'],
                'strands' => [ConfigStrandController::class, 'strand'],
                'dates' => [ConfigDateController::class, 'date'],
                'times' => [ConfigTimeController::class, 'time'],
                'venues' => [ConfigVenueController::class, 'venue'],
                'years' => [ConfigYearController::class, 'year'],
            ];

            foreach ($sections as $section => [$controller, $parameter]) {
                Route::prefix($section)->name("{$section}.")->controller($controller)->group(function () use ($parameter) {
                    Route::get('/', 'list')->name('list');
                    Route::post('/', 'store')->name('store');
                    Route::put("/{{$parameter}}", 'update')->name('update');
                    Route::delete("/{{$parameter}}", 'destroy')->name('destroy');
                });
            }
        });
    });

    // Enrollment module (menu key "enrollment-url")
    Route::prefix('enrollment')->name('enrollment.')->middleware('menu.access:enrollment-url')->group(function () {
        Route::controller(EnrollmentDashboardController::class)->group(function () {
            Route::get('/', 'index')->name('dashboard');
            Route::get('/charts', 'charts')->name('dashboard.charts');
            Route::get('/students/{group}/pdf', 'studentsPdf')
                ->whereIn('group', array_keys(EnrollmentDashboardController::STUDENT_GROUPS))
                ->name('dashboard.students-pdf');
        });

        // Enroll Student: search -> enrollment form ({student} = Student ID; schlyear & semester are query / form values)
        Route::prefix('enroll')->name('enroll.')->group(function () {
            Route::controller(EnrollStudentController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/find', 'findStudent')->name('find');
            });

            Route::controller(EnrollmentFormController::class)->group(function () {
                Route::get('/{student}', 'create')->name('create');
                Route::get('/{student}/section-subjects', 'sectionSubjects')->name('section-subjects');
                Route::post('/{student}/assess', 'assess')->name('assess');
                Route::post('/{student}', 'store')->name('store');
            });

            // Print RF (Registration Form PDF) of an enrolled student: ?schlyear=&semester=
            Route::get('/{student}/registration-form', [RegistrationFormController::class, 'show'])->name('registration-form');
        });

        // Edit Enrollment: search -> form of a saved enrollment ({student} = Student ID; schlyear & semester are query / form values)
        Route::prefix('edit')->name('edit.')->controller(EditEnrollmentController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/find', 'findEnrollment')->name('find');
            Route::get('/{student}', 'edit')->name('show');
            Route::get('/{student}/section-subjects', 'sectionSubjects')->name('section-subjects');
            Route::post('/{student}/assess', 'assess')->name('assess');
            Route::put('/{student}', 'update')->name('update');
            Route::put('/{student}/done-print', 'donePrint')->name('done-print');
            Route::delete('/{student}', 'destroy')->name('destroy');
        });

        // Enroll Student: queue panel (the user's own counter) and List of Pre-Enrolled Students
        Route::prefix('queue')->name('queue.')->controller(EnrollmentQueueController::class)->group(function () {
            Route::put('/status', 'updateStatus')->name('status');
            Route::put('/category', 'updateCategory')->name('category');
            Route::post('/next', 'next')->name('next');
            Route::post('/call', 'call')->name('call');
            Route::get('/pre-enrolled', 'preEnrolled')->name('pre-enrolled');
        });

        // Edit Dup. Fees: search -> a student's appraisal lines of a term (?campus=&schlyear=&semester=), delete duplicates
        Route::prefix('dup-fees')->name('dup-fees.')->controller(DuplicateFeesController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/find', 'find')->name('find');
            Route::get('/{student}', 'show')->name('show');
            Route::get('/{student}/fees', 'list')->name('list');
            Route::delete('/{student}/fees/{appraisal}', 'destroy')->whereNumber('appraisal')->name('destroy');
        });

        // Enrollment History: search students, view a student's enrollments, print the Pre-Enrollment Form
        Route::prefix('history')->name('history.')->controller(EnrollmentHistoryController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/search', 'search')->name('search');
            Route::get('/{student}', 'history')->name('show');
            Route::get('/{student}/pre-enrollment', 'preEnrollment')->name('pre-enrollment');
        });

        // Gradesheet: subjects of a term -> a subject's grades ({subject} = sub_offered.id, {grade} = studgrades.id)
        Route::prefix('gradesheet')->name('gradesheet.')->controller(GradesheetController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/subjects', 'subjects')->name('subjects');
            Route::get('/subjects/{subject}', 'show')->whereNumber('subject')->name('show');
            Route::get('/subjects/{subject}/students', 'students')->whereNumber('subject')->name('students');
            Route::put('/subjects/{subject}/submit', 'submit')->whereNumber('subject')->name('submit');
            Route::put('/grades/{grade}', 'saveGrade')->whereNumber('grade')->name('grade');
            Route::put('/grades/{grade}/completion', 'saveCompletion')->whereNumber('grade')->name('completion');
            // Grades password: at most 10 tries a minute
            Route::middleware('throttle:10,1')->group(function () {
                Route::put('/grades/{grade}/unlock', 'unlockGrade')->whereNumber('grade')->name('unlock-grade');
                Route::put('/grades/{grade}/completion/unlock', 'unlockCompletion')->whereNumber('grade')->name('unlock-completion');
            });
        });

        // Correction of Grades (Registrar, Administrators): change any grade, even submitted / INC
        Route::prefix('corrections')->name('corrections.')->middleware('registrar')->controller(GradeCorrectionController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/subjects', 'subjects')->name('subjects');
            Route::get('/subjects/{subject}', 'show')->whereNumber('subject')->name('show');
            Route::get('/subjects/{subject}/students', 'students')->whereNumber('subject')->name('students');
            Route::put('/grades/{grade}', 'correctGrade')->whereNumber('grade')->name('grade');
            Route::put('/grades/{grade}/completion', 'correctCompletion')->whereNumber('grade')->name('completion');
            // Grades password: at most 10 tries a minute
            Route::put('/grades/{grade}/unlock', 'unlock')->whereNumber('grade')->middleware('throttle:10,1')->name('unlock');
        });

        // Subjects (Registrar, Administrators): the subjects master list, add a subject
        Route::prefix('subjects')->name('subjects.')->middleware('registrar')->controller(SubjectController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/list', 'list')->name('list');
            Route::get('/next-code', 'nextCode')->name('next-code');
            Route::post('/', 'store')->name('store');
            Route::put('/{subject}', 'update')->name('update');
            // Delete = Inactive (and back to Active): subjects are never removed
            Route::put('/{subject}/status', 'updateStatus')->name('status');
        });

        // Student Information (Administrators, Registrar, Guidance): search students, view / edit a student ({student} = Student ID)
        Route::prefix('student-info')->name('student-info.')->middleware('student-info')->controller(StudentInformationController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/list', 'list')->name('list');
            Route::get('/{student}', 'show')->name('show');
            Route::put('/{student}', 'update')->name('update');
        });

        // Transferred Student (Registrar, Administrators): add another campus to a student's records
        Route::prefix('transfers')->name('transfers.')->middleware('registrar')->controller(StudentTransferController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/list', 'list')->name('list');
            Route::get('/lookup', 'lookup')->name('lookup');
            Route::post('/', 'store')->name('store');
        });

        // Print Student RF: search an enrollment (?campus=&stud_id=&schlyear=&semester=), the PDF is enroll.registration-form
        Route::prefix('print-rf')->name('print-rf.')->middleware('enrollment-reports')->controller(PrintRegistrationFormController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/find', 'find')->name('find');
        });

        // Student Per Degree: enrolled students per program + year + section (?campus=&schlyear=&semester=), list and PDF of a class
        Route::prefix('per-degree')->name('per-degree.')->middleware('enrollment-reports')->controller(StudentPerDegreeController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/list', 'list')->name('list');
            Route::get('/students', 'students')->name('students');
            Route::get('/pdf', 'pdf')->name('pdf');
        });

        // Attendance Per Degree: same as Student Per Degree, the PDF has a Signature column for the students
        Route::prefix('attendance-per-degree')->name('attendance-per-degree.')->middleware('enrollment-reports')->controller(AttendancePerDegreeController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/list', 'list')->name('list');
            Route::get('/students', 'students')->name('students');
            Route::get('/pdf', 'pdf')->name('pdf');
        });

        // Class Attendance: subjects offered in a term (?campus=&schlyear=&semester=), students and attendance sheet PDF of a subject
        Route::prefix('class-attendance')->name('class-attendance.')->middleware('enrollment-reports')->controller(ClassAttendanceController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/list', 'list')->name('list');
            Route::get('/{subject}/students', 'students')->whereNumber('subject')->name('students');
            Route::get('/{subject}/pdf', 'pdf')->whereNumber('subject')->name('pdf');
        });

        // View Student Grades: all grades of a student per term (?campus=&stud_id=)
        Route::prefix('student-grades')->name('student-grades.')->middleware('enrollment-reports')->controller(StudentGradesController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/find', 'find')->name('find');
        });
    });

    // Accounts / Settings module (menu key "setting-url")
    Route::prefix('settings')->name('settings.')->middleware('menu.access:setting-url')->group(function () {
        Route::get('/', [SettingController::class, 'index'])->name('index');

        // User's Management (administrators only)
        Route::prefix('users')->name('users.')->middleware('admin')->controller(SettingUsersController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/list', 'list')->name('list');
            Route::post('/', 'store')->name('store');
            Route::put('/{user}', 'update')->name('update');
            Route::put('/{user}/password', 'updatePassword')->name('password');
            Route::put('/{user}/status', 'updateStatus')->name('status');
            Route::get('/{user}/menu-access', 'showMenuAccess')->name('menu-access.show');
            Route::put('/{user}/menu-access', 'updateMenuAccess')->name('menu-access.update');
            Route::get('/{user}/year-access', 'showYearAccess')->name('year-access.show');
            Route::put('/{user}/year-access', 'updateYearAccess')->name('year-access.update');
        });

        // A.Y. & Semester (administrators only)
        Route::prefix('academic-terms')->name('academic-terms.')->middleware('admin')->controller(SettingAcademicTermController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/list', 'list')->name('list');
            Route::post('/', 'store')->name('store');
            Route::put('/{academicTerm}', 'update')->name('update');
        });

        // Grades Password (administrators only)
        Route::prefix('grade-passwords')->name('grade-passwords.')->middleware('admin')->controller(SettingGradePassController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/list', 'list')->name('list');
            Route::post('/', 'store')->name('store');
            Route::put('/{gradePass}', 'update')->name('update');
        });

        // Control Status: admission / enrollment / queueing switches (administrators only)
        Route::prefix('control-status')->name('control-status.')->middleware('admin')->controller(SettingControlStatusController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::put('/{control}', 'update')->whereIn('control', SettingControlStatusController::keys())->name('update');
        });

        // Campuses Control: student login per campus (administrators only)
        Route::prefix('campuses')->name('campuses.')->middleware('admin')->controller(SettingCampusController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::put('/{campus}', 'update')->name('update');
        });
    });
});

// Unknown URLs: a real route runs the "web" middleware (session), so a logged-in employee's
// 404 is shown inside their module layout (see App\Exceptions\Handler::renderHttpException)
Route::fallback(fn () => abort(404));

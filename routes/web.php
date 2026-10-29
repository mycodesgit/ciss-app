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
use App\Http\Controllers\Employee\Admission\AdmissionConfigController;
use App\Http\Controllers\Employee\Admission\Config\ConfigProgramController;
use App\Http\Controllers\Employee\Admission\Config\ConfigStrandController;
use App\Http\Controllers\Employee\Admission\Config\ConfigDateController;
use App\Http\Controllers\Employee\Admission\Config\ConfigTimeController;
use App\Http\Controllers\Employee\Admission\Config\ConfigVenueController;
use App\Http\Controllers\Employee\Admission\Config\ConfigYearController;
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

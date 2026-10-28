<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\StudentLoginController;
use App\Http\Controllers\Auth\FacultyLoginController;
use App\Http\Controllers\Auth\EmployeeLoginController;
use App\Http\Controllers\Employee\DashboardController;
use App\Http\Controllers\Employee\SettingController;
use App\Http\Controllers\Employee\SettingUsersController;
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
    });
});

<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\StudentLoginController;
use App\Http\Controllers\Auth\FacultyLoginController;
use App\Http\Controllers\Auth\EmployeeLoginController;
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

// Guest only (logged-in users are redirected away from login pages)
Route::middleware('guest')->group(function () {
    Route::get('/student/login', [StudentLoginController::class, 'index'])->name('student.login');
    Route::get('/faculty/login', [FacultyLoginController::class, 'index'])->name('faculty.login');
    Route::get('/employee', [EmployeeLoginController::class, 'index'])->name('employee.login'); // URL only, no button on the main page
});

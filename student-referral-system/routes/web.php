<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\SeminarController;
use App\Http\Controllers\Admin\SmsLogController;
use App\Http\Controllers\Counselor\CounselorDashboardController;
use App\Http\Controllers\Counselor\ReferralController;
use App\Http\Controllers\Counselor\InterventionController;
use App\Http\Controllers\Teacher\TeacherDashboardController;
use App\Http\Controllers\Teacher\AttendanceController;
use App\Http\Controllers\Teacher\BehavioralReportController;

// ─── Root ────────────────────────────────────────────────────
Route::get('/', function () {
    return redirect('/login');
});

// ─── Auth (Breeze) ───────────────────────────────────────────
require __DIR__.'/auth.php';

// ─── Post-login Role Redirect ────────────────────────────────
Route::get('/dashboard', function () {
    return match(auth()->user()->role) {
        'admin'              => redirect()->route('admin.dashboard'),
        'guidance_counselor' => redirect()->route('counselor.dashboard'),
        'teacher'            => redirect()->route('teacher.dashboard'),
        'student'            => redirect()->route('student.dashboard'),
        default              => redirect('/login'),
    };
})->middleware('auth')->name('dashboard');

// ─── Admin Routes ─────────────────────────────────────────────
Route::prefix('admin')
    ->middleware(['auth', 'admin'])
    ->name('admin.')
    ->group(function () {

    // Overview Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])
         ->name('dashboard');

    // Students
    Route::resource('students', StudentController::class);

    // Referrals (admin view)
    Route::get('/referrals', [ReferralController::class, 'index'])
         ->name('referrals.index');
    Route::get('/referrals/{referral}', [ReferralController::class, 'show'])
         ->name('referrals.show');

    // At-Risk Students
    Route::get('/risk', function () {
        return view('admin.risk.index');
    })->name('risk.index');

    // User Management
    Route::resource('users', UserController::class);

    // Seminars
    Route::resource('seminars', SeminarController::class);

    // SMS Logs
    Route::get('/sms-logs', [SmsLogController::class, 'index'])
         ->name('sms-logs.index');
});

// ─── Guidance Counselor Routes ────────────────────────────────
Route::prefix('counselor')
    ->middleware(['auth', 'counselor'])
    ->name('counselor.')
    ->group(function () {

    Route::get('/dashboard', [CounselorDashboardController::class, 'index'])
         ->name('dashboard');

    Route::resource('referrals', ReferralController::class);
    Route::patch('referrals/{referral}/status',
        [ReferralController::class, 'updateStatus'])
         ->name('referrals.updateStatus');

    Route::resource('interventions', InterventionController::class);
});

// ─── Teacher Routes ───────────────────────────────────────────
Route::prefix('teacher')
    ->middleware(['auth', 'teacher'])
    ->name('teacher.')
    ->group(function () {

    Route::get('/dashboard', [TeacherDashboardController::class, 'index'])
         ->name('dashboard');

    Route::resource('attendance', AttendanceController::class);
    Route::post('attendance/bulk',
        [AttendanceController::class, 'bulkStore'])
         ->name('attendance.bulk');

    Route::resource('behavioral-reports', BehavioralReportController::class);
});

// ─── Student Routes ───────────────────────────────────────────
Route::prefix('student')
    ->middleware(['auth', 'student'])
    ->name('student.')
    ->group(function () {

    Route::get('/dashboard', function () {
        return view('student.dashboard');
    })->name('dashboard');
});

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\SeminarController;
use App\Http\Controllers\Admin\SmsLogController;
use App\Http\Controllers\Counselor\CounselorDashboardController;
use App\Http\Controllers\Admin\ReferralController as AdminReferralController;
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

    // Referrals (admin management)
    Route::get('/referrals/export', [AdminReferralController::class, 'export'])
         ->name('referrals.export');
    Route::post('/referrals/bulk-action', [AdminReferralController::class, 'bulkAction'])
         ->name('referrals.bulkAction');
    Route::get('/referrals', [AdminReferralController::class, 'index'])
         ->name('referrals.index');
    Route::get('/referrals/create', [AdminReferralController::class, 'create'])
         ->name('referrals.create');
    Route::post('/referrals', [AdminReferralController::class, 'store'])
         ->name('referrals.store');
    Route::get('/referrals/{referral}', [AdminReferralController::class, 'show'])
         ->name('referrals.show');
    Route::get('/referrals/{referral}/edit', [AdminReferralController::class, 'edit'])
         ->name('referrals.edit');
    Route::put('/referrals/{referral}', [AdminReferralController::class, 'update'])
         ->name('referrals.update');
    Route::delete('/referrals/{referral}', [AdminReferralController::class, 'destroy'])
         ->name('referrals.destroy');
    Route::patch('/referrals/{referral}/status', [AdminReferralController::class, 'updateStatus'])
         ->name('referrals.updateStatus');

    // At-Risk Students
    Route::get('/risk/export', [\App\Http\Controllers\Admin\RiskController::class, 'export'])
         ->name('risk.export');
    Route::post('/risk/bulk-action', [\App\Http\Controllers\Admin\RiskController::class, 'bulkAction'])
         ->name('risk.bulkAction');
    Route::get('/risk', [\App\Http\Controllers\Admin\RiskController::class, 'index'])
         ->name('risk.index');
    Route::get('/risk/{student}', [\App\Http\Controllers\Admin\RiskController::class, 'show'])
         ->name('risk.show');

    // User Management
    Route::resource('users', UserController::class);
    Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])
         ->name('users.reset-password');

    // SMS Logs
    Route::get('/sms-logs', [SmsLogController::class, 'index'])
         ->name('sms-logs.index');

    // Seminars
    Route::resource('seminars', SeminarController::class);
    Route::post('seminars/{seminar}/assign', [SeminarController::class, 'assignStudents'])
         ->name('seminars.assign');
    Route::patch('seminars/{seminar}/attendance', [SeminarController::class, 'updateAttendance'])
         ->name('seminars.attendance');
    Route::get('seminars/{seminar}/print', [SeminarController::class, 'printRoster'])
         ->name('seminars.print');
    Route::get('seminars/{seminar}/export-roster', [SeminarController::class, 'exportRoster'])
         ->name('seminars.export');

    // Attendance (read-only monitoring)
    Route::get('/attendance/export', [\App\Http\Controllers\Admin\AttendanceController::class, 'export'])
         ->name('attendance.export');
    Route::post('/attendance/bulk-sms', [\App\Http\Controllers\Admin\AttendanceController::class, 'bulkSmsWarning'])
         ->name('attendance.bulkSmsWarning');
    Route::post('/attendance/{student}/sms-warning', [\App\Http\Controllers\Admin\AttendanceController::class, 'sendWarningSms'])
         ->name('attendance.smsWarning');
    Route::get('/attendance', [\App\Http\Controllers\Admin\AttendanceController::class, 'index'])
         ->name('attendance.index');

    // Behavioral Reports (read-only monitoring)
    Route::get('/behavioral-reports', [\App\Http\Controllers\Admin\BehavioralReportController::class, 'index'])
         ->name('behavioral-reports.index');
    Route::get('/behavioral-reports/{behavioral_report}', [\App\Http\Controllers\Admin\BehavioralReportController::class, 'show'])
         ->name('behavioral-reports.show');
    Route::patch('/behavioral-reports/{behavioral_report}/status', [\App\Http\Controllers\Admin\BehavioralReportController::class, 'updateStatus'])
         ->name('behavioral-reports.updateStatus');

    // Teachers
    Route::get('/teachers/export', [\App\Http\Controllers\Admin\TeacherController::class, 'export'])
         ->name('teachers.export');
    Route::get('/teachers/{teacher}/print', [\App\Http\Controllers\Admin\TeacherController::class, 'print'])
         ->name('teachers.print');
    Route::get('/teachers', [\App\Http\Controllers\Admin\TeacherController::class, 'index'])
         ->name('teachers.index');
    Route::get('/teachers/{teacher}', [\App\Http\Controllers\Admin\TeacherController::class, 'show'])
         ->name('teachers.show');

    // Settings
    Route::get('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'index'])
         ->name('settings.index');
    Route::post('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'update'])
         ->name('settings.update');

    // ML Engine Retrain
    Route::post('/ml/retrain', [\App\Http\Controllers\Admin\MLController::class, 'retrain'])
         ->name('ml.retrain');
    Route::post('/ml/upload-csv', [\App\Http\Controllers\Admin\MLController::class, 'uploadCsv'])
         ->name('ml.upload-csv');
});

// ─── Guidance Counselor Routes ────────────────────────────────
Route::prefix('counselor')
    ->middleware(['auth', 'counselor'])
    ->name('counselor.')
    ->group(function () {

    Route::get('/dashboard', [CounselorDashboardController::class, 'index'])
         ->name('dashboard');

    Route::get('referrals/{referral}/print', [ReferralController::class, 'print'])->name('referrals.print');
    Route::post('referrals/{referral}/log-parent-contact', [ReferralController::class, 'logParentContact'])->name('referrals.logParentContact');
    Route::resource('referrals', ReferralController::class);
    Route::patch('referrals/{referral}/status',
        [ReferralController::class, 'updateStatus'])
         ->name('referrals.updateStatus');

    Route::resource('interventions', InterventionController::class);

    // Seminars
    Route::resource('seminars', \App\Http\Controllers\Counselor\SeminarController::class);
    Route::post('seminars/{seminar}/assign', [\App\Http\Controllers\Counselor\SeminarController::class, 'assignStudents'])
         ->name('seminars.assign');
    Route::patch('seminars/{seminar}/attendance', [\App\Http\Controllers\Counselor\SeminarController::class, 'updateAttendance'])
         ->name('seminars.attendance');
    Route::patch('seminars/{seminar}/cancel', [\App\Http\Controllers\Counselor\SeminarController::class, 'cancel'])
         ->name('seminars.cancel');
    Route::get('seminars/{seminar}/print', [\App\Http\Controllers\Counselor\SeminarController::class, 'printRoster'])
         ->name('seminars.print');
    Route::get('seminars/{seminar}/export-roster', [\App\Http\Controllers\Counselor\SeminarController::class, 'exportRoster'])
         ->name('seminars.export');
});

// ─── Teacher Routes ───────────────────────────────────────────
Route::prefix('teacher')
    ->middleware(['auth', 'teacher'])
    ->name('teacher.')
    ->group(function () {

    Route::get('/dashboard', [TeacherDashboardController::class, 'index'])
         ->name('dashboard');

    Route::get('attendance/export', [AttendanceController::class, 'export'])
         ->name('attendance.export');
    Route::resource('attendance', AttendanceController::class);
    Route::post('attendance/bulk',
        [AttendanceController::class, 'bulkStore'])
         ->name('attendance.bulk');

    Route::resource('behavioral-reports', BehavioralReportController::class);
    Route::resource('referrals', \App\Http\Controllers\Teacher\ReferralController::class)->only(['index', 'create', 'store']);
});

// ─── Student Routes ───────────────────────────────────────────
Route::prefix('student')
    ->middleware(['auth', 'student'])
    ->name('student.')
    ->group(function () {

    Route::get('/dashboard', function () {
        return view('student.dashboard');
    })->name('dashboard');

    Route::get('/seminars/{seminar}/checkin', [\App\Http\Controllers\Student\SeminarCheckinController::class, 'checkin'])
         ->name('seminars.checkin');
});

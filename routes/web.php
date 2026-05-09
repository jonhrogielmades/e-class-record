<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceCalendarController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\GuardianController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'landing'])->name('landing');
Route::get('/guardian', [GuardianController::class, 'index'])->name('guardian.index');
Route::post('/guardian', [GuardianController::class, 'lookup'])->name('guardian.lookup');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('/class-list', [SectionController::class, 'index'])->name('sections.index');
    Route::get('/students', [StudentController::class, 'index'])->name('students.index');
    Route::get('/grading', [GradeController::class, 'index'])->name('grades.index');
    Route::get('/attendance-calendar', [AttendanceCalendarController::class, 'index'])->name('attendance.calendar');
    Route::get('/announcements', [AnnouncementController::class, 'index'])->name('announcements.index');
    Route::get('/assignments', [AssignmentController::class, 'index'])->name('assignments.index');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.readAll');
    Route::post('/notifications/{id}/toggle-read', [NotificationController::class, 'toggleRead'])->name('notifications.toggleRead');
    Route::get('/reports/grades.csv', [ReportController::class, 'gradesCsv'])->name('reports.gradesCsv');
    Route::get('/reports/attendance.csv', [ReportController::class, 'attendanceCsv'])->name('reports.attendanceCsv');
    Route::get('/reports/print', [ReportController::class, 'print'])->name('reports.print');
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::delete('/settings', [SettingsController::class, 'destroy'])->name('settings.destroy');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
    Route::post('/admin/teachers', [AdminController::class, 'storeTeacher'])->name('admin.teachers.store');
    Route::delete('/admin/users/{managedUser}', [AdminController::class, 'destroyUser'])->name('admin.users.destroy');
    Route::delete('/admin/sections/{section}', [AdminController::class, 'destroySection'])->name('admin.sections.destroy');
});

Route::middleware(['auth', 'role:teacher'])->group(function () {
    Route::post('/class-list', [SectionController::class, 'store'])->name('sections.store');
    Route::put('/class-list/{section}', [SectionController::class, 'update'])->name('sections.update');
    Route::delete('/class-list/{section}', [SectionController::class, 'destroy'])->name('sections.destroy');

    Route::post('/students', [StudentController::class, 'store'])->name('students.store');
    Route::put('/students/{student}', [StudentController::class, 'update'])->name('students.update');
    Route::delete('/students/{student}', [StudentController::class, 'destroy'])->name('students.destroy');

    Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');
    Route::put('/attendance/{attendance}', [AttendanceController::class, 'update'])->name('attendance.update');
    Route::delete('/attendance/{attendance}', [AttendanceController::class, 'destroy'])->name('attendance.destroy');

    Route::post('/grading', [GradeController::class, 'store'])->name('grades.store');
    Route::put('/grading/{grade}', [GradeController::class, 'update'])->name('grades.update');
    Route::delete('/grading/{grade}', [GradeController::class, 'destroy'])->name('grades.destroy');

    Route::post('/announcements', [AnnouncementController::class, 'store'])->name('announcements.store');
    Route::put('/announcements/{announcement}', [AnnouncementController::class, 'update'])->name('announcements.update');
    Route::delete('/announcements/{announcement}', [AnnouncementController::class, 'destroy'])->name('announcements.destroy');

    Route::post('/assignments', [AssignmentController::class, 'store'])->name('assignments.store');
    Route::put('/assignments/{assignment}', [AssignmentController::class, 'update'])->name('assignments.update');
    Route::delete('/assignments/{assignment}', [AssignmentController::class, 'destroy'])->name('assignments.destroy');
});

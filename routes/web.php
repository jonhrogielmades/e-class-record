<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'landing'])->name('landing');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/class-list', [SectionController::class, 'index'])->name('sections.index');
    Route::get('/students', [StudentController::class, 'index'])->name('students.index');
    Route::get('/grading', [GradeController::class, 'index'])->name('grades.index');
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::delete('/settings', [SettingsController::class, 'destroy'])->name('settings.destroy');
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
});
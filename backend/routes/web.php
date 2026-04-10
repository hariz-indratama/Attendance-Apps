<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Public routes - redirect to login
Route::get('/', fn () => redirect()->route('login'))->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

    // Employee Management
    Route::get('/employees', [\App\Http\Controllers\EmployeeController::class, 'index'])->name('employees.index');
    Route::post('/employees', [\App\Http\Controllers\EmployeeController::class, 'store'])->name('employees.store');
    Route::get('/employees/{employee}', [\App\Http\Controllers\EmployeeController::class, 'show'])->name('employees.show');
    Route::put('/employees/{employee}', [\App\Http\Controllers\EmployeeController::class, 'update'])->name('employees.update');
    Route::delete('/employees/{employee}', [\App\Http\Controllers\EmployeeController::class, 'destroy'])->name('employees.destroy');
    Route::patch('/employees/{employee}/toggle-status', [\App\Http\Controllers\EmployeeController::class, 'toggleStatus'])->name('employees.toggle-status');

    // Attendance Management
    Route::get('/attendance', [\App\Http\Controllers\AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/attendance/user/{userId}', [\App\Http\Controllers\AttendanceController::class, 'showUserAttendance'])->name('attendance.user');
    Route::put('/attendance/{attendance}', [\App\Http\Controllers\AttendanceController::class, 'update'])->name('attendance.update');
    Route::delete('/attendance/{attendance}', [\App\Http\Controllers\AttendanceController::class, 'destroy'])->name('attendance.destroy');
    Route::get('/attendance/statistics', [\App\Http\Controllers\AttendanceController::class, 'statistics'])->name('attendance.statistics');
    Route::get('/attendance/chart', [\App\Http\Controllers\AttendanceController::class, 'chart'])->name('attendance.chart');
    Route::get('/attendance/today', [\App\Http\Controllers\AttendanceController::class, 'todaySummary'])->name('attendance.today');

    // Payroll Management
    Route::get('/payroll', [\App\Http\Controllers\PayrollController::class, 'index'])->name('payroll.index');
    Route::get('/payroll/employee/{employee}', [\App\Http\Controllers\PayrollController::class, 'show'])->name('payroll.show');
    Route::get('/payroll/employee/{employee}/edit', [\App\Http\Controllers\PayrollController::class, 'edit'])->name('payroll.edit');
    Route::put('/payroll/employee/{employee}', [\App\Http\Controllers\PayrollController::class, 'update'])->name('payroll.update');

    // Schedule Management
    Route::get('/schedules', [\App\Http\Controllers\ScheduleController::class, 'index'])->name('schedules.index');
    Route::post('/schedules', [\App\Http\Controllers\ScheduleController::class, 'store'])->name('schedules.store');
    Route::post('/schedules/bulk', [\App\Http\Controllers\ScheduleController::class, 'bulkStore'])->name('schedules.bulk');
    Route::put('/schedules/{schedule}', [\App\Http\Controllers\ScheduleController::class, 'update'])->name('schedules.update');
    Route::delete('/schedules/{schedule}', [\App\Http\Controllers\ScheduleController::class, 'destroy'])->name('schedules.destroy');
    Route::patch('/schedules/{schedule}/toggle-status', [\App\Http\Controllers\ScheduleController::class, 'toggleStatus'])->name('schedules.toggle-status');

    // Reports
    Route::get('/reports', [\App\Http\Controllers\ReportsController::class, 'index'])->name('reports.index');
    Route::post('/reports/attendance', [\App\Http\Controllers\ReportsController::class, 'attendance'])->name('reports.attendance');
    Route::post('/reports/payroll', [\App\Http\Controllers\ReportsController::class, 'payroll'])->name('reports.payroll');

    // Settings
    Route::get('/settings', [\App\Http\Controllers\SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings/company', [\App\Http\Controllers\SettingsController::class, 'updateCompany'])->name('settings.company');
    Route::put('/settings/work-hours', [\App\Http\Controllers\SettingsController::class, 'updateWorkHours'])->name('settings.work-hours');
    Route::put('/settings/attendance', [\App\Http\Controllers\SettingsController::class, 'updateAttendance'])->name('settings.attendance');
    Route::put('/settings/notifications', [\App\Http\Controllers\SettingsController::class, 'updateNotifications'])->name('settings.notifications');

    // Profile
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/avatar', [\App\Http\Controllers\ProfileController::class, 'updateAvatar'])->name('profile.avatar');
    Route::delete('/profile/avatar', [\App\Http\Controllers\ProfileController::class, 'deleteAvatar'])->name('profile.avatar.delete');
    Route::put('/profile/password', [\App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::put('/profile/bank', [\App\Http\Controllers\ProfileController::class, 'updateBank'])->name('profile.bank');

    Route::post('/logout', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

require __DIR__.'/auth.php';

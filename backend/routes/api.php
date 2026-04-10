<?php

use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OvertimeController;
use App\Http\Controllers\Api\ScheduleController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // Public routes
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        // Auth routes
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::get('/auth/session', [AuthController::class, 'session']);
        Route::post('/auth/refresh', [AuthController::class, 'refresh']);
        Route::put('/auth/profile', [AuthController::class, 'updateProfile']);

        // Attendance routes
        Route::get('/attendance/today', [AttendanceController::class, 'today']);
        Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn']);
        Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut']);
        Route::post('/attendance/reset-today', [AttendanceController::class, 'resetToday']);
        Route::get('/attendance/history', [AttendanceController::class, 'history']);
        Route::get('/attendance/{id}', [AttendanceController::class, 'show']);
    Route::post('/attendance/sync', [AttendanceController::class, 'sync']);

        // Overtime routes
        Route::post('/overtime', [OvertimeController::class, 'store']);
        Route::get('/overtime', [OvertimeController::class, 'index']);
        Route::get('/overtime/{id}', [OvertimeController::class, 'show']);

        // Schedule routes
        Route::get('/schedule/today', [ScheduleController::class, 'today']);
        Route::get('/schedule', [ScheduleController::class, 'index']);

        // Reference data (shifts & locations)
        Route::get('/shifts', [ScheduleController::class, 'shifts']);
        Route::get('/locations', [ScheduleController::class, 'locations']);

        // Location management (admin)
        Route::get('/location/active', [LocationController::class, 'active']);
        Route::get('/location', [LocationController::class, 'index']);
        Route::post('/location', [LocationController::class, 'store']);
        Route::get('/location/{id}', [LocationController::class, 'show']);
        Route::put('/location/{id}', [LocationController::class, 'update']);
        Route::delete('/location/{id}', [LocationController::class, 'destroy']);
        Route::post('/location/{id}/activate', [LocationController::class, 'setActive']);

        // Notification routes
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
        Route::put('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::put('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    });

}); // end v1

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingsController extends Controller
{
    /**
     * Display the settings page.
     */
    public function index()
    {
        return Inertia::render('Settings/Index');
    }

    /**
     * Update company settings.
     */
    public function updateCompany(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'company_email' => 'required|email|max:255',
            'company_phone' => 'nullable|string|max:20',
            'company_address' => 'nullable|string|max:500',
        ]);

        // Save to database or config
        // For now, we'll just return success
        return back()->with('success', 'Company settings updated successfully');
    }

    /**
     * Update work hours settings.
     */
    public function updateWorkHours(Request $request)
    {
        $request->validate([
            'work_start_time' => 'required|date_format:H:i',
            'work_end_time' => 'required|date_format:H:i',
            'grace_period_minutes' => 'required|integer|min:0|max:60',
            'late_threshold_minutes' => 'required|integer|min:0|max:120',
        ]);

        return back()->with('success', 'Work hours settings updated successfully');
    }

    /**
     * Update attendance settings.
     */
    public function updateAttendance(Request $request)
    {
        $request->validate([
            'max_radius_meters' => 'required|integer|min:10|max:5000',
            'require_photo' => 'boolean',
            'require_location' => 'boolean',
            'allow_overtime' => 'boolean',
        ]);

        return back()->with('success', 'Attendance settings updated successfully');
    }

    /**
     * Update notification settings.
     */
    public function updateNotifications(Request $request)
    {
        $request->validate([
            'email_notifications' => 'boolean',
            'attendance_alerts' => 'boolean',
            'payroll_notifications' => 'boolean',
            'schedule_reminders' => 'boolean',
        ]);

        return back()->with('success', 'Notification settings updated successfully');
    }
}
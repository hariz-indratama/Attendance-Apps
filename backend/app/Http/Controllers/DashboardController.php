<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with real data.
     */
    public function index(Request $request)
    {
        $today = Carbon::today();

        // Get total employees count
        $totalEmployees = User::where('is_active', true)->count();

        // Get today's attendance stats
        $todayStats = $this->getTodayStats($today);

        // Get weekly attendance data for chart
        $weekData = $this->getWeekAttendanceData();

        // Get recent activities (latest attendance records)
        $recentActivities = $this->getRecentActivities();

        // Get upcoming schedules (next 7 days)
        $upcomingSchedules = $this->getUpcomingSchedules();

        return Inertia::render('Dashboard', [
            'stats' => [
                'total_employees' => $totalEmployees,
                'present_today' => $todayStats['present'],
                'on_leave' => $todayStats['on_leave'],
                'absent_today' => $todayStats['absent'],
            ],
            'week_data' => $weekData,
            'recent_activities' => $recentActivities,
            'upcoming_schedules' => $upcomingSchedules,
        ]);
    }

    /**
     * Get today's attendance statistics.
     */
    private function getTodayStats(Carbon $date)
    {
        $totalAttendance = Attendance::where('date', $date)->count();
        $totalEmployees = User::where('is_active', true)->count();

        // Count present (hadir, terlambat)
        $present = Attendance::where('date', $date)
            ->whereIn('status', ['hadir', 'terlambat'])
            ->count();

        // Count on leave (izin, sakit)
        $onLeave = Attendance::where('date', $date)
            ->whereIn('status', ['izin', 'sakit'])
            ->count();

        // Count absent (alpha)
        $absent = Attendance::where('date', $date)
            ->where('status', 'alpha')
            ->count();

        // If no attendance records exist today, calculate based on employees
        if ($totalAttendance === 0) {
            $present = $totalEmployees; // Assume all present if no records
            $absent = 0;
            $onLeave = 0;
        } else {
            // Check employees without attendance record
            $attendedUserIds = Attendance::where('date', $date)->pluck('user_id')->toArray();
            $activeUserIds = User::where('is_active', true)->pluck('id')->toArray();
            $notRecorded = array_diff($activeUserIds, $attendedUserIds);
            $absent += count($notRecorded);
        }

        return [
            'present' => $present,
            'on_leave' => $onLeave,
            'absent' => $absent,
            'total' => $totalEmployees,
        ];
    }

    /**
     * Get weekly attendance data for chart.
     */
    private function getWeekAttendanceData()
    {
        $data = [];
        $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);

            $total = Attendance::where('date', $date)->count();
            $present = Attendance::where('date', $date)
                ->whereIn('status', ['hadir', 'terlambat'])
                ->count();
            $onLeave = Attendance::where('date', $date)
                ->whereIn('status', ['izin', 'sakit'])
                ->count();
            $absent = Attendance::where('date', $date)
                ->where('status', 'alpha')
                ->count();

            // Calculate percentages based on active employees
            $totalEmployees = User::where('is_active', true)->count();
            if ($totalEmployees > 0) {
                $presentPercent = round(($present / $totalEmployees) * 100);
                $leavePercent = round(($onLeave / $totalEmployees) * 100);
                $absentPercent = round(($absent / $totalEmployees) * 100);
            } else {
                $presentPercent = 0;
                $leavePercent = 0;
                $absentPercent = 0;
            }

            $data[] = [
                'label' => $days[$date->dayOfWeek - 1] ?? $days[0],
                'present' => $presentPercent,
                'presentValue' => $present,
                'leave' => $leavePercent,
                'absent' => $absentPercent,
            ];
        }

        return $data;
    }

    /**
     * Get recent attendance activities.
     */
    private function getRecentActivities()
    {
        $attendances = Attendance::with('user')
            ->whereNotNull('clock_in_time')
            ->orderBy('clock_in_time', 'desc')
            ->limit(5)
            ->get();

        return $attendances->map(function ($attendance) {
            $name = $attendance->user ? $attendance->user->name : 'Unknown';
            $initials = $attendance->user
                ? implode('', array_map(fn($n) => strtoupper($n[0]), explode(' ', $name)))
                : 'UN';

            $action = 'Checked In';
            $type = 'in';

            if ($attendance->clock_out_time) {
                $action = 'Checked Out';
                $type = 'out';
            }

            return [
                'name' => $name,
                'initials' => substr($initials, 0, 2),
                'action' => $action,
                'time' => $attendance->clock_in_time ? Carbon::parse($attendance->clock_in_time)->format('h:i A') : 'N/A',
                'type' => $type,
            ];
        });
    }

    /**
     * Get upcoming schedules (placeholder - can be expanded).
     */
    private function getUpcomingSchedules()
    {
        // For now, return placeholder data
        // This can be expanded when Schedule model is populated
        return [
            ['name' => 'Team Meeting', 'date' => 'Today', 'time' => '10:00 AM', 'type' => 'meeting'],
            ['name' => 'Project Review', 'date' => 'Today', 'time' => '02:00 PM', 'type' => 'review'],
            ['name' => 'Office Closed', 'date' => 'Friday', 'time' => '17 Feb', 'type' => 'holiday'],
        ];
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use App\Models\Shift;
use App\Models\Location;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Display a listing of attendances.
     */
    public function index(Request $request)
    {
        $query = Attendance::with(['user', 'shift', 'location']);

        // Date range filter
        if ($request->has('date_from') && $request->date_from) {
            $query->where('date', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $query->where('date', '<=', $request->date_to);
        }

        // Single date filter
        if ($request->has('date') && $request->date) {
            $query->where('date', $request->date);
        }

        // Employee filter
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        // Status filter
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Shift filter
        if ($request->has('shift_id') && $request->shift_id) {
            $query->where('shift_id', $request->shift_id);
        }

        // Location filter
        if ($request->has('location_id') && $request->location_id) {
            $query->where('location_id', $request->location_id);
        }

        // Sort
        $sortBy = $request->get('sortBy', 'date');
        $sortDir = $request->get('sortDir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        // Pagination
        $perPage = $request->get('perPage', 25);
        $attendances = $query->paginate($perPage);

        // Get filters data
        $employees = User::where('is_active', true)->orderBy('name')->get(['id', 'name', 'employee_id']);
        $shifts = Shift::where('is_active', true)->get();
        $locations = Location::where('is_active', true)->get();

        // Statistics
        $stats = $this->getStatistics($request);

        return Inertia::render('Attendance/Index', [
            'attendances' => $attendances,
            'employees' => $employees,
            'shifts' => $shifts,
            'locations' => $locations,
            'stats' => $stats,
            'filters' => $request->only([
                'date_from', 'date_to', 'date', 'user_id', 'status',
                'shift_id', 'location_id', 'sortBy', 'sortDir', 'perPage'
            ]),
        ]);
    }

    /**
     * Display attendance for a specific employee.
     */
    public function showUserAttendance(Request $request, int $userId)
    {
        $user = User::with(['shift', 'location'])->findOrFail($userId);

        $query = Attendance::where('user_id', $userId)
            ->with(['shift', 'location']);

        // Date range filter
        if ($request->has('date_from') && $request->date_from) {
            $query->where('date', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $query->where('date', '<=', $request->date_to);
        }

        // Status filter
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $sortBy = $request->get('sortBy', 'date');
        $sortDir = $request->get('sortDir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $perPage = $request->get('perPage', 31);
        $attendances = $query->paginate($perPage);

        // Get statistics for this user
        $stats = $this->getUserStatistics($userId, $request);

        return Inertia::render('Attendance/User', [
            'user' => $user,
            'attendances' => $attendances,
            'stats' => $stats,
            'filters' => $request->only(['date_from', 'date_to', 'status', 'sortBy', 'sortDir', 'perPage']),
        ]);
    }

    /**
     * Get statistics for dashboard.
     */
    public function statistics(Request $request)
    {
        return response()->json($this->getStatistics($request));
    }

    /**
     * Get attendance summary for today.
     */
    public function todaySummary()
    {
        $today = Carbon::today();

        $total = Attendance::where('date', $today)->count();
        $present = Attendance::where('date', $today)
            ->whereIn('status', ['hadir', 'terlambat'])
            ->count();
        $late = Attendance::where('date', $today)
            ->where('status', 'terlambat')
            ->count();
        $absent = Attendance::where('date', $today)
            ->where('status', 'alpha')
            ->count();

        return response()->json([
            'total' => $total,
            'present' => $present,
            'late' => $late,
            'absent' => $absent,
        ]);
    }

    /**
     * Get attendance chart data.
     */
    public function chart(Request $request)
    {
        $days = $request->get('days', 7);
        $startDate = Carbon::today()->subDays($days - 1);

        $data = [];
        for ($i = 0; $i < $days; $i++) {
            $date = $startDate->copy()->addDays($i);

            $present = Attendance::where('date', $date)
                ->whereIn('status', ['hadir', 'terlambat'])
                ->count();

            $late = Attendance::where('date', $date)
                ->where('status', 'terlambat')
                ->count();

            $absent = Attendance::where('date', $date)
                ->where('status', 'alpha')
                ->count();

            $data[] = [
                'date' => $date->format('Y-m-d'),
                'label' => $date->format('D'),
                'present' => $present,
                'late' => $late,
                'absent' => $absent,
            ];
        }

        return response()->json($data);
    }

    /**
     * Update attendance record (for manual correction).
     */
    public function update(Request $request, Attendance $attendance)
    {
        $validated = $request->validate([
            'clock_in_time' => 'nullable|date_format:H:i:s',
            'clock_out_time' => 'nullable|date_format:H:i:s|after:clock_in_time',
            'status' => 'required|in:hadir,terlambat,alpha,izin,sakit',
            'notes' => 'nullable|string|max:500',
        ]);

        $attendance->update($validated);

        return redirect()->back()->with('success', 'Attendance updated successfully.');
    }

    /**
     * Delete attendance record.
     */
    public function destroy(Attendance $attendance)
    {
        $attendance->delete();

        return redirect()->back()->with('success', 'Attendance record deleted successfully.');
    }

    /**
     * Get statistics based on filters.
     */
    private function getStatistics(Request $request)
    {
        $query = Attendance::query();

        if ($request->has('date_from') && $request->date_from) {
            $query->where('date', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $query->where('date', '<=', $request->date_to);
        }
        if ($request->has('date') && $request->date) {
            $query->where('date', $request->date);
        }
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        $total = $query->count();

        $present = (clone $query)->whereIn('status', ['hadir', 'terlambat', 'onsite'])->count();
        $onTime = (clone $query)->where('status', 'hadir')->count();
        $late = (clone $query)->where('status', 'terlambat')->count();
        $absent = (clone $query)->where('status', 'alpha')->count();
        $permission = (clone $query)->whereIn('status', ['izin', 'sakit'])->count();
        $leave = 0;

        // Calculate average work hours
        $attendancesWithDuration = (clone $query)->whereNotNull('clock_in_time')
            ->whereNotNull('clock_out_time')
            ->get();

        $totalMinutes = 0;
        $countWithDuration = 0;
        foreach ($attendancesWithDuration as $att) {
            if ($att->clock_in_time && $att->clock_out_time) {
                $start = strtotime($att->clock_in_time);
                $end = strtotime($att->clock_out_time);
                $diff = $end - $start;
                if ($diff > 0) {
                    $totalMinutes += $diff / 60;
                    $countWithDuration++;
                }
            }
        }

        $avgHours = $countWithDuration > 0 ? round($totalMinutes / $countWithDuration / 60, 1) : 0;

        return [
            'total' => $total,
            'present' => $present,
            'on_time' => $onTime,
            'late' => $late,
            'absent' => $absent,
            'permission' => $permission,
            'leave' => $leave,
            'avg_hours' => $avgHours,
        ];
    }

    /**
     * Get statistics for specific user.
     */
    private function getUserStatistics(int $userId, Request $request)
    {
        $query = Attendance::where('user_id', $userId);

        if ($request->has('date_from') && $request->date_from) {
            $query->where('date', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $query->where('date', '<=', $request->date_to);
        }

        $total = $query->count();
        $present = (clone $query)->whereIn('status', ['hadir', 'terlambat', 'onsite'])->count();
        $late = (clone $query)->where('status', 'terlambat')->count();
        $absent = (clone $query)->where('status', 'alpha')->count();
        $permission = (clone $query)->whereIn('status', ['izin', 'sakit'])->count();
        $leave = 0;

        return [
            'total' => $total,
            'present' => $present,
            'late' => $late,
            'absent' => $absent,
            'permission' => $permission,
            'leave' => $leave,
        ];
    }
}

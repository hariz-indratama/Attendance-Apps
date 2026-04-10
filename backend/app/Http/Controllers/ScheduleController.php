<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\User;
use App\Models\Shift;
use App\Models\Location;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    /**
     * Display a listing of schedules.
     */
    public function index(Request $request)
    {
        $query = Schedule::with(['user', 'shift', 'location']);

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

        // Shift filter
        if ($request->has('shift_id') && $request->shift_id) {
            $query->where('shift_id', $request->shift_id);
        }

        // Location filter
        if ($request->has('location_id') && $request->location_id) {
            $query->where('location_id', $request->location_id);
        }

        // Status filter
        if ($request->has('is_active') && $request->is_active !== '') {
            $query->where('is_active', $request->is_active === 'true');
        }

        // Sort
        $sortBy = $request->get('sortBy', 'date');
        $sortDir = $request->get('sortDir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        // Pagination
        $perPage = $request->get('perPage', 25);
        $schedules = $query->paginate($perPage);

        // Get filters data
        $employees = User::where('is_active', true)->orderBy('name')->get(['id', 'name', 'employee_id']);
        $shifts = Shift::where('is_active', true)->get();
        $locations = Location::where('is_active', true)->get();

        // Statistics
        $stats = $this->getStatistics($request);

        return Inertia::render('Schedule/Index', [
            'schedules' => $schedules,
            'employees' => $employees,
            'shifts' => $shifts,
            'locations' => $locations,
            'stats' => $stats,
            'filters' => $request->only([
                'date_from', 'date_to', 'date', 'user_id', 'shift_id',
                'location_id', 'is_active', 'sortBy', 'sortDir', 'perPage'
            ]),
        ]);
    }

    /**
     * Store a newly created schedule.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'shift_id' => 'required|exists:shifts,id',
            'location_id' => 'required|exists:locations,id',
            'date' => 'required|date',
            'is_active' => 'boolean',
        ]);

        // Check for duplicate schedule (same user, same date)
        $exists = Schedule::where('user_id', $validated['user_id'])
            ->where('date', $validated['date'])
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->with('error', 'Schedule already exists for this employee on this date.');
        }

        Schedule::create($validated);

        return redirect()->route('schedules.index')
            ->with('success', 'Schedule created successfully.');
    }

    /**
     * Update the specified schedule.
     */
    public function update(Request $request, Schedule $schedule)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'shift_id' => 'required|exists:shifts,id',
            'location_id' => 'required|exists:locations,id',
            'date' => 'required|date',
            'is_active' => 'boolean',
        ]);

        // Check for duplicate schedule (excluding current record)
        $exists = Schedule::where('user_id', $validated['user_id'])
            ->where('date', $validated['date'])
            ->where('id', '!=', $schedule->id)
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->with('error', 'Schedule already exists for this employee on this date.');
        }

        $schedule->update($validated);

        return redirect()->route('schedules.index')
            ->with('success', 'Schedule updated successfully.');
    }

    /**
     * Remove the specified schedule.
     */
    public function destroy(Schedule $schedule)
    {
        $schedule->delete();

        return redirect()->route('schedules.index')
            ->with('success', 'Schedule deleted successfully.');
    }

    /**
     * Toggle schedule active status.
     */
    public function toggleStatus(Schedule $schedule)
    {
        $schedule->update(['is_active' => !$schedule->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $schedule->is_active,
        ]);
    }

    /**
     * Bulk create schedules.
     */
    public function bulkStore(Request $request)
    {
        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'shift_id' => 'required|exists:shifts,id',
            'location_id' => 'required|exists:locations,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);
        $created = 0;
        $skipped = 0;

        foreach ($validated['user_ids'] as $userId) {
            $currentDate = $startDate->copy();

            while ($currentDate->lte($endDate)) {
                // Skip weekends (Saturday = 6, Sunday = 0)
                if ($currentDate->dayOfWeek !== 0 && $currentDate->dayOfWeek !== 6) {
                    $exists = Schedule::where('user_id', $userId)
                        ->where('date', $currentDate->format('Y-m-d'))
                        ->exists();

                    if (!$exists) {
                        Schedule::create([
                            'user_id' => $userId,
                            'shift_id' => $validated['shift_id'],
                            'location_id' => $validated['location_id'],
                            'date' => $currentDate->format('Y-m-d'),
                            'is_active' => true,
                        ]);
                        $created++;
                    } else {
                        $skipped++;
                    }
                }
                $currentDate->addDay();
            }
        }

        $message = "Created {$created} schedules.";
        if ($skipped > 0) {
            $message .= " Skipped {$skipped} existing schedules.";
        }

        return redirect()->route('schedules.index')
            ->with('success', $message);
    }

    /**
     * Get statistics.
     */
    private function getStatistics(Request $request)
    {
        $query = Schedule::query();

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
        if ($request->has('shift_id') && $request->shift_id) {
            $query->where('shift_id', $request->shift_id);
        }

        $total = $query->count();
        $active = (clone $query)->where('is_active', true)->count();
        $inactive = (clone $query)->where('is_active', false)->count();

        // This week
        $thisWeekStart = Carbon::now()->startOfWeek();
        $thisWeekEnd = Carbon::now()->endOfWeek();
        $thisWeek = (clone $query)->whereBetween('date', [$thisWeekStart, $thisWeekEnd])->count();

        // Next week
        $nextWeekStart = Carbon::now()->startOfWeek()->addWeek();
        $nextWeekEnd = Carbon::now()->endOfWeek()->addWeek();
        $nextWeek = (clone $query)->whereBetween('date', [$nextWeekStart, $nextWeekEnd])->count();

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
            'this_week' => $thisWeek,
            'next_week' => $nextWeek,
        ];
    }
}

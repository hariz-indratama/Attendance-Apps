<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\Shift;
use App\Models\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ScheduleController extends Controller
{
    /**
     * Get user's schedule for today.
     */
    public function today(Request $request): JsonResponse
    {
        $today = now()->toDateString();

        $schedule = Schedule::where('user_id', $request->user()->id)
            ->where('date', $today)
            ->with(['shift', 'location'])
            ->first();

        return response()->json([
            'schedule' => $schedule,
        ]);
    }

    /**
     * Get user's schedules.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'month' => 'nullable|integer|between:1,12',
            'year' => 'nullable|integer|between:2020,2030',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = Schedule::where('user_id', $request->user()->id)
            ->with(['shift', 'location']);

        if ($request->month && $request->year) {
            $query->whereMonth('date', $request->month)
                  ->whereYear('date', $request->year);
        }

        $schedules = $query->orderBy('date', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json($schedules);
    }

    /**
     * Get all available shifts (for admin/manager).
     */
    public function shifts(Request $request): JsonResponse
    {
        $shifts = Shift::where('is_active', true)->get();

        return response()->json([
            'shifts' => $shifts,
        ]);
    }

    /**
     * Get all available locations (for admin/manager).
     */
    public function locations(Request $request): JsonResponse
    {
        $locations = Location::where('is_active', true)->get();

        return response()->json([
            'locations' => $locations,
        ]);
    }
}

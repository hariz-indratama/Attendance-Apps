
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Schedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\GeoService;
use Illuminate\Support\Facades\Validator;

class AttendanceController extends Controller
{
    public function __construct(
        private readonly GeoService $geoService,
    ) {}
    /**
     * Get today's attendance status.
     */
    public function today(Request $request): JsonResponse
    {
        $today = now()->toDateString();

        $attendance = Attendance::where('user_id', $request->user()->id)
            ->where('date', $today)
            ->with(['shift', 'location'])
            ->first();

        $schedule = Schedule::where('user_id', $request->user()->id)
            ->where('date', $today)
            ->with(['shift', 'location'])
            ->first();

        return response()->json([
            'attendance' => $attendance,
            'schedule' => $schedule,
        ]);
    }

    /**
     * Clock in.
     */
    public function clockIn(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'photo' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        $today = now()->toDateString();
        $now = now();

        // Get user's assigned location
        $location = $request->user()->location;
        $schedule = Schedule::where('user_id', $request->user()->id)
            ->where('date', $today)
            ->first();

        // Validate location if exists
        $isWithinRadius = true;
        $distance = null;
        $radiusUsed = null;

        if ($location && $location->latitude && $location->longitude) {
            $distance = $this->geoService->calculateDistance(
                $request->latitude,
                $request->longitude,
                $location->latitude,
                $location->longitude
            );
            $radiusUsed = $location->getEffectiveRadius();
            $isWithinRadius = $distance <= $radiusUsed;
        }

        // Determine status based on schedule
        $status = 'pending';
        if ($schedule && $schedule->shift) {
            $scheduleStartTime = $schedule->shift->start_time;
            $currentTime = $now->format('H:i:s');

            if ($currentTime > $scheduleStartTime) {
                $status = 'terlambat';
            } else {
                $status = 'hadir';
            }
        }

        $attendance = Attendance::firstOrCreate(
            [
                'user_id' => $request->user()->id,
                'date' => $today,
            ],
            [
                'clock_in_time' => $now->format('H:i:s'),
                'clock_in_latitude' => $request->latitude,
                'clock_in_longitude' => $request->longitude,
                'clock_in_photo' => $request->hasFile('photo') ? $request->file('photo')->store('photos') : null,
                'schedule_id' => $schedule?->id,
                'shift_id' => $schedule?->shift_id ?? $request->user()->shift_id,
                'location_id' => $location?->id,
                'status' => $status,
            ]
        );

        // If record already existed, only fill in clock_in fields if still empty
        if ($attendance->wasRecentlyCreated === false && !$attendance->clock_in_time) {
            $attendance->update([
                'clock_in_time' => $now->format('H:i:s'),
                'clock_in_latitude' => $request->latitude,
                'clock_in_longitude' => $request->longitude,
                'clock_in_photo' => $request->hasFile('photo') ? $request->file('photo')->store('photos') : null,
                'schedule_id' => $schedule?->id,
                'shift_id' => $schedule?->shift_id ?? $request->user()->shift_id,
                'location_id' => $location?->id,
                'status' => $status,
            ]);
        }

        if (!$isWithinRadius) {
            return response()->json([
                'message' => 'You are outside the office location. Clock in rejected.',
                'distance' => $distance,
                'radius_used' => $location?->getEffectiveRadius(),
            ], 400);
        }

        return response()->json([
            'message' => 'Clock in successful',
            'attendance' => $attendance->load(['shift', 'location']),
            'is_within_radius' => $isWithinRadius,
            'distance' => $distance,
            'radius_used' => $location?->getEffectiveRadius(),
        ]);
    }

    /**
     * Clock out.
     */
    public function clockOut(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'photo' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        $today = now()->toDateString();
        $now = now();

        $attendance = Attendance::where('user_id', $request->user()->id)
            ->where('date', $today)
            ->first();

        if (!$attendance || !$attendance->clock_in_time) {
            return response()->json([
                'message' => 'You have not clocked in today',
            ], 400);
        }

        if ($attendance->clock_out_time) {
            return response()->json([
                'message' => 'You have already clocked out today',
            ], 400);
        }

        // Validate location
        $location = $attendance->location;
        $isWithinRadius = true;
        $distance = null;
        $radiusUsed = null;

        if (!$location) {
            // No location assigned - allow clock out
        } elseif (!$location->latitude || !$location->longitude) {
            // Location exists but no coordinates - allow clock out
        } else {
            $distance = $this->geoService->calculateDistance(
                $request->latitude,
                $request->longitude,
                $location->latitude,
                $location->longitude
            );
            $radiusUsed = $location->getEffectiveRadius();
            $isWithinRadius = $distance <= $radiusUsed;
        }

        $attendance->update([
            'clock_out_time' => $now->format('H:i:s'),
            'clock_out_latitude' => $request->latitude,
            'clock_out_longitude' => $request->longitude,
            'clock_out_photo' => $request->hasFile('photo') ? $request->file('photo')->store('photos') : null,
        ]);

        return response()->json([
            'message' => $isWithinRadius ? 'Clock out successful' : 'Clock out successful but you are outside the office location',
            'attendance' => $attendance->fresh(['shift', 'location']),
            'is_within_radius' => $isWithinRadius,
            'distance' => $distance,
            'radius_used' => $radiusUsed,
        ]);
    }

    /**
     * Get attendance history.
     * Optimized: Removed unnecessary relationships and only select needed fields.
     */
    public function history(Request $request): JsonResponse
    {
        $request->validate([
            'month' => 'nullable|integer|between:1,12',
            'year' => 'nullable|integer|between:2020,2030',
            'status' => 'nullable|string',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        // Only select needed fields - avoid loading unnecessary data
        $query = Attendance::where('user_id', $request->user()->id)
            ->select([
                'id',
                'user_id',
                'date',
                'clock_in_time',
                'clock_out_time',
                'status',
            ]);

        if ($request->month && $request->year) {
            $query->whereMonth('date', $request->month)
                  ->whereYear('date', $request->year);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $attendances = $query->orderBy('date', 'desc')
            ->paginate($request->per_page ?? 31);

        return response()->json($attendances);
    }

    /**
     * Get attendance detail.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $attendance = Attendance::where('user_id', $request->user()->id)
            ->with(['shift', 'location', 'user'])
            ->findOrFail($id);

        return response()->json([
            'attendance' => $attendance,
            'work_duration' => $attendance->work_duration,
        ]);
    }

    /**
     * Reset today's attendance for testing purposes.
     * This allows testing clock in/out cycle repeatedly.
     */
    public function resetToday(Request $request): JsonResponse
    {
        $today = now()->toDateString();

        // Find today's attendance record
        $attendance = Attendance::where('user_id', $request->user()->id)
            ->where('date', $today)
            ->first();

        if ($attendance) {
            // Reset all clock in/out fields
            $attendance->update([
                'clock_in_time' => null,
                'clock_in_latitude' => null,
                'clock_in_longitude' => null,
                'clock_in_photo' => null,
                'clock_out_time' => null,
                'clock_out_latitude' => null,
                'clock_out_longitude' => null,
                'clock_out_photo' => null,
                'work_duration' => null,
                'status' => 'pending',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Attendance reset successfully. You can clock in again.',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'No attendance record to reset for today.',
        ]);
    }

    /**
     * Sync an offline-queued attendance record.
     * Idempotent: skips if a record for this user+date already exists server-side.
     */
    public function sync(Request $request): JsonResponse
    {
        $request->validate([
            'date' => 'required|date',
            'clock_in_time' => 'nullable',
            'clock_in_latitude' => 'nullable|numeric|between:-90,90',
            'clock_in_longitude' => 'nullable|numeric|between:-180,180',
            'clock_in_photo' => 'nullable|image|max:2048',
            'clock_out_time' => 'nullable',
            'clock_out_latitude' => 'nullable|numeric|between:-90,90',
            'clock_out_longitude' => 'nullable|numeric|between:-180,180',
            'clock_out_photo' => 'nullable|image|max:2048',
            'client_timestamp' => 'required|string',
        ]);

        $date = $request->date;
        $user = $request->user();

        // Idempotency: skip if attendance already exists for this user+date
        $existing = Attendance::where('user_id', $user->id)
            ->where('date', $date)
            ->first();

        if ($existing) {
            return response()->json([
                'synced' => true,
                'message' => 'Attendance already exists, skipped.',
                'attendance' => $existing,
            ]);
        }

        $location = $user->location;
        $schedule = Schedule::where('user_id', $user->id)
            ->where('date', $date)
            ->first();

        $clockInPath = $request->hasFile('clock_in_photo')
            ? $request->file('clock_in_photo')->store('photos')
            : null;
        $clockOutPath = $request->hasFile('clock_out_photo')
            ? $request->file('clock_out_photo')->store('photos')
            : null;

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $date,
            'clock_in_time' => $request->clock_in_time,
            'clock_in_latitude' => $request->clock_in_latitude,
            'clock_in_longitude' => $request->clock_in_longitude,
            'clock_in_photo' => $clockInPath,
            'clock_out_time' => $request->clock_out_time,
            'clock_out_latitude' => $request->clock_out_latitude,
            'clock_out_longitude' => $request->clock_out_longitude,
            'clock_out_photo' => $clockOutPath,
            'schedule_id' => $schedule?->id,
            'shift_id' => $schedule?->shift_id ?? $user->shift_id,
            'location_id' => $location?->id,
            'status' => 'pending',
        ]);

        return response()->json([
            'synced' => true,
            'message' => 'Offline attendance synced successfully.',
            'attendance' => $attendance->load(['shift', 'location']),
        ]);
    }
}

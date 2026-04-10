<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Overtime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OvertimeController extends Controller
{
    /**
     * Submit new overtime request.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'reason' => 'required|string|min:10',
            'document' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        // Check if overtime already exists for this date
        $existingOvertime = Overtime::where('user_id', $request->user()->id)
            ->where('date', $request->date)
            ->first();

        if ($existingOvertime) {
            return response()->json([
                'message' => 'Anda sudah mengajukan lembur untuk tanggal ini',
            ], 400);
        }

        $overtime = new Overtime();
        $overtime->user_id = $request->user()->id;
        $overtime->date = $request->date;
        $overtime->start_time = $request->start_time;
        $overtime->end_time = $request->end_time;
        $overtime->reason = $request->reason;
        $overtime->status = 'pending';

        if ($request->hasFile('document')) {
            $overtime->document = $request->file('document')->store('overtime_documents');
        }

        $overtime->save();

        return response()->json([
            'message' => 'Pengajuan lembur berhasil dikirim',
            'overtime' => $overtime,
        ], 201);
    }

    /**
     * Get overtime history.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'month' => 'nullable|integer|between:1,12',
            'year' => 'nullable|integer|between:2020,2030',
            'status' => 'nullable|string|in:pending,approved,rejected',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = Overtime::where('user_id', $request->user()->id);

        if ($request->month && $request->year) {
            $query->whereMonth('date', $request->month)
                  ->whereYear('date', $request->year);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $overtimes = $query->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 31);

        return response()->json($overtimes);
    }

    /**
     * Get overtime detail.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $overtime = Overtime::where('user_id', $request->user()->id)
            ->findOrFail($id);

        return response()->json([
            'overtime' => $overtime,
            'duration' => $overtime->duration,
        ]);
    }
}

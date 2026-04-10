<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    /**
     * Get all locations (for admin/manager).
     */
    public function index(Request $request): JsonResponse
    {
        \Log::info('Location index called', ['user' => $request->user()]);

        $locations = Location::orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'locations' => $locations,
        ]);
    }

    /**
     * Get active location (for attendance validation).
     */
    public function active(Request $request): JsonResponse
    {
        $location = Location::where('is_active', true)->first();

        if (!$location) {
            return response()->json([
                'success' => false,
                'message' => 'No active location configured',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'location' => $location,
        ]);
    }

    /**
     * Get single location details.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $location = Location::find($id);

        if (!$location) {
            return response()->json([
                'success' => false,
                'message' => 'Location not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'location' => $location,
        ]);
    }

    /**
     * Create new location.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius_meters' => 'required|integer|min:10|max:5000',
            'is_active' => 'boolean',
        ]);

        // If setting as active, deactivate other locations
        if ($request->boolean('is_active')) {
            Location::where('is_active', true)->update(['is_active' => false]);
        }

        $location = Location::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Location created successfully',
            'location' => $location,
        ], 201);
    }

    /**
     * Update existing location.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $location = Location::find($id);

        if (!$location) {
            return response()->json([
                'success' => false,
                'message' => 'Location not found',
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'address' => 'sometimes|string|max:500',
            'latitude' => 'sometimes|numeric|between:-90,90',
            'longitude' => 'sometimes|numeric|between:-180,180',
            'radius_meters' => 'sometimes|integer|min:10|max:5000',
            'is_active' => 'boolean',
        ]);

        // If setting as active, deactivate other locations
        if ($request->boolean('is_active')) {
            Location::where('is_active', true)
                ->where('id', '!=', $id)
                ->update(['is_active' => false]);
        }

        $location->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Location updated successfully',
            'location' => $location,
        ]);
    }

    /**
     * Delete location.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $location = Location::find($id);

        if (!$location) {
            return response()->json([
                'success' => false,
                'message' => 'Location not found',
            ], 404);
        }

        $location->delete();

        return response()->json([
            'success' => true,
            'message' => 'Location deleted successfully',
        ]);
    }

    /**
     * Set location as active.
     */
    public function setActive(Request $request, int $id): JsonResponse
    {
        $location = Location::find($id);

        if (!$location) {
            return response()->json([
                'success' => false,
                'message' => 'Location not found',
            ], 404);
        }

        // Deactivate all other locations
        Location::where('is_active', true)->update(['is_active' => false]);

        // Activate this location
        $location->update(['is_active' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Location activated successfully',
            'location' => $location,
        ]);
    }
}

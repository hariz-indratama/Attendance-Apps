<?php

namespace App\Services;

use App\Models\Location;

class GeoService
{
    /**
     * Calculate distance between two coordinates using Haversine formula.
     * Returns distance in meters.
     */
    public function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // meters

        $lat1Rad = deg2rad($lat1);
        $lat2Rad = deg2rad($lat2);
        $deltaLat = deg2rad($lat2 - $lat1);
        $deltaLon = deg2rad($lon2 - $lon1);

        $a = sin($deltaLat / 2) * sin($deltaLat / 2) +
            cos($lat1Rad) * cos($lat2Rad) *
            sin($deltaLon / 2) * sin($deltaLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Check if a coordinate is within a location's radius.
     */
    public function isWithinRadius(float $lat, float $lon, Location $location): bool
    {
        if (!$location->latitude || !$location->longitude) {
            return true; // No coordinates configured — allow
        }

        $distance = $this->calculateDistance(
            $lat,
            $lon,
            $location->latitude,
            $location->longitude
        );

        return $distance <= $location->getEffectiveRadius();
    }
}

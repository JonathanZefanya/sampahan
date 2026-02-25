<?php

namespace App\Libraries;

/**
 * GeoHelper
 *
 * Pure-PHP spatial helper utilities used by ReportModel.
 * No external dependencies required.
 */
class GeoHelper
{
    /**
     * Haversine great-circle distance between two coordinates (in metres).
     */
    public static function haversineDistance(
        float $lat1, float $lng1,
        float $lat2, float $lng2
    ): float {
        $earthRadius = 6_371_000; // metres

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    // ─── Point-in-Polygon ────────────────────────────────────────────────────

    /**
     * Entry point: accepts a GeoJSON geometry object (Polygon or MultiPolygon)
     * and tests whether ($lat, $lng) lies inside it.
     *
     * GeoJSON coordinates are [longitude, latitude] pairs.
     */
    public static function pointInPolygon(float $lat, float $lng, array $geometry): bool
    {
        $type = $geometry['type'] ?? null;

        if ($type === 'Polygon') {
            // coordinates[0] = exterior ring; [1..n] = holes (ignored for reject logic)
            return self::raycast($lng, $lat, $geometry['coordinates'][0]);
        }

        if ($type === 'MultiPolygon') {
            foreach ($geometry['coordinates'] as $polygon) {
                if (self::raycast($lng, $lat, $polygon[0])) {
                    return true;
                }
            }

            return false;
        }

        return true; // Unknown geometry → fail-open
    }

    /**
     * Ray-casting algorithm.
     * $ring: array of [lng, lat] pairs (GeoJSON order).
     */
    private static function raycast(float $x, float $y, array $ring): bool
    {
        $inside = false;
        $n      = count($ring);

        for ($i = 0, $j = $n - 1; $i < $n; $j = $i++) {
            $xi = $ring[$i][0];
            $yi = $ring[$i][1];
            $xj = $ring[$j][0];
            $yj = $ring[$j][1];

            $intersect = (($yi > $y) !== ($yj > $y))
                && ($x < ($xj - $xi) * ($y - $yi) / ($yj - $yi) + $xi);

            if ($intersect) {
                $inside = ! $inside;
            }
        }

        return $inside;
    }

    // ─── Bounding-box helpers ────────────────────────────────────────────────

    /**
     * Return [minLat, minLng, maxLat, maxLng] for a GeoJSON geometry.
     * Useful for fitting the Leaflet map to the city boundary.
     */
    public static function boundingBox(array $geometry): array
    {
        $coords = self::flattenCoordinates($geometry);

        $lats = array_column($coords, 1);
        $lngs = array_column($coords, 0);

        return [min($lats), min($lngs), max($lats), max($lngs)];
    }

    private static function flattenCoordinates(array $geometry): array
    {
        $flat = [];
        self::collectCoords($geometry['coordinates'] ?? [], $flat);
        return $flat;
    }

    private static function collectCoords(array $arr, array &$flat): void
    {
        if (empty($arr)) {
            return;
        }

        // Leaf node: [lng, lat]
        if (is_numeric($arr[0])) {
            $flat[] = $arr;
            return;
        }

        foreach ($arr as $child) {
            self::collectCoords($child, $flat);
        }
    }
}

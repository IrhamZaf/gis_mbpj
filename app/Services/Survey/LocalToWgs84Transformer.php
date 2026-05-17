<?php

namespace App\Services\Survey;

class LocalToWgs84Transformer
{
    private const METRES_PER_DEGREE_LAT = 111320.0;

    /**
     * @param  array<int, array{xb: float, yb: float}>  $points
     * @return array{centroid_x: float, centroid_y: float, points: array<int, array{xb: float, yb: float, lat: float, lng: float}>}
     */
    public function transform(array $points, float $anchorLat, float $anchorLng): array
    {
        if (empty($points)) {
            return ['centroid_x' => 0, 'centroid_y' => 0, 'points' => []];
        }

        $sumX = 0.0;
        $sumY = 0.0;
        $count = count($points);

        foreach ($points as $p) {
            $sumX += (float) $p['xb'];
            $sumY += (float) $p['yb'];
        }

        $cx = $sumX / $count;
        $cy = $sumY / $count;
        $lngScale = self::METRES_PER_DEGREE_LAT * cos(deg2rad($anchorLat));

        $transformed = [];
        foreach ($points as $p) {
            $xb = (float) $p['xb'];
            $yb = (float) $p['yb'];
            $transformed[] = array_merge($p, [
                'lat' => $anchorLat + (($yb - $cy) / self::METRES_PER_DEGREE_LAT),
                'lng' => $anchorLng + (($xb - $cx) / $lngScale),
            ]);
        }

        return [
            'centroid_x' => round($cx, 4),
            'centroid_y' => round($cy, 4),
            'points'     => $transformed,
        ];
    }
}

<?php

namespace App\Support;

/**
 * Approximate bounding box for Majlis Bandaraya Subang Jaya (MBSJ) jurisdiction.
 * Used to lock map views and validate report coordinates.
 */
final class MbsjArea
{
    /** @var float South latitude */
    public const SOUTH = 2.9700;

    /** @var float West longitude */
    public const WEST = 101.5100;

    /** @var float North latitude */
    public const NORTH = 3.1650;

    /** @var float East longitude */
    public const EAST = 101.6900;

    public const CENTER_LAT = 3.0565;

    public const CENTER_LNG = 101.5851;

    public static function contains(?float $lat, ?float $lng): bool
    {
        if ($lat === null || $lng === null) {
            return false;
        }

        return $lat >= self::SOUTH
            && $lat <= self::NORTH
            && $lng >= self::WEST
            && $lng <= self::EAST;
    }

    /**
     * Laravel validation rule fragment for latitude/longitude pair.
     * Use with a custom closure on either field.
     */
    public static function validationMessage(): string
    {
        return __('app.location_must_be_mbsj');
    }

    /**
     * @return array{south: float, west: float, north: float, east: float, center_lat: float, center_lng: float}
     */
    public static function toArray(): array
    {
        return [
            'south' => self::SOUTH,
            'west' => self::WEST,
            'north' => self::NORTH,
            'east' => self::EAST,
            'center_lat' => self::CENTER_LAT,
            'center_lng' => self::CENTER_LNG,
        ];
    }
}

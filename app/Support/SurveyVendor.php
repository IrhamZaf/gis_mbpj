<?php

namespace App\Support;

class SurveyVendor
{
    public const NAME = 'NZ Survey Consultant';

    public static function name(?string $fallback = null): string
    {
        $configured = config('gis.survey_vendor');

        if (is_string($configured) && trim($configured) !== '') {
            return trim($configured);
        }

        return $fallback ?: self::NAME;
    }
}

<?php

namespace App\Helpers;

use Illuminate\Support\Facades\File;

class Helpers
{
    /*
    |--------------------------------------------------------------------------
    | appClasses — build the master config array consumed by every layout blade
    |--------------------------------------------------------------------------
    */
    public static function appClasses()
    {
        $data = config('custom.custom');

        // Layout
        $layout          = $data['myLayout'];
        $theme           = $data['myTheme'];
        $style           = $data['myStyle'];
        $textDirection    = $data['myRTLMode'] ? 'rtl' : 'ltr';
        $hasCustomizer    = $data['hasCustomizer'];
        $displayCustomizer = $data['displayCustomizer'];
        $contentLayout   = $data['contentLayout'];
        $navbarType      = $data['navbarType'];
        $headerType      = $data['headerType'];
        $menuFixed       = $data['menuFixed'];
        $menuCollapsed   = $data['menuCollapsed'];
        $footerFixed     = $data['footerFixed'];
        $showDropdownOnHover = $data['showDropdownOnHover'];
        $customizerControls  = $data['customizerControls'];

        // Skins
        $skins    = $data['skins'];
        $skinName = $data['skinName'];
        $semiDark = $data['semiDark'];
        $color    = $data['color'];

        // Cookie overrides
        if (isset($_COOKIE['admin-theme'])) {
            $theme = $_COOKIE['admin-theme'];
        }
        if (isset($_COOKIE['admin-style'])) {
            $style = $_COOKIE['admin-style'];
        }
        if (isset($_COOKIE['admin-skinName'])) {
            $skinName = $_COOKIE['admin-skinName'];
        }
        if (isset($_COOKIE['admin-semiDark'])) {
            $semiDark = filter_var($_COOKIE['admin-semiDark'], FILTER_VALIDATE_BOOLEAN);
        }
        if (isset($_COOKIE['admin-primaryColor'])) {
            $color = $_COOKIE['admin-primaryColor'];
        }
        if (isset($_COOKIE['admin-menuCollapsed'])) {
            $menuCollapsed = $_COOKIE['admin-menuCollapsed'] === 'true' ? 'layout-menu-collapsed' : '';
        }

        // Theme opt for customizer (light / dark / system)
        $themeOpt = $theme;
        if ($theme === 'system') {
            $theme = 'light'; // default fallback
        }

        // Menu attributes based on layout
        $menuAttributes = [];
        if ($layout === 'vertical') {
            $menuAttributes = [
                'data-bg-class' => 'bg-menu-theme',
            ];
        }

        return [
            'layout'              => $layout,
            'theme'               => $theme,
            'themeOpt'            => $themeOpt,
            'style'               => $style,
            'textDirection'       => $textDirection,
            'hasCustomizer'       => $hasCustomizer,
            'displayCustomizer'   => $displayCustomizer,
            'contentLayout'       => $contentLayout,
            'navbarType'          => $navbarType,
            'headerType'          => $headerType,
            'menuFixed'           => $menuFixed,
            'menuCollapsed'       => $menuCollapsed,
            'footerFixed'         => $footerFixed,
            'showDropdownOnHover' => $showDropdownOnHover,
            'customizerControls'  => $customizerControls,
            'skins'               => $skins,
            'skinName'            => $skinName,
            'semiDark'            => $semiDark,
            'color'               => $color,
            'menuAttributes'      => $menuAttributes,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | updatePageConfig — let individual pages override defaults
    |--------------------------------------------------------------------------
    */
    public static function updatePageConfig($pageConfigs)
    {
        $demo = 'custom';

        if (isset($pageConfigs)) {
            foreach ($pageConfigs as $key => $val) {
                // Update the config value on the fly
                config(["$demo.$demo.$key" => $val]);
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | generatePrimaryColorCSS
    |--------------------------------------------------------------------------
    */
    public static function generatePrimaryColorCSS($color)
    {
        if (empty($color)) {
            return '';
        }

        // Convert hex to HSL components for CSS custom properties
        list($r, $g, $b) = sscanf($color, "#%02x%02x%02x");
        $r /= 255;
        $g /= 255;
        $b /= 255;

        $max   = max($r, $g, $b);
        $min   = min($r, $g, $b);
        $l     = ($max + $min) / 2;

        if ($max == $min) {
            $h = $s = 0;
        } else {
            $d = $max - $min;
            $s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);

            switch ($max) {
                case $r:
                    $h = (($g - $b) / $d + ($g < $b ? 6 : 0)) / 6;
                    break;
                case $g:
                    $h = (($b - $r) / $d + 2) / 6;
                    break;
                case $b:
                    $h = (($r - $g) / $d + 4) / 6;
                    break;
            }
        }

        $h = round($h * 360, 2);
        $s = round($s * 100, 2);
        $l = round($l * 100, 2);

        return ":root { --bs-primary: {$color}; --bs-primary-h: {$h}; --bs-primary-s: {$s}%; --bs-primary-l: {$l}%; }";
    }
}

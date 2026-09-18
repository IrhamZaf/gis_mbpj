<?php

namespace App\Support;

use App\Models\Unit;
use Illuminate\Support\Collection;

/**
 * Canonical Unit ↔ Category module map for GIS MBSJ navigation & routes.
 */
class UnitModule
{
    /** @var array<string, string> URL slug → unit code */
    public const UNIT_SLUGS = [
        'saliran-cerun' => 'SAL-CERUN',
        'jalan' => 'JLN',
        'structure' => 'STR',
        'm-e' => 'ME',
    ];

    /** @var array<string, string> URL slug → category code */
    public const CATEGORY_SLUGS = [
        'sinkhole' => 'SINKHOLE',
        'cerun' => 'CERUN',
        'borehole' => 'BOREHOLE',
    ];

    /** @var list<string> */
    public const CATEGORY_CODES = ['SINKHOLE', 'CERUN', 'BOREHOLE'];

    public static function unitCodeFromSlug(string $slug): ?string
    {
        return self::UNIT_SLUGS[$slug] ?? null;
    }

    public static function unitSlugFromCode(string $code): ?string
    {
        return array_search($code, self::UNIT_SLUGS, true) ?: null;
    }

    public static function categoryCodeFromSlug(string $slug): ?string
    {
        return self::CATEGORY_SLUGS[$slug] ?? null;
    }

    public static function categorySlugFromCode(string $code): ?string
    {
        // Backward-compatible alias
        if ($code === 'CERUN_RUNTUH') {
            return 'cerun';
        }

        return array_search($code, self::CATEGORY_SLUGS, true) ?: null;
    }

    public static function normalizeCategoryCode(?string $code): ?string
    {
        if ($code === null || $code === '') {
            return $code;
        }

        return $code === 'CERUN_RUNTUH' ? 'CERUN' : $code;
    }

    /**
     * @return list<string>
     */
    public static function unitSlugList(): array
    {
        return array_keys(self::UNIT_SLUGS);
    }

    /**
     * @return list<string>
     */
    public static function categorySlugList(): array
    {
        return array_keys(self::CATEGORY_SLUGS);
    }

    public static function dashboardRoute(string $unitCode): string
    {
        $slug = self::unitSlugFromCode($unitCode);
        if (! $slug) {
            return route('superadmin.dashboard');
        }

        return route($slug.'.dashboard');
    }

    public static function categoryRoute(string $unitCode, string $categoryCode): string
    {
        $unitSlug = self::unitSlugFromCode($unitCode);
        $catSlug = self::categorySlugFromCode(self::normalizeCategoryCode($categoryCode) ?? $categoryCode);
        if (! $unitSlug || ! $catSlug) {
            return self::dashboardRoute($unitCode);
        }

        return route($unitSlug.'.'.$catSlug);
    }

    public static function caseShowRoute(string $unitCode, $report): string
    {
        $slug = self::unitSlugFromCode($unitCode);
        if (! $slug) {
            return route('reports.show', $report);
        }

        return route($slug.'.cases.show', $report);
    }

    public static function caseCreateRoute(string $unitCode, string $categoryCode): string
    {
        $slug = self::unitSlugFromCode($unitCode);
        $code = self::normalizeCategoryCode($categoryCode) ?? $categoryCode;
        if (! $slug) {
            return '#';
        }

        return route($slug.'.cases.create', ['categoryCode' => $code]);
    }

    public static function caseEditRoute(string $unitCode, $report): string
    {
        $slug = self::unitSlugFromCode($unitCode);
        if (! $slug) {
            return '#';
        }

        return route($slug.'.cases.edit', $report);
    }

    /**
     * Sidebar icon for a unit code.
     */
    public static function unitIcon(string $code): string
    {
        return match ($code) {
            'SAL-CERUN' => 'tabler-droplet',
            'JLN' => 'tabler-road',
            'STR' => 'tabler-building',
            'ME' => 'tabler-settings-cog',
            default => 'tabler-building-community',
        };
    }

    /**
     * Sidebar icon for a category code.
     */
    public static function categoryIcon(string $code): string
    {
        return match (self::normalizeCategoryCode($code) ?? $code) {
            'SINKHOLE' => 'tabler-alert-triangle',
            'CERUN' => 'tabler-mountain',
            'BOREHOLE' => 'tabler-layers-intersect',
            default => 'tabler-folder',
        };
    }

    /**
     * Active units for navigation, ordered.
     *
     * @return Collection<int, Unit>
     */
    public static function navUnits(): Collection
    {
        $codes = array_values(self::UNIT_SLUGS);

        return Unit::query()
            ->active()
            ->whereIn('code', $codes)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->with(['categories' => fn ($q) => $q->active()->orderByRaw(
                "CASE code WHEN 'SINKHOLE' THEN 1 WHEN 'CERUN' THEN 2 WHEN 'CERUN_RUNTUH' THEN 2 WHEN 'BOREHOLE' THEN 3 ELSE 9 END"
            )->orderBy('name')])
            ->get();
    }

    /**
     * Whether the current route belongs to a unit module slug.
     */
    public static function isUnitRouteActive(string $unitSlug, string $currentRouteName): bool
    {
        return str_starts_with($currentRouteName, $unitSlug.'.');
    }

    public static function isCategoryRouteActive(string $unitSlug, string $categorySlug, string $currentRouteName): bool
    {
        return $currentRouteName === $unitSlug.'.'.$categorySlug;
    }
}

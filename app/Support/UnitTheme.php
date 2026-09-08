<?php

namespace App\Support;

use App\Models\Unit;

class UnitTheme
{
    /**
     * Visual theme per engineering unit code.
     *
     * @return array{code: string, name: string, label: string, color: string, soft: string, gradient: string}
     */
    public static function for(?Unit $unit): array
    {
        $code = $unit?->code ?? 'DEFAULT';
        $name = $unit?->name ?? 'Tiada Unit';

        $themes = [
            'JLN' => ['label' => 'primary', 'color' => '#0d6efd', 'soft' => 'rgba(13,110,253,.12)'],
            'SLR' => ['label' => 'info', 'color' => '#0dcaf0', 'soft' => 'rgba(13,202,240,.14)'],
            'STR' => ['label' => 'secondary', 'color' => '#6f42c1', 'soft' => 'rgba(111,66,193,.12)'],
            'ELK' => ['label' => 'warning', 'color' => '#d4a017', 'soft' => 'rgba(212,160,23,.14)'],
            'MEK' => ['label' => 'warning', 'color' => '#fd7e14', 'soft' => 'rgba(253,126,20,.14)'],
            'INF' => ['label' => 'success', 'color' => '#198754', 'soft' => 'rgba(25,135,84,.12)'],
            'CRN' => ['label' => 'danger', 'color' => '#dc3545', 'soft' => 'rgba(220,53,69,.12)'],
            'DEFAULT' => ['label' => 'primary', 'color' => '#0d6efd', 'soft' => 'rgba(13,110,253,.10)'],
        ];

        $t = $themes[$code] ?? $themes['DEFAULT'];

        return [
            'code'     => $code,
            'name'     => $name,
            'label'    => $t['label'],
            'color'    => $t['color'],
            'soft'     => $t['soft'],
            'gradient' => "linear-gradient(145deg, {$t['soft']}, rgba(255,255,255,.35))",
        ];
    }
}

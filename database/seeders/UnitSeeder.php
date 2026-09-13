<?php

namespace Database\Seeders;

use App\Models\Report;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $active = [
            ['name' => 'Saliran & Cerun', 'code' => 'SAL-CERUN', 'sort_order' => 1, 'description' => 'Unit Saliran & Cerun — Sinkhole dan Cerun Runtuh'],
            ['name' => 'Jalan', 'code' => 'JLN', 'sort_order' => 2, 'description' => 'Unit Jalan — Engineering'],
            ['name' => 'Structure', 'code' => 'STR', 'sort_order' => 3, 'description' => 'Unit Structure — Engineering'],
            ['name' => 'M&E', 'code' => 'ME', 'sort_order' => 4, 'description' => 'Unit M&E (Mekanikal & Elektrik) — Engineering'],
        ];

        foreach ($active as $unit) {
            Unit::updateOrCreate(
                ['code' => $unit['code']],
                [
                    'name' => $unit['name'],
                    'description' => $unit['description'],
                    'parent_id' => null,
                    'status' => 'active',
                    'sort_order' => $unit['sort_order'],
                ]
            );
        }

        // Soft-deactivate any other units (legacy — keep history)
        Unit::whereNotIn('code', ['SAL-CERUN', 'JLN', 'STR', 'ME'])
            ->update(['status' => 'inactive']);

        $this->remapLegacyAssignments();
    }

    /**
     * Move users/reports from legacy unit codes onto the 4 canonical units.
     */
    protected function remapLegacyAssignments(): void
    {
        $map = [
            'SAL-CERUN' => ['SLR', 'CRN'],
            'ME' => ['ELK', 'MEK'],
        ];

        foreach ($map as $targetCode => $legacyCodes) {
            $target = Unit::where('code', $targetCode)->first();
            if (! $target) {
                continue;
            }

            $legacyIds = Unit::whereIn('code', $legacyCodes)->pluck('id');
            if ($legacyIds->isEmpty()) {
                continue;
            }

            User::whereIn('unit_id', $legacyIds)->update(['unit_id' => $target->id]);
            Report::whereIn('unit_id', $legacyIds)->update(['unit_id' => $target->id]);
        }
    }
}

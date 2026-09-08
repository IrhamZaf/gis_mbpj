<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            ['name' => 'Jalan', 'code' => 'JLN', 'sort_order' => 1, 'description' => 'Unit Jalan — Engineering'],
            ['name' => 'Saliran', 'code' => 'SLR', 'sort_order' => 2, 'description' => 'Unit Saliran — Engineering'],
            ['name' => 'Struktur', 'code' => 'STR', 'sort_order' => 3, 'description' => 'Unit Struktur — Engineering'],
            ['name' => 'Elektrik', 'code' => 'ELK', 'sort_order' => 4, 'description' => 'Unit Elektrik — Engineering'],
            ['name' => 'Mekanikal', 'code' => 'MEK', 'sort_order' => 5, 'description' => 'Unit Mekanikal — Engineering'],
            ['name' => 'Infrastruktur', 'code' => 'INF', 'sort_order' => 6, 'description' => 'Unit Infrastruktur — Engineering'],
            ['name' => 'Cerun', 'code' => 'CRN', 'sort_order' => 7, 'description' => 'Unit Cerun — Engineering'],
        ];

        foreach ($units as $unit) {
            Unit::updateOrCreate(
                ['code' => $unit['code']],
                [
                    'name'        => $unit['name'],
                    'description' => $unit['description'],
                    'parent_id'   => null,
                    'status'      => 'active',
                    'sort_order'  => $unit['sort_order'],
                ]
            );
        }
    }
}

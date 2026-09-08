<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
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
            $exists = DB::table('units')->where('code', $unit['code'])->exists();
            if (! $exists) {
                DB::table('units')->insert([
                    'name'        => $unit['name'],
                    'code'        => $unit['code'],
                    'description' => $unit['description'],
                    'parent_id'   => null,
                    'status'      => 'active',
                    'sort_order'  => $unit['sort_order'],
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ]);
            }
        }

        $jalanId = DB::table('units')->where('code', 'JLN')->value('id');
        if ($jalanId) {
            DB::table('users')
                ->whereIn('role', ['surveyor', 'engineer'])
                ->whereNull('unit_id')
                ->update(['unit_id' => $jalanId]);

            DB::table('reports')
                ->whereNull('unit_id')
                ->update(['unit_id' => $jalanId]);
        }
    }

    public function down(): void
    {
        // Keep seeded units; do not wipe production data on rollback of backfill.
    }
};

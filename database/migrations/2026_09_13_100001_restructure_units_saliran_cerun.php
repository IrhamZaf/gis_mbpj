<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('units')) {
            return;
        }

        $now = now();

        // Ensure / update the 4 canonical active units
        $canonical = [
            ['code' => 'SAL-CERUN', 'name' => 'Saliran & Cerun', 'description' => 'Unit Saliran & Cerun — Sinkhole dan Cerun Runtuh', 'sort_order' => 1],
            ['code' => 'JLN', 'name' => 'Jalan', 'description' => 'Unit Jalan — Engineering', 'sort_order' => 2],
            ['code' => 'STR', 'name' => 'Structure', 'description' => 'Unit Structure — Engineering', 'sort_order' => 3],
            ['code' => 'ME', 'name' => 'M&E', 'description' => 'Unit M&E (Mekanikal & Elektrik) — Engineering', 'sort_order' => 4],
        ];

        foreach ($canonical as $unit) {
            $existing = DB::table('units')->where('code', $unit['code'])->first();
            if ($existing) {
                DB::table('units')->where('id', $existing->id)->update([
                    'name' => $unit['name'],
                    'description' => $unit['description'],
                    'status' => 'active',
                    'sort_order' => $unit['sort_order'],
                    'updated_at' => $now,
                ]);
            } else {
                DB::table('units')->insert([
                    'name' => $unit['name'],
                    'code' => $unit['code'],
                    'description' => $unit['description'],
                    'parent_id' => null,
                    'status' => 'active',
                    'sort_order' => $unit['sort_order'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // Soft-deactivate legacy units (keep historical data)
        DB::table('units')
            ->whereNotIn('code', ['SAL-CERUN', 'JLN', 'STR', 'ME'])
            ->update(['status' => 'inactive', 'updated_at' => $now]);
    }

    public function down(): void
    {
        // Reactivate common legacy codes; remove ME / SAL-CERUN if no dependents preferred — keep soft
        DB::table('units')
            ->whereIn('code', ['SLR', 'CRN', 'ELK', 'MEK', 'INF'])
            ->update(['status' => 'active', 'updated_at' => now()]);

        DB::table('units')->where('code', 'STR')->update(['name' => 'Struktur', 'updated_at' => now()]);
    }
};

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * @deprecated Use UnitCategorySeeder — kept so existing call sites keep working.
 */
class SaliranCerunCategorySeeder extends Seeder
{
    public function run(): void
    {
        $this->call(UnitCategorySeeder::class);
    }
}

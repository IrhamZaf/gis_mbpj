<?php

namespace Database\Seeders;

use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SaliranCerunStaffSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(UnitSeeder::class);

        $unit = Unit::where('code', 'SAL-CERUN')->first();
        if (! $unit) {
            return;
        }

        $password = Hash::make('password');

        User::updateOrCreate(
            ['email' => 'ta.saliran-cerun@mbsj.gov.my'],
            [
                'name' => 'TA Saliran & Cerun',
                'password' => $password,
                'role' => 'ta',
                'unit_id' => $unit->id,
                'status' => 'active',
                'phone' => '012-7001001',
            ]
        );

        User::updateOrCreate(
            ['email' => 'engineer.saliran-cerun@mbsj.gov.my'],
            [
                'name' => 'Engineer Saliran & Cerun',
                'password' => $password,
                'role' => 'engineer',
                'unit_id' => $unit->id,
                'status' => 'active',
                'phone' => '012-7001002',
            ]
        );

        // Optional: assign surveyor demo to SAL-CERUN for this phase testing
        // Keep surveyor@ on Jalan for legacy; add dedicated surveyor for module
        User::updateOrCreate(
            ['email' => 'surveyor.saliran-cerun@mbsj.gov.my'],
            [
                'name' => 'Surveyor Saliran & Cerun',
                'password' => $password,
                'role' => 'surveyor',
                'unit_id' => $unit->id,
                'status' => 'active',
                'phone' => '012-7001003',
            ]
        );

        $this->command?->info('SAL-CERUN staff seeded (password: password).');
    }
}

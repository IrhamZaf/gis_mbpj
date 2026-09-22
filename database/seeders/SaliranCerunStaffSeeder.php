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

        // Consultant is global (all units) — no per-unit surveyor accounts
        User::updateOrCreate(
            ['email' => 'consultant@mbsj.gov.my'],
            [
                'name' => 'Consultant MBSJ',
                'password' => $password,
                'role' => 'consultant',
                'unit_id' => null,
                'status' => 'active',
                'phone' => '012-7001003',
            ]
        );

        $this->command?->info('SAL-CERUN staff seeded (password: password).');
    }
}

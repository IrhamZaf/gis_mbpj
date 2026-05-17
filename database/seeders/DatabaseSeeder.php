<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\ReportCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Default Users ───────────────────────────────────
        User::updateOrCreate(
            ['email' => 'admin@mbpj.gov.my'],
            [
                'name'     => 'Super Admin',
                'password' => Hash::make('password'),
                'role'     => 'superadmin',
            ]
        );

        User::updateOrCreate(
            ['email' => 'surveyor@mbpj.gov.my'],
            [
                'name'     => 'Admin Surveyor',
                'password' => Hash::make('password'),
                'role'     => 'surveyor',
            ]
        );

        User::updateOrCreate(
            ['email' => 'engineer@mbpj.gov.my'],
            [
                'name'     => 'Engineer MBPJ',
                'password' => Hash::make('password'),
                'role'     => 'engineer',
            ]
        );

        // ── Default Report Categories ───────────────────────
        ReportCategory::updateOrCreate(
            ['slug' => 'sinkhole'],
            ['name' => 'Sinkhole', 'description' => 'Laporan berkaitan sinkhole']
        );

        ReportCategory::updateOrCreate(
            ['slug' => 'cerun-tanah-runtuh'],
            ['name' => 'Cerun / Tanah Runtuh', 'description' => 'Laporan berkaitan cerun dan tanah runtuh']
        );

        ReportCategory::updateOrCreate(
            ['slug' => 'utiliti-bawah-tanah'],
            ['name' => 'Utiliti Bawah Tanah', 'description' => 'Laporan berkaitan utiliti bawah tanah']
        );

        $this->call(ReportSeeder::class);
    }
}

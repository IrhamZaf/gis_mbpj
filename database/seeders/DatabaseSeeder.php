<?php

namespace Database\Seeders;

use App\Models\Report;
use App\Models\ReportCategory;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(UnitSeeder::class);

        $jalan = Unit::where('code', 'JLN')->first();

        User::updateOrCreate(
            ['email' => 'admin@mbsj.gov.my'],
            [
                'name'     => 'Super Admin',
                'password' => Hash::make('password'),
                'role'     => 'superadmin',
                'unit_id'  => null,
                'status'   => 'active',
            ]
        );

        User::updateOrCreate(
            ['email' => 'surveyor@mbsj.gov.my'],
            [
                'name'     => 'Admin Surveyor',
                'password' => Hash::make('password'),
                'role'     => 'surveyor',
                'unit_id'  => $jalan?->id,
                'status'   => 'active',
            ]
        );

        User::updateOrCreate(
            ['email' => 'engineer@mbsj.gov.my'],
            [
                'name'     => 'Engineer MBSJ',
                'password' => Hash::make('password'),
                'role'     => 'engineer',
                'unit_id'  => $jalan?->id,
                'status'   => 'active',
            ]
        );

        User::updateOrCreate(
            ['email' => 'ta@mbsj.gov.my'],
            [
                'name'     => 'TA Unit Jalan',
                'password' => Hash::make('password'),
                'role'     => 'ta',
                'unit_id'  => $jalan?->id,
                'status'   => 'active',
            ]
        );

        User::updateOrCreate(
            ['email' => 'director@mbsj.gov.my'],
            [
                'name'     => 'Pengarah Kejuruteraan',
                'password' => Hash::make('password'),
                'role'     => 'director',
                'unit_id'  => null,
                'status'   => 'active',
            ]
        );

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
        $this->call(WorkflowDemoSeeder::class);
        $this->call(SaliranCerunCategorySeeder::class);
        $this->call(SaliranCerunStaffSeeder::class);
        $this->call(SaliranCerunDemoSeeder::class);

        // Backfill unit_id on existing reports without unit
        if ($jalan) {
            Report::whereNull('unit_id')->update(['unit_id' => $jalan->id]);
            User::whereIn('role', ['surveyor', 'engineer', 'ta'])
                ->whereNull('unit_id')
                ->update(['unit_id' => $jalan->id]);
        }
    }
}

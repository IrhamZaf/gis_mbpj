<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Replace Surveyor / Vendor with Consultant (all-unit report creator)
        DB::table('users')->where('role', 'surveyor')->update([
            'role' => 'consultant',
            'unit_id' => null,
        ]);

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('superadmin','consultant','engineer','ta','director') NOT NULL DEFAULT 'engineer'");
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('superadmin','surveyor','consultant','engineer','ta','director') NOT NULL DEFAULT 'engineer'");
        }
    }
};

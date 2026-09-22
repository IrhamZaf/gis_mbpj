<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('superadmin','surveyor','consultant','engineer','ta','director') NOT NULL DEFAULT 'engineer'");
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::table('users')->where('role', 'consultant')->update(['role' => 'surveyor']);
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('superadmin','surveyor','engineer','ta','director') NOT NULL DEFAULT 'engineer'");
    }
};

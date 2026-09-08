<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('superadmin','surveyor','engineer','ta','director') NOT NULL DEFAULT 'engineer'");
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::table('users')->whereIn('role', ['ta', 'director'])->update(['role' => 'engineer']);
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('superadmin','surveyor','engineer') NOT NULL DEFAULT 'engineer'");
        }
    }
};

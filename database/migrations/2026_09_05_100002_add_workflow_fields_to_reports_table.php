<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->string('workflow_status')->nullable()->after('status');
            $table->string('file_number')->nullable()->after('report_number');
            $table->string('vendor_name')->nullable()->after('location_name');
        });

        // Backfill workflow from legacy status values
        DB::table('reports')->where('status', 'draft')->update([
            'workflow_status' => null,
        ]);

        DB::table('reports')->where('status', 'submitted')->update([
            'workflow_status' => 'pending_site_visit',
        ]);

        DB::table('reports')->where('status', 'under_review')->update([
            'status' => 'submitted',
            'workflow_status' => 'pending_engineer_verification',
        ]);

        DB::table('reports')->where('status', 'returned')->update([
            'status' => 'submitted',
            'workflow_status' => 'engineer_returned',
        ]);

        DB::table('reports')->where('status', 'approved')->update([
            'status' => 'completed',
            'workflow_status' => 'approved',
        ]);

        DB::table('reports')->where('status', 'completed')->whereNull('workflow_status')->update([
            'workflow_status' => 'approved',
        ]);

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE reports MODIFY COLUMN status ENUM('draft','submitted','completed') NOT NULL DEFAULT 'draft'");
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE reports MODIFY COLUMN status ENUM('draft','submitted','under_review','returned','approved','completed') NOT NULL DEFAULT 'draft'");
        }

        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn(['workflow_status', 'file_number', 'vendor_name']);
        });
    }
};

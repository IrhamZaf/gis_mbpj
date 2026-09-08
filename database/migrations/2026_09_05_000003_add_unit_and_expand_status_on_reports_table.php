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
            $table->foreignId('unit_id')->nullable()->after('user_id')->constrained('units')->nullOnDelete();
            $table->text('review_note')->nullable()->after('submitted_at');
            $table->timestamp('reviewed_at')->nullable()->after('review_note');
            $table->foreignId('reviewed_by')->nullable()->after('reviewed_at')->constrained('users')->nullOnDelete();
        });

        // Expand status enum (MySQL). Existing values remain valid.
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE reports MODIFY COLUMN status ENUM('draft','submitted','under_review','returned','approved','completed') NOT NULL DEFAULT 'draft'");
        } else {
            // SQLite / others: recreate is complex; skip if not mysql
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::table('reports')->whereNotIn('status', ['draft', 'submitted'])->update(['status' => 'submitted']);
            DB::statement("ALTER TABLE reports MODIFY COLUMN status ENUM('draft','submitted') NOT NULL DEFAULT 'draft'");
        }

        Schema::table('reports', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropColumn(['review_note', 'reviewed_at']);
            $table->dropConstrainedForeignId('unit_id');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->index(['unit_id', 'category_id'], 'reports_unit_category_index');
            $table->index(['unit_id', 'workflow_status'], 'reports_unit_workflow_index');
            $table->index(['unit_id', 'status'], 'reports_unit_status_index');
            $table->index(['unit_id', 'created_at'], 'reports_unit_created_index');
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropIndex('reports_unit_category_index');
            $table->dropIndex('reports_unit_workflow_index');
            $table->dropIndex('reports_unit_status_index');
            $table->dropIndex('reports_unit_created_index');
        });
    }
};

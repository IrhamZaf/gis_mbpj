<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            if (! Schema::hasColumn('reports', 'address')) {
                $table->string('address')->nullable()->after('location_name');
            }
            if (! Schema::hasColumn('reports', 'gps_accuracy')) {
                $table->decimal('gps_accuracy', 8, 2)->nullable()->after('address');
            }
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            if (Schema::hasColumn('reports', 'gps_accuracy')) {
                $table->dropColumn('gps_accuracy');
            }
            if (Schema::hasColumn('reports', 'address')) {
                $table->dropColumn('address');
            }
        });
    }
};

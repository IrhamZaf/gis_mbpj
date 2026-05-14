<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survey_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_id')->nullable()->constrained('incidents')->nullOnDelete();
            $table->foreignId('surveyor_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->date('survey_date');
            $table->json('gps_coordinates')->nullable();
            $table->json('geojson_data')->nullable();
            $table->text('notes')->nullable();
            $table->string('original_filename')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_data');
    }
};

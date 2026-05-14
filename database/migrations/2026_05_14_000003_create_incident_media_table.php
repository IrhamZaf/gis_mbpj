<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incident_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_id')->constrained('incidents')->cascadeOnDelete();
            $table->string('type', 32);
            $table->string('file_path');
            $table->string('caption')->nullable();
            $table->string('upload_phase', 16)->default('during');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incident_media');
    }
};

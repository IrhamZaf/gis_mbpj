<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gis_layers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type', 32);
            $table->string('file_path')->nullable();
            $table->longText('geojson_data')->nullable();
            $table->json('metadata')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gis_layers');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incident_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_id')->constrained('incidents')->cascadeOnDelete();
            $table->foreignId('engineer_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->text('risk_assessment')->nullable();
            $table->text('recommendation')->nullable();
            $table->boolean('is_approved')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incident_reviews');
    }
};

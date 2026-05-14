<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->string('incident_number', 32)->unique();
            $table->string('category', 32);
            $table->date('date_reported');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('address')->nullable();
            $table->string('risk_level', 32);
            $table->string('status', 48);
            $table->text('description')->nullable();
            $table->foreignId('reported_by')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('assigned_engineer')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};

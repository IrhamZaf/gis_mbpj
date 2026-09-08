<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained('reports')->cascadeOnDelete();
            $table->foreignId('ta_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('file_number')->nullable();
            $table->string('reference')->nullable();
            $table->date('visit_date')->nullable();
            $table->time('visit_time')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('gps_accuracy', 8, 2)->nullable();
            $table->longText('laporan_pj_pjk')->nullable();
            $table->text('visit_notes')->nullable();
            $table->string('ta_designation')->nullable();
            $table->string('ta_signature')->nullable();
            $table->enum('status', ['draft', 'submitted'])->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->unique('report_id');
        });

        Schema::create('site_visit_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_visit_id')->constrained('site_visits')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('caption')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamp('taken_at')->nullable();
            $table->timestamps();
        });

        Schema::create('engineer_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained('reports')->cascadeOnDelete();
            $table->foreignId('engineer_user_id')->constrained('users')->cascadeOnDelete();
            $table->text('remarks')->nullable();
            $table->enum('decision', ['verified', 'returned']);
            $table->string('signature')->nullable();
            $table->string('designation')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('director_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained('reports')->cascadeOnDelete();
            $table->foreignId('director_user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('decision', ['approved', 'rejected']);
            $table->text('remarks')->nullable();
            $table->string('signature')->nullable();
            $table->string('designation')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('workflow_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained('reports')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('role')->nullable();
            $table->string('action');
            $table->string('from_status')->nullable();
            $table->string('to_status')->nullable();
            $table->text('remarks')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_histories');
        Schema::dropIfExists('director_approvals');
        Schema::dropIfExists('engineer_verifications');
        Schema::dropIfExists('site_visit_photos');
        Schema::dropIfExists('site_visits');
    }
};

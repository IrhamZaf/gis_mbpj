<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('survey_data', function (Blueprint $table) {
            $table->string('vendor_name')->nullable()->after('surveyor_id');
            $table->string('surveyor_name')->nullable()->after('vendor_name');
            $table->string('survey_type')->nullable()->after('survey_date');
            $table->text('technical_notes')->nullable()->after('notes');
            $table->json('gis_metadata')->nullable()->after('geojson_data');
            $table->json('converted_coordinates')->nullable()->after('gis_metadata');
            $table->string('review_status', 48)->default('pending_engineer_review')->after('original_filename');
            $table->unsignedInteger('version')->default(1)->after('review_status');
            $table->foreignId('parent_survey_id')->nullable()->after('version')->constrained('survey_data')->nullOnDelete();
        });

        Schema::create('survey_uploads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_data_id')->constrained('survey_data')->cascadeOnDelete();
            $table->string('category', 64);
            $table->string('file_path');
            $table->string('original_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->json('meta')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_uploads');

        Schema::table('survey_data', function (Blueprint $table) {
            $table->dropForeign(['parent_survey_id']);
            $table->dropColumn([
                'vendor_name',
                'surveyor_name',
                'survey_type',
                'technical_notes',
                'gis_metadata',
                'converted_coordinates',
                'review_status',
                'version',
                'parent_survey_id',
            ]);
        });
    }
};

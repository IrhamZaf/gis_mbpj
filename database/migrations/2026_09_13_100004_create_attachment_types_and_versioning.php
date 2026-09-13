<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attachment_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->boolean('required')->default(true);
            $table->json('allowed_extensions');
            $table->unsignedInteger('max_size')->default(51200); // KB (50MB)
            $table->timestamps();
        });

        Schema::create('category_attachment_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('report_categories')->cascadeOnDelete();
            $table->foreignId('attachment_type_id')->constrained('attachment_types')->cascadeOnDelete();
            $table->string('display_name');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['category_id', 'attachment_type_id'], 'cat_att_type_unique');
        });

        Schema::table('report_attachments', function (Blueprint $table) {
            if (! Schema::hasColumn('report_attachments', 'attachment_type_id')) {
                $table->foreignId('attachment_type_id')->nullable()->after('report_id')->constrained('attachment_types')->nullOnDelete();
            }
            if (! Schema::hasColumn('report_attachments', 'original_filename')) {
                $table->string('original_filename')->nullable()->after('file_name');
            }
            if (! Schema::hasColumn('report_attachments', 'stored_filename')) {
                $table->string('stored_filename')->nullable()->after('original_filename');
            }
            if (! Schema::hasColumn('report_attachments', 'uploaded_by')) {
                $table->foreignId('uploaded_by')->nullable()->after('file_size')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('report_attachments', 'uploaded_at')) {
                $table->timestamp('uploaded_at')->nullable()->after('uploaded_by');
            }
            if (! Schema::hasColumn('report_attachments', 'version')) {
                $table->unsignedInteger('version')->default(1)->after('uploaded_at');
            }
            if (! Schema::hasColumn('report_attachments', 'is_current')) {
                $table->boolean('is_current')->default(true)->after('version');
            }
        });
    }

    public function down(): void
    {
        Schema::table('report_attachments', function (Blueprint $table) {
            if (Schema::hasColumn('report_attachments', 'attachment_type_id')) {
                $table->dropConstrainedForeignId('attachment_type_id');
            }
            foreach (['original_filename', 'stored_filename', 'uploaded_at', 'version', 'is_current'] as $col) {
                if (Schema::hasColumn('report_attachments', $col)) {
                    $table->dropColumn($col);
                }
            }
            if (Schema::hasColumn('report_attachments', 'uploaded_by')) {
                $table->dropConstrainedForeignId('uploaded_by');
            }
        });

        Schema::dropIfExists('category_attachment_types');
        Schema::dropIfExists('attachment_types');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('report_attachments', function (Blueprint $table) {
            $table->string('document_type', 20)->default('other')->after('file_size');
            $table->json('parsed_data')->nullable()->after('document_type');
            $table->string('parse_status', 20)->default('pending')->after('parsed_data');
            $table->text('parse_message')->nullable()->after('parse_status');
        });
    }

    public function down(): void
    {
        Schema::table('report_attachments', function (Blueprint $table) {
            $table->dropColumn(['document_type', 'parsed_data', 'parse_status', 'parse_message']);
        });
    }
};

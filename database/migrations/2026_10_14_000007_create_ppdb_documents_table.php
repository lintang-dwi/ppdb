<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ppdb_documents', function (Blueprint $table) {
            $table->id();
            $table->string('school_id', 50)->index();
            $table->foreignId('applicant_id')->constrained('ppdb_applicants')->onDelete('cascade');
            $table->string('document_type', 50);
            $table->string('original_file_name', 255)->nullable();
            $table->string('drive_file_id', 255)->nullable();
            $table->text('drive_view_url')->nullable();
            $table->boolean('is_physical_submitted')->default(false);
            $table->enum('verification_status', ['pending', 'valid', 'invalid'])->default('pending');
            $table->string('rejection_reason', 255)->nullable();
            $table->timestamps();

            $table->index(['applicant_id', 'document_type']);
            $table->foreign('school_id')->references('school_code')->on('schools')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ppdb_documents');
    }
};

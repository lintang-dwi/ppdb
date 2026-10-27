<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ppdb_applicants', function (Blueprint $table) {
            $table->id();
            $table->string('school_id', 50)->index();
            $table->foreignId('wave_id')->constrained('ppdb_waves')->onDelete('cascade');
            $table->foreignId('track_id')->nullable()->constrained('ppdb_tracks')->onDelete('set null');
            $table->string('registration_number', 50)->unique();
            $table->enum('registration_origin', ['online', 'offline_walk_in'])->default('online');
            $table->string('full_name', 255);
            $table->string('nik', 20)->index();
            $table->date('birth_date');
            $table->string('birth_place', 100)->nullable();
            $table->enum('gender', ['L', 'P'])->nullable();
            $table->string('phone_whatsapp', 25);
            $table->json('custom_fields')->nullable();
            $table->enum('document_completeness', ['complete_digital', 'complete_physical', 'incomplete'])->default('incomplete');
            $table->enum('status', ['submitted', 'need_revision', 'verified', 'accepted', 'completed', 'cancelled'])->default('submitted');
            $table->text('verification_notes')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->string('verified_by', 100)->nullable();
            $table->timestamps();

            $table->index(['school_id', 'status']);
            $table->index(['registration_number', 'birth_date']);
            $table->foreign('school_id')->references('school_code')->on('schools')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ppdb_applicants');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ppdb_form_fields', function (Blueprint $table) {
            $table->id();
            $table->string('school_id', 50)->index();
            $table->foreignId('wave_id')->nullable()->constrained('ppdb_waves')->onDelete('cascade');
            $table->string('step', 50); // biodata, orang_tua, asal_sekolah, berkas, tambahan
            $table->string('field_key', 100);
            $table->string('field_label', 150);
            $table->enum('field_type', ['text', 'number', 'select', 'radio', 'checkbox', 'textarea', 'date', 'file']);
            $table->json('options')->nullable();
            $table->boolean('is_required')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('order_index')->default(0);
            $table->timestamps();

            $table->index(['school_id', 'step', 'is_active']);
            $table->foreign('school_id')->references('school_code')->on('schools')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ppdb_form_fields');
    }
};

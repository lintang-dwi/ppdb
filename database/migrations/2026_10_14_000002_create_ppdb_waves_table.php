<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ppdb_waves', function (Blueprint $table) {
            $table->id();
            $table->string('school_id', 50)->index();
            $table->string('name', 150);
            $table->string('academic_year', 20);
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->dateTime('announcement_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['school_id', 'is_active']);
            $table->foreign('school_id')->references('school_code')->on('schools')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ppdb_waves');
    }
};

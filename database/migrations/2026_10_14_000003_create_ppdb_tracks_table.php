<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ppdb_tracks', function (Blueprint $table) {
            $table->id();
            $table->string('school_id', 50)->index();
            $table->string('name', 100);
            $table->string('slug', 100);
            $table->integer('quota')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('order_index')->default(0);
            $table->timestamps();

            $table->index(['school_id', 'is_active']);
            $table->unique(['school_id', 'slug']);
            $table->foreign('school_id')->references('school_code')->on('schools')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ppdb_tracks');
    }
};

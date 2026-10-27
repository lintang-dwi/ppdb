<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ppdb_users', function (Blueprint $table) {
            $table->id();
            $table->string('school_id', 50)->index();
            $table->string('name', 255);
            $table->string('email', 255);
            $table->string('password', 255);
            $table->rememberToken();
            $table->timestamps();

            $table->unique(['school_id', 'email']);
            $table->foreign('school_id')->references('school_code')->on('schools')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ppdb_users');
    }
};

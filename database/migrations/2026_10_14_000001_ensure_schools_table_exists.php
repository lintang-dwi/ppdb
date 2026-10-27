<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ensures the schools table exists for testing or standalone environments.
     * In saas_core, this table is already present and will be skipped.
     */
    public function up(): void
    {
        if (! Schema::hasTable('schools')) {
            Schema::create('schools', function (Blueprint $table) {
                $table->string('school_code', 50)->primary();
                $table->uuid('uuid')->nullable();
                $table->string('name');
                $table->string('subdomain')->unique();
                $table->string('custom_domain')->nullable()->unique();
                $table->boolean('custom_domain_verified')->default(false);
                $table->string('theme')->default('default');
                $table->string('status')->default('active');
                $table->string('logo')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        // Never drop schools table as it belongs to saas_core master registry.
    }
};

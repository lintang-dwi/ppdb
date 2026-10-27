<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            if (! Schema::hasColumn('schools', 'google_drive_refresh_token')) {
                $table->text('google_drive_refresh_token')->nullable()->after('status');
            }
            if (! Schema::hasColumn('schools', 'google_drive_email')) {
                $table->string('google_drive_email', 255)->nullable()->after('google_drive_refresh_token');
            }
            if (! Schema::hasColumn('schools', 'google_drive_root_folder_id')) {
                $table->string('google_drive_root_folder_id', 255)->nullable()->after('google_drive_email');
            }
        });
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn([
                'google_drive_refresh_token',
                'google_drive_email',
                'google_drive_root_folder_id',
            ]);
        });
    }
};

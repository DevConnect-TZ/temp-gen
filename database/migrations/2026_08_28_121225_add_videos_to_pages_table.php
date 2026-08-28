<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('pages', 'videos')) {
            Schema::table('pages', function (Blueprint $table) {
                $table->json('videos')->nullable()->after('video_path');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('pages', 'videos')) {
            Schema::table('pages', function (Blueprint $table) {
                $table->dropColumn('videos');
            });
        }
    }
};

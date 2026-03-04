<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify template enum to include 'custom'
        DB::statement("ALTER TABLE pages MODIFY template ENUM('template1', 'template2', 'custom') DEFAULT 'template1'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE pages MODIFY template ENUM('template1', 'template2') DEFAULT 'template1'");
    }
};

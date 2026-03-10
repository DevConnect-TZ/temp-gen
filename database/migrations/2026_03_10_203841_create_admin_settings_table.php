<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Seed default credentials
        DB::table('admin_settings')->insert([
            ['key' => 'admin_email',    'value' => 'admin@example.com',         'created_at' => now(), 'updated_at' => now()],
            ['key' => 'admin_password', 'value' => Hash::make('password'),       'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_settings');
    }
};

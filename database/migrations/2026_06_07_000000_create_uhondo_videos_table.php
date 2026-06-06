<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('uhondo_videos', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('episode_label')->nullable();
            $table->text('description')->nullable();
            $table->string('video_path');
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uhondo_videos');
    }
};

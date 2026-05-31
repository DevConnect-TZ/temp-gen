<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            $this->recreateTable([
                'template1', 'template2', 'template3', 'custom',
            ]);
        } else {
            DB::statement("ALTER TABLE pages MODIFY template ENUM('template1', 'template2', 'template3', 'custom') DEFAULT 'template1'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            $this->recreateTable([
                'template1', 'template2', 'custom',
            ]);
        } else {
            DB::statement("ALTER TABLE pages MODIFY template ENUM('template1', 'template2', 'custom') DEFAULT 'template1'");
        }
    }

    /**
     * Recreate the pages table with a given template set (SQLite compatible).
     *
     * @param  array<string>  $templates
     */
    private function recreateTable(array $templates): void
    {
        $existingPages = DB::table('pages')->get();

        Schema::rename('pages', 'pages_old');

        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('template')->default('template1');
            $table->decimal('price', 10, 2)->nullable();
            $table->string('payment_gateway')->nullable();
            $table->string('video_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        foreach ($existingPages as $page) {
            DB::table('pages')->insert([
                'id' => $page->id,
                'title' => $page->title,
                'slug' => $page->slug,
                'template' => $page->template,
                'price' => $page->price,
                'payment_gateway' => $page->payment_gateway,
                'video_path' => $page->video_path,
                'is_active' => $page->is_active,
                'created_at' => $page->created_at,
                'updated_at' => $page->updated_at,
            ]);
        }

        Schema::drop('pages_old');
    }
};

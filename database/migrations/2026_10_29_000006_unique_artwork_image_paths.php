<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('artworks') && ! Schema::hasIndex('artworks', 'artworks_image_unique')) {
            Schema::table('artworks', function (Blueprint $table): void {
                $table->unique('image', 'artworks_image_unique');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('artworks') && Schema::hasIndex('artworks', 'artworks_image_unique')) {
            Schema::table('artworks', function (Blueprint $table): void {
                $table->dropUnique('artworks_image_unique');
            });
        }
    }
};

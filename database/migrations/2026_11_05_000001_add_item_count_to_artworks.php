<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('artworks', 'item_count')) {
            Schema::table('artworks', function (Blueprint $table): void {
                $table->unsignedInteger('item_count')->default(1);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('artworks', 'item_count')) {
            Schema::table('artworks', function (Blueprint $table): void {
                $table->dropColumn('item_count');
            });
        }
    }
};
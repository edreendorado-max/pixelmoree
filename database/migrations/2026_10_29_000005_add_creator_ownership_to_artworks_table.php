<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('artworks', 'user_id')) {
            Schema::table('artworks', function (Blueprint $table): void {
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('artworks', 'image_hash')) {
            Schema::table('artworks', function (Blueprint $table): void {
                $table->string('image_hash', 64)->nullable()->unique();
            });
        }

        if (! Schema::hasColumn('users', 'avatar_path')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->string('avatar_path')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('artworks', 'image_hash')) {
            Schema::table('artworks', function (Blueprint $table): void {
                $table->dropUnique(['image_hash']);
                $table->dropColumn('image_hash');
            });
        }

        if (Schema::hasColumn('artworks', 'user_id')) {
            Schema::table('artworks', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('user_id');
            });
        }

        if (Schema::hasColumn('users', 'avatar_path')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropColumn('avatar_path');
            });
        }
    }
};

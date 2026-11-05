<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'google_id')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->string('google_id', 191)->nullable()->unique();
            });
        }

        if (! Schema::hasColumn('users', 'facebook_id')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->string('facebook_id', 191)->nullable()->unique();
            });
        }

        if (! Schema::hasTable('password_reset_tokens')) {
            Schema::create('password_reset_tokens', function (Blueprint $table): void {
                $table->string('email', 254)->primary();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');

        if (Schema::hasColumn('users', 'google_id')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropUnique(['google_id']);
                $table->dropColumn('google_id');
            });
        }

        if (Schema::hasColumn('users', 'facebook_id')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropUnique(['facebook_id']);
                $table->dropColumn('facebook_id');
            });
        }
    }
};
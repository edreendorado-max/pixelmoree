<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('artwork_views', function (Blueprint $table): void {
            $table->id();
            $table->string('artwork_id', 140);
            $table->char('viewer_key', 64);
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['artwork_id', 'viewer_key']);
            $table->foreign('artwork_id')->references('id')->on('artworks')->cascadeOnDelete();
        });

        Schema::create('artwork_likes', function (Blueprint $table): void {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('artwork_id', 140);
            $table->timestamps();
            $table->primary(['user_id', 'artwork_id']);
            $table->foreign('artwork_id')->references('id')->on('artworks')->cascadeOnDelete();
        });

        Schema::create('creator_views', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('creator_id')->constrained('users')->cascadeOnDelete();
            $table->char('viewer_key', 64);
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['creator_id', 'viewer_key']);
        });

        Schema::create('creator_likes', function (Blueprint $table): void {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('creator_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['user_id', 'creator_id']);
        });

        Schema::create('creator_follows', function (Blueprint $table): void {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('creator_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['user_id', 'creator_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('creator_follows');
        Schema::dropIfExists('creator_likes');
        Schema::dropIfExists('creator_views');
        Schema::dropIfExists('artwork_likes');
        Schema::dropIfExists('artwork_views');
    }
};
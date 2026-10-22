<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('artworks')) {
            return;
        }

        Schema::create('artworks', function (Blueprint $table): void {
            $table->string('id', 140)->primary();
            $table->string('title', 120);
            $table->string('creator', 80);
            $table->string('category', 50);
            $table->decimal('price', 10, 2)->default(0);
            $table->string('image', 255);
            $table->string('description', 500);
            $table->string('status', 16)->default('Review');
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artworks');
    }
};

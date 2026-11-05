<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('artwork_price_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->string('artwork_id', 140);
            $table->date('snapshot_date');
            $table->decimal('price', 12, 2);
            $table->timestamps();
            $table->unique(['artwork_id', 'snapshot_date']);
            $table->foreign('artwork_id')->references('id')->on('artworks')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artwork_price_snapshots');
    }
};

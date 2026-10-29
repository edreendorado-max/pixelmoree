<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'pixel_coin_balance')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->unsignedBigInteger('pixel_coin_balance')->default(0);
            });
        }

        if (! Schema::hasTable('pixel_coin_transactions')) {
            Schema::create('pixel_coin_transactions', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('artwork_id', 140)->nullable();
                $table->string('type', 24);
                $table->unsignedBigInteger('amount');
                $table->unsignedBigInteger('balance_after');
                $table->string('description', 255);
                $table->timestamps();
                $table->foreign('artwork_id')->references('id')->on('artworks')->nullOnDelete();
                $table->unique(['user_id', 'type', 'artwork_id'], 'pixel_coin_transactions_purchase_unique');
                $table->index(['user_id', 'created_at'], 'pixel_coin_transactions_user_created_index');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pixel_coin_transactions');

        if (Schema::hasColumn('users', 'pixel_coin_balance')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropColumn('pixel_coin_balance');
            });
        }
    }
};

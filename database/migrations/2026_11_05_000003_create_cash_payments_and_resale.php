<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->unsignedBigInteger('cash_balance_minor')->default(0);
            $table->unsignedBigInteger('cash_reserved_minor')->default(0);
        });

        Schema::create('payment_orders', function (Blueprint $table): void {
            $table->uuid('purchase_id')->primary();
            $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('seller_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('artwork_id', 140)->nullable();
            $table->string('type', 24);
            $table->unsignedBigInteger('amount_minor');
            $table->char('currency', 3)->default('PHP');
            $table->string('payment_method', 24);
            $table->string('transaction_reference', 120)->nullable();
            $table->string('proof_path', 255)->nullable();
            $table->string('status', 24)->default('pending_review');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->foreign('artwork_id')->references('id')->on('artworks')->nullOnDelete();
            $table->index(['status', 'created_at']);
        });

        Schema::create('cash_ledger_entries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->uuid('purchase_id');
            $table->string('type', 24);
            $table->string('direction', 8);
            $table->unsignedBigInteger('amount_minor');
            $table->unsignedBigInteger('balance_after_minor');
            $table->timestamps();
            $table->foreign('purchase_id')->references('purchase_id')->on('payment_orders')->cascadeOnDelete();
            $table->unique(['user_id', 'purchase_id', 'type']);
        });

        Schema::create('artwork_ownerships', function (Blueprint $table): void {
            $table->id();
            $table->string('artwork_id', 140)->unique();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->uuid('acquired_purchase_id')->nullable()->unique();
            $table->timestamps();
            $table->foreign('artwork_id')->references('id')->on('artworks')->cascadeOnDelete();
            $table->foreign('acquired_purchase_id')->references('purchase_id')->on('payment_orders')->nullOnDelete();
        });

        Schema::create('resale_listings', function (Blueprint $table): void {
            $table->id();
            $table->string('artwork_id', 140)->unique();
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('price_minor');
            $table->string('evidence_path', 255);
            $table->text('ownership_declaration');
            $table->string('status', 24)->default('pending_review');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->foreign('artwork_id')->references('id')->on('artworks')->cascadeOnDelete();
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resale_listings');
        Schema::dropIfExists('artwork_ownerships');
        Schema::dropIfExists('cash_ledger_entries');
        Schema::dropIfExists('payment_orders');
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['cash_balance_minor', 'cash_reserved_minor']);
        });
    }
};

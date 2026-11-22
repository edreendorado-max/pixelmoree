<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('admin_payment_settings');
        Schema::dropIfExists('pixel_coin_transactions');

        $userColumns = array_values(array_filter(
            ['pixel_coin_balance', 'pixel_coin_reserved', 'cash_reserved_minor', 'gcash_number', 'gcash_qr_path', 'maya_qr_path'],
            static fn (string $column): bool => Schema::hasColumn('users', $column),
        ));
        if ($userColumns !== []) {
            Schema::table('users', static function (Blueprint $table) use ($userColumns): void {
                $table->dropColumn($userColumns);
            });
        }

        foreach (['payment_orders_method_reference_unique', 'payment_orders_payout_reference_unique'] as $index) {
            if (Schema::hasIndex('payment_orders', $index, 'unique')) {
                Schema::table('payment_orders', static function (Blueprint $table) use ($index): void {
                    $table->dropUnique($index);
                });
            }
        }
        if (Schema::hasColumn('payment_orders', 'reviewed_by')) {
            Schema::table('payment_orders', static function (Blueprint $table): void {
                $table->dropForeign(['reviewed_by']);
            });
        }

        $orderColumns = array_values(array_filter(
            ['coin_amount', 'fee_amount_minor', 'transaction_reference', 'proof_path', 'reviewed_by', 'payout_email', 'payout_reference'],
            static fn (string $column): bool => Schema::hasColumn('payment_orders', $column),
        ));
        if ($orderColumns !== []) {
            Schema::table('payment_orders', static function (Blueprint $table) use ($orderColumns): void {
                $table->dropColumn($orderColumns);
            });
        }
    }

    public function down(): void
    {
        $userColumns = array_values(array_filter(
            ['pixel_coin_balance', 'pixel_coin_reserved', 'cash_reserved_minor', 'gcash_number', 'gcash_qr_path', 'maya_qr_path'],
            static fn (string $column): bool => ! Schema::hasColumn('users', $column),
        ));
        if ($userColumns !== []) {
            Schema::table('users', static function (Blueprint $table) use ($userColumns): void {
                foreach ($userColumns as $column) {
                    match ($column) {
                        'pixel_coin_balance', 'pixel_coin_reserved', 'cash_reserved_minor' => $table->unsignedBigInteger($column)->default(0),
                        'gcash_number' => $table->string($column, 30)->nullable(),
                        default => $table->string($column, 255)->nullable(),
                    };
                }
            });
        }

        $orderColumns = array_values(array_filter(
            ['coin_amount', 'fee_amount_minor', 'transaction_reference', 'proof_path', 'reviewed_by', 'payout_email', 'payout_reference'],
            static fn (string $column): bool => ! Schema::hasColumn('payment_orders', $column),
        ));
        if ($orderColumns !== []) {
            Schema::table('payment_orders', static function (Blueprint $table) use ($orderColumns): void {
                foreach ($orderColumns as $column) {
                    match ($column) {
                        'coin_amount', 'fee_amount_minor' => $table->unsignedBigInteger($column)->default(0),
                        'transaction_reference' => $table->string($column, 120)->nullable(),
                        'proof_path' => $table->string($column, 255)->nullable(),
                        'reviewed_by' => $table->foreignId($column)->nullable()->constrained('users')->nullOnDelete(),
                        'payout_email' => $table->string($column, 254)->nullable(),
                        default => $table->string($column, 120)->nullable(),
                    };
                }
            });
        }

        if (! Schema::hasIndex('payment_orders', 'payment_orders_method_reference_unique', 'unique')) {
            Schema::table('payment_orders', static function (Blueprint $table): void {
                $table->unique(['payment_method', 'transaction_reference'], 'payment_orders_method_reference_unique');
            });
        }
        if (! Schema::hasIndex('payment_orders', 'payment_orders_payout_reference_unique', 'unique')) {
            Schema::table('payment_orders', static function (Blueprint $table): void {
                $table->unique('payout_reference', 'payment_orders_payout_reference_unique');
            });
        }

        if (! Schema::hasTable('admin_payment_settings')) {
            Schema::create('admin_payment_settings', static function (Blueprint $table): void {
                $table->unsignedTinyInteger('id')->primary();
                $table->string('gcash_qr_path', 255)->nullable();
                $table->string('gcash_account_name', 120)->nullable();
                $table->string('gcash_number', 30)->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('pixel_coin_transactions')) {
            Schema::create('pixel_coin_transactions', static function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('artwork_id', 140)->nullable();
                $table->uuid('artwork_item_id')->nullable();
                $table->uuid('payment_order_id')->nullable();
                $table->uuid('transaction_id')->nullable()->unique('pixel_coin_transactions_transaction_id_unique');
                $table->string('type', 24);
                $table->string('direction', 8)->default('credit');
                $table->unsignedBigInteger('amount');
                $table->unsignedBigInteger('balance_after');
                $table->string('description', 255);
                $table->timestamps();
                $table->foreign('artwork_id')->references('id')->on('artworks')->nullOnDelete();
                $table->index(['user_id', 'created_at'], 'pixel_coin_transactions_user_created_idx');
            });
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'gcash_number')) $table->string('gcash_number', 30)->nullable();
            if (! Schema::hasColumn('users', 'pixel_coin_reserved')) $table->unsignedBigInteger('pixel_coin_reserved')->default(0);
        });

        Schema::create('artwork_items', function (Blueprint $table): void {
            $table->uuid('item_id')->primary();
            $table->string('artwork_id', 140);
            $table->unsignedInteger('edition_number');
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->uuid('acquired_purchase_id')->nullable()->unique();
            $table->uuid('reserved_purchase_id')->nullable()->unique();
            $table->string('status', 16)->default('available');
            $table->timestamps();
            $table->foreign('artwork_id')->references('id')->on('artworks')->cascadeOnDelete();
            $table->unique(['artwork_id', 'edition_number']);
            $table->index(['artwork_id', 'status']);
        });

        DB::table('artworks')->orderBy('id')->chunk(100, static function ($artworks): void {
            foreach ($artworks as $artwork) {
                $quantity = max(1, (int) ($artwork->item_count ?? 1));
                $legacyOwner = DB::table('artwork_ownerships')->where('artwork_id', $artwork->id)->first();
                for ($edition = 1; $edition <= $quantity; $edition++) {
                    DB::table('artwork_items')->insertOrIgnore([
                        'item_id' => (string) Str::uuid(),
                        'artwork_id' => $artwork->id,
                        'edition_number' => $edition,
                        'owner_id' => $edition === 1 ? $legacyOwner?->owner_id : null,
                        'acquired_purchase_id' => $edition === 1 ? $legacyOwner?->acquired_purchase_id : null,
                        'status' => $edition === 1 && $legacyOwner !== null ? 'owned' : 'available',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        });

        // Store coins in hundredths so fractional coin conversions remain precise.
        DB::table('users')->update(['pixel_coin_balance' => DB::raw('pixel_coin_balance * 100')]);
        DB::table('pixel_coin_transactions')->update([
            'amount' => DB::raw('amount * 100'),
            'balance_after' => DB::raw('balance_after * 100'),
        ]);

        Schema::table('payment_orders', function (Blueprint $table): void {
            $table->uuid('item_id')->nullable();
            $table->unsignedBigInteger('coin_amount')->default(0);
            $table->unsignedBigInteger('fee_amount_minor')->default(0);
            $table->index(['type', 'status', 'created_at'], 'payment_orders_type_status_created_index');
        });
        DB::table('payment_orders')->where('type', 'nft_purchase')->orderBy('created_at')->chunk(100, static function ($orders): void {
            foreach ($orders as $order) {
                $item = DB::table('artwork_items')->where('acquired_purchase_id', $order->purchase_id)->first();
                if ($item === null && $order->status === 'pending_review' && $order->artwork_id !== null) {
                    $item = DB::table('artwork_items')->where('artwork_id', $order->artwork_id)->where('status', 'available')->orderBy('edition_number')->first();
                    if ($item !== null) DB::table('artwork_items')->where('item_id', $item->item_id)->update([
                        'status' => 'reserved', 'reserved_purchase_id' => $order->purchase_id, 'updated_at' => now(),
                    ]);
                }
                if ($item !== null) DB::table('payment_orders')->where('purchase_id', $order->purchase_id)->update([
                    'item_id' => $item->item_id,
                    'coin_amount' => $order->coin_amount > 0 ? $order->coin_amount : intdiv((int) $order->amount_minor * 185, 200),
                ]);
            }
        });
        Schema::table('ownership_transfers', function (Blueprint $table): void {
            $table->uuid('item_id')->nullable();
        });

        if (Schema::hasIndex('pixel_coin_transactions', 'pixel_coin_transactions_purchase_unique', 'unique')) {
            Schema::table('pixel_coin_transactions', function (Blueprint $table): void {
                $table->dropUnique('pixel_coin_transactions_purchase_unique');
            });
        }
        Schema::table('pixel_coin_transactions', function (Blueprint $table): void {
            $table->uuid('transaction_id')->nullable();
            $table->string('direction', 8)->default('credit');
            $table->uuid('artwork_item_id')->nullable();
            $table->uuid('payment_order_id')->nullable();
            $table->unique('transaction_id', 'pixel_coin_transactions_transaction_id_unique');
            $table->index(['user_id', 'created_at'], 'pixel_coin_transactions_user_created_idx');
        });
        DB::table('pixel_coin_transactions')->whereNull('transaction_id')->orderBy('id')->chunkById(100, static function ($rows): void {
            foreach ($rows as $row) DB::table('pixel_coin_transactions')->where('id', $row->id)->update(['transaction_id' => (string) Str::uuid()]);
        });

        Schema::create('admin_payment_settings', function (Blueprint $table): void {
            $table->unsignedTinyInteger('id')->primary();
            $table->string('gcash_qr_path', 255)->nullable();
            $table->string('gcash_account_name', 120)->nullable();
            $table->string('gcash_number', 30)->nullable();
            $table->timestamps();
        });
        DB::table('admin_payment_settings')->insert([
            'id' => 1, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_payment_settings');
        Schema::dropIfExists('artwork_items');
        Schema::table('pixel_coin_transactions', function (Blueprint $table): void {
            $table->dropUnique('pixel_coin_transactions_transaction_id_unique');
            $table->dropIndex('pixel_coin_transactions_user_created_idx');
            $table->dropColumn(['transaction_id', 'direction', 'artwork_item_id', 'payment_order_id']);
            $table->unique(['user_id', 'type', 'artwork_id'], 'pixel_coin_transactions_purchase_unique');
        });
        Schema::table('payment_orders', function (Blueprint $table): void {
            $table->dropIndex('payment_orders_type_status_created_index');
            $table->dropColumn(['item_id', 'coin_amount', 'fee_amount_minor']);
        });
        Schema::table('ownership_transfers', function (Blueprint $table): void {
            $table->dropColumn('item_id');
        });
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['gcash_number', 'pixel_coin_reserved']);
        });
    }
};

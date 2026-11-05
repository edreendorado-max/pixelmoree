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
        $hasDuplicatePaymentReferences = DB::table('payment_orders')
            ->whereNotNull('transaction_reference')
            ->select('payment_method', 'transaction_reference')
            ->groupBy('payment_method', 'transaction_reference')
            ->havingRaw('COUNT(*) > 1')
            ->exists();
        if ($hasDuplicatePaymentReferences) {
            throw new RuntimeException('Cannot enforce unique manual payment references: duplicate references already exist.');
        }

        if (! Schema::hasColumn('users', 'account_id')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->uuid('account_id')->nullable();
            });
        }
        if (! Schema::hasIndex('users', 'users_account_id_unique')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->unique('account_id', 'users_account_id_unique');
            });
        }
        DB::table('users')->whereNull('account_id')->orderBy('id')->chunkById(100, static function ($users): void {
            foreach ($users as $user) {
                DB::table('users')->where('id', $user->id)->update(['account_id' => (string) Str::uuid()]);
            }
        });

        if (! Schema::hasTable('user_profiles')) {
            Schema::create('user_profiles', function (Blueprint $table): void {
                $table->foreignId('user_id')->primary()->constrained()->cascadeOnDelete();
                $table->string('phone', 30)->nullable();
                $table->text('bio')->nullable();
                $table->string('avatar_path', 255)->nullable();
                $table->string('banner_path', 255)->nullable();
                $table->timestamps();
            });
        }
        DB::table('users')->orderBy('id')->chunkById(100, static function ($users): void {
            foreach ($users as $user) {
                $profile = [
                    'phone' => $user->phone,
                    'bio' => $user->bio,
                    'avatar_path' => $user->avatar_path,
                    'banner_path' => $user->banner_path,
                    'updated_at' => now(),
                ];
                $query = DB::table('user_profiles')->where('user_id', $user->id);
                if ($query->exists()) {
                    $query->update($profile);
                } else {
                    DB::table('user_profiles')->insert($profile + ['user_id' => $user->id, 'created_at' => now()]);
                }
            }
        });

        if (! Schema::hasColumn('artworks', 'product_id')) {
            Schema::table('artworks', function (Blueprint $table): void {
                $table->uuid('product_id')->nullable();
            });
        }
        if (! Schema::hasIndex('artworks', 'artworks_product_id_unique')) {
            Schema::table('artworks', function (Blueprint $table): void {
                $table->unique('product_id', 'artworks_product_id_unique');
            });
        }
        DB::table('artworks')->whereNull('product_id')->orderBy('id')->chunkById(100, static function ($artworks): void {
            foreach ($artworks as $artwork) {
                DB::table('artworks')->where('id', $artwork->id)->update(['product_id' => (string) Str::uuid()]);
            }
        }, 'id');

        if (! Schema::hasColumn('payment_orders', 'payout_email')) {
            Schema::table('payment_orders', function (Blueprint $table): void {
                $table->string('payout_email', 254)->nullable();
                $table->string('payout_reference', 120)->nullable();
            });
        }
        if (! Schema::hasIndex('payment_orders', 'payment_orders_method_reference_unique')) {
            Schema::table('payment_orders', function (Blueprint $table): void {
                $table->unique(['payment_method', 'transaction_reference'], 'payment_orders_method_reference_unique');
            });
        }
        if (! Schema::hasIndex('resale_listings', 'resale_listings_artwork_status_index')) {
            Schema::table('resale_listings', function (Blueprint $table): void {
                $table->index(['artwork_id', 'status'], 'resale_listings_artwork_status_index');
            });
        }
        if (Schema::hasIndex('resale_listings', 'resale_listings_artwork_id_unique', 'unique')) {
            Schema::table('resale_listings', function (Blueprint $table): void {
                $table->dropUnique('resale_listings_artwork_id_unique');
            });
        }

        if (! Schema::hasTable('ownership_transfers')) {
            Schema::create('ownership_transfers', function (Blueprint $table): void {
                $table->id();
                $table->string('product_id', 36);
                $table->foreignId('from_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('to_user_id')->constrained('users')->restrictOnDelete();
                $table->uuid('purchase_id')->unique();
                $table->unsignedBigInteger('amount_minor');
                $table->char('currency', 3)->default('PHP');
                $table->timestamps();
                $table->foreign('product_id')->references('product_id')->on('artworks')->cascadeOnDelete();
                $table->foreign('purchase_id')->references('purchase_id')->on('payment_orders')->restrictOnDelete();
            });
        }

        DB::table('artwork_ownerships')
            ->join('artworks', 'artworks.id', '=', 'artwork_ownerships.artwork_id')
            ->whereNotNull('artworks.product_id')
            ->whereNotNull('artwork_ownerships.acquired_purchase_id')
            ->select([
                'artworks.product_id',
                'artwork_ownerships.owner_id',
                'artwork_ownerships.acquired_purchase_id',
                'artwork_ownerships.created_at',
                'artwork_ownerships.updated_at',
            ])
            ->orderBy('artwork_ownerships.id')
            ->chunk(100, static function ($ownerships): void {
                foreach ($ownerships as $ownership) {
                    $order = DB::table('payment_orders')->where('purchase_id', $ownership->acquired_purchase_id)->first();
                    if ($order === null) continue;
                    DB::table('ownership_transfers')->insertOrIgnore([
                        'product_id' => $ownership->product_id,
                        'from_user_id' => $order->seller_id,
                        'to_user_id' => $ownership->owner_id,
                        'purchase_id' => $ownership->acquired_purchase_id,
                        'amount_minor' => $order->amount_minor,
                        'currency' => $order->currency,
                        'created_at' => $ownership->created_at,
                        'updated_at' => $ownership->updated_at,
                    ]);
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('ownership_transfers');
        if (Schema::hasIndex('resale_listings', 'resale_listings_artwork_status_index')) {
            Schema::table('resale_listings', function (Blueprint $table): void {
                $table->dropIndex('resale_listings_artwork_status_index');
            });
        }
        if (! Schema::hasIndex('resale_listings', 'resale_listings_artwork_id_unique', 'unique')) {
            Schema::table('resale_listings', function (Blueprint $table): void {
                $table->unique('artwork_id');
            });
        }
        if (Schema::hasColumn('payment_orders', 'payout_email')) {
            Schema::table('payment_orders', function (Blueprint $table): void {
                if (Schema::hasIndex('payment_orders', 'payment_orders_method_reference_unique', 'unique')) {
                    $table->dropUnique('payment_orders_method_reference_unique');
                }
                $table->dropColumn(['payout_email', 'payout_reference']);
            });
        }
        if (Schema::hasColumn('artworks', 'product_id')) {
            Schema::table('artworks', function (Blueprint $table): void {
                if (Schema::hasIndex('artworks', 'artworks_product_id_unique', 'unique')) {
                    $table->dropUnique('artworks_product_id_unique');
                }
                $table->dropColumn('product_id');
            });
        }
        Schema::dropIfExists('user_profiles');
        if (Schema::hasColumn('users', 'account_id')) {
            Schema::table('users', function (Blueprint $table): void {
                if (Schema::hasIndex('users', 'users_account_id_unique', 'unique')) {
                    $table->dropUnique('users_account_id_unique');
                }
                $table->dropColumn('account_id');
            });
        }
    }
};

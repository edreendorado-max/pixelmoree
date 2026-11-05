<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $hasDuplicatePayoutReferences = DB::table('payment_orders')
            ->whereNotNull('payout_reference')
            ->select('payout_reference')
            ->groupBy('payout_reference')
            ->havingRaw('COUNT(*) > 1')
            ->exists();
        if ($hasDuplicatePayoutReferences) {
            throw new RuntimeException('Cannot enforce unique payout references: duplicate references already exist.');
        }

        if (! Schema::hasIndex('payment_orders', 'payment_orders_payout_reference_unique')) {
            Schema::table('payment_orders', function (Blueprint $table): void {
                $table->unique('payout_reference', 'payment_orders_payout_reference_unique');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasIndex('payment_orders', 'payment_orders_payout_reference_unique', 'unique')) {
            Schema::table('payment_orders', function (Blueprint $table): void {
                $table->dropUnique('payment_orders_payout_reference_unique');
            });
        }
    }
};

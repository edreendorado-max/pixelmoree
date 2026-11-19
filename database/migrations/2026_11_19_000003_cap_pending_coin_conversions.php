<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // The selected cash-out policy is PHP 1.20 per PC. Keep unsettled
        // orders created under the old 185/200 top-up rate below that value.
        DB::table('payment_orders')
            ->where('status', 'pending_review')
            ->whereIn('type', ['nft_purchase', 'pixel_coin_topup'])
            ->orderBy('purchase_id')
            ->chunk(100, static function ($orders): void {
                foreach ($orders as $order) {
                    $basePhpMinor = (int) $order->amount_minor;
                    if ($order->type === 'pixel_coin_topup') {
                        $basePhpMinor = max(0, $basePhpMinor - (int) $order->fee_amount_minor);
                    }

                    $safeCoinMinor = intdiv($basePhpMinor * 100, 120);
                    $coinMinor = (int) $order->coin_amount;
                    if ($coinMinor <= 0) {
                        $coinMinor = intdiv($basePhpMinor * 5, 6);
                    }

                    $coinMinor = min($coinMinor, $safeCoinMinor);
                    DB::table('payment_orders')
                        ->where('purchase_id', $order->purchase_id)
                        ->update(['coin_amount' => $coinMinor]);
                }
            });
    }

    public function down(): void
    {
        // Do not restore an unsafe coin amount after an order has been reviewed.
    }
};

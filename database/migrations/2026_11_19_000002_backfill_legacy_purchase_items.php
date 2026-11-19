<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('payment_orders')->where('type', 'nft_purchase')->whereNull('item_id')->orderBy('created_at')->chunk(100, static function ($orders): void {
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
    }

    public function down(): void
    {
        // The backfill is intentionally preserved; deleting its item assignments would lose order history.
    }
};

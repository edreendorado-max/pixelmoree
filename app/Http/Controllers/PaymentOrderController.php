<?php

namespace App\Http\Controllers;

use App\Models\Artwork;
use App\Models\PaymentOrder;
use App\Models\User;
use App\Notifications\MarketplaceNotification;
use App\Support\PixelCoinConversion;
use App\Support\PixelCoinLedger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PaymentOrderController extends Controller
{
    public function receipt(Request $request, PaymentOrder $order): View
    {
        abort_unless(in_array((int) $request->user()->id, [(int) $order->buyer_id, (int) $order->seller_id], true), 404);
        abort_unless(in_array($order->type, ['nft_purchase', 'nft_coin_purchase', 'pixel_coin_topup', 'creator_payout'], true), 404);

        return view('user.receipt', ['order' => $order->load(['artwork', 'buyer', 'seller'])]);
    }

    public function checkout(Request $request, string $artworkId, PixelCoinConversion $conversion): View
    {
        $this->ensurePaymentsEnabled();
        $artwork = Artwork::query()->whereKey($artworkId)->where('status', 'Listed')->firstOrFail();
        $items = DB::table('artwork_items')->where('artwork_id', $artwork->id)->where('status', 'available')->orderBy('edition_number')->get();
        if ($items->isEmpty()) {
            $resale = DB::table('resale_listings')->where('artwork_id', $artwork->id)->where('status', 'listed')->first();
            if ($resale !== null && (int) $artwork->item_count === 1) {
                $items = DB::table('artwork_items')->where('artwork_id', $artwork->id)->where('owner_id', $resale->seller_id)->where('status', 'owned')->get();
            }
        }
        abort_if($items->isEmpty(), 409, 'This edition is sold out.');
        $sellerId = $this->sellerIdFor($artwork);
        abort_if((int) $sellerId === (int) $request->user()->id, 422, 'You cannot buy your own artwork.');
        $seller = User::query()->findOrFail($sellerId);
        $amountMinor = $this->artworkPriceMinor($artwork);
        $coinAmount = $conversion->coinsForCashMinor($amountMinor);
        $request->session()->put('payment_checkout.' . $artwork->id, [
            'started_at' => now()->timestamp, 'seller_id' => (int) $sellerId, 'amount_minor' => $amountMinor,
        ]);

        return view('payment-checkout', compact('artwork', 'seller', 'amountMinor', 'items', 'coinAmount'));
    }

    public function store(Request $request, PixelCoinLedger $ledger, PixelCoinConversion $conversion): RedirectResponse
    {
        $this->ensurePaymentsEnabled();
        $validated = $request->validate([
            'artwork_id' => ['required', 'string', 'exists:artworks,id'],
            'item_id' => ['required', 'uuid', 'exists:artwork_items,item_id'],
            'payment_method' => ['required', 'in:simulation,pixel_coin'],
            'transaction_reference' => ['prohibited'],
            'proof' => ['prohibited'],
        ]);
        $artwork = Artwork::query()->whereKey($validated['artwork_id'])->where('status', 'Listed')->firstOrFail();
        $sellerId = $this->sellerIdFor($artwork);
        abort_if((int) $sellerId === (int) $request->user()->id, 422, 'You cannot buy your own artwork.');
        $checkout = $request->session()->get('payment_checkout.' . $artwork->id);
        abort_unless($this->validCheckoutSession($checkout) && $checkout['seller_id'] === (int) $sellerId, 403, 'Start checkout again before buying.');
        $amountMinor = $this->artworkPriceMinor($artwork);
        abort_unless($checkout['amount_minor'] === $amountMinor, 409, 'The price changed. Review checkout again.');
        $coinAmount = $conversion->coinsForCashMinor($amountMinor);

        if ($validated['payment_method'] === 'simulation') {
            $orderId = (string) Str::uuid();
            DB::transaction(function () use ($request, $validated, $sellerId, $artwork, $amountMinor, $coinAmount, $orderId, $ledger): void {
                    $item = DB::table('artwork_items')->where('item_id', $validated['item_id'])->where('artwork_id', $artwork->id)->lockForUpdate()->first();
                    $isResale = DB::table('resale_listings')->where('artwork_id', $artwork->id)->where('seller_id', $sellerId)->where('status', 'listed')->exists();
                    abort_unless($item && ($item->status === 'available' || ($isResale && $item->status === 'owned' && (int) $item->owner_id === (int) $sellerId)), 409, 'That edition item is no longer available.');
                    $order = PaymentOrder::create([
                        'purchase_id' => $orderId, 'buyer_id' => $request->user()->id,
                        'seller_id' => $sellerId, 'artwork_id' => $artwork->id, 'item_id' => $item->item_id,
                        'type' => 'nft_purchase', 'amount_minor' => $amountMinor, 'coin_amount' => $coinAmount,
                        'currency' => 'PHP', 'payment_method' => 'simulation', 'status' => 'approved', 'reviewed_at' => now(),
                    ]);
                    $this->acquireItem($order, $item);
                    $buyer = User::query()->findOrFail($request->user()->id);
                    $seller = User::query()->findOrFail($sellerId);
                    $ledger->credit($seller, $coinAmount, 'sale', 'Simulated sale: '.$artwork->title, $artwork->id, $item->item_id, $orderId);
                    $buyer->notify(new MarketplaceNotification('Simulated purchase complete', 'You purchased '.$artwork->title.'. Receipt '.$orderId.'. The edition is now in your collection.', route('user.purchases')));
                    $seller->notify(new MarketplaceNotification('Artwork sold', $buyer->name.' purchased '.$artwork->title.' for PHP '.number_format($amountMinor / 100, 2).'. Simulated creator credit: '.PixelCoinLedger::format($coinAmount).' Pixel Coins. Receipt '.$orderId.'.', route('user.earnings')));
                });
            $request->session()->forget('payment_checkout.' . $artwork->id);

            return redirect()->route('user.purchases')->with('status', 'Simulated purchase complete. Your receipt is ready and the edition is in your collection. No real payment was made.');
        }

        $orderId = (string) Str::uuid();
        DB::transaction(function () use ($request, $validated, $artwork, $sellerId, $amountMinor, $coinAmount, $orderId, $ledger): void {
            $item = DB::table('artwork_items')->where('item_id', $validated['item_id'])->where('artwork_id', $artwork->id)->lockForUpdate()->first();
            $isResale = DB::table('resale_listings')->where('artwork_id', $artwork->id)->where('seller_id', $sellerId)->where('status', 'listed')->exists();
            abort_unless($item && ($item->status === 'available' || ($isResale && $item->status === 'owned' && (int) $item->owner_id === (int) $sellerId)), 409, 'That edition item was just purchased. Choose another available item.');
            $buyer = User::query()->lockForUpdate()->findOrFail($request->user()->id);
            $seller = User::query()->lockForUpdate()->findOrFail($sellerId);
            $order = PaymentOrder::create([
                'purchase_id' => $orderId, 'buyer_id' => $buyer->id, 'seller_id' => $seller->id,
                'artwork_id' => $artwork->id, 'item_id' => $item->item_id, 'type' => 'nft_coin_purchase',
                'amount_minor' => $amountMinor, 'coin_amount' => $coinAmount, 'currency' => 'PHP',
                'payment_method' => 'pixel_coin', 'status' => 'approved', 'reviewed_at' => now(),
            ]);
            $ledger->debit($buyer, $coinAmount, 'purchase', 'NFT purchase: ' . $artwork->title, $artwork->id, $item->item_id, $orderId);
            $ledger->credit($seller, $coinAmount, 'sale', 'Pixel Coin from sale: ' . $artwork->title, $artwork->id, $item->item_id, $orderId);
            $this->acquireItem($order, $item);
            User::query()->findOrFail($buyer->id)->notify(new MarketplaceNotification('Pixel Coin purchase complete', 'You purchased '.$artwork->title.'. Receipt '.$orderId.'.', route('user.purchases')));
            User::query()->findOrFail($seller->id)->notify(new MarketplaceNotification('Artwork sold', 'Your artwork '.$artwork->title.' was purchased for '.PixelCoinLedger::format($coinAmount).' Pixel Coins.', route('user.earnings')));
        });
        $request->session()->forget('payment_checkout.' . $artwork->id);

        return redirect()->route('user.purchases')->with('status', 'NFT purchased with Pixel Coin. Its unique edition item is now in your collection.');
    }

    private function acquireItem(PaymentOrder $order, object $item): void
    {
        DB::table('artwork_items')->where('item_id', $item->item_id)->update([
            'owner_id' => $order->buyer_id, 'status' => 'owned',
            'acquired_purchase_id' => $order->purchase_id, 'reserved_purchase_id' => null, 'updated_at' => now(),
        ]);
        DB::table('ownership_transfers')->insert([
            'product_id' => Artwork::query()->whereKey($order->artwork_id)->value('product_id'),
            'item_id' => $item->item_id, 'from_user_id' => $order->seller_id, 'to_user_id' => $order->buyer_id,
            'purchase_id' => $order->purchase_id, 'amount_minor' => $order->amount_minor,
            'currency' => $order->currency, 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('resale_listings')->where('artwork_id', $order->artwork_id)->where('seller_id', $order->seller_id)->where('status', 'listed')
            ->update(['status' => 'sold', 'updated_at' => now()]);
        $artwork = Artwork::query()->whereKey($order->artwork_id)->firstOrFail();
        if ((int) $artwork->item_count === 1) {
            DB::table('artwork_ownerships')->updateOrInsert(
                ['artwork_id' => $order->artwork_id],
                ['owner_id' => $order->buyer_id, 'acquired_purchase_id' => $order->purchase_id, 'created_at' => now(), 'updated_at' => now()],
            );
        }
    }

    private function ensurePaymentsEnabled(): void
    {
        abort_unless(config('payments.enabled') && config('payments.simulation'), 503, 'Simulated purchases are currently disabled.');
    }

    private function validCheckoutSession(mixed $checkout): bool
    {
        return is_array($checkout) && is_int($checkout['started_at'] ?? null)
            && $checkout['started_at'] >= now()->subMinutes(30)->timestamp
            && is_int($checkout['seller_id'] ?? null) && is_int($checkout['amount_minor'] ?? null);
    }

    private function sellerIdFor(Artwork $artwork): int
    {
        $resaleSeller = DB::table('resale_listings')->where('artwork_id', $artwork->id)->where('status', 'listed')->value('seller_id');
        if ($resaleSeller !== null) return (int) $resaleSeller;
        return (int) $artwork->user_id;
    }

    private function artworkPriceMinor(Artwork $artwork): int
    {
        $resalePrice = DB::table('resale_listings')->where('artwork_id', $artwork->id)->where('status', 'listed')->value('price_minor');
        return $resalePrice !== null ? (int) $resalePrice : (int) round((float) $artwork->price * (float) config('payments.php_per_eth', 200000) * 100);
    }

}

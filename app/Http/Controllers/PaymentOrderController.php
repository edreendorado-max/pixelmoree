<?php

namespace App\Http\Controllers;

use App\Models\Artwork;
use App\Models\PaymentOrder;
use App\Models\User;
use App\Support\PixelCoinConversion;
use App\Support\PixelCoinLedger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PaymentOrderController extends Controller
{
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
        $adminPayment = DB::table('admin_payment_settings')->where('id', 1)->first();
        $hasAdminQr = is_string($adminPayment?->gcash_qr_path) && Storage::disk('local')->exists($adminPayment->gcash_qr_path);
        $request->session()->put('payment_checkout.' . $artwork->id, [
            'started_at' => now()->timestamp, 'seller_id' => (int) $sellerId, 'amount_minor' => $amountMinor,
        ]);

        return view('payment-checkout', compact('artwork', 'seller', 'amountMinor', 'items', 'coinAmount', 'hasAdminQr', 'adminPayment'));
    }

    public function qr(Request $request, string $artworkId, string $method)
    {
        $this->ensurePaymentsEnabled();
        abort_unless($method === 'gcash', 404);
        $checkout = $request->session()->get('payment_checkout.' . $artworkId);
        abort_unless($this->validCheckoutSession($checkout), 403);
        $path = DB::table('admin_payment_settings')->where('id', 1)->value('gcash_qr_path');
        abort_unless(is_string($path) && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path);
    }

    public function store(Request $request, PixelCoinLedger $ledger, PixelCoinConversion $conversion): RedirectResponse
    {
        $this->ensurePaymentsEnabled();
        $validated = $request->validate([
            'artwork_id' => ['required', 'string', 'exists:artworks,id'],
            'item_id' => ['required', 'uuid', 'exists:artwork_items,item_id'],
            'payment_method' => ['required', 'in:gcash,pixel_coin'],
            'transaction_reference' => ['required_if:payment_method,gcash', 'nullable', 'string', 'max:120'],
            'proof' => ['required_if:payment_method,gcash', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);
        $artwork = Artwork::query()->whereKey($validated['artwork_id'])->where('status', 'Listed')->firstOrFail();
        $sellerId = $this->sellerIdFor($artwork);
        abort_if((int) $sellerId === (int) $request->user()->id, 422, 'You cannot buy your own artwork.');
        $checkout = $request->session()->get('payment_checkout.' . $artwork->id);
        abort_unless($this->validCheckoutSession($checkout) && $checkout['seller_id'] === (int) $sellerId, 403, 'Start checkout again before buying.');
        $amountMinor = $this->artworkPriceMinor($artwork);
        abort_unless($checkout['amount_minor'] === $amountMinor, 409, 'The price changed. Review checkout again.');
        $coinAmount = $conversion->coinsForCashMinor($amountMinor);

        if ($validated['payment_method'] === 'gcash') {
            $qrPath = DB::table('admin_payment_settings')->where('id', 1)->value('gcash_qr_path');
            abort_unless(is_string($qrPath) && Storage::disk('local')->exists($qrPath), 503, 'Admin GCash payment instructions are not configured yet.');
            $this->assertReferenceAvailable($validated['transaction_reference']);
            $path = $validated['proof']->store('payment-proofs', 'local');
            if (! is_string($path)) throw new \RuntimeException('Payment proof could not be stored securely.');
            try {
                DB::transaction(function () use ($request, $validated, $sellerId, $artwork, $amountMinor, $coinAmount, $path): void {
                    $item = DB::table('artwork_items')->where('item_id', $validated['item_id'])->where('artwork_id', $artwork->id)->lockForUpdate()->first();
                    $isResale = DB::table('resale_listings')->where('artwork_id', $artwork->id)->where('seller_id', $sellerId)->where('status', 'listed')->exists();
                    abort_unless($item && ($item->status === 'available' || ($isResale && $item->status === 'owned' && (int) $item->owner_id === (int) $sellerId)), 409, 'That edition item is no longer available.');
                    $orderId = (string) Str::uuid();
                    PaymentOrder::create([
                        'purchase_id' => $orderId, 'buyer_id' => $request->user()->id,
                        'seller_id' => $sellerId, 'artwork_id' => $artwork->id, 'item_id' => $item->item_id,
                        'type' => 'nft_purchase', 'amount_minor' => $amountMinor, 'coin_amount' => $coinAmount,
                        'currency' => 'PHP', 'payment_method' => 'gcash',
                        'transaction_reference' => $validated['transaction_reference'], 'proof_path' => $path,
                    ]);
                    DB::table('artwork_items')->where('item_id', $item->item_id)->update([
                        'status' => 'reserved', 'reserved_purchase_id' => $orderId, 'updated_at' => now(),
                    ]);
                });
            } catch (\Throwable $exception) {
                Storage::disk('local')->delete($path);
                throw $exception;
            }
            $request->session()->forget('payment_checkout.' . $artwork->id);

            return redirect()->route('user.purchases')->with('status', 'GCash receipt sent for admin review. The selected item is reserved until the admin approves or rejects the payment.');
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
        });
        $request->session()->forget('payment_checkout.' . $artwork->id);

        return redirect()->route('user.purchases')->with('status', 'NFT purchased with Pixel Coin. Its unique edition item is now in your collection.');
    }

    public function review(Request $request, PaymentOrder $order, PixelCoinLedger $ledger): RedirectResponse
    {
        $this->ensurePaymentsEnabled();
        $validated = $request->validate(['decision' => ['required', 'in:approve,reject']]);
        DB::transaction(function () use ($request, $order, $validated, $ledger): void {
            $order = PaymentOrder::query()->lockForUpdate()->findOrFail($order->purchase_id);
            abort_unless($order->status === 'pending_review', 409, 'This transaction has already been reviewed.');
            if ($validated['decision'] === 'reject') {
                $order->update(['status' => 'rejected', 'reviewed_by' => $request->user()->id, 'reviewed_at' => now()]);
                if ($order->type === 'nft_purchase' && $order->item_id) {
                    $item = DB::table('artwork_items')->where('item_id', $order->item_id)->where('reserved_purchase_id', $order->purchase_id)->first();
                    if ($item !== null) DB::table('artwork_items')->where('item_id', $order->item_id)->update([
                        'status' => $item->owner_id === null ? 'available' : 'owned', 'reserved_purchase_id' => null, 'updated_at' => now(),
                    ]);
                }
                return;
            }
            if ($order->type === 'nft_purchase') {
                abort_unless($order->item_id && $order->seller_id && $order->proof_path, 422, 'The NFT purchase record is incomplete.');
                $item = DB::table('artwork_items')->where('item_id', $order->item_id)->lockForUpdate()->first();
                abort_unless($item && $item->status === 'reserved' && $item->reserved_purchase_id === $order->purchase_id, 409, 'This edition item reservation is missing.');
                $this->acquireItem($order, $item);
                $ledger->credit(User::query()->findOrFail($order->seller_id), (int) $order->coin_amount, 'sale', 'Pixel Coin from approved NFT sale', $order->artwork_id, $item->item_id, $order->purchase_id);
            } elseif ($order->type === 'pixel_coin_topup') {
                abort_unless($order->buyer_id && $order->coin_amount > 0 && $order->proof_path, 422, 'The top-up record is incomplete.');
                $ledger->credit(User::query()->findOrFail($order->buyer_id), (int) $order->coin_amount, 'topup', 'Approved Pixel Coin top-up', null, null, $order->purchase_id);
            } else {
                abort(422, 'This transaction type cannot be approved here.');
            }
            $order->update(['status' => 'approved', 'reviewed_by' => $request->user()->id, 'reviewed_at' => now()]);
        });

        return back()->with('status', $validated['decision'] === 'approve'
            ? 'Payment verified. Pixel Coin was credited and the NFT item was transferred when applicable.'
            : 'Payment rejected. No coins or ownership were transferred.');
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

    private function assertReferenceAvailable(string $reference): void
    {
        if (DB::table('payment_orders')->where('payment_method', 'gcash')->where('transaction_reference', $reference)->exists()) {
            throw ValidationException::withMessages(['transaction_reference' => 'This GCash transaction reference has already been submitted.']);
        }
    }

    private function ensurePaymentsEnabled(): void
    {
        abort_unless(config('payments.enabled'), 503, 'Payments are currently disabled.');
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

<?php

namespace App\Http\Controllers;

use App\Models\Artwork;
use App\Models\PaymentOrder;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PaymentOrderController extends Controller
{
    private const METHODS = ['gcash', 'maya'];

    public function checkout(Request $request, string $artworkId): View
    {
        $this->ensurePaymentsEnabled();

        $artwork = Artwork::query()->whereKey($artworkId)->where('status', 'Listed')->firstOrFail();
        $sellerId = $this->sellerIdFor($artwork);
        abort_if((int) $sellerId === (int) $request->user()->id, 422, 'You cannot buy your own artwork or resale listing.');
        $seller = User::query()->findOrFail($sellerId);
        $paymentMethods = array_values(array_filter(self::METHODS, static function (string $method) use ($seller): bool {
            $path = $seller->getAttribute($method . '_qr_path');

            return is_string($path) && Storage::disk('local')->exists($path);
        }));
        $amountMinor = $this->artworkPriceMinor($artwork);
        if ($paymentMethods !== []) {
            $request->session()->put('payment_checkout.' . $artwork->id, [
                'started_at' => now()->timestamp,
                'seller_id' => (int) $sellerId,
                'amount_minor' => $amountMinor,
            ]);
        }

        return view('payment-checkout', [
            'artwork' => $artwork,
            'seller' => $seller,
            'amountMinor' => $amountMinor,
            'paymentMethods' => $paymentMethods,
        ]);
    }

    public function qr(Request $request, string $artworkId, string $method)
    {
        $this->ensurePaymentsEnabled();
        abort_unless(in_array($method, self::METHODS, true), 404);
        $checkout = $request->session()->get('payment_checkout.' . $artworkId);
        abort_unless($this->validCheckoutSession($checkout), 403);

        $artwork = Artwork::query()->whereKey($artworkId)->where('status', 'Listed')->firstOrFail();
        $sellerId = $this->sellerIdFor($artwork);
        abort_unless($checkout['seller_id'] === $sellerId, 403);
        $seller = User::query()->findOrFail($sellerId);
        abort_if((int) $seller->id === (int) $request->user()->id, 403);
        $path = $seller->getAttribute($method . '_qr_path');
        abort_unless(is_string($path) && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensurePaymentsEnabled();

        $validated = $request->validate([
            'artwork_id' => ['required', 'string', 'exists:artworks,id'],
            'payment_method' => ['required', 'in:gcash,maya'],
            'transaction_reference' => ['required', 'string', 'max:120'],
            'proof' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);
        $artwork = Artwork::query()->whereKey($validated['artwork_id'])->where('status', 'Listed')->firstOrFail();
        $sellerId = $this->sellerIdFor($artwork);
        abort_if((int) $sellerId === (int) $request->user()->id, 422, 'You cannot buy your own artwork or resale listing.');
        $checkout = $request->session()->get('payment_checkout.' . $artwork->id);
        abort_unless($this->validCheckoutSession($checkout) && $checkout['seller_id'] === $sellerId, 403, 'Start checkout again before submitting a receipt.');
        $amountMinor = $this->artworkPriceMinor($artwork);
        if ($checkout['amount_minor'] !== $amountMinor) {
            throw ValidationException::withMessages(['payment_method' => 'The listing price changed. Review the current checkout amount and submit a new payment if needed.']);
        }
        $seller = User::query()->findOrFail($sellerId);
        $qrPath = $seller->getAttribute($validated['payment_method'] . '_qr_path');
        abort_unless(is_string($qrPath) && Storage::disk('local')->exists($qrPath), 422, 'The selected payment method is not configured by this creator.');
        $this->assertReferenceAvailable($validated['payment_method'], $validated['transaction_reference']);

        $path = $validated['proof']->store('payment-proofs', 'local');
        if (! is_string($path)) {
            throw new \RuntimeException('Payment proof could not be stored securely.');
        }

        try {
            PaymentOrder::create([
                'purchase_id' => (string) Str::uuid(),
                'buyer_id' => $request->user()->id,
                'seller_id' => $sellerId,
                'artwork_id' => $artwork->id,
                'type' => 'nft_purchase',
                'amount_minor' => $amountMinor,
                'currency' => 'PHP',
                'payment_method' => $validated['payment_method'],
                'transaction_reference' => $validated['transaction_reference'],
                'proof_path' => $path,
            ]);
        } catch (\Throwable $exception) {
            $disk = Storage::disk('local');
            if ($disk->exists($path) && ! $disk->delete($path)) {
                throw new \RuntimeException('The failed payment receipt could not be removed from private storage.', 0, $exception);
            }
            throw $exception;
        }
        $request->session()->forget('payment_checkout.' . $artwork->id);

        return redirect('/user/purchases')->with('status', 'Receipt submitted for manual admin verification. NFT ownership changes only after approval.');
    }

    public function review(Request $request, PaymentOrder $order): RedirectResponse
    {
        $this->ensurePaymentsEnabled();

        $validated = $request->validate(['decision' => ['required', 'in:approve,reject']]);
        DB::transaction(function () use ($request, $order, $validated): void {
            $order = PaymentOrder::query()->lockForUpdate()->findOrFail($order->purchase_id);
            abort_unless($order->type === 'nft_purchase', 422, 'Only NFT purchase receipts can be reviewed.');
            abort_unless($order->status === 'pending_review', 409, 'This payment request has already been reviewed.');
            abort_unless(in_array($order->payment_method, self::METHODS, true), 422, 'This payment method is not supported.');
            abort_unless($order->transaction_reference && $order->proof_path, 422, 'The payment reference and proof are required before review.');
            if ($validated['decision'] === 'reject') {
                $order->update(['status' => 'rejected', 'reviewed_by' => $request->user()->id, 'reviewed_at' => now()]);
                return;
            }
            $this->acquire($order);
            $order->update(['status' => 'approved', 'reviewed_by' => $request->user()->id, 'reviewed_at' => now()]);
        });

        return back()->with('status', $validated['decision'] === 'approve'
            ? 'Receipt approved. Ownership was transferred after manual review.'
            : 'Receipt rejected. NFT ownership was not transferred.');
    }

    private function assertReferenceAvailable(string $method, string $reference): void
    {
        if (DB::table('payment_orders')->where('payment_method', $method)
            ->where('transaction_reference', $reference)->exists()) {
            throw ValidationException::withMessages([
                'transaction_reference' => 'This transaction reference has already been submitted.',
            ]);
        }
    }

    private function ensurePaymentsEnabled(): void
    {
        abort_unless(config('payments.enabled'), 503, 'NFT checkout is currently unavailable.');
    }

    private function validCheckoutSession(mixed $checkout): bool
    {
        return is_array($checkout)
            && is_int($checkout['started_at'] ?? null)
            && $checkout['started_at'] >= now()->subMinutes(30)->timestamp
            && is_int($checkout['seller_id'] ?? null)
            && is_int($checkout['amount_minor'] ?? null);
    }

    private function acquire(PaymentOrder $order): void
    {
        $artwork = Artwork::query()->whereKey($order->artwork_id)->firstOrFail();
        $ownership = DB::table('artwork_ownerships')->where('artwork_id', $order->artwork_id)->lockForUpdate()->first();
        $previousOwnerId = $ownership?->owner_id ?? $artwork->user_id;
        if ($ownership !== null) {
            $resale = DB::table('resale_listings')->where('artwork_id', $order->artwork_id)
                ->where('seller_id', $ownership->owner_id)->where('status', 'listed')->lockForUpdate()->first();
            abort_if($resale === null, 409, 'This single-edition NFT is already owned.');
            DB::table('artwork_ownerships')->where('id', $ownership->id)->update([
                'owner_id' => $order->buyer_id, 'acquired_purchase_id' => $order->purchase_id, 'updated_at' => now(),
            ]);
            DB::table('resale_listings')->where('id', $resale->id)->update(['status' => 'sold', 'updated_at' => now()]);
        } else {
            DB::table('artwork_ownerships')->insert([
                'artwork_id' => $order->artwork_id, 'owner_id' => $order->buyer_id,
                'acquired_purchase_id' => $order->purchase_id, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        DB::table('ownership_transfers')->insert([
            'product_id' => $artwork->product_id,
            'from_user_id' => $previousOwnerId,
            'to_user_id' => $order->buyer_id,
            'purchase_id' => $order->purchase_id,
            'amount_minor' => $order->amount_minor,
            'currency' => $order->currency,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function sellerIdFor(Artwork $artwork): int
    {
        $listedSellerId = DB::table('resale_listings')->where('artwork_id', $artwork->id)
            ->where('status', 'listed')->value('seller_id');
        if ($listedSellerId !== null) {
            return (int) $listedSellerId;
        }

        $ownerId = DB::table('artwork_ownerships')->where('artwork_id', $artwork->id)->value('owner_id');
        abort_if($ownerId !== null, 409, 'This artwork is not currently listed for resale.');

        return (int) $artwork->user_id;
    }

    private function artworkPriceMinor(Artwork $artwork): int
    {
        $resalePrice = DB::table('resale_listings')->where('artwork_id', $artwork->id)
            ->where('status', 'listed')->value('price_minor');

        if ($resalePrice !== null) return (int) $resalePrice;

        return (int) round((float) $artwork->price * (float) config('payments.php_per_eth', 200000) * 100);
    }
}

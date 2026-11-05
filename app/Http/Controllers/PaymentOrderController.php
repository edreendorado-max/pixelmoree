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

class PaymentOrderController extends Controller
{
    private const METHODS = ['gcash', 'maya', 'paypal'];

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'artwork_id' => ['required', 'string', 'exists:artworks,id'],
            'payment_method' => ['required', 'in:gcash,maya,paypal,cash_wallet'],
            'transaction_reference' => ['required_unless:payment_method,cash_wallet', 'nullable', 'string', 'max:120'],
            'proof' => ['required_unless:payment_method,cash_wallet', 'nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ]);
        $artwork = Artwork::query()->whereKey($validated['artwork_id'])->where('status', 'Listed')->firstOrFail();
        $sellerId = DB::table('resale_listings')->where('artwork_id', $artwork->id)
            ->where('status', 'listed')->value('seller_id') ?? $artwork->user_id;
        abort_if((int) $sellerId === (int) $request->user()->id, 422, 'You cannot buy your own artwork or resale listing.');
        if ($validated['payment_method'] !== 'cash_wallet') {
            $this->assertReferenceAvailable($validated['payment_method'], $validated['transaction_reference']);
        }
        $path = isset($validated['proof']) ? $validated['proof']->store('payment-proofs', 'local') : null;
        if (isset($validated['proof']) && ! is_string($path)) {
            throw new \RuntimeException('Payment proof could not be stored securely.');
        }

        try {
            DB::transaction(function () use ($request, $validated, $artwork, $path, $sellerId): void {
                if ($validated['payment_method'] === 'cash_wallet') {
                    $buyer = User::query()->lockForUpdate()->findOrFail($request->user()->id);
                    $amount = $this->artworkPriceMinor($artwork);
                    if ($buyer->cash_balance_minor - $buyer->cash_reserved_minor < $amount) {
                        throw ValidationException::withMessages(['payment_method' => 'Your cleared PHP wallet balance is not enough.']);
                    }
                    $buyer->cash_balance_minor -= $amount;
                    $buyer->save();
                    $requestId = (string) Str::uuid();
                    $order = PaymentOrder::create([
                        'purchase_id' => $requestId, 'buyer_id' => $buyer->id, 'seller_id' => $sellerId,
                        'artwork_id' => $artwork->id, 'type' => 'nft_purchase', 'amount_minor' => $amount,
                        'payment_method' => 'cash_wallet', 'status' => 'approved',
                    ]);
                    $this->ledger($buyer, $order, 'nft_purchase', 'debit', $amount);
                    $this->acquire($order);
                    if ($order->seller_id !== null) {
                        $seller = User::query()->lockForUpdate()->findOrFail($order->seller_id);
                        $seller->cash_balance_minor += $amount;
                        $seller->save();
                        $this->ledger($seller, $order, 'nft_sale', 'credit', $amount);
                    }
                    return;
                }

                $this->createOrder($request, $artwork, 'nft_purchase', $validated['payment_method'], $this->artworkPriceMinor($artwork), $validated['transaction_reference'], $path, $sellerId);
            });
        } catch (\Throwable $exception) {
            if ($path !== null) Storage::disk('local')->delete($path);
            throw $exception;
        }

        return redirect('/user/purchases')->with('status', 'Payment submitted for admin verification. NFT ownership changes only after approval.');
    }

    public function topUp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'amount_php' => ['required', 'integer', 'in:100,500,1000'],
            'payment_method' => ['required', 'in:gcash,maya,paypal'],
            'transaction_reference' => ['required', 'string', 'max:120'],
            'proof' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ]);
        $this->assertReferenceAvailable($validated['payment_method'], $validated['transaction_reference']);
        $path = $validated['proof']->store('payment-proofs', 'local');
        if (! is_string($path)) throw new \RuntimeException('Payment proof could not be stored securely.');
        $this->createOrder($request, null, 'cash_topup', $validated['payment_method'], $validated['amount_php'] * 100, $validated['transaction_reference'], $path);

        return back()->with('status', 'Top-up submitted. Your cash balance will update after admin verification.');
    }

    public function prepaidTopUp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'amount_php' => ['required', 'integer', 'in:50,100,300'],
            'transaction_reference' => ['required', 'string', 'max:120'],
            'proof' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ]);
        $this->assertReferenceAvailable('prepaid_load', $validated['transaction_reference']);
        $path = $validated['proof']->store('payment-proofs', 'local');
        if (! is_string($path)) throw new \RuntimeException('Payment proof could not be stored securely.');
        $this->createOrder($request, null, 'pixel_topup', 'prepaid_load', $validated['amount_php'] * 100, $validated['transaction_reference'], $path);

        return back()->with('status', 'Prepaid-load top-up submitted for verification. Prepaid load is not an NFT checkout method.');
    }

    public function withdraw(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'amount_php' => ['required', 'integer', 'min:100', 'max:100000'],
            'paypal_email' => ['required', 'email', 'max:254'],
        ]);
        $amount = $validated['amount_php'] * 100;
        DB::transaction(function () use ($request, $validated, $amount): void {
            $user = User::query()->lockForUpdate()->findOrFail($request->user()->id);
            if ($user->cash_balance_minor - $user->cash_reserved_minor < $amount) {
                throw ValidationException::withMessages(['amount_php' => 'The requested payout exceeds your cleared, unreserved balance.']);
            }
            $user->cash_reserved_minor += $amount;
            $user->save();
            PaymentOrder::create([
                'purchase_id' => (string) Str::uuid(),
                'buyer_id' => $user->id,
                'type' => 'withdrawal',
                'amount_minor' => $amount,
                'currency' => 'PHP',
                'payment_method' => 'paypal',
                'payout_email' => $validated['paypal_email'],
            ]);
        });

        return back()->with('status', 'PayPal payout request submitted for admin review; nothing has been sent yet.');
    }

    public function review(Request $request, PaymentOrder $order): RedirectResponse
    {
        $validated = $request->validate([
            'decision' => ['required', 'in:approve,reject,mark_paid'],
            'payout_reference' => ['required_if:decision,mark_paid', 'nullable', 'string', 'max:120'],
        ]);
        DB::transaction(function () use ($request, $order, $validated): void {
            $order = PaymentOrder::query()->lockForUpdate()->findOrFail($order->purchase_id);
            $user = User::query()->lockForUpdate()->findOrFail($order->buyer_id);
            if ($order->type === 'withdrawal') {
                if ($validated['decision'] === 'reject') {
                    abort_unless(in_array($order->status, ['pending_review', 'payout_approved'], true), 409, 'This withdrawal can no longer be rejected.');
                    abort_if($user->cash_reserved_minor < $order->amount_minor, 409, 'The reserved withdrawal amount is inconsistent.');
                    $user->cash_reserved_minor -= $order->amount_minor;
                    $user->save();
                    $order->update(['status' => 'rejected', 'reviewed_by' => $request->user()->id, 'reviewed_at' => now()]);
                    return;
                }
                if ($validated['decision'] === 'approve') {
                    abort_unless($order->status === 'pending_review', 409, 'This withdrawal has already been reviewed.');
                    $order->update(['status' => 'payout_approved', 'reviewed_by' => $request->user()->id, 'reviewed_at' => now()]);
                    return;
                }
                abort_unless($order->status === 'payout_approved', 409, 'Approve the withdrawal before recording its payout.');
                abort_if($user->cash_reserved_minor < $order->amount_minor || $user->cash_balance_minor < $order->amount_minor, 409, 'The reserved withdrawal amount is inconsistent.');
                if (DB::table('payment_orders')->where('payout_reference', $validated['payout_reference'])
                    ->where('purchase_id', '<>', $order->purchase_id)->exists()) {
                    throw ValidationException::withMessages([
                        'payout_reference' => 'This payout reference has already been recorded.',
                    ]);
                }
                $user->cash_reserved_minor -= $order->amount_minor;
                $user->cash_balance_minor -= $order->amount_minor;
                $user->save();
                $order->update([
                    'status' => 'paid',
                    'payout_reference' => $validated['payout_reference'],
                    'reviewed_by' => $request->user()->id,
                    'reviewed_at' => now(),
                ]);
                $this->ledger($user, $order, 'paypal_payout', 'debit', $order->amount_minor);
                return;
            }
            abort_unless($order->status === 'pending_review', 409, 'This payment request has already been reviewed.');
            abort_unless(in_array($validated['decision'], ['approve', 'reject'], true), 422, 'This action is only valid for a withdrawal.');
            if ($validated['decision'] === 'reject') {
                $order->update(['status' => 'rejected', 'reviewed_by' => $request->user()->id, 'reviewed_at' => now()]);
                return;
            }

            if ($order->type === 'cash_topup') {
                $user->cash_balance_minor += $order->amount_minor;
                $user->save();
                $this->ledger($user, $order, 'cash_topup', 'credit', $order->amount_minor);
            } elseif ($order->type === 'pixel_topup') {
                $coinAmount = (int) ($order->amount_minor / 100) * (int) config('payments.pixel_coin_per_php', 100);
                abort_if($user->pixel_coin_balance + $coinAmount > 1_000_000_000, 409, 'This Pixel Coin account balance would exceed its limit.');
                $user->pixel_coin_balance += $coinAmount;
                $user->save();
                $user->pixelCoinTransactions()->create([
                    'type' => 'prepaid_load_top_up', 'amount' => $coinAmount,
                    'balance_after' => $user->pixel_coin_balance, 'description' => 'Admin-verified prepaid-load top-up',
                ]);
            } elseif ($order->type === 'nft_purchase') {
                $this->acquire($order);
                if ($order->seller_id !== null) {
                    $seller = User::query()->lockForUpdate()->findOrFail($order->seller_id);
                    $seller->cash_balance_minor += $order->amount_minor;
                    $seller->save();
                    $this->ledger($seller, $order, 'nft_sale', 'credit', $order->amount_minor);
                }
            }

            $order->update(['status' => 'approved', 'reviewed_by' => $request->user()->id, 'reviewed_at' => now()]);
        });

        return back()->with('status', 'Payment request reviewed. Verify incoming funds before approval; PayPal payouts are only recorded as paid after the transfer is sent.');
    }

    private function createOrder(Request $request, ?Artwork $artwork, string $type, string $method, int $amount, ?string $reference, ?string $path, ?int $sellerId = null): PaymentOrder
    {
        if ($reference !== null) {
            $this->assertReferenceAvailable($method, $reference);
        }
        return PaymentOrder::create([
            'purchase_id' => (string) Str::uuid(), 'buyer_id' => $request->user()->id,
            'seller_id' => $artwork === null ? null : ($sellerId ?? $artwork->user_id), 'artwork_id' => $artwork?->id, 'type' => $type,
            'amount_minor' => $amount, 'currency' => 'PHP', 'payment_method' => $method,
            'transaction_reference' => $reference, 'proof_path' => $path,
        ]);
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

    private function ledger(User $user, PaymentOrder $order, string $type, string $direction, int $amount): void
    {
        DB::table('cash_ledger_entries')->insert([
            'user_id' => $user->id, 'purchase_id' => $order->purchase_id, 'type' => $type,
            'direction' => $direction, 'amount_minor' => $amount, 'balance_after_minor' => $user->cash_balance_minor,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function artworkPriceMinor(Artwork $artwork): int
    {
        $resalePrice = DB::table('resale_listings')->where('artwork_id', $artwork->id)
            ->where('status', 'listed')->value('price_minor');

        if ($resalePrice !== null) return (int) $resalePrice;

        return (int) round((float) $artwork->price * (float) config('payments.php_per_eth', 200000) * 100);
    }
}

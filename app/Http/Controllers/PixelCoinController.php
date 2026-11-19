<?php

namespace App\Http\Controllers;

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

class PixelCoinController extends Controller
{
    public function index(Request $request, PixelCoinConversion $conversion): View
    {
        return view('user.pixel-coins', [
            'user' => $request->user(),
            'transactions' => $request->user()->pixelCoinTransactions()->latest()->limit(50)->get(),
            'orders' => PaymentOrder::query()->where('buyer_id', $request->user()->id)->whereIn('type', ['pixel_coin_topup', 'creator_payout'])->latest()->limit(30)->get(),
            'hasAdminQr' => $this->hasAdminQr(),
            'adminPayment' => DB::table('admin_payment_settings')->where('id', 1)->first(),
            'minimumTopupPhp' => config('payments.pixel_coin_min_topup_php', 20),
            'coinsForPhp200' => $conversion->coinsForCashMinor(20000),
            'paymentsEnabled' => (bool) config('payments.enabled'),
            'topupFeePercent' => (float) config('payments.pixel_coin_topup_fee_percent', 1.5),
        ]);
    }

    public function qr()
    {
        abort_unless(config('payments.enabled'), 503);
        $path = DB::table('admin_payment_settings')->where('id', 1)->value('gcash_qr_path');
        abort_unless(is_string($path) && Storage::disk('local')->exists($path), 404);
        return Storage::disk('local')->response($path);
    }

    public function topUp(Request $request, PixelCoinConversion $conversion): RedirectResponse
    {
        abort_unless(config('payments.enabled'), 503, 'Pixel Coin top-ups are currently disabled.');
        $validated = $request->validate([
            'amount_php' => ['required', 'numeric', 'min:' . config('payments.pixel_coin_min_topup_php', 20), 'max:100000', 'decimal:0,2'],
            'transaction_reference' => ['required', 'string', 'max:120'],
            'proof' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);
        abort_unless($this->hasAdminQr(), 503, 'Admin GCash instructions are not available yet.');
        if (DB::table('payment_orders')->where('payment_method', 'gcash')->where('transaction_reference', $validated['transaction_reference'])->exists()) {
            throw ValidationException::withMessages(['transaction_reference' => 'This GCash transaction reference has already been submitted.']);
        }
        $baseMinor = (int) round((float) $validated['amount_php'] * 100);
        $feeMinor = (int) round($baseMinor * (float) config('payments.pixel_coin_topup_fee_percent', 1.5) / 100);
        $coinMinor = $conversion->coinsForCashMinor($baseMinor);
        $proof = $validated['proof']->store('payment-proofs', 'local');
        if (! is_string($proof)) throw new \RuntimeException('Payment proof could not be stored securely.');
        try {
            PaymentOrder::create([
                'purchase_id' => (string) Str::uuid(), 'buyer_id' => $request->user()->id,
                'type' => 'pixel_coin_topup', 'amount_minor' => $baseMinor + $feeMinor,
                'fee_amount_minor' => $feeMinor, 'coin_amount' => $coinMinor,
                'currency' => 'PHP', 'payment_method' => 'gcash',
                'transaction_reference' => $validated['transaction_reference'], 'proof_path' => $proof,
            ]);
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($proof);
            throw $exception;
        }
        return back()->with('status', 'Top-up receipt submitted. The requested Pixel Coins will be credited after manual admin review.');
    }

    public function withdraw(Request $request, PixelCoinLedger $ledger): RedirectResponse
    {
        abort_unless(config('payments.enabled'), 503, 'Withdrawals are currently disabled.');
        $validated = $request->validate([
            'coin_amount' => ['required', 'numeric', 'min:' . config('payments.pixel_coin_min_withdrawal', 200), 'max:1000000', 'decimal:0,2'],
        ]);
        $user = $request->user();
        if (! $user->gcash_number || ! $user->gcash_qr_path || ! Storage::disk('local')->exists($user->gcash_qr_path)) {
            throw ValidationException::withMessages(['gcash' => 'Save your GCash number and payout QR code in Profile settings first.']);
        }
        $coinMinor = (int) round((float) $validated['coin_amount'] * 100);
        $payoutMinor = (int) round($coinMinor * (float) config('payments.pixel_coin_payout_php_per_coin', 1.2)); // coinMinor is hundredths; output is centavos.
        DB::transaction(function () use ($user, $coinMinor, $payoutMinor, $ledger): void {
            $orderId = (string) Str::uuid();
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);
            $order = PaymentOrder::create([
                'purchase_id' => $orderId, 'buyer_id' => $lockedUser->id,
                'type' => 'creator_payout', 'amount_minor' => $payoutMinor, 'coin_amount' => $coinMinor,
                'currency' => 'PHP', 'payment_method' => 'gcash', 'status' => 'pending_review',
            ]);
            $ledger->debit($lockedUser, $coinMinor, 'withdrawal', 'Pixel Coin withdrawal request ' . $orderId, null, null, $order->purchase_id);
            DB::table('users')->where('id', $lockedUser->id)->increment('pixel_coin_reserved', $coinMinor);
        });
        return back()->with('status', 'Withdrawal request sent to the admin. Coins are reserved until it is paid or rejected.');
    }

    public function reviewWithdrawal(Request $request, PaymentOrder $order, PixelCoinLedger $ledger): RedirectResponse
    {
        abort_unless(config('payments.enabled'), 503);
        $validated = $request->validate([
            'decision' => ['required', 'in:paid,reject'],
            'payout_reference' => ['required_if:decision,paid', 'nullable', 'string', 'max:120'],
        ]);
        DB::transaction(function () use ($request, $order, $validated, $ledger): void {
            $order = PaymentOrder::query()->lockForUpdate()->findOrFail($order->purchase_id);
            abort_unless($order->type === 'creator_payout' && $order->status === 'pending_review', 409, 'This withdrawal is not pending.');
            if ($validated['decision'] === 'reject') {
                $order->update(['status' => 'rejected', 'reviewed_by' => $request->user()->id, 'reviewed_at' => now()]);
                $creator = User::query()->lockForUpdate()->findOrFail($order->buyer_id);
                $creator->decrement('pixel_coin_reserved', (int) $order->coin_amount);
                $ledger->credit($creator, (int) $order->coin_amount, 'withdrawal_refund', 'Refund for rejected withdrawal ' . $order->purchase_id, null, null, $order->purchase_id);
                return;
            }
            $reference = trim($validated['payout_reference']);
            if (DB::table('payment_orders')->where('payout_reference', $reference)->exists()) {
                throw ValidationException::withMessages(['payout_reference' => 'This GCash payout reference is already recorded.']);
            }
            $order->update([
                'status' => 'paid', 'payout_reference' => $reference,
                'reviewed_by' => $request->user()->id, 'reviewed_at' => now(),
            ]);
            User::query()->whereKey($order->buyer_id)->decrement('pixel_coin_reserved', (int) $order->coin_amount);
        });
        return back()->with('status', $validated['decision'] === 'paid' ? 'Payout marked paid and recorded.' : 'Withdrawal rejected and Pixel Coins refunded.');
    }

    private function hasAdminQr(): bool
    {
        $path = DB::table('admin_payment_settings')->where('id', 1)->value('gcash_qr_path');
        return config('payments.enabled') && is_string($path) && Storage::disk('local')->exists($path);
    }
}

<?php

namespace App\Http\Controllers;

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

class PixelCoinController extends Controller
{
    public function index(Request $request, PixelCoinConversion $conversion): View
    {
        return view('user.pixel-coins', [
            'user' => $request->user(),
            'transactions' => $request->user()->pixelCoinTransactions()->latest()->limit(50)->get(),
            'orders' => PaymentOrder::query()->where('buyer_id', $request->user()->id)->whereIn('type', ['pixel_coin_topup', 'creator_payout'])->latest()->limit(30)->get(),
            'minimumTopupPhp' => config('payments.pixel_coin_min_topup_php', 20),
            'coinsForPhp200' => $conversion->coinsForCashMinor(20000),
            'paymentsEnabled' => (bool) config('payments.enabled'),
            'topupFeePercent' => (float) config('payments.pixel_coin_topup_fee_percent', 1.5),
        ]);
    }

    public function topUp(Request $request, PixelCoinConversion $conversion, PixelCoinLedger $ledger): RedirectResponse
    {
        abort_unless(config('payments.enabled') && config('payments.simulation'), 503, 'Simulated Pixel Coin top-ups are currently disabled.');
        $validated = $request->validate(['amount_php' => ['required', 'numeric', 'min:' . config('payments.pixel_coin_min_topup_php', 20), 'max:100000', 'decimal:0,2']]);
        $baseMinor = (int) round((float) $validated['amount_php'] * 100);
        $feeMinor = (int) round($baseMinor * (float) config('payments.pixel_coin_topup_fee_percent', 1.5) / 100);
        $coinMinor = $conversion->coinsForCashMinor($baseMinor);
        $orderId = (string) Str::uuid();
        DB::transaction(function () use ($request, $baseMinor, $feeMinor, $coinMinor, $orderId, $ledger): void {
            $user = User::query()->lockForUpdate()->findOrFail($request->user()->id);
            $order = PaymentOrder::create([
                'purchase_id' => $orderId, 'buyer_id' => $user->id,
                'type' => 'pixel_coin_topup', 'amount_minor' => $baseMinor + $feeMinor,
                'fee_amount_minor' => $feeMinor, 'coin_amount' => $coinMinor,
                'currency' => 'PHP', 'payment_method' => 'simulation', 'status' => 'approved', 'reviewed_at' => now(),
            ]);
            $ledger->credit($user, $coinMinor, 'topup', 'Simulated Pixel Coin top-up', null, null, $order->purchase_id);
            $user->notify(new MarketplaceNotification('Simulated Pixel Coin top-up complete', PixelCoinLedger::format($coinMinor).' Pixel Coins were added. Receipt '.$orderId.'. No real payment was made.', route('user.pixel-coins')));
        });
        return back()->with('status', 'Simulated top-up complete. Pixel Coins and your receipt are ready; no real payment was made.');
    }

    public function withdraw(Request $request, PixelCoinLedger $ledger): RedirectResponse
    {
        abort_unless(config('payments.enabled') && config('payments.simulation'), 503, 'Simulated withdrawals are currently disabled.');
        $validated = $request->validate([
            'coin_amount' => ['required', 'numeric', 'min:' . config('payments.pixel_coin_min_withdrawal', 200), 'max:1000000', 'decimal:0,2'],
        ]);
        $user = $request->user();
        $coinMinor = (int) round((float) $validated['coin_amount'] * 100);
        $payoutMinor = (int) round($coinMinor * (float) config('payments.pixel_coin_payout_php_per_coin', 1.2)); // coinMinor is hundredths; output is centavos.
        DB::transaction(function () use ($user, $coinMinor, $payoutMinor, $ledger): void {
            $orderId = (string) Str::uuid();
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);
            $order = PaymentOrder::create([
                'purchase_id' => $orderId, 'buyer_id' => $lockedUser->id,
                'type' => 'creator_payout', 'amount_minor' => $payoutMinor, 'coin_amount' => $coinMinor,
                'currency' => 'PHP', 'payment_method' => 'simulation', 'status' => 'paid', 'reviewed_at' => now(),
            ]);
            $ledger->debit($lockedUser, $coinMinor, 'withdrawal', 'Pixel Coin withdrawal request ' . $orderId, null, null, $order->purchase_id);
            $lockedUser->notify(new MarketplaceNotification('Simulated withdrawal complete', PixelCoinLedger::format($coinMinor).' Pixel Coins were removed from your simulated wallet. Receipt '.$orderId.'. No cash was transferred.', route('user.pixel-coins')));
        });
        return back()->with('status', 'Simulated withdrawal complete. Your receipt is recorded; no cash was transferred.');
    }

}

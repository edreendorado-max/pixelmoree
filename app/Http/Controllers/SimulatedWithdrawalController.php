<?php

namespace App\Http\Controllers;

use App\Models\PaymentOrder;
use App\Models\User;
use App\Notifications\MarketplaceNotification;
use App\Support\CashLedger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SimulatedWithdrawalController extends Controller
{
    public function __invoke(Request $request, CashLedger $ledger): RedirectResponse
    {
        abort_unless(config('payments.enabled') && config('payments.simulation'), 503, 'Simulated withdrawals are currently disabled.');

        $validated = $request->validate([
            'amount_php' => ['required', 'numeric', 'min:0.01', 'max:1000000', 'decimal:0,2'],
        ]);
        $amountMinor = (int) round((float) $validated['amount_php'] * 100);
        $userId = (int) $request->user()->id;

        DB::transaction(function () use ($userId, $amountMinor, $ledger): void {
            $user = User::query()->lockForUpdate()->findOrFail($userId);
            $orderId = (string) Str::uuid();
            PaymentOrder::create([
                'purchase_id' => $orderId,
                'buyer_id' => $user->id,
                'type' => 'creator_payout',
                'amount_minor' => $amountMinor,
                'currency' => 'PHP',
                'payment_method' => 'simulation',
                'status' => 'paid',
                'reviewed_at' => now(),
            ]);

            $ledger->debit($user, $amountMinor, 'withdrawal', $orderId);
            $user->notify(new MarketplaceNotification(
                'Simulated withdrawal complete',
                'PHP '.number_format($amountMinor / 100, 2).' was deducted from your simulated earnings balance. Receipt '.$orderId.'. No cash was transferred.',
                route('user.earnings'),
            ));
        });

        return redirect()->route('user.earnings')->with('status', 'Simulated withdrawal complete. Your receipt is recorded; no cash was transferred.');
    }
}

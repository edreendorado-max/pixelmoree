<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UserEarningsController extends Controller
{
    public function __invoke(Request $request): View
    {
        $salesQuery = DB::table('payment_orders')
            ->leftJoin('artworks', 'artworks.id', '=', 'payment_orders.artwork_id')
            ->where('payment_orders.seller_id', $request->user()->id)
            ->where('payment_orders.type', 'nft_purchase')
            ->where('payment_orders.payment_method', 'simulation')
            ->where('payment_orders.status', 'approved');

        $summary = (clone $salesQuery)
            ->selectRaw(
                'COUNT(*) as sales_count, COALESCE(SUM(payment_orders.amount_minor), 0) as total_minor'
            )
            ->first();

        $recentSales = (clone $salesQuery)
            ->select([
                'artworks.title', 'payment_orders.amount_minor', 'payment_orders.purchase_id', 'payment_orders.created_at',
            ])
            ->orderByDesc('payment_orders.created_at')
            ->orderByDesc('payment_orders.purchase_id')
            ->limit(20)
            ->get();

        $last30DaysMinor = (clone $salesQuery)
            ->where('payment_orders.created_at', '>=', now()->subDays(30))
            ->sum('payment_orders.amount_minor');
        $withdrawnMinor = DB::table('cash_ledger_entries')
            ->where('user_id', $request->user()->id)
            ->where('type', 'withdrawal')
            ->where('direction', 'debit')
            ->sum('amount_minor');
        $recentWithdrawals = DB::table('payment_orders')
            ->where('buyer_id', $request->user()->id)
            ->where('type', 'creator_payout')
            ->where('payment_method', 'simulation')
            ->latest()
            ->limit(10)
            ->get(['purchase_id', 'amount_minor', 'status', 'created_at']);

        return view('home', [
            'creatorEarnings' => [
                'totalMinor' => (int) $summary->total_minor,
                'salesCount' => (int) $summary->sales_count,
                'last30DaysMinor' => (int) $last30DaysMinor,
                'availableMinor' => (int) $request->user()->fresh()->cash_balance_minor,
                'withdrawnMinor' => (int) $withdrawnMinor,
                'recentSales' => $recentSales,
                'recentWithdrawals' => $recentWithdrawals,
            ],
        ]);
    }
}

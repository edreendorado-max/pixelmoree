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
            ->join('artworks', 'artworks.id', '=', 'payment_orders.artwork_id')
            ->where('payment_orders.seller_id', $request->user()->id)
            ->where('payment_orders.type', 'nft_purchase')
            ->where('payment_orders.status', 'approved');

        $summary = (clone $salesQuery)
            ->selectRaw(
                'COUNT(*) as sales_count, COALESCE(SUM(payment_orders.amount_minor), 0) as total_cash_minor'
            )
            ->first();

        $recentSales = (clone $salesQuery)
            ->select([
                'artworks.title',
                'payment_orders.amount_minor',
                'payment_orders.created_at',
            ])
            ->orderByDesc('payment_orders.created_at')
            ->orderByDesc('payment_orders.purchase_id')
            ->limit(20)
            ->get();

        $last30DaysCashMinor = (clone $salesQuery)
            ->where('payment_orders.created_at', '>=', now()->subDays(30))
            ->sum('payment_orders.amount_minor');

        return view('home', [
            'creatorEarnings' => [
                'totalCashMinor' => (int) $summary->total_cash_minor,
                'salesCount' => (int) $summary->sales_count,
                'last30DaysCashMinor' => (int) $last30DaysCashMinor,
                'recentSales' => $recentSales,
            ],
        ]);
    }
}

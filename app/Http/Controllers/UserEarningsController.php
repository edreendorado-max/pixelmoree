<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UserEarningsController extends Controller
{
    public function __invoke(Request $request): View
    {
        $salesQuery = DB::table('pixel_coin_transactions')
            ->leftJoin('artworks', 'artworks.id', '=', 'pixel_coin_transactions.artwork_id')
            ->where('pixel_coin_transactions.user_id', $request->user()->id)
            ->where('pixel_coin_transactions.type', 'sale')
            ->where('pixel_coin_transactions.direction', 'credit');

        $summary = (clone $salesQuery)
            ->selectRaw(
                'COUNT(*) as sales_count, COALESCE(SUM(pixel_coin_transactions.amount), 0) as total_coin_minor'
            )
            ->first();

        $recentSales = (clone $salesQuery)
            ->select([
                'artworks.title', 'pixel_coin_transactions.amount as coin_minor', 'pixel_coin_transactions.created_at',
            ])
            ->orderByDesc('pixel_coin_transactions.created_at')
            ->orderByDesc('pixel_coin_transactions.id')
            ->limit(20)
            ->get();

        $last30DaysCashMinor = (clone $salesQuery)
            ->where('pixel_coin_transactions.created_at', '>=', now()->subDays(30))
            ->sum('pixel_coin_transactions.amount');

        return view('home', [
            'creatorEarnings' => [
                'totalCoinMinor' => (int) $summary->total_coin_minor,
                'salesCount' => (int) $summary->sales_count,
                'last30DaysCoinMinor' => (int) $last30DaysCashMinor,
                'recentSales' => $recentSales,
            ],
        ]);
    }
}

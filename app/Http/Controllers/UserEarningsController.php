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
            ->join('artworks', 'artworks.id', '=', 'pixel_coin_transactions.artwork_id')
            ->where('artworks.user_id', $request->user()->id)
            ->where('pixel_coin_transactions.type', 'purchase');

        $summary = (clone $salesQuery)
            ->selectRaw(
                'COUNT(*) as sales_count, COALESCE(SUM(pixel_coin_transactions.amount), 0) as total_pixel_coins'
            )
            ->first();

        $recentSales = (clone $salesQuery)
            ->select([
                'artworks.title',
                'pixel_coin_transactions.amount',
                'pixel_coin_transactions.created_at',
            ])
            ->orderByDesc('pixel_coin_transactions.created_at')
            ->orderByDesc('pixel_coin_transactions.id')
            ->limit(20)
            ->get();

        $last30DaysPixelCoins = (clone $salesQuery)
            ->where('pixel_coin_transactions.created_at', '>=', now()->subDays(30))
            ->sum('pixel_coin_transactions.amount');

        return view('home', [
            'creatorEarnings' => [
                'totalPixelCoins' => (int) $summary->total_pixel_coins,
                'salesCount' => (int) $summary->sales_count,
                'last30DaysPixelCoins' => (int) $last30DaysPixelCoins,
                'recentSales' => $recentSales,
            ],
        ]);
    }
}

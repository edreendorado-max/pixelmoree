<?php

namespace App\Support;

use App\Models\Artwork;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ArtworkPriceDashboard
{
    public function captureDailySnapshot(): int
    {
        $artworks = Artwork::query()
            ->where('status', 'Listed')
            ->with(['user' => static fn ($query) => $query->withCount(['followers', 'profileViews'])])
            ->withCount(['likes', 'views'])
            ->withCount(['paymentOrders as recent_market_sales_count' => static fn ($query) => $query
                ->whereIn('type', ['nft_purchase', 'nft_coin_purchase'])->where('status', 'approved')
                ->where('created_at', '>=', now()->subDays(30))])
            ->withMax(['paymentOrders as last_market_sale_at' => static fn ($query) => $query
                ->whereIn('type', ['nft_purchase', 'nft_coin_purchase'])->where('status', 'approved')], 'created_at')
            ->get();

        $creatorSales = DB::table('payment_orders as purchases')
            ->join('artworks as sold_artworks', 'sold_artworks.id', '=', 'purchases.artwork_id')
            ->whereIn('purchases.type', ['nft_purchase', 'nft_coin_purchase'])
            ->where('purchases.status', 'approved')
            ->whereNotNull('sold_artworks.user_id')
            ->groupBy('sold_artworks.user_id')
            ->selectRaw('sold_artworks.user_id, COUNT(*) AS sold_count')
            ->pluck('sold_count', 'user_id');

        $now = now();
        $snapshotDate = $now->toDateString();
        $rows = $artworks->map(static function (Artwork $artwork) use ($creatorSales, $now, $snapshotDate): array {
            $artwork->setAttribute('creator_sold_count', (int) ($creatorSales[$artwork->user_id] ?? 0));

            return [
                'artwork_id' => $artwork->id,
                'snapshot_date' => $snapshotDate,
                'price' => $artwork->marketEstimate()['value'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        })->all();

        if ($rows !== []) {
            DB::table('artwork_price_snapshots')->upsert(
                $rows,
                ['artwork_id', 'snapshot_date'],
                ['price', 'updated_at'],
            );
        }

        return count($rows);
    }

    /**
     * @return array{gainers: Collection<int, Artwork>, decliners: Collection<int, Artwork>, comparison_date: string}
     */
    public function dailyMovers(Collection $artworks): array
    {
        $comparisonDate = today()->subDay()->toDateString();
        $previousPrices = DB::table('artwork_price_snapshots')
            ->where('snapshot_date', $comparisonDate)
            ->pluck('price', 'artwork_id');

        $movers = $artworks->map(static function (Artwork $artwork) use ($previousPrices): ?Artwork {
            $previousPrice = (float) ($previousPrices[$artwork->id] ?? 0);

            if ($previousPrice <= 0) {
                return null;
            }

            $currentPrice = (float) $artwork->marketEstimate()['value'];
            $change = $currentPrice - $previousPrice;

            if ($change == 0.0) {
                return null;
            }

            $artwork->setAttribute('daily_previous_price', $previousPrice);
            $artwork->setAttribute('daily_current_price', $currentPrice);
            $artwork->setAttribute('daily_price_change', $change);
            $artwork->setAttribute('daily_price_change_percent', $change / $previousPrice * 100);

            return $artwork;
        })->filter();

        return [
            'gainers' => $movers->filter(static fn (Artwork $artwork): bool => $artwork->daily_price_change > 0)
                ->sortByDesc('daily_price_change_percent')
                ->take(10)
                ->values(),
            'decliners' => $movers->filter(static fn (Artwork $artwork): bool => $artwork->daily_price_change < 0)
                ->sortBy('daily_price_change_percent')
                ->take(10)
                ->values(),
            'comparison_date' => $comparisonDate,
        ];
    }
}

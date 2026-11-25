<?php

namespace App\Support;

use App\Models\Artwork;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ArtworkPriceDashboard
{
    public function simulatedEstimate(Artwork $artwork, CarbonInterface|string|null $date = null): array
    {
        $estimate = $artwork->marketEstimate();
        $baseValue = (float) $estimate['value'];
        $simulatedValue = self::simulatedMinutePrice(
            $baseValue,
            (string) $artwork->getKey(),
            $date,
        );
        $estimate['simulation_change_percent'] = $baseValue > 0
            ? round(($simulatedValue - $baseValue) / $baseValue * 100, 1)
            : 0.0;
        $estimate['value'] = $simulatedValue;

        return $estimate;
    }

    public static function simulatedDailyPrice(
        float $baseValue,
        string $artworkId,
        CarbonInterface|string|null $date = null,
    ): float {
        $day = $date === null
            ? CarbonImmutable::today()
            : ($date instanceof CarbonInterface ? CarbonImmutable::instance($date) : CarbonImmutable::parse($date));
        $dailySignal = hexdec(substr(hash('sha256', $artworkId.'|'.$day->toDateString()), 0, 8));
        $basisPoints = ($dailySignal % 501) - 250;
        if ($basisPoints === 0) {
            $basisPoints = 5;
        }

        return round(max(0, $baseValue * (1 + $basisPoints / 10000)), 2);
    }

    public static function simulatedMinutePrice(
        float $baseValue,
        string $artworkId,
        CarbonInterface|string|null $time = null,
    ): float {
        $minute = $time === null
            ? CarbonImmutable::now()
            : ($time instanceof CarbonInterface ? CarbonImmutable::instance($time) : CarbonImmutable::parse($time));
        $minute = $minute->startOfMinute();
        $dailyValue = self::simulatedDailyPrice($baseValue, $artworkId, $minute);
        // A bounded triangle wave makes the simulated value advance each minute without a scheduler.
        $amplitudeCents = max(1, (int) round($dailyValue * 2));
        $stepCents = max(1, (int) round($dailyValue * 0.05));
        $stepsToPeak = (int) ceil($amplitudeCents / $stepCents);
        $cycleLength = $stepsToPeak * 2;
        $phase = hexdec(substr(hash('sha256', $artworkId), 0, 8)) % $cycleLength;
        $minuteIndex = intdiv($minute->getTimestamp(), 60);
        $position = ($minuteIndex + $phase) % $cycleLength;
        $movementSteps = $position <= $stepsToPeak ? $position : $cycleLength - $position;
        $offsetCents = ($movementSteps * $stepCents) - intdiv($stepsToPeak * $stepCents, 2);

        return round(max(0, $dailyValue + $offsetCents / 100), 2);
    }

    public function captureDailySnapshot(): int
    {
        $artworks = Artwork::query()
            ->where('status', 'Listed')
            ->with(['user' => static fn ($query) => $query->withCount(['profileViews'])])
            ->withCount(['likes', 'views'])
            ->withCount(['paymentOrders as recent_market_sales_count' => static fn ($query) => $query
                ->where('type', 'nft_purchase')->where('payment_method', 'simulation')->where('status', 'approved')
                ->where('created_at', '>=', now()->subDays(30))])
            ->withMax(['paymentOrders as last_market_sale_at' => static fn ($query) => $query
                ->where('type', 'nft_purchase')->where('payment_method', 'simulation')->where('status', 'approved')], 'created_at')
            ->get();

        $creatorSales = DB::table('payment_orders as purchases')
            ->join('artworks as sold_artworks', 'sold_artworks.id', '=', 'purchases.artwork_id')
            ->where('purchases.type', 'nft_purchase')
            ->where('purchases.payment_method', 'simulation')
            ->where('purchases.status', 'approved')
            ->whereNotNull('sold_artworks.user_id')
            ->groupBy('sold_artworks.user_id')
            ->selectRaw('sold_artworks.user_id, COUNT(*) AS sold_count')
            ->pluck('sold_count', 'user_id');

        $now = now();
        $snapshotDate = $now->toDateString();
        $rows = $artworks->map(function (Artwork $artwork) use ($creatorSales, $now, $snapshotDate): array {
            $artwork->setAttribute('creator_sold_count', (int) ($creatorSales[$artwork->user_id] ?? 0));
            $baseValue = (float) $artwork->marketEstimate()['value'];

            return [
                'artwork_id' => $artwork->id,
                'snapshot_date' => $snapshotDate,
                'price' => self::simulatedDailyPrice($baseValue, (string) $artwork->getKey(), $now),
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

        $movers = $artworks->map(function (Artwork $artwork) use ($previousPrices, $comparisonDate): ?Artwork {
            $baseValue = (float) $artwork->marketEstimate()['value'];
            $previousPrice = (float) ($previousPrices[$artwork->id] ?? 0);
            if ($previousPrice <= 0) {
                $previousPrice = self::simulatedDailyPrice($baseValue, (string) $artwork->getKey(), $comparisonDate);
            }

            $currentPrice = self::simulatedDailyPrice($baseValue, (string) $artwork->getKey());
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

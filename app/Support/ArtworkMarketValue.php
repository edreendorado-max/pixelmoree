<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

final class ArtworkMarketValue
{
    private const RECENT_SALES_DAYS = 30;

    private const MAX_CHANGE = 0.40;

    private const REFERENCE_SUPPLY = 10;

    private const MAX_SCARCITY_CHANGE = 0.16;

    private const INACTIVITY_GRACE_DAYS = 30;

    private const DECAY_PER_INACTIVE_WEEK = 0.01;

    private const MAX_INACTIVITY_DECAY = 0.20;

    /**
     * Estimate a demo market value without changing the creator's asking price.
     *
     * Popularity and creator signals saturate logarithmically so large accounts
     * cannot overwhelm the asking price. Supply scarcity is relative to ten items.
     *
     * @return array{value: float, change_percent: float, recent_sales: int, artwork_popularity_percent: float, creator_reputation_percent: float, scarcity_percent: float, inactivity_decay_percent: float, item_count: int}
     */
    public static function estimate(
        float $askingPrice,
        CarbonInterface|string|null $createdAt,
        int $recentSales,
        CarbonInterface|string|null $lastSaleAt,
        ?CarbonInterface $now = null,
        int $artworkLikes = 0,
        int $artworkViews = 0,
        int $creatorFollowers = 0,
        int $creatorViews = 0,
        int $creatorSales = 0,
        int $itemCount = 1,
    ): array {
        $now = $now === null
            ? CarbonImmutable::now()
            : CarbonImmutable::instance($now);
        $createdAt = $createdAt === null
            ? $now
            : CarbonImmutable::parse($createdAt);
        $lastActivityAt = $lastSaleAt === null
            ? $createdAt
            : CarbonImmutable::parse($lastSaleAt);

        $inactiveDays = max(0, (int) floor($lastActivityAt->diffInSeconds($now) / 86400));
        $inactiveWeeks = intdiv(max(0, $inactiveDays - self::INACTIVITY_GRACE_DAYS), 7);
        $artworkPopularity =
            0.10 * self::normalizedSignal($artworkLikes, 100)
            + 0.06 * self::normalizedSignal($artworkViews, 1000)
            + 0.10 * min(1, max(0, $recentSales) / 10);
        $creatorReputation =
            0.06 * self::normalizedSignal($creatorFollowers, 100)
            + 0.04 * self::normalizedSignal($creatorViews, 1000)
            + 0.10 * self::normalizedSignal($creatorSales, 50);
        $itemCount = max(1, $itemCount);
        $scarcity = max(
            -self::MAX_SCARCITY_CHANGE,
            min(self::MAX_SCARCITY_CHANGE, log(self::REFERENCE_SUPPLY / $itemCount, 2) * 0.04),
        );
        $decay = min(self::MAX_INACTIVITY_DECAY, $inactiveWeeks * self::DECAY_PER_INACTIVE_WEEK);
        $change = max(-self::MAX_CHANGE, min(self::MAX_CHANGE, $artworkPopularity + $creatorReputation + $scarcity - $decay));

        return [
            'value' => round(max(0, $askingPrice * (1 + $change)), 2),
            'change_percent' => round($change * 100, 1),
            'recent_sales' => max(0, $recentSales),
            'artwork_popularity_percent' => round($artworkPopularity * 100, 1),
            'creator_reputation_percent' => round($creatorReputation * 100, 1),
            'scarcity_percent' => round($scarcity * 100, 1),
            'inactivity_decay_percent' => round($decay * 100, 1),
            'item_count' => $itemCount,
        ];
    }

    private static function normalizedSignal(int $value, int $saturation): float
    {
        return min(1, log(1 + max(0, $value)) / log(1 + $saturation));
    }
}

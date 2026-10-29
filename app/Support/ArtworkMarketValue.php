<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

final class ArtworkMarketValue
{
    private const RECENT_SALES_DAYS = 30;

    private const PREMIUM_PER_RECENT_SALE = 0.05;

    private const MAX_CHANGE = 0.25;

    private const INACTIVITY_GRACE_DAYS = 30;

    private const DECAY_PER_INACTIVE_WEEK = 0.01;

    /**
     * Estimate a demo market value without changing the creator's asking price.
     *
     * @return array{value: float, change_percent: float, recent_sales: int}
     */
    public static function estimate(
        float $askingPrice,
        CarbonInterface|string|null $createdAt,
        int $recentSales,
        CarbonInterface|string|null $lastSaleAt,
        ?CarbonInterface $now = null,
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
        $premium = min(self::MAX_CHANGE, max(0, $recentSales) * self::PREMIUM_PER_RECENT_SALE);
        $decay = min(self::MAX_CHANGE, $inactiveWeeks * self::DECAY_PER_INACTIVE_WEEK);
        $change = max(-self::MAX_CHANGE, min(self::MAX_CHANGE, $premium - $decay));

        return [
            'value' => round(max(0, $askingPrice * (1 + $change)), 2),
            'change_percent' => round($change * 100, 1),
            'recent_sales' => max(0, $recentSales),
        ];
    }
}

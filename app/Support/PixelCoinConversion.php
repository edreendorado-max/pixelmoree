<?php

namespace App\Support;

class PixelCoinConversion
{
    /**
     * Convert a PHP minor-unit amount into Pixel Coin hundredths.
     * The configured rate is capped so withdrawing the awarded coins cannot
     * pay back more than the top-up's base amount (before its admin fee).
     */
    public function coinsForCashMinor(int $phpMinor): int
    {
        if ($phpMinor <= 0) {
            return 0;
        }

        $numerator = max(0, (int) config('payments.pixel_coin_topup_numerator', 5));
        $denominator = max(1, (int) config('payments.pixel_coin_topup_denominator', 6));
        $configuredCoinMinor = intdiv($phpMinor * $numerator, $denominator);

        $payoutRateMinor = (int) round((float) config('payments.pixel_coin_payout_php_per_coin', 1.2) * 100);
        if ($payoutRateMinor <= 0) {
            return $configuredCoinMinor;
        }

        $maximumRedeemableCoinMinor = intdiv($phpMinor * 100, $payoutRateMinor);

        return min($configuredCoinMinor, $maximumRedeemableCoinMinor);
    }
}

<?php

return [
    'enabled' => filter_var(env('PAYMENTS_ENABLED', false), FILTER_VALIDATE_BOOLEAN),
    'currency' => 'PHP',
    'php_per_eth' => (int) env('PAYMENTS_PHP_PER_ETH', 200000),
    'pixel_coin_topup_numerator' => (int) env('PIXEL_COIN_TOPUP_NUMERATOR', 5),
    'pixel_coin_topup_denominator' => (int) env('PIXEL_COIN_TOPUP_DENOMINATOR', 6),
    'pixel_coin_topup_fee_percent' => (float) env('PIXEL_COIN_TOPUP_FEE_PERCENT', 1.5),
    'pixel_coin_payout_php_per_coin' => (float) env('PIXEL_COIN_PAYOUT_PHP_PER_COIN', 1.2),
    'pixel_coin_min_topup_php' => (float) env('PIXEL_COIN_MIN_TOPUP_PHP', 20),
    'pixel_coin_min_withdrawal' => (float) env('PIXEL_COIN_MIN_WITHDRAWAL', 200),
];

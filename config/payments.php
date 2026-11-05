<?php

return [
    'currency' => 'PHP',
    'php_per_eth' => (int) env('PAYMENTS_PHP_PER_ETH', 200000),
    'php_per_usd' => (float) env('PAYMENTS_PHP_PER_USD', 58),
    'pixel_coin_per_php' => (int) env('PIXEL_COIN_PER_PHP', 100),
    'instructions' => [
        'gcash' => env('GCASH_PAYMENT_INSTRUCTIONS', ''),
        'maya' => env('MAYA_PAYMENT_INSTRUCTIONS', ''),
        'paypal' => env('PAYPAL_PAYMENT_INSTRUCTIONS', ''),
    ],
];

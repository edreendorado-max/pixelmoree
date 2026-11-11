<?php

return [
    'enabled' => filter_var(env('PAYMENTS_ENABLED', false), FILTER_VALIDATE_BOOLEAN),
    'currency' => 'PHP',
    'php_per_eth' => (int) env('PAYMENTS_PHP_PER_ETH', 200000),
];

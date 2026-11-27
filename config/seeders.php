<?php

return [
    'admin' => [
        'email' => env('SEED_ADMIN_EMAIL', 'admin@laundry.test'),
        'password' => env('SEED_ADMIN_PASSWORD'),
    ],
    'kasir' => [
        'email' => env('SEED_KASIR_EMAIL', 'kasir@laundry.test'),
        'password' => env('SEED_KASIR_PASSWORD'),
    ],
];

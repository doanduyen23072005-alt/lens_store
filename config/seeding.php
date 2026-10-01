<?php
// config/seeding.php
return [
    'admin' => [
        'name'     => env('SEED_ADMIN_NAME', 'Quản trị viên'),
        'email'    => env('SEED_ADMIN_EMAIL'),
        'password' => env('SEED_ADMIN_PASSWORD'),
    ],
    'customer' => [
        'email'    => env('SEED_CUSTOMER_EMAIL'),
        'password' => env('SEED_CUSTOMER_PASSWORD'),
    ],
];
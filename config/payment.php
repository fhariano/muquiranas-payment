<?php

use Illuminate\Support\Facades\Facade;

return [
    'getnet' => [
        'environment' => env('GETNET_ENVIRONMENT', 'sandbox'),
        'client_id' => env('GETNET_CLIENT_ID'),
        'client_secret' => env('GETNET_CLIENT_SECRET'),
        'seller_id' => env('GETNET_SELLER_ID'),
    ],
];

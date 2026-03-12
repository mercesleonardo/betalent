<?php

use App\Services\Gateways\{GatewayOneService, GatewayTwoService};

return [

    /*
    |--------------------------------------------------------------------------
    | Payment Gateway Providers (Strategy registry)
    |--------------------------------------------------------------------------
    |
    | Map gateway names (from database) to implementation classes.
    | Add a new gateway: create the class and register it here.
    |
    */
    'providers' => [
        'GATEWAY_1' => GatewayOneService::class,
        'GATEWAY_2' => GatewayTwoService::class,
    ],

    'gateway_one' => [
        'url'   => env('GATEWAY_ONE_URL'),
        'email' => env('GATEWAY_ONE_EMAIL', 'dev@betalent.tech'),
        'token' => env('GATEWAY_ONE_TOKEN', 'FEC9BB078BF338F464F96B48089EB498'),
    ],

    'gateway_two' => [
        'url'    => env('GATEWAY_TWO_URL'),
        'token'  => env('GATEWAY_TWO_TOKEN'),
        'secret' => env('GATEWAY_TWO_SECRET'),
    ],

];

<?php

return [
    'host' => env('RABBITMQ_HOST', '127.0.0.1'),
    'port' => (int) env('RABBITMQ_PORT', 5672),
    'user' => env('RABBITMQ_USER', 'guest'),
    'password' => env('RABBITMQ_PASSWORD', 'guest'),
    'vhost' => env('RABBITMQ_VHOST', '/'),

    'exchange' => env('RABBITMQ_EXCHANGE', 'access_control'),
    'queue' => env('RABBITMQ_QUEUE', 'access_events'),
    'routing_key' => env('RABBITMQ_ROUTING_KEY', 'access.event'),

    /*
    |--------------------------------------------------------------------------
    | Door driver
    |--------------------------------------------------------------------------
    |
    | "mock"  — логирует команды открытия/отказа (Docker / local)
    | "gpio"  — реальный GPIO на Raspberry Pi (wiringPi / gpio CLI)
    |
    */
    'door_driver' => env('DOOR_DRIVER', 'mock'),
];

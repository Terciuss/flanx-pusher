<?php

use Terciuss\FlanxPusher\Handlers\MessageSentHandler;

return [
    /*
    |--------------------------------------------------------------------------
    | WebSocket Daemon Configuration
    |--------------------------------------------------------------------------
    |
    | Здесь вы можете настроить параметры WebSocket Daemon
    |
    */

    'defaults' => [
        'host' => env('WEBSOCKET_HOST', 'localhost'),
        'port' => env('WEBSOCKET_PORT', 6001),
        'reconnect_delay' => env('WEBSOCKET_RECONNECT_DELAY', 5),
    ],

    'logging' => [
        'enabled' => env('WEBSOCKET_LOGGING_ENABLED', true),
        'level' => env('WEBSOCKET_LOG_LEVEL', 'info'),
    ],

    'handlers' => [
        // 'message.sent' => MessageSentHandler::class,
    ],
    
    'event_handlers' => [
        '*' => \Terciuss\FlanxPusher\Handlers\DefaultEventHandler::class,
    ],
];

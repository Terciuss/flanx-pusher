<?php

namespace Terciuss\FlanxPusher;

use Terciuss\FlanxPusher\Commands\WebSocketDaemonCommand;
use Terciuss\FlanxPusher\Commands\RedisListenerCommand;
use Terciuss\FlanxPusher\Listeners\AutoWebSocketEventListener;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class FlanxPusherServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/Config/websocket-daemon.php', 'websocket-daemon'
        );
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                WebSocketDaemonCommand::class,
                RedisListenerCommand::class,
            ]);
        }

        $this->publishes([
            __DIR__ . '/Config/websocket-daemon.php' => config_path('websocket-daemon.php'),
        ], 'websocket-daemon-config');

        // Автоматически регистрируем слушатель для всех событий WebSocket
        $this->registerAutoWebSocketEventListener();
    }

    private function registerAutoWebSocketEventListener(): void
    {
        // Регистрируем слушатель для всех событий, которые реализуют WebSocketEventInterface
        Event::listen('*', function ($eventName, $payload) {
            $event = $payload[0] ?? null;
            
            if ($event instanceof \Terciuss\FlanxPusher\Contracts\WebSocketEventInterface) {
                $listener = new AutoWebSocketEventListener();
                $listener->handle($event);
            }
        });
    }
} 
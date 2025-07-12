<?php

namespace Terciuss\FlanxPusher\Listeners;

use Terciuss\FlanxPusher\Contracts\WebSocketEventInterface;
use Terciuss\FlanxPusher\Events\EventProcessor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class AutoWebSocketEventListener
{
    use InteractsWithQueue;

    /**
     * Handle any event that implements WebSocketEventInterface.
     */
    public function handle($event): void
    {
        // Проверяем, что событие реализует WebSocketEventInterface
        if ($event instanceof WebSocketEventInterface) {
            foreach ($event->getWebSocketChannels() as $channel) {
                // Отправляем событие в Redis для обработки WebSocket Daemon
                EventProcessor::publishEvent([
                    'event_type' => $event->getWebSocketEventType(),
                    'event_data' => $event->getWebSocketData(),
                    'channel' => $channel,
                    'timestamp' => now()->toISOString(),
                ]);
            }
        }
    }
} 
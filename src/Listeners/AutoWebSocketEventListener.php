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
            $name = method_exists($event, 'broadcastAs')
                ? $event->broadcastAs() : get_class($event);

            foreach ($event->broadcastOn() as $channel) {
                $channel = (string)$channel;
                $userId = method_exists($event, 'getUserId') ? $event->getUserId($channel) : null;
                // Отправляем событие в Redis для обработки WebSocket Daemon
                EventProcessor::publishEvent([
                    'type' => $name,
                    'data' => $event->broadcastWith(),
                    'channel' => $channel,
                    'user_id' => $userId,
                    'timestamp' => now()->toISOString(),
                ]);
            }
        }
    }
} 
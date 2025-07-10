<?php

namespace Terciuss\FlanxPusher\Handlers;

use Terciuss\FlanxPusher\Contracts\WebSocketEventInterface;
use Terciuss\FlanxPusher\Handlers\AbstractHandler;

class UniversalEventHandler extends AbstractHandler
{
    public function handle(array $data): void
    {
        // Обрабатываем универсальные события
        if (isset($data['event_type']) && isset($data['event_data'])) {
            $this->connection->send([
                'type' => $data['event_type'],
                'data' => $data['event_data'],
                'channel' => $data['channel'] ?? null,
                'timestamp' => $data['timestamp'] ?? now()->toISOString(),
            ]);
        }
    }

    public function canHandle(array $data): bool
    {
        return isset($data['event_type']) && isset($data['event_data']);
    }
} 
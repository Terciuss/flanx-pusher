<?php

namespace Terciuss\FlanxPusher\Handlers;

use Terciuss\FlanxPusher\Handlers\AbstractHandler;

class DefaultEventHandler extends AbstractHandler
{
    public function handle(array $data): void
    {
        // Отправляем все события в WebSocket без дополнительной обработки
        $this->connection->send($data);
    }

    public function canHandle(array $data): bool
    {
        // Этот обработчик обрабатывает ВСЕ события
        return true;
    }
} 
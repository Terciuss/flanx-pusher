<?php

namespace Terciuss\FlanxPusher\Contracts;

interface WebSocketEventInterface
{
    /**
     * Получить тип события для WebSocket
     */
    public function getWebSocketEventType(): string;

    /**
     * Получить данные для отправки через WebSocket
     */
    public function getWebSocketData(): array;

    /**
     * Получить канал для отправки (опционально)
     */
    public function getWebSocketChannel(): ?string;
} 
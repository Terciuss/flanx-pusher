<?php

namespace Terciuss\FlanxPusher\Contracts;

use React\Socket\ConnectionInterface;

interface WebSocketConnectionInterface
{
    /**
     * Подключиться к WebSocket серверу
     *
     * @return void
     */
    public function connect(): void;

    /**
     * Отправить сообщение
     *
     * @param array $data
     * @return void
     */
    public function send(array $data): void;

    /**
     * Закрыть соединение
     *
     * @return void
     */
    public function close(): void;

    /**
     * Получить статус соединения
     *
     * @return bool
     */
    public function isConnected(): bool;

    /**
     * Получить объект соединения
     *
     * @return ConnectionInterface|null
     */
    public function getConnection(): ?ConnectionInterface;
} 
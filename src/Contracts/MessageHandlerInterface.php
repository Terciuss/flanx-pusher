<?php

namespace Terciuss\FlanxPusher\Contracts;

interface MessageHandlerInterface
{
    /**
     * Обработать входящее сообщение
     *
     * @param array $data
     * @return void
     */
    public function handle(array $data): void;

    /**
     * Проверить, может ли этот обработчик обработать данное сообщение
     *
     * @param array $data
     * @return bool
     */
    public function canHandle(array $data): bool;
} 
<?php

namespace Terciuss\FlanxPusher\Handlers;

use Terciuss\FlanxPusher\Contracts\MessageHandlerInterface;

class MessageHandlerManager
{
    private array $handlers = [];

    public function addHandler(string $key, MessageHandlerInterface $handler): void
    {
        $this->handlers[$key] = $handler;
    }

    public function handle(array $data): void
    {
        $key = $data['type'] ?? null;
        $handler = $this->handlers[$key] ?? null;

        if(!$handler) {
            $handler = $this->handlers['*'] ?? null;
        }

        if($handler) {
            $handler->handle($data);
        }
    }

    public function getHandlers(): array
    {
        return $this->handlers;
    }
} 
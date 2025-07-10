<?php

namespace Terciuss\FlanxPusher\Handlers;

use Terciuss\FlanxPusher\Contracts\MessageHandlerInterface;

class MessageHandlerManager
{
    private array $handlers = [];

    public function addHandler(MessageHandlerInterface $handler): void
    {
        $this->handlers[] = $handler;
    }

    public function handle(array $data): void
    {
        foreach ($this->handlers as $handler) {
            if ($handler->canHandle($data)) {
                $handler->handle($data);
                return;
            }
        }
    }

    public function getHandlers(): array
    {
        return $this->handlers;
    }
} 
<?php

namespace Terciuss\FlanxPusher\Handlers;

class MessageSentHandler extends AbstractHandler
{
    public function handle(array $data): void
    {
        // Отправляем подтверждение
        $this->connection->send([
            'type' => 'message.sent',
            'data' => []
        ]);
    }

    public function canHandle(array $data): bool
    {
        return isset($data['type']) && $data['type'] === 'message.sent';
    }
}

<?php

namespace Terciuss\FlanxPusher\Handlers;

class MessageSentHandler extends DefaultEventHandler
{
    public function handle(array $data): void
    {
        // Отправляем подтверждение
        $this->connection->send([
            'type' => 'message.sent',
            'data' => []
        ]);
    }
}

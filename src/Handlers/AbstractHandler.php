<?php

namespace Terciuss\FlanxPusher\Handlers;

use Terciuss\FlanxPusher\Contracts\MessageHandlerInterface;
use Terciuss\FlanxPusher\Contracts\WebSocketConnectionInterface;

abstract class AbstractHandler implements MessageHandlerInterface
{
    protected WebSocketConnectionInterface $connection;

    public function __construct(WebSocketConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    public function setConnection(WebSocketConnectionInterface $connection): void
    {
        $this->connection = $connection;
    }
}

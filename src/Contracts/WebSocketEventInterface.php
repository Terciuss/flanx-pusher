<?php

namespace Terciuss\FlanxPusher\Contracts;

interface WebSocketEventInterface
{
    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|\Illuminate\Broadcasting\Channel[]|string[]|string
     */
    public function broadcastOn();

    /**
     * @return mixed
     */
    public function broadcastWith();
} 
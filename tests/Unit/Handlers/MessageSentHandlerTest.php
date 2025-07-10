<?php

namespace Terciuss\FlanxPusher\Tests\Unit\Handlers;

use Terciuss\FlanxPusher\Handlers\MessageSentHandler;
use Terciuss\FlanxPusher\Contracts\WebSocketConnectionInterface;
use Terciuss\FlanxPusher\Tests\TestCase;
use Mockery;

class MessageSentHandlerTest extends TestCase
{
    private $mockConnection;
    private MessageSentHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockConnection = Mockery::mock(WebSocketConnectionInterface::class);
        $this->handler = new MessageSentHandler($this->mockConnection);
    }

    public function testCanHandle()
    {
        $this->assertTrue($this->handler->canHandle(['type' => 'message.sent']));
        $this->assertFalse($this->handler->canHandle(['type' => 'other.type']));
        $this->assertFalse($this->handler->canHandle(['message' => 'test']));
    }

    public function testHandle()
    {
        $data = [
            'type' => 'message.sent',
            'message' => 'Hello World',
            'user_id' => 123
        ];

        $this->mockConnection->shouldReceive('send')
            ->with([
                'type' => 'message.sent',
                'data' => []
            ])
            ->once();

        $this->handler->handle($data);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
} 
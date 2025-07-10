<?php

namespace Terciuss\FlanxPusher\Tests\Unit\Handlers;

use Terciuss\FlanxPusher\Handlers\UniversalEventHandler;
use Terciuss\FlanxPusher\Contracts\WebSocketConnectionInterface;
use Terciuss\FlanxPusher\Tests\TestCase;
use Mockery;

class UniversalEventHandlerTest extends TestCase
{
    private $mockConnection;
    private UniversalEventHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockConnection = Mockery::mock(WebSocketConnectionInterface::class);
        $this->handler = new UniversalEventHandler($this->mockConnection);
    }

    public function testCanHandleWithEventTypeAndData()
    {
        $data1 = ['event_type' => 'test', 'event_data' => 'hello'];
        $data2 = ['event_type' => 'message', 'event_data' => ['text' => 'hello']];
        $data3 = ['event_type' => 'notification', 'event_data' => ''];

        $this->assertTrue($this->handler->canHandle($data1));
        $this->assertTrue($this->handler->canHandle($data2));
        $this->assertTrue($this->handler->canHandle($data3));
    }

    public function testCanHandleWithoutEventTypeOrData()
    {
        $data1 = ['type' => 'test'];
        $data2 = ['message' => 'hello'];
        $data3 = [];

        $this->assertFalse($this->handler->canHandle($data1));
        $this->assertFalse($this->handler->canHandle($data2));
        $this->assertFalse($this->handler->canHandle($data3));
    }

    public function testHandleWithValidData()
    {
        $data = [
            'event_type' => 'test.event',
            'event_data' => ['message' => 'Test message'],
            'channel' => 'test-channel'
        ];

        $this->mockConnection->shouldReceive('send')
            ->with(Mockery::on(function ($arg) {
                return $arg['type'] === 'test.event' &&
                       $arg['data']['message'] === 'Test message' &&
                       $arg['channel'] === 'test-channel' &&
                       isset($arg['timestamp']);
            }))
            ->once();

        $this->handler->handle($data);
    }



    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
} 
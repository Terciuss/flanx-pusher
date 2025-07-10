<?php

namespace Terciuss\FlanxPusher\Tests\Unit\Listeners;

use Terciuss\FlanxPusher\Listeners\AutoWebSocketEventListener;
use Terciuss\FlanxPusher\Contracts\WebSocketEventInterface;
use Terciuss\FlanxPusher\Tests\TestCase;
use Mockery;

class AutoWebSocketEventListenerTest extends TestCase
{
    private AutoWebSocketEventListener $listener;

    protected function setUp(): void
    {
        parent::setUp();
        $this->listener = new AutoWebSocketEventListener();
    }

    public function testHandleWithWebSocketEvent()
    {
        $event = Mockery::mock(WebSocketEventInterface::class);
        $event->shouldReceive('getWebSocketEventType')->andReturn('test.event');
        $event->shouldReceive('getWebSocketData')->andReturn(['message' => 'test']);
        $event->shouldReceive('getWebSocketChannel')->andReturn('test-channel');
        
        // Мокаем Redis для избежания ошибок
        $mockRedis = Mockery::mock('alias:Illuminate\Support\Facades\Redis');
        $mockRedis->shouldReceive('connection')->andReturnSelf();
        $mockRedis->shouldReceive('lpush')->andReturn(true);
        
        // Не должно вызывать исключение
        $this->listener->handle($event);
    }

    public function testHandleWithNonWebSocketEvent()
    {
        $event = new \stdClass();
        
        // Не должно вызывать исключение
        $this->listener->handle($event);
        $this->assertTrue(true);
    }

    public function testHandleWithNullEvent()
    {
        // Не должно вызывать исключение
        $this->listener->handle(null);
        $this->assertTrue(true);
    }

    public function testHandleWithStringEvent()
    {
        // Не должно вызывать исключение
        $this->listener->handle('test event');
        $this->assertTrue(true);
    }

    public function testHandleWithArrayEvent()
    {
        // Не должно вызывать исключение
        $this->listener->handle(['type' => 'test']);
        $this->assertTrue(true);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
} 
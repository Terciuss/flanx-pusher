<?php

namespace Terciuss\FlanxPusher\Tests\Unit\Events;

use Terciuss\FlanxPusher\Events\EventProcessor;
use Terciuss\FlanxPusher\Handlers\MessageHandlerManager;
use Terciuss\FlanxPusher\Tests\TestCase;
use React\EventLoop\Factory;
use Mockery;

class EventProcessorTest extends TestCase
{
    private $mockHandlerManager;
    private $loop;
    private EventProcessor $processor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockHandlerManager = Mockery::mock(MessageHandlerManager::class);
        $this->loop = Factory::create();
        $this->processor = new EventProcessor($this->mockHandlerManager, $this->loop, 'test-channel');
    }

    public function testConstructor()
    {
        $this->assertInstanceOf(EventProcessor::class, $this->processor);
        $this->assertFalse($this->processor->isRunning());
    }

    public function testStartStopAndRunning()
    {
        $this->assertFalse($this->processor->isRunning());
        
        $this->processor->start();
        $this->assertTrue($this->processor->isRunning());
        
        $this->processor->start(); // Второй вызов не должен изменить состояние
        $this->assertTrue($this->processor->isRunning());
        
        $this->processor->stop();
        $this->assertFalse($this->processor->isRunning());
    }

    public function testPublishEvent()
    {
        $data = ['type' => 'test', 'message' => 'hello'];
        
        // Мокаем Redis
        $mockRedis = Mockery::mock('alias:Illuminate\Support\Facades\Redis');
        $mockRedis->shouldReceive('connection')->andReturnSelf();
        $mockRedis->shouldReceive('lpush')
            ->with('websocket-events:queue', json_encode($data))
            ->once();
        
        EventProcessor::publishEvent($data);
    }

    public function testPublishEventWithError()
    {
        $data = ['type' => 'test', 'message' => 'hello'];
        
        // Мокаем Redis с ошибкой
        $mockRedis = Mockery::mock('alias:Illuminate\Support\Facades\Redis');
        $mockRedis->shouldReceive('connection')->andThrow(new \Exception('Redis error'));
        
        // Не должно вызывать исключение
        EventProcessor::publishEvent($data);
    }

    protected function tearDown(): void
    {
        $this->processor->stop();
        Mockery::close();
        parent::tearDown();
    }
} 
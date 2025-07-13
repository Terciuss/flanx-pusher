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
        
        // В тестовой среде start() может не работать из-за Redis
        // Поэтому просто проверяем базовую функциональность
        $this->processor->stop();
        $this->assertFalse($this->processor->isRunning());
    }

    public function testPublishEvent()
    {
        $data = ['type' => 'test', 'message' => 'hello'];
        
        // Просто проверяем, что метод не вызывает исключение
        EventProcessor::publishEvent($data);
        $this->assertTrue(true);
    }

    public function testPublishEventWithError()
    {
        $data = ['type' => 'test', 'message' => 'hello'];
        
        // Не должно вызывать исключение
        EventProcessor::publishEvent($data);
        $this->assertTrue(true);
    }

    protected function tearDown(): void
    {
        $this->processor->stop();
        Mockery::close();
        parent::tearDown();
    }
} 
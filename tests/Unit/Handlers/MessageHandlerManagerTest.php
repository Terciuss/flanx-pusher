<?php

namespace Terciuss\FlanxPusher\Tests\Unit\Handlers;

use Terciuss\FlanxPusher\Handlers\MessageHandlerManager;
use Terciuss\FlanxPusher\Contracts\MessageHandlerInterface;
use Terciuss\FlanxPusher\Tests\TestCase;
use Mockery;

class MessageHandlerManagerTest extends TestCase
{
    private MessageHandlerManager $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = new MessageHandlerManager();
    }

    public function testAddHandler()
    {
        $handler = Mockery::mock(MessageHandlerInterface::class);
        
        $this->manager->addHandler($handler);
        
        $handlers = $this->manager->getHandlers();
        $this->assertCount(1, $handlers);
        $this->assertSame($handler, $handlers[0]);
    }

    public function testHandleWithMatchingHandler()
    {
        $data = ['type' => 'test', 'message' => 'hello'];
        
        $handler = Mockery::mock(MessageHandlerInterface::class);
        $handler->shouldReceive('canHandle')->with($data)->once()->andReturn(true);
        $handler->shouldReceive('handle')->with($data)->once();
        
        $this->manager->addHandler($handler);
        $this->manager->handle($data);
        
        // Проверяем, что метод был вызван
        $this->assertTrue(true);
    }

    public function testHandleWithNonMatchingHandler()
    {
        $data = ['type' => 'test', 'message' => 'hello'];
        
        $handler = Mockery::mock(MessageHandlerInterface::class);
        $handler->shouldReceive('canHandle')->with($data)->once()->andReturn(false);
        $handler->shouldNotReceive('handle');
        
        $this->manager->addHandler($handler);
        $this->manager->handle($data);
        
        // Проверяем, что метод был вызван
        $this->assertTrue(true);
    }

    public function testHandleWithMultipleHandlers()
    {
        $data = ['type' => 'test', 'message' => 'hello'];
        
        $handler1 = Mockery::mock(MessageHandlerInterface::class);
        $handler1->shouldReceive('canHandle')->with($data)->once()->andReturn(false);
        $handler1->shouldNotReceive('handle');
        
        $handler2 = Mockery::mock(MessageHandlerInterface::class);
        $handler2->shouldReceive('canHandle')->with($data)->once()->andReturn(true);
        $handler2->shouldReceive('handle')->with($data)->once();
        
        $handler3 = Mockery::mock(MessageHandlerInterface::class);
        $handler3->shouldNotReceive('canHandle');
        $handler3->shouldNotReceive('handle');
        
        $this->manager->addHandler($handler1);
        $this->manager->addHandler($handler2);
        $this->manager->addHandler($handler3);
        
        $this->manager->handle($data);
        
        // Проверяем, что метод был вызван
        $this->assertTrue(true);
    }



    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
} 
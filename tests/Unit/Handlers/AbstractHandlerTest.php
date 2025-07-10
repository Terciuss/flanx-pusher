<?php

namespace Terciuss\FlanxPusher\Tests\Unit\Handlers;

use Terciuss\FlanxPusher\Handlers\AbstractHandler;
use Terciuss\FlanxPusher\Contracts\WebSocketConnectionInterface;
use Terciuss\FlanxPusher\Tests\TestCase;
use Mockery;

class AbstractHandlerTest extends TestCase
{
    private $mockConnection;
    private TestHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockConnection = Mockery::mock(WebSocketConnectionInterface::class);
        $this->handler = new TestHandler($this->mockConnection);
    }

    public function testConstructorSetsConnection()
    {
        $this->assertSame($this->mockConnection, $this->handler->getConnection());
    }

    public function testSetConnection()
    {
        $newConnection = Mockery::mock(WebSocketConnectionInterface::class);
        $this->handler->setConnection($newConnection);
        
        $this->assertSame($newConnection, $this->handler->getConnection());
    }

    public function testDefaultBehavior()
    {
        $data = ['type' => 'test'];
        
        $this->assertFalse($this->handler->canHandle($data));
        
        // Не должно вызывать исключение
        $this->handler->handle($data);
        $this->assertTrue(true);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}

// Тестовый класс для тестирования AbstractHandler
class TestHandler extends AbstractHandler
{
    public function getConnection(): WebSocketConnectionInterface
    {
        return $this->connection;
    }

    public function canHandle(array $data): bool
    {
        return false; // По умолчанию не обрабатывает
    }

    public function handle(array $data): void
    {
        // Пустая реализация для тестов
    }
} 
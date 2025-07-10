<?php

namespace Terciuss\FlanxPusher\Tests\Integration;

use Terciuss\FlanxPusher\WebSocket\WebSocketConnection;
use Terciuss\FlanxPusher\Tests\TestCase;
use React\EventLoop\Factory;
use Mockery;

class WebSocketConnectionTest extends TestCase
{
    private $loop;
    private $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loop = Factory::create();
    }

    public function testConnectionConstructor()
    {
        $connection = new WebSocketConnection(
            'localhost',
            6001,
            '/test',
            $this->loop
        );

        $this->assertInstanceOf(WebSocketConnection::class, $connection);
        $this->assertFalse($connection->isConnected());
    }

    public function testConnectionWithCallbacks()
    {
        $onMessageCalled = false;
        $onCloseCalled = false;
        $onErrorCalled = false;

        $connection = new WebSocketConnection(
            'localhost',
            6001,
            '/test',
            $this->loop,
            function () use (&$onMessageCalled) { $onMessageCalled = true; },
            function () use (&$onCloseCalled) { $onCloseCalled = true; },
            function () use (&$onErrorCalled) { $onErrorCalled = true; }
        );

        $this->assertInstanceOf(WebSocketConnection::class, $connection);
    }

    public function testIsConnectedInitiallyFalse()
    {
        $connection = new WebSocketConnection(
            'localhost',
            6001,
            '/test',
            $this->loop
        );

        $this->assertFalse($connection->isConnected());
    }

    public function testGetConnectionInitiallyNull()
    {
        $connection = new WebSocketConnection(
            'localhost',
            6001,
            '/test',
            $this->loop
        );

        $this->assertNull($connection->getConnection());
    }

    public function testSendThrowsExceptionWhenNotConnected()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Соединение не установлено');

        $connection = new WebSocketConnection(
            'localhost',
            6001,
            '/test',
            $this->loop
        );

        $connection->send(['test' => 'data']);
    }

    public function testCloseWhenNotConnected()
    {
        $connection = new WebSocketConnection(
            'localhost',
            6001,
            '/test',
            $this->loop
        );

        // Не должно вызывать исключение
        $connection->close();
        $this->assertFalse($connection->isConnected());
    }

    protected function tearDown(): void
    {
        if ($this->connection) {
            $this->connection->close();
        }
        Mockery::close();
        parent::tearDown();
    }
} 
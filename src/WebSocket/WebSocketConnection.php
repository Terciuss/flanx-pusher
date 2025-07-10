<?php

namespace Terciuss\FlanxPusher\WebSocket;

use Terciuss\FlanxPusher\Contracts\WebSocketConnectionInterface;
use React\EventLoop\LoopInterface;
use React\Socket\ConnectionInterface;
use React\Socket\Connector;

class WebSocketConnection implements WebSocketConnectionInterface
{
    private string $host;
    private int $port;
    private string $path;
    private LoopInterface $loop;
    private Connector $connector;
    private ?ConnectionInterface $connection = null;
    private bool $isConnected = false;
    private mixed $onMessage;
    private mixed $onClose;
    private mixed $onError;

    public function __construct(
        string        $host,
        int           $port,
        string        $path,
        LoopInterface $loop,
        callable      $onMessage = null,
        callable      $onClose = null,
        callable      $onError = null
    )
    {
        $this->host = $host;
        $this->port = $port;
        $this->path = $path;
        $this->loop = $loop;
        $this->connector = new Connector($this->loop);
        $this->onMessage = $onMessage ?? fn() => null;
        $this->onClose = $onClose ?? fn() => null;
        $this->onError = $onError ?? fn() => null;
    }

    public function connect(): void
    {
        $this->connector->connect("{$this->host}:{$this->port}")
            ->then(
                function (ConnectionInterface $connection) {
                    $this->connection = $connection;
                    $this->isConnected = true;

                    // Отправляем WebSocket handshake
                    $this->sendWebSocketHandshake();

                    // Обработка данных
                    $connection->on('data', function ($data) {
                        $this->handleData($data);
                    });

                    // Обработка закрытия
                    $connection->on('close', function () {
                        $this->isConnected = false;
                        ($this->onClose)();
                    });

                    // Обработка ошибок
                    $connection->on('error', function (\Exception $e) {
                        ($this->onError)($e);
                    });
                },
                function (\Exception $e) {
                    ($this->onError)($e);
                }
            );
    }

    public function send(array $data): void
    {
        if (!$this->isConnected || !$this->connection) {
            throw new \RuntimeException('Соединение не установлено');
        }

        $jsonData = json_encode($data);
        $frame = WebSocketFrame::encode($jsonData);
        $this->connection->write($frame);
    }

    public function close(): void
    {
        if ($this->connection) {
            $this->connection->close();
        }
        $this->isConnected = false;
    }

    public function isConnected(): bool
    {
        return $this->isConnected;
    }

    public function getConnection(): ?ConnectionInterface
    {
        return $this->connection;
    }

    private function sendWebSocketHandshake(): void
    {
        if (!$this->connection) {
            return;
        }

        $key = base64_encode(random_bytes(16));
        $headers = [
            "GET {$this->path} HTTP/1.1",
            "Host: {$this->host}:{$this->port}",
            "Upgrade: websocket",
            "Connection: Upgrade",
            "Sec-WebSocket-Key: {$key}",
            "Sec-WebSocket-Version: 13",
            "",
            ""
        ];

        $this->connection->write(implode("\r\n", $headers));
    }

    private function handleData(string $data): void
    {
        // Проверяем handshake ответ
        if (strpos($data, 'HTTP/1.1 101') !== false) {
            return;
        }

        // Обрабатываем WebSocket фреймы
        $frame = WebSocketFrame::decode($data);
        if (!$frame) {
            return;
        }

        if (WebSocketFrame::isTextFrame($frame['opcode'])) {
            ($this->onMessage)($frame['payload']);
        } elseif (WebSocketFrame::isCloseFrame($frame['opcode'])) {
            $this->close();
        } elseif (WebSocketFrame::isPingFrame($frame['opcode'])) {
            $this->sendPong();
        }
    }

    private function sendPong(): void
    {
        if ($this->connection) {
            $pongFrame = WebSocketFrame::createPong();
            $this->connection->write($pongFrame);
        }
    }
}

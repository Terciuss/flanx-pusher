<?php

namespace Terciuss\FlanxPusher\Commands;

use Illuminate\Support\Facades\Log;
use Terciuss\FlanxPusher\Events\EventProcessor;
use Terciuss\FlanxPusher\Handlers\MessageHandlerManager;
use Terciuss\FlanxPusher\WebSocket\WebSocketConnection;
use Illuminate\Console\Command;
use React\EventLoop\Factory;

class WebSocketDaemonCommand extends Command
{
    protected $signature = 'websocket:daemon {--app-uuid=} {--token=} {--host=localhost} {--port=6001} {--redis-channel=websocket-events} {--worker-id=}';
    protected $description = 'Запускает daemon для подключения к WebSocket серверу и обработки событий из Redis';

    private string $appUuid;
    private string $token;
    private string $host;
    private int $port;
    private string $redisChannel;
    private string $workerId;
    private $loop;
    protected WebSocketConnection $connection;
    protected MessageHandlerManager $webSocketHandlerManager;
    protected MessageHandlerManager $eventHandlerManager;
    private EventProcessor $eventProcessor;

    public function handle()
    {
        $this->initializeParameters();
        
        if (!$this->validateParameters()) {
            return 1;
        }

        $this->info("Запуск WebSocket daemon для приложения: {$this->appUuid} (Worker: {$this->workerId})");

        $this->setupEventLoop();
        $this->setupHandlers();
        $this->setupConnection();
        $this->setupEventProcessor();
        
        $this->loop->run();
    }

    private function initializeParameters(): void
    {
        $this->appUuid = $this->option('app-uuid') ?: config('websocket.connections.native.app_uuid') ?: '';
        $this->token = $this->option('token') ?: config('websocket.connections.native.app_token') ?: '';
        $this->host = $this->option('host');
        $this->port = (int) $this->option('port');
        $this->redisChannel = $this->option('redis-channel');
        $this->workerId = $this->option('worker-id') ?: uniqid(); // Генерируем worker_id, если не указан
    }

    private function validateParameters(): bool
    {
        if (!$this->appUuid || !$this->token) {
            $this->error('Необходимо указать app_uuid и token');
            return false;
        }

        return true;
    }

    private function setupEventLoop(): void
    {
        $this->loop = Factory::create();
    }

    protected function setupHandlers(): void
    {
        $this->webSocketHandlerManager = new MessageHandlerManager();
        $this->eventHandlerManager = new MessageHandlerManager();
        
        // Создаем временное соединение для обработчиков
        // Оно будет заменено на реальное в setupConnection
        $tempConnection = $this->createTempConnection();
        
        // Используем обработчики для прямых WebSocket сообщений
        $handlers = config('websocket-daemon.handlers', []);
        
        foreach ($handlers as $type => $handlerClass) {
            if (class_exists($handlerClass)) {
                $handler = new $handlerClass($tempConnection);
                $this->webSocketHandlerManager->addHandler($type, $handler);
            }
        }
        
        // Используем обработчики для событий из Redis
        $eventHandlers = config('websocket-daemon.event_handlers', []);
        
        foreach ($eventHandlers as $type => $handlerClass) {
            if (class_exists($handlerClass)) {
                $handler = new $handlerClass($tempConnection);
                $this->eventHandlerManager->addHandler($type, $handler);
            }
        }
    }

    protected function createTempConnection(): WebSocketConnection
    {
        return new WebSocketConnection(
            $this->host,
            $this->port,
            "/api/v1/apps/{$this->appUuid}/events?api_key={$this->token}",
            $this->loop
        );
    }

    private function setupConnection(): void
    {
        $path = "/api/v1/apps/{$this->appUuid}/events?api_key={$this->token}";

        $this->info("Подключение к WebSocket серверу: {$this->host}:{$this->port}{$path} (Worker: {$this->workerId})");

        $this->connection = new WebSocketConnection(
            $this->host,
            $this->port,
            $path,
            $this->loop,
            [$this, 'handleMessage'],
            [$this, 'handleClose'],
            [$this, 'handleError']
        );

        // Обновляем соединения в обработчиках
        $this->updateHandlersConnection();

        $this->connection->connect();
    }

    private function updateHandlersConnection(): void
    {
        foreach ($this->webSocketHandlerManager->getHandlers() as $handler) {
            if (method_exists($handler, 'setConnection')) {
                $handler->setConnection($this->connection);
            }
        }
        
        foreach ($this->eventHandlerManager->getHandlers() as $handler) {
            if (method_exists($handler, 'setConnection')) {
                $handler->setConnection($this->connection);
            }
        }
    }

    private function setupEventProcessor(): void
    {
        $this->eventProcessor = new EventProcessor($this->eventHandlerManager, $this->loop, $this->redisChannel, $this->workerId);
        
        // Запускаем обработку событий
        $this->loop->addTimer(0.1, function () {
            try {
                $this->info("Запуск обработки событий из Redis канала: {$this->redisChannel} (Worker: {$this->workerId})");
                $this->eventProcessor->start();
            } catch (\Exception $e) {
                $this->warn("Не удалось запустить обработку событий Redis: " . $e->getMessage());
                $this->warn("Daemon продолжит работу без обработки событий из Redis");
            }
        });
    }

    public function handleMessage(string $payload): void
    {
        try {
            $data = json_decode($payload, true);

            if (!$data) {
                $this->warn("Невалидное JSON сообщение: {$payload}");
                return;
            }

            $this->info("Получено сообщение: " . json_encode($data));
            $this->webSocketHandlerManager->handle($data);

        } catch (\Exception $e) {
            $this->error("Ошибка обработки сообщения: " . $e->getMessage());
            Log::error($e);
        }
    }

    public function handleClose(): void
    {
        $this->info('Соединение закрыто');
        $this->reconnect();
    }

    public function handleError(\Exception $e): void
    {
        $this->error('Ошибка соединения: ' . $e->getMessage());
        $this->reconnect();
    }

    private function reconnect(): void
    {
        // Добавляем случайную задержку для избежания одновременных переподключений
        $delay = 5 + rand(0, 5); // 5-10 секунд
        $this->warn("Переподключение через {$delay} секунд (Worker: {$this->workerId})");

        $this->loop->addTimer($delay, function () {
            $this->setupConnection();
        });
    }
} 
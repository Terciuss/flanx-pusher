<?php

namespace Terciuss\FlanxPusher\Events;

use Terciuss\FlanxPusher\Handlers\MessageHandlerManager;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use React\EventLoop\LoopInterface;

class EventProcessor
{
    private MessageHandlerManager $handlerManager;
    private string $redisChannel;
    private string $workerId;
    private bool $isRunning = false;
    private LoopInterface $loop;
    private $timer;
    private $redisConnection;

    public function __construct(MessageHandlerManager $handlerManager, LoopInterface $loop, string $redisChannel = 'websocket-events', string $workerId = '')
    {
        $this->handlerManager = $handlerManager;
        $this->loop = $loop;
        $this->redisChannel = $redisChannel;
        $this->workerId = $workerId;
    }

    /**
     * Запускает обработку событий из Redis с использованием pub/sub
     */
    public function start(): void
    {
        if ($this->isRunning) {
            return;
        }

        try {
            $this->isRunning = true;
            $this->redisConnection = Redis::connection();
            
            $workerInfo = $this->workerId ? " (Worker: {$this->workerId})" : '';
            Log::info("Запуск обработки событий Redis через pub/sub канал: {$this->redisChannel}{$workerInfo}");
            
            // Используем таймер для периодической проверки Redis pub/sub
            $this->timer = $this->loop->addPeriodicTimer(0.1, function () {
                $this->checkRedisPubSub();
            });
            
        } catch (\Exception $e) {
            $workerInfo = $this->workerId ? " (Worker: {$this->workerId})" : '';
            Log::error("Ошибка запуска обработки событий Redis{$workerInfo}: " . $e->getMessage());
            $this->isRunning = false;
        }
    }

    /**
     * Проверяет события в Redis через pub/sub
     */
    private function checkRedisPubSub(): void
    {
        try {
            // Используем BRPOP для атомарного получения сообщения из очереди
            // Это гарантирует, что только один воркер получит каждое сообщение
            $result = $this->redisConnection->brpop($this->redisChannel . ':queue', 0.1);
            
            if ($result && isset($result[1])) {
                $this->processEvent($result[1]);
            }
            
        } catch (\Exception $e) {
            $workerInfo = $this->workerId ? " (Worker: {$this->workerId})" : '';
            Log::error("Ошибка проверки событий Redis pub/sub{$workerInfo}: " . $e->getMessage());
        }
    }

    /**
     * Обрабатывает событие из Redis
     */
    private function processEvent(string $message): void
    {
        try {
            $data = json_decode($message, true);
            
            if (!$data) {
                $workerInfo = $this->workerId ? " (Worker: {$this->workerId})" : '';
                Log::warning("Невалидное JSON сообщение из Redis{$workerInfo}: " . $message);
                return;
            }

            // Обрабатываем событие через менеджер обработчиков
            $this->handlerManager->handle($data);

        } catch (\Exception $e) {
            $workerInfo = $this->workerId ? " (Worker: {$this->workerId})" : '';
            Log::error("Ошибка обработки события из Redis{$workerInfo}: " . $e->getMessage());
        }
    }

    /**
     * Отправляет событие в Redis через pub/sub
     */
    public static function publishEvent(array $data): void
    {
        try {
            $redis = Redis::connection();
            $jsonData = json_encode($data);
            
            // Отправляем событие в очередь для обработки воркерами
            $redis->lpush('websocket-events:queue', $jsonData);
        } catch (\Exception $e) {
            Log::error('Ошибка отправки события в Redis: ' . $e->getMessage());
        }
    }

    /**
     * Останавливает обработку событий
     */
    public function stop(): void
    {
        $this->isRunning = false;
        
        if ($this->timer) {
            $this->loop->cancelTimer($this->timer);
        }
    }

    /**
     * Проверяет, запущен ли процесс
     */
    public function isRunning(): bool
    {
        return $this->isRunning;
    }
} 
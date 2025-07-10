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
    private bool $isRunning = false;
    private LoopInterface $loop;
    private $timer;

    public function __construct(MessageHandlerManager $handlerManager, LoopInterface $loop, string $redisChannel = 'websocket-events')
    {
        $this->handlerManager = $handlerManager;
        $this->loop = $loop;
        $this->redisChannel = $redisChannel;
    }

    /**
     * Запускает обработку событий из Redis с использованием таймеров
     */
    public function start(): void
    {
        if ($this->isRunning) {
            return;
        }

        try {
            Log::info('Запуск обработки событий из Redis канала: ' . $this->redisChannel);
            
            $this->isRunning = true;
            
            // Используем таймер для периодической проверки Redis
            $this->timer = $this->loop->addPeriodicTimer(1, function () {
                $this->checkRedisEvents();
            });
            
        } catch (\Exception $e) {
            Log::error('Ошибка запуска обработки событий Redis: ' . $e->getMessage());
            $this->isRunning = false;
        }
    }

    /**
     * Проверяет события в Redis
     */
    private function checkRedisEvents(): void
    {
        try {
            $redis = Redis::connection();
            
            // Используем неблокирующий подход - проверяем последние сообщения
            $messages = $redis->lrange($this->redisChannel . ':queue', 0, 10);
            
            foreach ($messages as $message) {
                $this->processEvent($message);
                // Удаляем обработанное сообщение
                $redis->lrem($this->redisChannel . ':queue', 0, $message);
            }
            
        } catch (\Exception $e) {
            Log::error('Ошибка проверки событий Redis: ' . $e->getMessage());
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
                Log::warning('Невалидное JSON сообщение из Redis: ' . $message);
                return;
            }

            Log::info('Получено событие из Redis: ' . json_encode($data));
            
            // Обрабатываем событие через менеджер обработчиков
            $this->handlerManager->handle($data);

        } catch (\Exception $e) {
            Log::error('Ошибка обработки события из Redis: ' . $e->getMessage());
        }
    }

    /**
     * Отправляет событие в Redis
     */
    public static function publishEvent(array $data): void
    {
        try {
            $redis = Redis::connection();
            
            // Сохраняем событие в список для обработки
            $redis->lpush('websocket-events:queue', json_encode($data));
            
            Log::info('Событие отправлено в Redis: ' . json_encode($data));
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
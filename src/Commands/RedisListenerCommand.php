<?php

namespace Terciuss\FlanxPusher\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

class RedisListenerCommand extends Command
{
    protected $signature = 'websocket:redis-listener {channel=websocket-events}';
    protected $description = 'Слушатель Redis событий для WebSocket Daemon';

    public function handle()
    {
        $channel = $this->argument('channel');
        
        $this->info("Запуск Redis слушателя для канала: {$channel}");
        
        try {
            $redis = Redis::connection();
            
            $this->info("Подключение к Redis каналу: {$channel}");
            
            // Подписываемся на канал
            $redis->subscribe([$channel], function ($message) {
                $this->processMessage($message);
            });
            
        } catch (\Exception $e) {
            $this->error("Ошибка подключения к Redis: " . $e->getMessage());
            return 1;
        }
    }

    private function processMessage(string $message): void
    {
        try {
            $data = json_decode($message, true);
            
            if (!$data) {
                $this->warn("Невалидное JSON сообщение: {$message}");
                return;
            }

            $this->info("Получено событие: " . json_encode($data));
            
            // Здесь можно добавить логику обработки события
            // или отправить его в основной процесс через IPC
            
        } catch (\Exception $e) {
            $this->error("Ошибка обработки сообщения: " . $e->getMessage());
        }
    }
} 
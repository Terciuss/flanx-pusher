# Flanx Pusher Package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/terciuss/flanx-pusher.svg)](https://packagist.org/packages/terciuss/flanx-pusher)
[![Total Downloads](https://img.shields.io/packagist/dt/terciuss/flanx-pusher.svg)](https://packagist.org/packages/terciuss/flanx-pusher)
[![License](https://img.shields.io/packagist/l/terciuss/flanx-pusher.svg)](https://packagist.org/packages/terciuss/flanx-pusher)
[![Tests](https://github.com/Terciuss/flanx-pusher/actions/workflows/tests.yml/badge.svg)](https://github.com/Terciuss/flanx-pusher/actions/workflows/tests.yml)

Пакет для работы с WebSocket соединениями в Laravel приложениях с поддержкой Pusher-совместимого протокола. Предоставляет мощную систему для создания real-time приложений с автоматической обработкой событий и масштабируемой архитектурой.

## 🚀 Возможности

- ✅ **WebSocket Daemon** - Автономный процесс для обработки WebSocket соединений
- ✅ **Pusher-совместимый протокол** - Полная совместимость с Pusher API
- ✅ **Система обработчиков сообщений** - Гибкая архитектура для обработки различных типов событий
- ✅ **Автоматическое переподключение** - Устойчивость к сбоям сети
- ✅ **Интеграция с Laravel** - Полная интеграция через Service Provider
- ✅ **Настраиваемое логирование** - Детальное логирование для отладки
- ✅ **Команды Artisan** - Удобные команды для управления
- ✅ **Redis интеграция** - Использование Redis для очередей событий
- ✅ **Event-driven архитектура** - Обработка событий через Laravel Events
- ✅ **Тестирование** - Полное покрытие тестами

## 🔗 Связанные проекты

- **[flanx-pusher-client](https://www.npmjs.com/package/flanx-pusher-client)** - Клиентская библиотека для JavaScript/TypeScript с поддержкой WebSocket соединений и Pusher-совместимого протокола.

## 📦 Установка

### Через Composer

```bash
composer require terciuss/flanx-pusher
```

### Публикация конфигурации

```bash
php artisan vendor:publish --tag=websocket-daemon-config
```



## 🔧 Использование

### Запуск WebSocket Daemon

```bash
php artisan websocket:daemon --app-uuid=your-app-uuid --token=your-token
```

Daemon автоматически обрабатывает WebSocket соединения и события из Redis очереди.

### Параметры команды

| Параметр | Описание | Обязательный | По умолчанию |
|----------|----------|--------------|--------------|
| `--app-uuid` | UUID приложения | Да | - |
| `--token` | Токен для аутентификации | Да | - |
| `--host` | Хост WebSocket сервера | Нет | localhost |
| `--port` | Порт WebSocket сервера | Нет | 6001 |
| `--daemon` | Запуск в фоновом режиме | Нет | false |



## ⚙️ Конфигурация

Настройки пакета находятся в файле `config/websocket-daemon.php`:

```php
return [
    'defaults' => [
        'host' => env('WEBSOCKET_HOST', 'localhost'),
        'port' => env('WEBSOCKET_PORT', 6001),
        'reconnect_delay' => env('WEBSOCKET_RECONNECT_DELAY', 5),
    ],
    
    'logging' => [
        'enabled' => env('WEBSOCKET_LOGGING_ENABLED', true),
        'level' => env('WEBSOCKET_LOG_LEVEL', 'info'),
    ],
    
    'handlers' => [
        // 'message.sent' => MessageSentHandler::class,
    ],
    
    'event_handlers' => [
        '*' => \Terciuss\FlanxPusher\Handlers\DefaultEventHandler::class,
    ],
];
```

### Переменные окружения

```env
WEBSOCKET_HOST=localhost
WEBSOCKET_PORT=6001
WEBSOCKET_RECONNECT_DELAY=5
WEBSOCKET_LOGGING_ENABLED=true
WEBSOCKET_LOG_LEVEL=info
```

## 🏗️ Архитектура

### Основные компоненты

#### 1. WebSocketConnection
Управляет WebSocket соединением, обрабатывает фреймы и обеспечивает стабильное соединение.

#### 2. MessageHandlerManager
Центральный менеджер для обработки различных типов сообщений через систему обработчиков.

#### 3. EventProcessor
Обрабатывает события из Redis и распределяет их по соответствующим обработчикам.

#### 4. Handlers
Специализированные классы для обработки конкретных типов сообщений.

### Создание собственного обработчика

```php
<?php

namespace App\Handlers;

use Terciuss\FlanxPusher\Handlers\AbstractHandler;
use Terciuss\FlanxPusher\Contracts\WebSocketConnectionInterface;

class CustomEventHandler extends AbstractHandler
{
    public function handle(array $data): void
    {
        // Ваша логика обработки
        $this->connection->send([
            'type' => 'custom.event',
            'data' => $data,
            'timestamp' => now()->toISOString(),
        ]);
    }

    public function canHandle(array $data): bool
    {
        return isset($data['type']) && $data['type'] === 'custom.event';
    }
}
```

### Регистрация обработчика

```php
// В конфигурации
'handlers' => [
    'custom.event' => \App\Handlers\CustomEventHandler::class,
],

// Или программно
$manager = app(MessageHandlerManager::class);
$manager->addHandler('custom.event', new CustomEventHandler($connection));
```

## 📡 Отправка событий

### Через EventProcessor

```php
use Terciuss\FlanxPusher\Events\EventProcessor;

EventProcessor::publishEvent([
    'type' => 'user.message',
    'data' => [
        'user_id' => 123,
        'message' => 'Hello World!',
    ],
    'channel' => 'user.123',
]);
```

### Через Laravel Events

```php
<?php

namespace App\Events;

use Terciuss\FlanxPusher\Contracts\WebSocketEventInterface;

class UserMessageSent implements WebSocketEventInterface
{
    public function __construct(
        public int $userId,
        public string $message
    ) {}

    public function broadcastOn()
    {
        return ["user.{$this->userId}"];
    }

    public function broadcastWith()
    {
        return [
            'user_id' => $this->userId,
            'message' => $this->message,
            'timestamp' => now()->toISOString(),
        ];
    }

    public function broadcastAs()
    {
        return 'user.message';
    }

    public function getUserId($channel)
    {
        return $this->userId;
    }
}
```

## 🧪 Тестирование

### Запуск тестов

```bash
composer test
```

### Покрытие кода

```bash
composer test -- --coverage-html coverage/
```

### Структура тестов

- `tests/Unit/` - Модульные тесты
- `tests/Feature/` - Функциональные тесты
- `tests/Integration/` - Интеграционные тесты

## 🔍 Отладка

### Логирование

```php
// Включение детального логирования
Log::channel('websocket')->info('WebSocket event processed', $data);
```

### Мониторинг

```bash
# Просмотр логов
tail -f storage/logs/websocket.log

# Проверка статуса daemon
ps aux | grep websocket:daemon
```

## 📋 Требования

- **PHP** >= 8.1
- **Laravel** >= 10.0
- **Redis** (для очередей событий)
- **ReactPHP** (для асинхронной обработки)

## 🤝 Вклад в проект

1. Fork репозитория
2. Создайте feature branch (`git checkout -b feature/amazing-feature`)
3. Commit изменения (`git commit -m 'Add amazing feature'`)
4. Push в branch (`git push origin feature/amazing-feature`)
5. Откройте Pull Request

## 📄 Лицензия

MIT License. Смотрите файл [LICENSE](LICENSE) для получения дополнительной информации.

## 👨‍💻 Автор

**Pavel Terciuss**

- GitHub: [@Terciuss](https://github.com/Terciuss)
- Email: mr.terks@yandex.ru
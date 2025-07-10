# Flanx Pusher Package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/terciuss/flanx-pusher.svg)](https://packagist.org/packages/terciuss/flanx-pusher)
[![Total Downloads](https://img.shields.io/packagist/dt/terciuss/flanx-pusher.svg)](https://packagist.org/packages/terciuss/flanx-pusher)
[![License](https://img.shields.io/packagist/l/terciuss/flanx-pusher.svg)](https://packagist.org/packages/terciuss/flanx-pusher)

Пакет для работы с WebSocket соединениями в Laravel приложениях с поддержкой Pusher-совместимого протокола.

## Возможности

- ✅ WebSocket daemon для Laravel
- ✅ Поддержка Pusher-совместимого протокола
- ✅ Автоматическое переподключение
- ✅ Система обработчиков сообщений
- ✅ Интеграция с Laravel Service Provider
- ✅ Настраиваемое логирование
- ✅ Поддержка команд Artisan

## Установка

### Через Composer

```bash
composer require terciuss/flanx-pusher
```

### Публикация конфигурации

```bash
php artisan vendor:publish --tag=websocket-daemon-config
```

## Использование

### Запуск Daemon

```bash
php artisan websocket:daemon --app-uuid=your-app-uuid --token=your-token
```

### Параметры команды

- `--app-uuid` - UUID приложения (обязательный)
- `--token` - Токен для аутентификации (обязательный)
- `--host` - Хост WebSocket сервера (по умолчанию: localhost)
- `--port` - Порт WebSocket сервера (по умолчанию: 6001)

### Конфигурация

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
        'message.sent' => \Terciuss\FlanxPusher\Handlers\MessageSentHandler::class,
    ],
];
```

## Архитектура

### Компоненты

1. **WebSocketConnection** - Управляет WebSocket соединением
2. **WebSocketFrame** - Обрабатывает WebSocket фреймы
3. **MessageHandlerManager** - Управляет обработчиками сообщений
4. **Handlers** - Обработчики различных типов сообщений

### Создание собственного обработчика

```php
<?php

namespace App\Handlers;

use Terciuss\FlanxPusher\Contracts\MessageHandlerInterface;

class CustomHandler implements MessageHandlerInterface
{

    public function handle(array $data): void
    {
        // Ваша логика обработки
    }

    public function canHandle(array $data): bool
    {
        return isset($data['type']) && $data['type'] === 'custom.type';
    }
}
```

## Требования

- PHP >= 8.1
- Laravel >= 10.0

## Автор

**Pavel Terciuss**

- GitHub: [@Terciuss](https://github.com/Terciuss)
- Email: mr.terks@yandex.ru

## Связанные проекты

- **[flanx-pusher-client](https://www.npmjs.com/package/flanx-pusher-client)** - Клиентская библиотека для JavaScript/TypeScript с поддержкой WebSocket соединений и Pusher-совместимого протокола.

## Лицензия

MIT License. Смотрите файл [LICENSE](LICENSE) для получения дополнительной информации. 
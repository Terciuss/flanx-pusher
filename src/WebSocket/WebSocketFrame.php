<?php

namespace Terciuss\FlanxPusher\WebSocket;

class WebSocketFrame
{
    private const OPCODE_TEXT = 0x1;
    private const OPCODE_CLOSE = 0x8;
    private const OPCODE_PING = 0x9;
    private const OPCODE_PONG = 0xA;

    /**
     * Закодировать данные в WebSocket фрейм
     *
     * @param string $payload
     * @return string
     */
    public static function encode(string $payload): string
    {
        $length = strlen($payload);
        $frame = chr(129); // FIN + opcode (text frame)

        if ($length <= 125) {
            $frame .= chr(128 | $length); // MASK + length
        } elseif ($length <= 65535) {
            $frame .= chr(128 | 126) . pack('n', $length); // MASK + 126 + length
        } else {
            $frame .= chr(128 | 127) . pack('J', $length); // MASK + 127 + length
        }

        // Генерируем маску (4 байта)
        $mask = random_bytes(4);
        $frame .= $mask;

        // Применяем маску к payload
        $maskedPayload = '';
        for ($i = 0; $i < $length; $i++) {
            $maskedPayload .= chr(ord($payload[$i]) ^ ord($mask[$i % 4]));
        }

        return $frame . $maskedPayload;
    }

    /**
     * Декодировать WebSocket фрейм
     *
     * @param string $data
     * @return array|null
     */
    public static function decode(string $data): ?array
    {
        if (strlen($data) < 2) {
            return null;
        }

        $opcode = ord($data[0]) & 0x0F;
        $masked = (ord($data[1]) & 0x80) !== 0;
        $length = ord($data[1]) & 0x7F;
        $offset = 2;

        if ($length === 126) {
            if (strlen($data) < 4) {
                return null;
            }
            $length = unpack('n', substr($data, 2, 2))[1];
            $offset = 4;
        } elseif ($length === 127) {
            if (strlen($data) < 10) {
                return null;
            }
            $length = unpack('J', substr($data, 2, 8))[1];
            $offset = 10;
        }

        // Обработка маски
        $mask = null;
        if ($masked) {
            if (strlen($data) < $offset + 4) {
                return null;
            }
            $mask = substr($data, $offset, 4);
            $offset += 4;
        }

        if (strlen($data) < $offset + $length) {
            return null;
        }

        $payload = substr($data, $offset, $length);

        // Применяем маску если она есть
        if ($masked && $mask) {
            $unmasked = '';
            for ($i = 0; $i < $length; $i++) {
                $unmasked .= chr(ord($payload[$i]) ^ ord($mask[$i % 4]));
            }
            $payload = $unmasked;
        }

        return [
            'opcode' => $opcode,
            'payload' => $payload,
            'length' => $length
        ];
    }

    /**
     * Создать PONG фрейм
     *
     * @return string
     */
    public static function createPong(): string
    {
        return chr(138) . chr(0); // Pong frame
    }

    /**
     * Проверить, является ли фрейм текстовым
     *
     * @param int $opcode
     * @return bool
     */
    public static function isTextFrame(int $opcode): bool
    {
        return $opcode === self::OPCODE_TEXT;
    }

    /**
     * Проверить, является ли фрейм закрывающим
     *
     * @param int $opcode
     * @return bool
     */
    public static function isCloseFrame(int $opcode): bool
    {
        return $opcode === self::OPCODE_CLOSE;
    }

    /**
     * Проверить, является ли фрейм PING
     *
     * @param int $opcode
     * @return bool
     */
    public static function isPingFrame(int $opcode): bool
    {
        return $opcode === self::OPCODE_PING;
    }
} 
<?php

namespace Terciuss\FlanxPusher\Tests\Unit\WebSocket;

use Terciuss\FlanxPusher\WebSocket\WebSocketFrame;
use Terciuss\FlanxPusher\Tests\TestCase;

class WebSocketFrameTest extends TestCase
{
    public function testEncodeTextFrame()
    {
        $payload = 'Hello World';
        $encoded = WebSocketFrame::encode($payload);
        
        $this->assertIsString($encoded);
        $this->assertNotEmpty($encoded);
        
        // Проверяем, что закодированный фрейм можно декодировать
        $decoded = WebSocketFrame::decode($encoded);
        $this->assertIsArray($decoded);
        $this->assertEquals($payload, $decoded['payload']);
        $this->assertEquals(0x1, $decoded['opcode']); // Text frame
    }

    public function testDecodeTextFrame()
    {
        $payload = 'Test Message';
        $encoded = WebSocketFrame::encode($payload);
        $decoded = WebSocketFrame::decode($encoded);
        
        $this->assertEquals($payload, $decoded['payload']);
        $this->assertEquals(0x1, $decoded['opcode']);
        $this->assertArrayHasKey('length', $decoded);
    }

    public function testDecodeCloseFrame()
    {
        $closeFrame = "\x88\x00"; // Close frame
        $decoded = WebSocketFrame::decode($closeFrame);
        
        $this->assertEquals(0x8, $decoded['opcode']);
        $this->assertArrayHasKey('length', $decoded);
    }

    public function testDecodePingFrame()
    {
        $pingFrame = "\x89\x00"; // Ping frame
        $decoded = WebSocketFrame::decode($pingFrame);
        
        $this->assertEquals(0x9, $decoded['opcode']);
        $this->assertArrayHasKey('length', $decoded);
    }

    public function testDecodePongFrame()
    {
        $pongFrame = "\x8A\x00"; // Pong frame
        $decoded = WebSocketFrame::decode($pongFrame);
        
        $this->assertEquals(0xA, $decoded['opcode']);
        $this->assertArrayHasKey('length', $decoded);
    }

    public function testFrameTypeChecks()
    {
        $this->assertTrue(WebSocketFrame::isTextFrame(0x1));
        $this->assertFalse(WebSocketFrame::isTextFrame(0x2));
        $this->assertFalse(WebSocketFrame::isTextFrame(0x8));
        
        $this->assertTrue(WebSocketFrame::isCloseFrame(0x8));
        $this->assertFalse(WebSocketFrame::isCloseFrame(0x1));
        $this->assertFalse(WebSocketFrame::isCloseFrame(0x9));
        
        $this->assertTrue(WebSocketFrame::isPingFrame(0x9));
        $this->assertFalse(WebSocketFrame::isPingFrame(0x1));
        $this->assertFalse(WebSocketFrame::isPingFrame(0x8));
    }

    public function testCreatePong()
    {
        $pongFrame = WebSocketFrame::createPong();
        
        $this->assertIsString($pongFrame);
        $this->assertEquals("\x8A\x00", $pongFrame);
    }

    public function testDecodeInvalidFrame()
    {
        $invalidFrame = "invalid";
        $result = WebSocketFrame::decode($invalidFrame);
        
        $this->assertNull($result);
    }

    public function testEncodeEmptyString()
    {
        $encoded = WebSocketFrame::encode('');
        $decoded = WebSocketFrame::decode($encoded);
        
        $this->assertEquals('', $decoded['payload']);
    }

    public function testEncodeLongString()
    {
        $longPayload = str_repeat('A', 1000);
        $encoded = WebSocketFrame::encode($longPayload);
        $decoded = WebSocketFrame::decode($encoded);
        
        $this->assertEquals($longPayload, $decoded['payload']);
    }
} 
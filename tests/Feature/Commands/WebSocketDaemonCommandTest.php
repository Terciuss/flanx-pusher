<?php

namespace Terciuss\FlanxPusher\Tests\Feature\Commands;

use Terciuss\FlanxPusher\Tests\TestCase;
use Terciuss\FlanxPusher\Commands\WebSocketDaemonCommand;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WebSocketDaemonCommandTest extends TestCase
{
    use RefreshDatabase;

    public function testCommandExists()
    {
        $command = new WebSocketDaemonCommand();
        $this->assertInstanceOf(WebSocketDaemonCommand::class, $command);
    }
} 
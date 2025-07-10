<?php

namespace Terciuss\FlanxPusher\Tests\Feature\ServiceProvider;

use Terciuss\FlanxPusher\Tests\TestCase;
use Terciuss\FlanxPusher\FlanxPusherServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FlanxPusherServiceProviderTest extends TestCase
{
    use RefreshDatabase;

    public function testServiceProviderExists()
    {
        $provider = new FlanxPusherServiceProvider($this->app);
        $this->assertInstanceOf(FlanxPusherServiceProvider::class, $provider);
    }

    public function testServiceProviderMethods()
    {
        $provider = new FlanxPusherServiceProvider($this->app);
        
        // Не должно вызывать исключение
        $provider->boot();
        $provider->register();
        $this->assertTrue(true);
    }
} 
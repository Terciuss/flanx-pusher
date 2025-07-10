<?php

namespace Terciuss\FlanxPusher\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Terciuss\FlanxPusher\FlanxPusherServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            FlanxPusherServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Настройка тестовой базы данных
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Настройка Redis для тестов
        $app['config']->set('redis.default', 'testing');
        $app['config']->set('redis.connections.testing', [
            'host' => '127.0.0.1',
            'password' => null,
            'port' => 6379,
            'database' => 1,
        ]);

        // Настройка WebSocket для тестов
        $app['config']->set('websocket-daemon.defaults.host', 'localhost');
        $app['config']->set('websocket-daemon.defaults.port', 6001);
        $app['config']->set('websocket-daemon.logging.enabled', false);
    }
} 
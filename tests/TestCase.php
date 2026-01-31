<?php

namespace C14r\DataStore\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use C14r\DataStore\DataStoreServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    protected function getPackageProviders($app): array
    {
        return [
            DataStoreServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'DataStore' => \C14r\DataStore\Facades\DataStore::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }
}

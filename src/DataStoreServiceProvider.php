<?php

namespace C14r\DataStore;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use C14r\DataStore\Services\StorageService;
use C14r\DataStore\Console\Commands\CleanupExpiredDataStore;

class DataStoreServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge config
        $this->mergeConfigFrom(
            __DIR__.'/../config/datastore.php', 'datastore'
        );

        // Register DataStore Facade (global, no storable, no namespace by default)
        $this->app->singleton('datastore', function ($app) {
            return new StorageService(null, null);
        });

        // Register generic StorageService
        $this->app->bind(StorageService::class, function ($app) {
            // Check if user is authenticated
            $user = Auth::user();
            
            if (!$user) {
                // Return global instance if no user
                return new StorageService(null, config('datastore.default_namespace'));
            }

            return new StorageService($user, config('datastore.default_namespace'));
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load helpers
        require_once __DIR__ . '/helpers.php';
        
        // Publish config
        $this->publishes([
            __DIR__.'/../config/datastore.php' => config_path('datastore.php'),
        ], 'datastore-config');

        // Publish migrations
        $this->publishes([
            __DIR__.'/../database/migrations/create_data_stores_table.php.stub' => database_path('migrations/'.date('Y_m_d_His', time()).'_create_data_stores_table.php'),
        ], 'datastore-migrations');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                CleanupExpiredDataStore::class,
            ]);
        }
    }
}

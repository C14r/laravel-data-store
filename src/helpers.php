<?php

use C14r\DataStore\Services\StorageService;
use C14r\DataStore\Facades\DataStore;

if (!function_exists('datastore')) {
    /**
     * Get DataStore instance with optional namespace.
     *
     * @param string|array|null $namespace
     * @return StorageService
     */
    function datastore(string|array|null $namespace = null): StorageService
    {
        if ($namespace === null) {
            return app('datastore');
        }
        
        return DataStore::inNamespace($namespace);
    }
}

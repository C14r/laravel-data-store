<?php

namespace C14r\DataStore\Traits;

use C14r\DataStore\Services\StorageService;
use C14r\DataStore\Models\DataStore;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasDataStore
{
    /**
     * Get DataStore service scoped to this model.
     *
     * @param string|array|null $namespace
     * @return StorageService
     */
    public function dataStore(string|array|null $namespace = null): StorageService
    {
        return app(StorageService::class)->for($this)->inNamespace($namespace);
    }

    /**
     * Get all data store entries for this model.
     *
     * @return MorphMany
     */
    public function dataStores(): MorphMany
    {
        return $this->morphMany(DataStore::class, 'storable');
    }

    /**
     * Quick set value in data store.
     *
     * @param string|array $key
     * @param mixed $value
     * @param string|array|null $namespace
     * @param int|null $ttl
     * @return DataStore
     */
    public function storeData(string|array $key, mixed $value, string|array|null $namespace = null, ?int $ttl = null): DataStore
    {
        return $this->dataStore($namespace)->set($key, $value, $ttl);
    }

    /**
     * Quick get value from data store.
     *
     * @param string|array $key
     * @param mixed $default
     * @param string|array|null $namespace
     * @param string|null $as
     * @return mixed
     */
    public function retrieveData(string|array $key, mixed $default = null, string|array|null $namespace = null, ?string $as = null): mixed
    {
        return $this->dataStore($namespace)->get($key, $default, $as);
    }

    /**
     * Check if key exists in data store.
     *
     * @param string|array $key
     * @param string|array|null $namespace
     * @return bool
     */
    public function hasData(string|array $key, string|array|null $namespace = null): bool
    {
        return $this->dataStore($namespace)->has($key);
    }

    /**
     * Delete key from data store.
     *
     * @param string|array $key
     * @param string|array|null $namespace
     * @return bool
     */
    public function deleteData(string|array $key, string|array|null $namespace = null): bool
    {
        return $this->dataStore($namespace)->delete($key);
    }

    /**
     * Clear all data for this model in namespace.
     *
     * @param string|array|null $namespace
     * @return int
     */
    public function clearData(string|array|null $namespace = null): int
    {
        return $this->dataStore($namespace)->clear();
    }
}

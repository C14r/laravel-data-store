<?php

namespace C14r\DataStore\Services;

use C14r\DataStore\Models\DataStore;
use C14r\DataStore\Events\DataStoreSet;
use C14r\DataStore\Events\DataStoreUpdated;
use C14r\DataStore\Events\DataStoreDeleted;
use C14r\DataStore\Events\DataStoreCleared;
use C14r\DataStore\Query\DataStoreQueryBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class StorageService
{
    protected ?Model $storable = null;
    protected ?string $namespace = null;

    /**
     * Constructor.
     */
    public function __construct(?Model $storable = null, ?string $namespace = null)
    {
        if ($storable) {
            $this->storable = $storable;
        }

        $this->namespace = $this->normalizeNamespace($namespace);
    }

    /**
     * Set the storable to a user model.
     */
    public function forUser(?Model $user = null): static
    {
        if ($user === null) {
            $user = Auth::user();

            if (!$user) {
                throw new \RuntimeException('No authenticated user found.');
            }

        }
        $userInstance = $user instanceof Model ? $user : app(config('auth.providers.users.model'))->findOrFail($user);

        return new static($userInstance, $this->namespace);
    }

    /**
     * Set the storable to a group model.
     */
    public function forGroup(Model $group): static
    {
        return $this->for($group);
    }

    /**
     * Set the storable to a team model.
     */
    public function forTeam(Model $team): static
    {
        return $this->for($team);
    }

    /**
     * Set the storable to an organization model.
     */
    public function forOrganization(Model $organization): static
    {
        return $this->for($organization);
    }

    /**
     * Set the storable to any model.
     */
    public function for(Model $model): static
    {
        return new static($model, $this->namespace);
    }

    /**
     * Set the namespace.
     */
    public function inNamespace(string|array $namespace): static
    {
        return new static($this->storable, $this->normalizeNamespace($namespace));
    }

    /**
     * Normalize namespace input.
     */
    protected function normalizeNamespace(string|array $namespace): ?string
    {
        if ($namespace === null || $namespace === '') return null;
        if (is_array($namespace)) return implode('.', array_filter($namespace));
        return $namespace;
    }

    /**
     * Normalize key input.
     */
    protected function normalizeKey(string|array $key): string
    {
        if (is_array($key)) return implode('.', array_filter($key));
        return $key;
    }

    /**
     * Set a value.
     */
    public function set(string|array $key, mixed $value, ?int $ttlSeconds = null): DataStore
    {
        $key = $this->normalizeKey($key);
        $expiresAt = $ttlSeconds ? now()->addSeconds($ttlSeconds) : null;
        
        // Auto-convert Spatie Data objects to array
        if (is_object($value) && method_exists($value, 'toArray') && is_a($value, 'Spatie\LaravelData\Data')) {
            $value = $value->toArray();
        }

        // Check if exists for update event
        $existing = $this->buildBaseQuery()->where('key', $key)->first();
        
        $dataStore = DataStore::updateOrCreate(
            [
                'storable_type' => $this->getStorableType(),
                'storable_id' => $this->getStorableId(),
                'namespace' => $this->namespace,
                'key' => $key,
            ],
            [
                'value' => $value,
                'expires_at' => $expiresAt,
            ]
        );

        // Dispatch events
        if ($existing) {
            event(new DataStoreUpdated(
                $this->getStorableType(),
                $this->getStorableId(),
                $this->namespace,
                $key,
                $existing->value,
                $value
            ));
        } else {
            event(new DataStoreSet(
                $this->getStorableType(),
                $this->getStorableId(),
                $this->namespace,
                $key,
                $value,
                $ttlSeconds
            ));
        }

        return $dataStore;
    }

    /**
     * Get a value.
     */
    public function get(string|array $key, mixed $default = null, ?string $as = null): mixed
    {
        $key = $this->normalizeKey($key);
        $storage = $this->buildBaseQuery()->where('key', $key)->notExpired()->first();
        $value = $storage?->value ?? $default;
        
        // Auto-convert to Spatie Data object if 'as' parameter provided
        if ($as !== null && $value !== null && $value !== $default) {
            if (class_exists($as) && method_exists($as, 'from')) {
                try {
                    return $as::from($value);
                } catch (\Throwable $e) {
                    return $value;
                }
            }
        }
        
        return $value;
    }

    /**
     * Get value as Spatie Data object (convenience method).
     */
    public function data(string|array $key, string $class, mixed $default = null): mixed
    {
        return $this->get($key, $default, $class);
    }

    /**
     * Get collection of Spatie Data objects.
     */
    public function collection(string|array $key, string $class, mixed $default = []): mixed
    {
        $value = $this->get($key, $default);
        
        if ($value === $default) {
            return $default;
        }

        // Check if Spatie Data class has collection method
        if (class_exists($class) && method_exists($class, 'collection')) {
            try {
                return $class::collection($value);
            } catch (\Throwable $e) {
                return $value;
            }
        }

        return $value;
    }

    /**
     * Check if a key exists.
     */
    public function has(string|array $key): bool
    {
        $key = $this->normalizeKey($key);
        
        return $this->buildBaseQuery()->where('key', $key)->notExpired()->exists();
    }

    /**
     * Delete a key.
     */
    public function delete(string|array $key): bool
    {
        $key = $this->normalizeKey($key);
        $storage = $this->buildBaseQuery()->where('key', $key)->first();
        
        if (!$storage) {
            return false;
        }

        $deleted = $storage->delete();

        if ($deleted) {
            event(new DataStoreDeleted(
                $this->getStorableType(),
                $this->getStorableId(),
                $this->namespace,
                $key,
                $storage->value
            ));
        }

        return $deleted;
    }

    /**
     * Get all keys.
     */
    public function keys(): Collection
    {
        return $this->buildBaseQuery()->notExpired()->pluck('key');
    }

    /**
     * Get all key-value pairs.
     */
    public function all(): Collection
    {
        return $this->buildBaseQuery()->notExpired()->get()->mapWithKeys(fn($item) => [$item->key => $item->value]);
    }

    /**
     * Clear all entries in the current scope.
     */
    public function clear(): int
    {
        $count = $this->buildBaseQuery()->count();
        $deleted = $this->buildBaseQuery()->delete();

        if ($deleted > 0) {
            event(new DataStoreCleared(
                $this->getStorableType(),
                $this->getStorableId(),
                $this->namespace,
                $deleted
            ));
        }

        return $deleted;
    }

    /**
     * Get keys starting with a prefix.
     */
    public function keysStartingWith(string|array $prefix): array
    {
        $prefix = $this->normalizeKey($prefix);
        
        return $this->buildBaseQuery()->where('key', 'LIKE', $prefix . '%')->notExpired()->pluck('key')->toArray();
    }

    /**
     * Get key-value pairs starting with a prefix.
     */
    public function startingWith(string|array $prefix): Collection
    {
        $prefix = $this->normalizeKey($prefix);
        return $this->buildBaseQuery()->where('key', 'LIKE', $prefix . '%')->notExpired()->get()
            ->mapWithKeys(fn($item) => [$item->key => $item->value]);
    }

    /**
     * Get nested array from keys starting with a prefix.
     */
    public function nestedFrom(string|array $prefix): array
    {
        $prefix = $this->normalizeKey($prefix);
        $entries = $this->buildBaseQuery()->where('key', 'LIKE', $prefix . '%')->notExpired()->get();
        return $this->buildNestedArray($entries, $prefix);
    }

    /**
     * Get all entries as nested array.
     */
    public function nested(): array
    {
        $entries = $this->buildBaseQuery()->notExpired()->get();
        return $this->buildNestedArray($entries);
    }

    /**
     * Helper to build nested array structure.
     */
    protected function buildNestedArray(Collection $entries, ?string $stripPrefix = null): array
    {
        $result = [];
        foreach ($entries as $entry) {
            $key = $entry->key;
            if ($stripPrefix && str_starts_with($key, $stripPrefix)) {
                $key = ltrim(substr($key, strlen($stripPrefix)), '.');
            }
            $parts = explode('.', $key);
            $current = &$result;
            foreach ($parts as $i => $part) {
                if ($i === count($parts) - 1) {
                    $current[$part] = $entry->value;
                } else {
                    if (!isset($current[$part]) || !is_array($current[$part])) {
                        $current[$part] = [];
                    }
                    $current = &$current[$part];
                }
            }
        }
        return $result;
    }

    /**
     * Export datastore to a JSON file.
     */
    public function export(string $filename, ?string $disk = null): bool
    {
        $disk = $disk ?? config('datastore.export.disk');
        $data = $this->buildBaseQuery()->notExpired()->get()->map(fn($item) => [
            'key' => $item->key,
            'value' => $item->value,
            'namespace' => $item->namespace,
            'storable_type' => $item->storable_type,
            'storable_id' => $item->storable_id,
            'expires_at' => $item->expires_at?->toIso8601String(),
            'created_at' => $item->created_at->toIso8601String(),
        ])->toArray();

        $json = json_encode([
            'exported_at' => now()->toIso8601String(),
            'scope' => [
                'storable_type' => $this->getStorableType(),
                'storable_id' => $this->getStorableId(),
                'namespace' => $this->namespace,
            ],
            'count' => count($data),
            'data' => $data,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return Storage::disk($disk)->put($filename, $json);
    }

    /**
     * Import datastore from a JSON file.
     */
    public function import(string $filename, ?string $disk = null, bool $overwrite = true): int
    {
        $disk = $disk ?? config('datastore.export.disk');
        if (!Storage::disk($disk)->exists($filename)) {
            throw new \RuntimeException("File not found: {$filename}");
        }
        $importData = json_decode(Storage::disk($disk)->get($filename), true);
        if (!isset($importData['data']) || !is_array($importData['data'])) {
            throw new \RuntimeException("Invalid import file format");
        }
        $imported = 0;
        foreach ($importData['data'] as $entry) {
            if (!$overwrite && $this->has($entry['key'])) continue;
            $ttl = null;
            if (isset($entry['expires_at']) && $entry['expires_at']) {
                $ttl = now()->diffInSeconds(Carbon::parse($entry['expires_at']), false);
                if ($ttl <= 0) continue;
            }
            $this->set($entry['key'], $entry['value'], $ttl > 0 ? (int) $ttl : null);
            $imported++;
        }
        return $imported;
    }

    /**
     * Optimized batch get using single query.
     */
    public function getMany(array $keys): Collection
    {
        $normalizedKeys = array_map(fn($key) => $this->normalizeKey($key), $keys);
        return $this->buildBaseQuery()->whereIn('key', $normalizedKeys)->notExpired()->get()
            ->mapWithKeys(fn($item) => [$item->key => $item->value]);
    }

    /**
     * Optimized batch set using transaction.
     */
    public function setMany(array $values, ?int $ttlSeconds = null): void
    {
        DB::transaction(function() use ($values, $ttlSeconds) {
            foreach ($values as $key => $value) {
                $this->set($key, $value, $ttlSeconds);
            }
        });
    }

    /**
     * Optimized batch delete using single query.
     */
    public function deleteMany(array $keys): int
    {
        $normalizedKeys = array_map(fn($key) => $this->normalizeKey($key), $keys);
        $entries = $this->buildBaseQuery()->whereIn('key', $normalizedKeys)->get();
        
        $deleted = $this->buildBaseQuery()->whereIn('key', $normalizedKeys)->delete();

        // Dispatch events for each deleted entry
        foreach ($entries as $entry) {
            event(new DataStoreDeleted(
                $this->getStorableType(),
                $this->getStorableId(),
                $this->namespace,
                $entry->key,
                $entry->value
            ));
        }

        return $deleted;
    }

    /**
     * Update the TTL of a key.
     */
    public function touch(string|array $key, ?int $ttlSeconds = null): bool
    {
        $key = $this->normalizeKey($key);
        $expiresAt = $ttlSeconds ? now()->addSeconds($ttlSeconds) : null;
        return $this->buildBaseQuery()->where('key', $key)->update(['expires_at' => $expiresAt]) > 0;
    }

    /**
     * Get the TTL of a key in seconds.
     */
    public function ttl(string|array $key): ?int
    {
        $key = $this->normalizeKey($key);
        $storage = $this->buildBaseQuery()->where('key', $key)->first();
        if (!$storage || !$storage->expires_at) return null;
        $seconds = now()->diffInSeconds($storage->expires_at, false);
        return $seconds > 0 ? (int) $seconds : 0;
    }

    /**
     * Increment a numeric value.
     */
    public function increment(string|array $key, int $amount = 1): int
    {
        $current = $this->get($key, 0);
        $new = (int) $current + $amount;
        $this->set($key, $new);
        return $new;
    }

    /**
     * Decrement a numeric value.
     */
    public function decrement(string|array $key, int $amount = 1): int
    {
        return $this->increment($key, -$amount);
    }

    /**
     * Create a query builder for advanced queries.
     */
    public function query(): DataStoreQueryBuilder
    {
        $builder = new DataStoreQueryBuilder();
        
        if ($this->storable) {
            $builder->for($this->storable);
        }
        
        if ($this->namespace) {
            $builder->inNamespace($this->namespace);
        }
        
        return $builder;
    }

    /**
     * Build the base query for the current scope.
     */
    protected function buildBaseQuery()
    {
        $query = DataStore::query();

        if ($this->storable !== null) {
            $query->where('storable_type', $this->getStorableType())->where('storable_id', $this->getStorableId());
        } else {
            $query->whereNull('storable_type')->whereNull('storable_id');
        }

        if ($this->namespace !== null) {
            $query->where('namespace', $this->namespace);
        } else {
            $query->whereNull('namespace');
        }
        
        return $query;
    }

    /**
     * Get storable type.
     */
    public function getStorableType(): ?string
    {
        return $this->storable?->getMorphClass();
    }

    /**
     * Get storable ID.
     */
    public function getStorableId(): ?int
    {
        return $this->storable?->getKey();
    }

    /**
     * Get storable model.
     */
    public function getStorable(): ?Model
    {
        return $this->storable;
    }

    /**
     * Get current namespace.
     */
    public function getNamespace(): ?string
    {
        return $this->namespace;
    }
}

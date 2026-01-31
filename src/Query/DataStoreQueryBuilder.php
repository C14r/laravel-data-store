<?php

namespace C14r\DataStore\Query;

use C14r\DataStore\Models\DataStore;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class DataStoreQueryBuilder
{
    protected Builder $query;
    protected ?string $storableType = null;
    protected ?int $storableId = null;
    protected ?string $namespace = null;

    public function __construct()
    {
        $this->query = DataStore::query();
    }

    /**
     * Scope to a specific storable.
     */
    public function for($model): static
    {
        if (is_object($model)) {
            $this->storableType = $model->getMorphClass();
            $this->storableId = $model->getKey();
        }
        return $this;
    }

    /**
     * Scope to a specific namespace.
     */
    public function inNamespace(string|array|null $namespace): static
    {
        if (is_array($namespace)) {
            $namespace = implode('.', array_filter($namespace));
        }
        $this->namespace = $namespace;
        return $this;
    }

    /**
     * Only global entries (no owner).
     */
    public function global(): static
    {
        $this->storableType = null;
        $this->storableId = null;
        return $this;
    }

    /**
     * Filter by key pattern.
     */
    public function keyLike(string $pattern): static
    {
        $this->query->where('key', 'LIKE', $pattern);
        return $this;
    }

    /**
     * Filter keys starting with prefix.
     */
    public function keyStartsWith(string $prefix): static
    {
        return $this->keyLike($prefix . '%');
    }

    /**
     * Only non-expired entries.
     */
    public function notExpired(): static
    {
        $this->query->notExpired();
        return $this;
    }

    /**
     * Only expired entries.
     */
    public function expired(): static
    {
        $this->query->expired();
        return $this;
    }

    /**
     * Filter by created date.
     */
    public function createdAfter($date): static
    {
        $this->query->where('created_at', '>', $date);
        return $this;
    }

    /**
     * Filter by created date.
     */
    public function createdBefore($date): static
    {
        $this->query->where('created_at', '<', $date);
        return $this;
    }

    /**
     * Order by column.
     */
    public function orderBy(string $column, string $direction = 'asc'): static
    {
        $this->query->orderBy($column, $direction);
        return $this;
    }

    /**
     * Limit results.
     */
    public function limit(int $limit): static
    {
        $this->query->limit($limit);
        return $this;
    }

    /**
     * Offset results.
     */
    public function offset(int $offset): static
    {
        $this->query->offset($offset);
        return $this;
    }

    /**
     * Execute query and get results.
     */
    public function get(): Collection
    {
        $this->applyScopes();
        return $this->query->get();
    }

    /**
     * Get results as key-value pairs.
     */
    public function pluck(): Collection
    {
        $this->applyScopes();
        return $this->query->get()->mapWithKeys(fn($item) => [$item->key => $item->value]);
    }

    /**
     * Get first result.
     */
    public function first(): ?DataStore
    {
        $this->applyScopes();
        return $this->query->first();
    }

    /**
     * Count results.
     */
    public function count(): int
    {
        $this->applyScopes();
        return $this->query->count();
    }

    /**
     * Check if any results exist.
     */
    public function exists(): bool
    {
        $this->applyScopes();
        return $this->query->exists();
    }

    /**
     * Delete matching entries.
     */
    public function delete(): int
    {
        $this->applyScopes();
        return $this->query->delete();
    }

    /**
     * Apply storable and namespace scopes.
     */
    protected function applyScopes(): void
    {
        if ($this->storableType !== null && $this->storableId !== null) {
            $this->query->where('storable_type', $this->storableType)
                       ->where('storable_id', $this->storableId);
        } elseif ($this->storableType === null && $this->storableId === null) {
            $this->query->whereNull('storable_type')
                       ->whereNull('storable_id');
        }

        if ($this->namespace !== null) {
            $this->query->where('namespace', $this->namespace);
        }
    }

    /**
     * Get the underlying Eloquent builder.
     */
    public function toEloquentBuilder(): Builder
    {
        $this->applyScopes();
        return $this->query;
    }
}

<?php

namespace C14r\DataStore\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Builder;

class DataStore extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'storable_type',
        'storable_id',
        'namespace',
        'key',
        'value',
        'expires_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'value' => 'array',
        'expires_at' => 'datetime',
    ];

    /**
     * Create a new instance and set table name from config.
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('datastore.table_name', 'data_stores'));
    }

    /**
     * Get the owning storable model (User, Group, Team, etc.).
     */
    public function storable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope a query to only include non-expired entries.
     */
    public function scopeNotExpired(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Scope a query to only include expired entries.
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->whereNotNull('expires_at')->where('expires_at', '<=', now());
    }

    /**
     * Scope a query for a specific namespace.
     */
    public function scopeInNamespace(Builder $query, string $namespace): Builder
    {
        return $query->where('namespace', $namespace);
    }

    /**
     * Scope a query for a specific storable (User, Group, etc.).
     */
    public function scopeForStorable(Builder $query, string $type, int $id): Builder
    {
        return $query->where('storable_type', $type)->where('storable_id', $id);
    }

    /**
     * Scope a query for global entries (no owner).
     */
    public function scopeGlobal(Builder $query): Builder
    {
        return $query->whereNull('storable_type')->whereNull('storable_id');
    }

    /**
     * Check if the entry has expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if the entry is still valid.
     */
    public function isValid(): bool
    {
        return !$this->isExpired();
    }

    /**
     * Check if the entry is global (no owner).
     */
    public function isGlobal(): bool
    {
        return $this->storable_type === null && $this->storable_id === null;
    }
}

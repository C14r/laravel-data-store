<?php

namespace C14r\DataStore\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \C14r\DataStore\Services\StorageService forUser($user = null)
 * @method static \C14r\DataStore\Services\StorageService forGroup(\Illuminate\Database\Eloquent\Model $group)
 * @method static \C14r\DataStore\Services\StorageService forTeam(\Illuminate\Database\Eloquent\Model $team)
 * @method static \C14r\DataStore\Services\StorageService forOrganization(\Illuminate\Database\Eloquent\Model $organization)
 * @method static \C14r\DataStore\Services\StorageService for(\Illuminate\Database\Eloquent\Model $model)
 * @method static \C14r\DataStore\Services\StorageService inNamespace(string|array|null $namespace)
 * @method static \C14r\DataStore\Models\DataStore set(string|array $key, mixed $value, ?int $ttlSeconds = null)
 * @method static mixed get(string|array $key, mixed $default = null, ?string $as = null)
 * @method static mixed data(string|array $key, string $class, mixed $default = null)
 * @method static mixed collection(string|array $key, string $class, mixed $default = [])
 * @method static bool has(string|array $key)
 * @method static bool delete(string|array $key)
 * @method static \Illuminate\Support\Collection keys()
 * @method static \Illuminate\Support\Collection all()
 * @method static int clear()
 * @method static array keysStartingWith(string|array $prefix)
 * @method static \Illuminate\Support\Collection startingWith(string|array $prefix)
 * @method static array nestedFrom(string|array $prefix)
 * @method static array nested()
 * @method static bool export(string $filename, ?string $disk = null)
 * @method static int import(string $filename, ?string $disk = null, bool $overwrite = true)
 * @method static \Illuminate\Support\Collection getMany(array $keys)
 * @method static void setMany(array $values, ?int $ttlSeconds = null)
 * @method static int deleteMany(array $keys)
 * @method static bool touch(string|array $key, ?int $ttlSeconds = null)
 * @method static int|null ttl(string|array $key)
 * @method static int increment(string|array $key, int $amount = 1)
 * @method static int decrement(string|array $key, int $amount = 1)
 * @method static \C14r\DataStore\Query\DataStoreQueryBuilder query()
 * @method static string|null getStorableType()
 * @method static int|null getStorableId()
 * @method static \Illuminate\Database\Eloquent\Model|null getStorable()
 * @method static string|null getNamespace()
 *
 * @see \C14r\DataStore\Services\StorageService
 */
class DataStore extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'datastore';
    }
}

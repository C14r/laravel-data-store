# Laravel DataStore

[![Latest Version on Packagist](https://img.shields.io/packagist/v/c14r/laravel-data-store.svg?style=flat-square)](https://packagist.org/packages/c14r/laravel-data-store)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/c14r/laravel-data-store/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/c14r/laravel-data-store/actions?query=workflow%3Arun-tests+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/c14r/laravel-data-store.svg?style=flat-square)](https://packagist.org/packages/c14r/laravel-data-store)

A flexible polymorphic key-value storage system for Laravel with namespaces, TTL support, nested data structures, and more!

Store data for Users, Teams, Organizations, or globally with an elegant, chainable API.

## Features

- 🔗 **Polymorphic Storage** - Attach data to any Eloquent model (User, Team, Organization, etc.)
- 🏷️ **Namespaces** - Organize data with dot-notation namespaces
- ⏱️ **TTL Support** - Automatic expiration with time-to-live
- 🌳 **Nested Structures** - Hierarchical data with dot-notation keys
- 📦 **Export/Import** - JSON-based backup and restore
- 🔍 **Flexible Querying** - Flat or nested data retrieval
- 🧹 **Auto Cleanup** - Scheduled removal of expired entries
- ✨ **Facade Support** - Clean, expressive API
- 🎯 **Spatie Data Integration** - Seamless support for Data Transfer Objects
- 🎉 **Event System** - Automatic event dispatching (v1.1)
- 🏗️ **Model Trait** - HasDataStore for easy model integration (v1.1)
- 🔍 **Query Builder** - Advanced query capabilities (v1.1)
- 📦 **DataCollection Support** - Type-safe collections with Spatie Data (v1.1)

## Spatie Laravel Data Integration

DataStore has **seamless integration** with `spatie/laravel-data` for type-safe DTOs:

```php
use Spatie\LaravelData\Data;

class UserPreferences extends Data {
    public function __construct(
        public string $theme,
        public string $language,
        public int $itemsPerPage,
    ) {}
}

// Auto-converts to array when storing
DataStore::set('preferences', new UserPreferences('dark', 'de', 20));

// Auto-converts to Data object when retrieving
$prefs = DataStore::get('preferences', as: UserPreferences::class);
echo $prefs->theme; // IDE autocomplete!
```

See [Spatie Integration Guide](SPATIE_INTEGRATION.md) for details.

## Installation

Install the package via Composer:

```bash
composer require c14r/laravel-data-store
```

Publish and run migrations:

```bash
php artisan vendor:publish --tag="datastore-migrations"
php artisan migrate
```

Optionally, publish the config file:

```bash
php artisan vendor:publish --tag="datastore-config"
```

## Quick Start

```php
use C14r\DataStore\Facades\DataStore;
use C14r\DataStore\Traits\HasDataStore;

// Global storage
DataStore::set('site_name', 'My Website');

// User storage (auto-detects authenticated user)
DataStore::forUser()->set('theme', 'dark');

// Model trait (v1.1)
class User extends Authenticatable {
    use HasDataStore;
}

$user->dataStore('preferences')->set('theme', 'dark');
$theme = $user->retrieveData('theme', 'light', 'preferences');

// Helper function (v1.1)
datastore('cache')->set('key', 'value', 3600);

// Query Builder (v1.1)
$drafts = DataStore::query()
    ->forUser($user)
    ->keyStartsWith('draft')
    ->notExpired()
    ->get();

// Events (v1.1)
Event::listen(DataStoreSet::class, function($event) {
    Log::info("Data set: {$event->key}");
});
```

## Usage

### Basic Operations

```php
// Set value
DataStore::set('key', 'value');

// Get value
$value = DataStore::get('key');
$value = DataStore::get('missing_key', 'default');

// Check existence
if (DataStore::has('key')) {
    //
}

// Delete
DataStore::delete('key');
```

### Scoping

```php
// Global (no owner)
DataStore::set('app_version', '1.0');

// For User (null = auth()->user())
DataStore::forUser()->set('theme', 'dark');
DataStore::forUser($user)->set('language', 'en');

// For any model
DataStore::forTeam($team)->set('name', 'My Team');
DataStore::forGroup($group)->set('permissions', [...]);
DataStore::for($organization)->set('billing', [...]);
```

### Namespaces

```php
// Simple namespace
$settings = DataStore::inNamespace('settings');
$settings->set('items_per_page', 20);

// Nested namespaces
$prefs = DataStore::inNamespace('user.preferences');
// or
$prefs = DataStore::inNamespace(['user', 'preferences']);

// Combine with user
$userSettings = DataStore::forUser()->inNamespace('settings');
```

### Nested Keys

```php
// Array notation
DataStore::set(['config', 'app', 'name'], 'MyApp');

// Dot notation
DataStore::set('config.app.version', '1.0');

// Get nested
$name = DataStore::get('config.app.name');
$name = DataStore::get(['config', 'app', 'name']);
```

### Data Retrieval

```php
// All keys (Collection)
$keys = DataStore::keys();

// All data (Collection - flat)
$data = DataStore::all();

// Keys starting with prefix (Array)
$keys = DataStore::keysStartingWith('user.123');
// ['user.123.name', 'user.123.email']

// Flat data starting with prefix (Collection)
$data = DataStore::startingWith('user.123');
// ['user.123.name' => 'John', 'user.123.email' => 'john@example.com']

// Nested structure from prefix (Array)
$user = DataStore::nestedFrom('user.123');
// ['name' => 'John', 'email' => 'john@example.com']

// Nested structure from current scope (Array)
$nested = DataStore::inNamespace('config')->nested();
// ['app' => ['name' => 'MyApp', 'version' => '1.0']]
```

### TTL (Time To Live)

```php
// Set with TTL (in seconds)
DataStore::set('session_token', 'abc123', 3600); // 1 hour

// Check TTL
$ttl = DataStore::ttl('session_token'); // Returns seconds left

// Extend TTL
DataStore::touch('session_token', 7200); // Extend to 2 hours
```

### Bulk Operations

```php
// Set multiple
DataStore::setMany([
    'key1' => 'value1',
    'key2' => 'value2',
], 3600); // Optional TTL

// Get multiple
$values = DataStore::getMany(['key1', 'key2']);

// Delete multiple
DataStore::deleteMany(['key1', 'key2']);
```

### Counters

```php
// Increment
DataStore::increment('page_views');
DataStore::increment('page_views', 5);

// Decrement
DataStore::decrement('downloads', 3);
```

### Export / Import

```php
// Export
DataStore::forUser()->export('backups/user-preferences.json');

// Import
DataStore::forUser()->import('backups/user-preferences.json');

// Import without overwriting
DataStore::forUser()->import('backup.json', null, false);
```

## Configuration

The config file `config/datastore.php` allows you to customize:

- Default namespace
- Table name
- Default TTL
- Auto-cleanup scheduling
- Export disk and path

## Artisan Commands

### Cleanup Expired Entries

```bash
# Delete all expired entries
php artisan datastore:cleanup

# Dry run (show what would be deleted)
php artisan datastore:cleanup --dry-run

# Cleanup specific namespace
php artisan datastore:cleanup --namespace=cache

# Cleanup specific model type
php artisan datastore:cleanup --type="App\Models\User"
```

### Schedule Auto-Cleanup

In `routes/console.php`:

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('datastore:cleanup')->daily();
```

## Model Relationships (Optional)

Add to your models to access data stores via relationships:

```php
use C14r\DataStore\Models\DataStore;

class User extends Authenticatable
{
    public function dataStores()
    {
        return $this->morphMany(DataStore::class, 'storable');
    }
}

// Usage
$user->dataStores()->where('namespace', 'preferences')->get();
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Security

If you discover any security related issues, please email your-email@example.com instead of using the issue tracker.

## Credits

- [c14r](https://github.com/c14r)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

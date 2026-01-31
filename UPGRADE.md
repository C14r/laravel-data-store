# Upgrade Guide

## From 1.0.0 to 1.1.0

### Breaking Changes

**None!** Version 1.1.0 is 100% backward compatible.

### New Features

#### 1. Spatie Laravel Data Integration

You can now use Spatie Data objects seamlessly:

```php
// Install (optional)
composer require spatie/laravel-data

// Use Data objects
class Settings extends Data {
    public function __construct(
        public string $theme,
        public string $language
    ) {}
}

// Store (auto-converts)
DataStore::set('settings', new Settings('dark', 'de'));

// Retrieve (auto-converts)
$settings = DataStore::get('settings', as: Settings::class);
```

#### 2. New Helper Methods

```php
// Explicit Data object methods
DataStore::setData('key', $dataObject);
$data = DataStore::data('key', DataClass::class);
```

#### 3. Auth Facade Change

Internally changed from `auth()->user()` to `Auth::user()`. No impact on your code.

### Migration Steps

**No migration needed!** Simply update:

```bash
composer update c14r/laravel-data-store
```

### Optional: Install Spatie Data

To use the new Data object features:

```bash
composer require spatie/laravel-data
```

See [SPATIE_INTEGRATION.md](SPATIE_INTEGRATION.md) for full documentation.

### What's Next

Check out the [CHANGELOG](CHANGELOG.md) for all improvements.

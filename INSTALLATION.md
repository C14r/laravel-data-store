# Installation Guide

## Requirements

- PHP 8.1 or higher
- Laravel 10.x or 11.x

## Step 1: Install via Composer

```bash
composer require c14r/laravel-data-store
```

## Step 2: Publish and Run Migrations

```bash
# Publish migration
php artisan vendor:publish --tag="datastore-migrations"

# Run migration
php artisan migrate
```

This creates the `data_stores` table in your database.

## Step 3: Publish Configuration (Optional)

```bash
php artisan vendor:publish --tag="datastore-config"
```

This creates `config/datastore.php` where you can customize:

- Table name
- Default namespace
- Default TTL
- Auto-cleanup settings
- Export settings

## Step 4: Add to Models (Optional)

Add the relationship to models that will use DataStore:

```php
use C14r\DataStore\Models\DataStore;

class User extends Authenticatable
{
    public function dataStores()
    {
        return $this->morphMany(DataStore::class, 'storable');
    }
}
```

## Step 5: Schedule Cleanup (Recommended)

In `routes/console.php`:

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('datastore:cleanup')->daily();
```

Or manually in your `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('datastore:cleanup')->daily();
}
```

## Verify Installation

Test the installation:

```php
use C14r\DataStore\Facades\DataStore;

DataStore::set('test', 'Hello DataStore!');
$value = DataStore::get('test'); // "Hello DataStore!"
```

## Troubleshooting

### Facade not found

Make sure the service provider is auto-discovered. Check `composer.json`:

```json
"extra": {
    "laravel": {
        "providers": [
            "C14r\\DataStore\\DataStoreServiceProvider"
        ]
    }
}
```

Then run:
```bash
composer dump-autoload
php artisan config:clear
```

### Migration issues

If migration fails, ensure your database connection is configured correctly in `.env`.

### Permission issues

Ensure your storage directory is writable if using file-based exports:

```bash
chmod -R 775 storage
```

## Next Steps

- Read the [Usage Guide](USAGE.md)
- Check out [Examples](README.md#usage)
- Review [Configuration](config/datastore.php)

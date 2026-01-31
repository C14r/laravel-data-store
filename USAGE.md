# Usage Guide

## Table of Contents

- [Basic Operations](#basic-operations)
- [Scoping](#scoping)
- [Namespaces](#namespaces)
- [Nested Data](#nested-data)
- [Data Retrieval Methods](#data-retrieval-methods)
- [TTL (Expiration)](#ttl-expiration)
- [Bulk Operations](#bulk-operations)
- [Counters](#counters)
- [Export & Import](#export--import)
- [Real-World Examples](#real-world-examples)

## Basic Operations

### Set and Get

```php
use C14r\DataStore\Facades\DataStore;

// Set a value
DataStore::set('app_name', 'My Application');

// Get a value
$name = DataStore::get('app_name');

// Get with default
$theme = DataStore::get('theme', 'light');
```

### Check and Delete

```php
// Check if key exists
if (DataStore::has('settings')) {
    // Key exists
}

// Delete a key
DataStore::delete('old_setting');

// Clear all in current scope
DataStore::clear();
```

## Scoping

### Global Storage

```php
// No owner - available globally
DataStore::set('site_maintenance', false);
DataStore::set('api_version', 'v2');
```

### User Storage

```php
// Auto-detect authenticated user
DataStore::forUser()->set('theme', 'dark');

// Specific user
$user = User::find(1);
DataStore::forUser($user)->set('language', 'en');

// By user ID
DataStore::forUser(123)->set('timezone', 'UTC');
```

### Other Models

```php
// Team
$team = Team::find(1);
DataStore::forTeam($team)->set('plan', 'enterprise');

// Group
$group = Group::find(1);
DataStore::forGroup($group)->set('permissions', ['admin' => true]);

// Any model
$organization = Organization::find(1);
DataStore::for($organization)->set('settings', [...]);
```

## Namespaces

### Simple Namespace

```php
$settings = DataStore::inNamespace('settings');
$settings->set('items_per_page', 20);
$settings->set('default_currency', 'EUR');
```

### Nested Namespaces

```php
// Dot notation
$prefs = DataStore::inNamespace('user.preferences');
$prefs->set('theme', 'dark');

// Array notation
$config = DataStore::inNamespace(['app', 'config', 'mail']);
$config->set('driver', 'smtp');
```

### Combining User + Namespace

```php
// User's invoice preferences
$invoicePrefs = DataStore::forUser()
    ->inNamespace('invoices.preferences');
    
$invoicePrefs->set('default_currency', 'EUR');
$invoicePrefs->set('tax_rate', 19);

// Reusable instance
$storage = DataStore::forUser()->inNamespace('cart');
$storage->set('items', []);
$storage->increment('total_items');
```

## Nested Data

### Nested Keys

```php
// Array notation
DataStore::set(['config', 'app', 'name'], 'MyApp');
DataStore::set(['config', 'app', 'version'], '1.0');
DataStore::set(['config', 'db', 'host'], 'localhost');

// Dot notation
DataStore::set('user.123.profile.name', 'John Doe');
DataStore::set('user.123.profile.email', 'john@example.com');

// Get nested values
$name = DataStore::get('config.app.name');
$email = DataStore::get(['user', '123', 'profile', 'email']);
```

## Data Retrieval Methods

### All Keys

```php
// Get all keys as Collection
$keys = DataStore::keys();
// Collection: ['key1', 'key2', 'key3']
```

### All Data (Flat)

```php
// Get all data as flat Collection
$data = DataStore::all();
// Collection: ['key1' => 'value1', 'key2' => 'value2']
```

### Keys Starting With

```php
// Get only keys that start with prefix
$userKeys = DataStore::keysStartingWith('user.123');
// Array: ['user.123.name', 'user.123.email', 'user.123.age']
```

### Flat Data Starting With

```php
// Get flat key-value pairs with prefix
$userData = DataStore::startingWith('user.123');
// Collection: [
//   'user.123.name' => 'John',
//   'user.123.email' => 'john@example.com'
// ]
```

### Nested From Prefix

```php
// Get nested structure from prefix
$customer = DataStore::nestedFrom('customer.CUST-123');
// Array: [
//   'name' => 'ACME Corp',
//   'email' => 'info@acme.com',
//   'settings' => [
//     'notifications' => true
//   ]
// ]
```

### Nested From Scope

```php
// Get nested structure from current scope
$invoices = DataStore::inNamespace('invoices');
$invoices->set('draft.INV-001', ['total' => 100]);
$invoices->set('draft.INV-002', ['total' => 200]);
$invoices->set('finalized.INV-003', ['total' => 500]);

$allInvoices = $invoices->nested();
// Array: [
//   'draft' => [
//     'INV-001' => ['total' => 100],
//     'INV-002' => ['total' => 200]
//   ],
//   'finalized' => [
//     'INV-003' => ['total' => 500]
//   ]
// ]
```

## TTL (Expiration)

### Set with TTL

```php
// Expires in 1 hour (3600 seconds)
DataStore::set('session_token', 'abc123', 3600);

// Expires in 24 hours
DataStore::set('daily_cache', ['data'], 86400);

// Permanent (no TTL)
DataStore::set('permanent_setting', 'value');
```

### Check TTL

```php
// Get remaining seconds
$ttl = DataStore::ttl('session_token');

if ($ttl > 0) {
    echo "Expires in {$ttl} seconds";
} else if ($ttl === 0) {
    echo "Expired";
} else {
    echo "No expiration";
}
```

### Extend TTL

```php
// Extend to 2 hours
DataStore::touch('session_token', 7200);

// Remove expiration
DataStore::touch('session_token', null);
```

## Bulk Operations

### Set Multiple

```php
DataStore::setMany([
    'key1' => 'value1',
    'key2' => 'value2',
    'key3' => 'value3',
]);

// With TTL
DataStore::setMany([
    'cache_key1' => 'data1',
    'cache_key2' => 'data2',
], 3600);
```

### Get Multiple

```php
$values = DataStore::getMany(['key1', 'key2', 'key3']);
// Collection: ['key1' => 'value1', 'key2' => 'value2', ...]
```

### Delete Multiple

```php
$deleted = DataStore::deleteMany(['key1', 'key2', 'key3']);
// Returns: number of deleted entries
```

## Counters

```php
// Initialize counter
DataStore::set('page_views', 0);

// Increment by 1
DataStore::increment('page_views');

// Increment by amount
DataStore::increment('page_views', 5);

// Decrement
DataStore::decrement('downloads', 3);

// Get value
$views = DataStore::get('page_views');
```

## Export & Import

### Export

```php
// Export global data
DataStore::export('backups/global.json');

// Export user data
DataStore::forUser($user)->export("backups/user-{$user->id}.json");

// Export with custom disk
DataStore::export('backup.json', 's3');
```

### Import

```php
// Import and overwrite
DataStore::import('backups/global.json');

// Import without overwriting existing
DataStore::forUser($user)->import('backup.json', null, false);
```

## Real-World Examples

### User Preferences

```php
$prefs = DataStore::forUser()->inNamespace('preferences');

$prefs->set('theme', 'dark');
$prefs->set('language', 'de');
$prefs->set('timezone', 'Europe/Berlin');
$prefs->set('notifications', [
    'email' => true,
    'push' => false,
    'sms' => false,
]);

// Get all preferences
$allPrefs = $prefs->all();
```

### Shopping Cart

```php
$cart = DataStore::forUser()->inNamespace('cart');

// Add item
$cart->set(['items', 'PROD-123'], [
    'name' => 'Laptop',
    'price' => 999.99,
    'quantity' => 1,
]);

// Update quantity
$item = $cart->get(['items', 'PROD-123']);
$item['quantity'] = 2;
$cart->set(['items', 'PROD-123'], $item);

// Get all cart items
$items = $cart->nestedFrom('items');

// Clear cart
$cart->clear();
```

### Team Settings

```php
$team = Team::find(1);
$teamSettings = DataStore::forTeam($team)->inNamespace('settings');

$teamSettings->set('max_members', 50);
$teamSettings->set('features', [
    'api_access' => true,
    'priority_support' => true,
]);

// Get settings
$maxMembers = $teamSettings->get('max_members');
$features = $teamSettings->get('features');
```

### Temporary Session Data

```php
$session = DataStore::forUser()->inNamespace('session');

// Store CSRF token (1 hour)
$session->set('csrf_token', Str::random(40), 3600);

// Store last activity (30 minutes)
$session->set('last_activity', now()->toIso8601String(), 1800);

// Check if expired
if (!$session->has('csrf_token')) {
    // Token expired, regenerate
}
```

### Analytics

```php
$analytics = DataStore::inNamespace('analytics');

// Track page views
$analytics->increment('page_views');
$analytics->increment("page_views.{$page}");

// Track downloads
$analytics->increment("downloads.{$file}");

// Get stats
$totalViews = $analytics->get('page_views', 0);
$fileDownloads = $analytics->get("downloads.{$file}", 0);
```

### Feature Flags

```php
$flags = DataStore::inNamespace('features');

$flags->set('new_dashboard', true);
$flags->set('beta_api', false);
$flags->set('experimental_ui', true);

// Check feature
if ($flags->get('new_dashboard', false)) {
    // Show new dashboard
}
```

### Multi-Tenant Application

```php
$tenant = Tenant::find(1);
$user = auth()->user();

// Tenant-scoped user preferences
$prefs = DataStore::for($tenant)
    ->inNamespace(['users', $user->id, 'preferences']);
    
$prefs->set('dashboard_layout', 'grid');
$prefs->set('default_view', 'list');

// Organization-wide settings
$orgSettings = DataStore::for($tenant)
    ->inNamespace('settings');
    
$orgSettings->set('billing_day', 15);
$orgSettings->set('invoice_prefix', 'ORG');
```

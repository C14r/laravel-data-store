# 📦 Laravel DataStore - Package Overview

## What is Laravel DataStore?

A production-ready Laravel package that provides a flexible, polymorphic key-value storage system with namespaces, TTL support, and nested data structures.

## 🎯 Use Cases

- **User Preferences** - Store theme, language, timezone per user
- **Shopping Carts** - Session-based or persistent cart data
- **Feature Flags** - Toggle features per user/team/organization
- **Analytics** - Track views, downloads, events
- **Cache Layer** - Alternative to Redis/Memcached with model scoping
- **Settings Management** - App-wide or user-specific configurations
- **Multi-Tenant Data** - Scope data to tenants/organizations
- **Temporary Sessions** - Store CSRF tokens, temporary data with TTL

## ✨ Key Features

| Feature | Description |
|---------|-------------|
| 🔗 **Polymorphic** | Attach to any model (User, Team, Organization) |
| 🏷️ **Namespaces** | Organize with `invoices.drafts.2025` |
| ⏱️ **TTL** | Auto-expire after X seconds |
| 🌳 **Nested** | Hierarchical keys: `user.123.profile.email` |
| 📦 **Export/Import** | JSON backup/restore |
| 🔍 **Flexible Queries** | Flat or nested retrieval |
| 🧹 **Auto Cleanup** | Scheduled removal of expired data |
| ✨ **Facade** | Clean API: `DataStore::set()` |

## 📊 Quick Comparison

### vs. Session Storage
- ✅ Persists across sessions
- ✅ Works for any model (not just current user)
- ✅ Supports TTL and namespaces
- ✅ Database-backed (queryable)

### vs. Cache (Redis/Memcached)
- ✅ Polymorphic (per user/team/org)
- ✅ Permanent or TTL-based
- ✅ Structured (nested keys)
- ✅ Exportable/importable
- ❌ Slower than in-memory cache

### vs. Custom JSON Columns
- ✅ Consistent API
- ✅ Automatic cleanup
- ✅ Multi-model support
- ✅ Export/import built-in
- ✅ Indexed for performance

## 🚀 Installation

```bash
composer require c14r/laravel-data-store
php artisan vendor:publish --tag="datastore-migrations"
php artisan migrate
```

## 💡 Quick Examples

### Global Settings
```php
DataStore::set('site_name', 'My App');
DataStore::set('maintenance', false);
```

### User Preferences
```php
DataStore::forUser()->set('theme', 'dark');
DataStore::forUser($user)->set('language', 'en');
```

### Team Settings
```php
DataStore::forTeam($team)->set('plan', 'enterprise');
```

### With Namespace
```php
$cart = DataStore::forUser()->inNamespace('cart');
$cart->set('items', []);
$cart->increment('total');
```

### Nested Data
```php
DataStore::set('config.app.name', 'MyApp');
$nested = DataStore::nested();
// ['config' => ['app' => ['name' => 'MyApp']]]
```

### With TTL
```php
DataStore::set('token', 'abc123', 3600); // 1 hour
```

## 📁 Package Contents

### Core Files
- ✅ Service Provider (auto-discovered)
- ✅ Eloquent Model with polymorphic relations
- ✅ Storage Service with 25+ methods
- ✅ Facade for easy access
- ✅ Artisan cleanup command
- ✅ Publishable migration
- ✅ Publishable config

### Testing
- ✅ Feature tests (end-to-end)
- ✅ Unit tests (isolated)
- ✅ Pest framework support
- ✅ GitHub Actions CI/CD
- ✅ Multi-version testing (Laravel 10/11, PHP 8.1/8.2/8.3)

### Documentation
- ✅ README with quick start
- ✅ INSTALLATION guide
- ✅ USAGE guide with examples
- ✅ CHANGELOG
- ✅ CONTRIBUTING guide
- ✅ Package structure docs
- ✅ Publishing guide

## 🎨 API Overview

### Scoping
```php
forUser($user = null)    // null = auth()->user()
forTeam($team)
forGroup($group)
forOrganization($org)
for($model)              // Any model
inNamespace($namespace)  // String or array
```

### CRUD
```php
set($key, $value, $ttl = null)
get($key, $default = null)
has($key)
delete($key)
clear()
```

### Retrieval
```php
keys()                    // All keys (Collection)
all()                     // Flat key-value (Collection)
keysStartingWith($prefix) // Keys array
startingWith($prefix)     // Flat Collection
nestedFrom($prefix)       // Nested array
nested()                  // Nested from scope
```

### Bulk
```php
setMany($values, $ttl = null)
getMany($keys)
deleteMany($keys)
```

### TTL
```php
touch($key, $ttl = null)
ttl($key)
```

### Counters
```php
increment($key, $amount = 1)
decrement($key, $amount = 1)
```

### Export/Import
```php
export($filename, $disk = null)
import($filename, $disk = null, $overwrite = true)
```

## 🏗️ Architecture

```
User/Team/Org
    ↓
DataStore (Model)
    ↓
StorageService
    ↓
DataStore Facade
```

### Database Schema
```sql
data_stores
├── id
├── storable_type    (nullable) - polymorphic
├── storable_id      (nullable) - polymorphic
├── namespace        (nullable) - dot notation
├── key              - unique per scope
├── value            (JSON)
├── expires_at       (nullable)
└── timestamps
```

## 🎯 Performance

- **Indexed** queries (storable, namespace, key)
- **Scoped** queries (only relevant data)
- **Lazy** loading where applicable
- **Efficient** cleanup with batching

## 🔒 Security

- ✅ Mass assignment protection
- ✅ SQL injection protected (Eloquent)
- ✅ XSS safe (JSON casting)
- ✅ Scoped to authenticated users

## 📈 Roadmap

Potential future features:
- Encryption support
- Event broadcasting
- Query caching
- Multi-database support
- GraphQL API
- Admin UI

## 🤝 Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md)

## 📄 License

MIT License - see [LICENSE](LICENSE)

## 🔗 Links

- GitHub: https://github.com/c14r/laravel-data-store
- Packagist: https://packagist.org/packages/c14r/laravel-data-store
- Issues: https://github.com/c14r/laravel-data-store/issues

## ⭐ Star History

If you find this package useful, please give it a star on GitHub!

---

**Built with ❤️ for the Laravel community**

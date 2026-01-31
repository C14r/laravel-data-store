# Changelog

All notable changes to `laravel-data-store` will be documented in this file.

## 1.1.0 - 2025-01-30

### 🎉 Major Update - Enterprise Features

#### Added
- 🎯 **Event System** - Automatic event dispatching
  - `DataStoreSet` - When data is created
  - `DataStoreUpdated` - When data is updated
  - `DataStoreDeleted` - When data is deleted
  - `DataStoreCleared` - When scope is cleared
  
- 🏗️ **Model Trait** - HasDataStore for easy model integration
  - `$model->dataStore($namespace)` - Get storage service
  - `$model->storeData($key, $value)` - Quick set
  - `$model->retrieveData($key)` - Quick get
  - `$model->hasData($key)` - Check existence
  - `$model->deleteData($key)` - Delete key
  - `$model->clearData()` - Clear all
  
- 🔍 **Query Builder** - Advanced query capabilities
  - Chainable API for complex queries
  - Methods: `keyStartsWith()`, `notExpired()`, `createdAfter()`, etc.
  - Order, limit, offset support
  - Returns Collections or DataStore models
  
- 📦 **Spatie DataCollection Support**
  - New `collection()` method for type-safe collections
  - Works seamlessly with Spatie\LaravelData\DataCollection
  
- ⚡ **Batch Optimizations**
  - `setMany()` now uses DB transactions for better performance
  - Reduced query count for bulk operations
  
- 🛠️ **Helper Function**
  - Global `datastore($namespace)` function
  - Quick access without facade import

#### Removed
- ❌ **setData()** method - Redundant, use `set()` which auto-detects Data objects

#### Changed
- `auth()->user()` → `Auth::user()` for consistency

### Enhanced
- Events dispatched automatically on all mutations
- Better performance for bulk operations
- Improved developer experience with model trait

## 1.0.0 - 2025-01-29

### Initial Release

#### Features
- ✨ Polymorphic storage for any Eloquent model
- ✨ Namespace support with dot-notation
- ✨ TTL (Time To Live) for automatic expiration
- ✨ Nested data structures with hierarchical keys
- ✨ Export/Import functionality (JSON)
- ✨ Multiple retrieval methods:
  - `keysStartingWith()` - Array of keys
  - `startingWith()` - Flat key-value pairs
  - `nestedFrom()` - Nested structure from prefix
  - `nested()` - Nested structure from scope
- ✨ Bulk operations (setMany, getMany, deleteMany)
- ✨ Counter operations (increment, decrement)
- ✨ Artisan command for cleanup
- ✨ Auto-detection of authenticated user
- ✨ Comprehensive test suite

#### API
- `forUser()` - Scope to user (auto-detects auth if null)
- `forGroup()` - Scope to group
- `forTeam()` - Scope to team
- `forOrganization()` - Scope to organization
- `for()` - Scope to any model
- `inNamespace()` - Scope to namespace
- `set()` - Store value
- `get()` - Retrieve value
- `has()` - Check existence
- `delete()` - Delete entry
- `clear()` - Clear all in scope
- `keys()` - Get all keys
- `all()` - Get all data (flat)
- `export()` - Export to JSON
- `import()` - Import from JSON

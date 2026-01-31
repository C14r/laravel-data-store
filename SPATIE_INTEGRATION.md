# 🔗 Spatie Laravel Data Integration

Laravel DataStore has **seamless integration** with `spatie/laravel-data` for type-safe Data Transfer Objects.

## Installation

```bash
composer require spatie/laravel-data
```

## Features

✅ **Auto-conversion** - Data objects automatically convert to/from arrays  
✅ **Type-safe** - Full IDE autocomplete and type checking  
✅ **Validation** - Built-in validation in Data objects  
✅ **Clean API** - Natural, fluent syntax  

## Quick Example

```php
use Spatie\LaravelData\Data;
use C14r\DataStore\Facades\DataStore;

// Define Data Object
class UserPreferences extends Data
{
    public function __construct(
        public string $theme,
        public string $language,
        public int $itemsPerPage,
    ) {}
}

// Store (auto-converts to array)
$prefs = new UserPreferences('dark', 'de', 20);
DataStore::forUser()->set('preferences', $prefs);

// Retrieve (auto-converts to Data object)
$prefs = DataStore::forUser()->get('preferences', as: UserPreferences::class);

// Type-safe access!
echo $prefs->theme; // IDE autocomplete!
```

## API Methods

### 1. Standard Methods (with auto-conversion)

#### set() - Auto-converts Data objects
```php
$data = new UserPreferences('dark', 'de', 20);

// Automatically calls toArray() if Data object
DataStore::set('key', $data);
```

#### get() - With 'as' parameter
```php
// Returns Data object directly
$prefs = DataStore::get('preferences', as: UserPreferences::class);

// With default
$prefs = DataStore::get('preferences', 
    default: new UserPreferences('light', 'en', 10),
    as: UserPreferences::class
);
```

### 2. Convenience Methods

#### setData() - Explicit Data object storage
```php
DataStore::setData('preferences', $userPreferences);
DataStore::setData('settings', $settings, ttlSeconds: 3600);
```

#### data() - Type-safe retrieval
```php
$prefs = DataStore::data('preferences', UserPreferences::class);
$settings = DataStore::data('settings', AppSettings::class, $default);
```

## Complex Example: Multi-Tenant E-Commerce Cart

```php
use Spatie\LaravelData\Data;
use C14r\DataStore\Facades\DataStore;

// === Data Objects ===

class Money extends Data
{
    public function __construct(
        public float $amount,
        public string $currency
    ) {}
}

class CartItem extends Data
{
    public function __construct(
        public string $product_id,
        public string $name,
        public Money $price,
        public int $quantity,
        public ?int $discount_percent = null
    ) {}
    
    public function total(): Money
    {
        $amount = $this->price->amount * $this->quantity;
        if ($this->discount_percent) {
            $amount *= (1 - $this->discount_percent / 100);
        }
        return new Money($amount, $this->price->currency);
    }
}

class Cart extends Data
{
    /** @var CartItem[] */
    public function __construct(
        public array $items,
        public ?string $coupon_code = null,
    ) {}
    
    public function total(): Money
    {
        $total = 0;
        foreach ($this->items as $item) {
            $total += $item->total()->amount;
        }
        return new Money($total, 'EUR');
    }
}

// === Service ===

class CartService
{
    public function __construct(
        private User $user,
        private Organization $org
    ) {}
    
    private function storage()
    {
        return DataStore::for($this->org)
            ->inNamespace(['users', $this->user->id, 'cart']);
    }
    
    public function addItem(string $productId, int $qty): void
    {
        // Get cart as Data object (or create new)
        $cart = $this->storage()->get('current', as: Cart::class) 
            ?? new Cart(items: []);
        
        $product = Product::find($productId);
        
        $item = new CartItem(
            product_id: $product->id,
            name: $product->name,
            price: new Money($product->price, 'EUR'),
            quantity: $qty
        );
        
        $cart->items[] = $item;
        
        // Store (auto-converts to array)
        $this->storage()->set('current', $cart, 604800);
    }
    
    public function getCart(): ?Cart
    {
        return $this->storage()->get('current', as: Cart::class);
    }
    
    public function applyCoupon(string $code): bool
    {
        $cart = $this->getCart();
        if (!$cart) return false;
        
        $coupon = Coupon::where('code', $code)
            ->where('organization_id', $this->org->id)
            ->first();
            
        if (!$coupon) return false;
        
        foreach ($cart->items as &$item) {
            $item->discount_percent = $coupon->discount_percent;
        }
        
        $cart->coupon_code = $code;
        $this->storage()->set('current', $cart, 604800);
        
        return true;
    }
}

// === Controller ===

class CartController
{
    public function add(Request $request)
    {
        $service = new CartService(Auth::user(), Auth::user()->organization);
        
        $service->addItem(
            $request->input('product_id'),
            $request->input('quantity', 1)
        );
        
        $cart = $service->getCart(); // Returns Cart Data object!
        
        return response()->json([
            'cart' => $cart,
            'total' => $cart->total()
        ]);
    }
}
```

## Validation Example

```php
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Max;

class UserSettings extends Data
{
    public function __construct(
        #[Min(1), Max(100)]
        public int $items_per_page,
        
        #[Email]
        public string $notification_email,
        
        public string $theme,
    ) {}
}

// Validation happens automatically
try {
    $settings = UserSettings::from([
        'items_per_page' => 150, // Error: Max 100
        'notification_email' => 'invalid',
        'theme' => 'dark',
    ]);
} catch (ValidationException $e) {
    // Handle validation errors
}

// Valid data
$settings = UserSettings::from([
    'items_per_page' => 20,
    'notification_email' => 'user@example.com',
    'theme' => 'dark',
]);

DataStore::set('settings', $settings);
```

## Best Practices

### 1. Always use Data objects for structured data

```php
// ❌ Bad - Plain arrays
DataStore::set('profile', [
    'name' => 'John',
    'email' => 'john@example.com'
]);

// ✅ Good - Data objects
class Profile extends Data {
    public function __construct(
        public string $name,
        public string $email
    ) {}
}

DataStore::set('profile', new Profile('John', 'john@example.com'));
```

### 2. Use validation in Data objects

```php
class Invoice extends Data
{
    public function __construct(
        #[Min(0)]
        public float $total,
        
        #[In(['EUR', 'USD', 'GBP'])]
        public string $currency,
    ) {}
}
```

### 3. Leverage type safety

```php
// ❌ Without Data objects
$theme = DataStore::get('settings')['theme'] ?? 'light';

// ✅ With Data objects
$settings = DataStore::get('settings', as: UserSettings::class);
$theme = $settings->theme; // Type-safe, IDE support
```

### 4. Use helper methods for clarity

```php
// Explicit Data object methods
DataStore::setData('preferences', $preferences);
$preferences = DataStore::data('preferences', UserPreferences::class);
```

## How It Works

1. **set()** checks if value is a Data object → calls `toArray()` automatically
2. **get()** with `as` parameter → calls `DataClass::from($array)` automatically
3. No configuration needed - works out of the box!

## Comparison

| Without Spatie Data | With Spatie Data |
|---------------------|------------------|
| `$data = DataStore::get('key')` | `$data = DataStore::get('key', as: Settings::class)` |
| `$theme = $data['theme'] ?? 'light'` | `$theme = $data->theme` |
| No validation | Automatic validation |
| No IDE support | Full autocomplete |
| Arrays everywhere | Type-safe objects |

## Additional Resources

- [Spatie Laravel Data Documentation](https://spatie.be/docs/laravel-data)
- [DataStore Documentation](README.md)
- [Usage Examples](USAGE.md)

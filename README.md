# One Loop

[![Latest Version on Packagist](https://img.shields.io/packagist/v/hassan/one-loop.svg?style=flat-square)](https://packagist.org/packages/hassan/one-loop)
[![Build Status](https://badgen.net/travis/dhassanali/one-loop/master)](https://travis-ci.org/dhassanali/one-loop)
[![License](https://badgen.net/packagist/license/hassan/one-loop)](https://packagist.org/packages/hassan/one-loop)
[![Coverage Status](https://badgen.net/codecov/c/github/dhassanali/one-loop)](https://codecov.io/github/dhassanali/one-loop)

A Laravel/PHP Package for Minimizing Collection/Array Iterations - **optimized for large datasets (100,000+ records)**.

## 📊 Performance Benchmarks

Real-world performance tests show significant improvements with large datasets:

| Dataset Size | Operations | Standard PHP | OneLoop | Improvement |
|-------------|-----------|--------------|---------|-------------|
| 500,000 | Complex (3+ ops) | 163.03 ms | 105.84 ms | **35% faster** ✅ |
| 500,000 | Simple (2 ops) | 226.32 ms | 161.79 ms | **29% faster** ✅ |
| 100,000 | Complex (3+ ops) | 25.83 ms | 18.54 ms | **28% faster** ✅ |
| 10,000 | Any | 0.98 ms | 1.84 ms | 88% slower ⚠️ |
| 1,000 | Any | 0.08 ms | 0.19 ms | 137% slower ⚠️ |

### ⚠️ Performance Warning

**This package is optimized for large datasets.** It provides significant performance improvements when:
- Processing **100,000+ records**
- Chaining **2-3+ operations** (filter, map, reject, etc.)
- Running **batch jobs** or **data processing tasks**

For small datasets (< 50,000 records), standard PHP array functions or Laravel Collections will be faster due to lower overhead.

## Installation

Install the package via composer:

```bash
composer require hassan/one-loop
```

## Usage

### Basic Example

```php
$users = App\User::all();

$ids = one_loop($users)->reject(static function ($user) {
    return $user->age < 20;
})
->map(static function ($user) {
    return $user->id;
})
->apply();
```

## 🆕 New Features in v2.0

### Early Exit with `limit()` / `take()`

Stop processing once you have enough results:

```php
// Get first 100 active users
$users = one_loop($allUsers)
    ->filter(fn($user) => $user->active)
    ->limit(100)
    ->apply();

// Alias: take()
$users = one_loop($allUsers)
    ->filter(fn($user) => $user->active)
    ->take(100)
    ->apply();
```

### Extract Properties with `pluck()`

```php
// Pluck by property name
$emails = one_loop($users)
    ->pluck('email')
    ->apply();

// Pluck with callback
$fullNames = one_loop($users)
    ->pluck(fn($user) => $user->first_name . ' ' . $user->last_name)
    ->apply();
```

### Remove Duplicates with `unique()`

```php
// Unique values
$uniqueDepartments = one_loop($employees)
    ->pluck('department')
    ->unique()
    ->apply();

// Unique by key
$uniqueUsers = one_loop($users)
    ->unique('email')
    ->apply();
```

### Group Items with `groupBy()`

```php
// Group by property
$byDepartment = one_loop($employees)
    ->groupBy('department')
    ->apply();

// Group by callback
$byAgeGroup = one_loop($users)
    ->groupBy(function($user) {
        if ($user->age < 30) return 'young';
        if ($user->age < 50) return 'middle';
        return 'senior';
    })
    ->apply();
```

### Conditional Operations with `when()`

```php
$shouldFilterActive = true;

$result = one_loop($users)
    ->when($shouldFilterActive, function($loop) {
        $loop->filter(fn($user) => $user->active);
    })
    ->map(fn($user) => $user->id)
    ->apply();
```

### Laravel Collection Integration

OneLoop automatically integrates with Laravel Collections:

```php
use Illuminate\Support\Collection;

// Use on any Collection
$result = User::all()
    ->oneLoop()
    ->filter(fn($user) => $user->active)
    ->map(fn($user) => $user->id)
    ->apply();
```

## 🎓 Available Methods

### Filtering
- `filter(callable $callback)` - Keep items that match condition
- `reject(callable $callback)` - Remove items that match condition

### Transformation
- `map(callable $callback)` - Transform each item
- `pluck(string|callable $value)` - Extract specific property

### Uniqueness & Grouping
- `unique(?string|callable $key = null)` - Remove duplicates
- `groupBy(string|callable $groupBy)` - Group by key or callback

### Limiting
- `limit(int $limit)` - Limit results (early exit)
- `take(int $count)` - Alias for limit()

### Conditional
- `when(bool $condition, callable $callback, ?callable $default = null)` - Conditional operations

### Execution
- `apply()` - Execute all queued operations and return results

## 📊 When to Use OneLoop

### ✅ Perfect For:
- **Large datasets** (100K+ records)
- **Batch processing** jobs
- **ETL operations**
- **Data migrations**
- **Complex filtering** with 2-3+ operations
- **Report generation**
- **Product catalog filtering** (e-commerce)
- **Customer segmentation** (marketing)

### ❌ Not Ideal For:
- Small datasets (< 50K records)
- Single operation (just one filter or map)
- Real-time web requests with small result sets
- When microseconds matter with tiny datasets

## 🎯 Real-World Example

```php
// E-commerce: Process large product catalog
$products = Product::all()  // 500,000 products
    ->oneLoop()
    ->filter(fn($p) => $p->active)
    ->reject(fn($p) => $p->stock <= 0)
    ->when($categoryFilter, fn($loop) => 
        $loop->filter(fn($p) => in_array($p->category_id, $categoryFilter))
    )
    ->map(fn($p) => [
        'id' => $p->id,
        'name' => $p->name,
        'price' => $p->price * 0.9  // 10% discount
    ])
    ->limit(1000)
    ->apply();

// Result: 35% faster than standard operations!
```

## 🧪 Testing

```bash
composer test
```

## 📝 Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 🔒 Security

If you discover any security related issues, please email hello@hassan-ali.me instead of using the issue tracker.

## 📜 License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

## 🙏 Credits

- [Hassan Ali](https://github.com/dhassanali)
- [All Contributors](../../contributors)
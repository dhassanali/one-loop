# Changelog

All notable changes to `one-loop` will be documented in this file.

## [2.0.0] - 2025-11-20

### Added
- **Early Exit Optimization**: New `limit()` and `take()` methods for stopping iteration early
- **Pluck Support**: Extract specific properties with `pluck()` method
- **Unique Items**: Remove duplicates with `unique()` method
- **Group By**: Group items by key or callback with `groupBy()` method
- **Conditional Operations**: New `when()` method for conditional operation chains
- **Filter Method**: Added explicit `filter()` method (complement to existing `reject()`)
- **Laravel Collection Integration**: Automatic `oneLoop()` macro for Laravel Collections
- **Comprehensive Test Suite**: Full PHPUnit test coverage for all features

### Changed
- **Improved Performance**: Optimized internal iteration logic
- **Better Documentation**: Added performance benchmarks and usage guidelines
- **Enhanced README**: Included real-world examples and performance warnings
- **Extended PHP Support**: Now supports PHP 7.2.5 through 8.3
- **Extended Laravel Support**: Now supports Laravel 5.8 through 11.0

### Performance
- Benchmarked with datasets from 1K to 500K records
- Shows 28-35% improvement on large datasets (100K+ records)
- Added performance warnings for small datasets

### Breaking Changes
- None - fully backward compatible with v1.x

## [1.0.0] - 2019

### Added
- Initial release
- Basic `reject()` and `map()` operations
- Single-loop optimization for array operations
- Helper function `one_loop()`
- Support for Laravel Collections and arrays
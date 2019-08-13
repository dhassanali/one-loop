# One Loop

[![Latest Version on Packagist](https://img.shields.io/packagist/v/hassan/one-loop.svg?style=flat-square)](https://packagist.org/packages/hassan/one-loop)
[![Build Status](https://badgen.net/travis/dhassanali/one-loop/master)](https://travis-ci.org/dhassanali/one-loop)
[![License](https://badgen.net/packagist/license/hassan/one-loop)](https://packagist.org/packages/hassan/one-loop)
[![Coverage Status](https://badgen.net/codecov/c/github/dhassanali/one-loop)](https://codecov.io/github/dhassanali/one-loop)

A Laravel/PHP Package for Minimizing Collection/Array Iterations

## Installation

Install the package via composer:

```bash
composer require hassan/one-loop
```

## Usage

``` php
$users = App\User::all();

$ids = one_loop($users)->reject(static function ($user) {
    return $user->age < 20;
})
->map(static function ($user) {
    return $user->id;
})
->apply();
```

### Security

If you discover any security related issues, please email hello@hassan-ali.me instead of using the issue tracker.

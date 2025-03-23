# Currencies Conversion Library (for Laravel)

This library is used to convert currency based on the ČNB register.

## Installation
```bash
composer require batlify/currencies-conversion
```

## Publish Config
```bash
php artisan vendor:publish --tag=currencies-conversion-config
```

## Example Of Usage
```php
<?php

namespace App\Http\Controllers;

use Batlify\CurrenciesConversion\CurrencyConverter;

class TestController extends Controller
{
    public function test()
    {
        $result = CurrencyConverter::convert(100.50, 'EUR');

        return response()->json($result);
    }

}
```
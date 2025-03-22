<?php

use Batlify\CurrencyConversion\CurrencyConverter;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Config::set('currencies-conversion.supported_currencies', [
        'CZK', 'EUR', 'USD', 'GBP'
    ]);
});

it('converts between CZK and EUR correctly', function () {
    Http::fake([
        '*' => Http::response(mockCnbRates())
    ]);

    $converter = new CurrencyConverter();
    $result = $converter->convert(100, 'EUR', 'CZK');

    expect($result)->toBeFloat();
    expect($result)->toBeGreaterThan(0);
});

it('throws exception for unsupported currency', function () {
    $converter = new CurrencyConverter();

    $converter->convert(100, 'XYZ', 'CZK');
})->throws(InvalidArgumentException::class);

it('throws exception when CNB API fails', function () {
    Http::fake([
        '*' => Http::response(null, 500)
    ]);

    $converter = new CurrencyConverter();

    $converter->convert(100, 'EUR', 'CZK');
})->throws(RuntimeException::class);

it('defaults fromCurrency to config value', function () {
    Http::fake([
        '*' => Http::response(mockCnbRates())
    ]);

    Config::set('currencies-conversion.default_currency', 'CZK');

    $converter = new CurrencyConverter();
    $result = $converter->convert(100, 'EUR');

    expect($result)->toBeFloat();
    expect($result)->toBeGreaterThan(0);
});

function mockCnbRates(): string
{
    return <<<EOD
země|měna|množství|kód|kurz
EMU|euro|1|EUR|24,980
USA|dolar|1|USD|23,061
Velká Británie|libra|1|GBP|29,815
EOD;
}

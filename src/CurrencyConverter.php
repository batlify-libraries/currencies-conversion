<?php

namespace Batlify\CurrenciesConversion;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use InvalidArgumentException;

final class CurrencyConverter
{
    public static function convert(
        float $amount,
        string $toCurrency,
        ?string $fromCurrency = null
    ): float {
        $fromCurrency ??= Config::get('currencies-conversion.default_currency', 'CZK');
        self::validateCurrencies($toCurrency, $fromCurrency);

        $rates = self::getCnbRates();

        $fromRate = $rates[$fromCurrency] ?? throw new InvalidArgumentException("Currency [$fromCurrency] is not available from CNB rates");
        $toRate = $rates[$toCurrency] ?? throw new InvalidArgumentException("Currency [$toCurrency] is not available from CNB rates");

        return round(($amount * $fromRate) / $toRate, 2);
    }

    private static function getCnbRates(): array
    {
        $url = 'https://www.cnb.cz/cs/financni_trhy/devizovy_trh/kurzy_devizoveho_trhu/denni_kurz.txt';
        $response = Http::get($url);

        if (! $response->ok()) {
            throw new RuntimeException('Failed to fetch CNB rates');
        }

        return self::parseCnbResponse($response->body());
    }

    private static function parseCnbResponse(string $body): array
    {
        $rates = collect(explode("\n", $body))
            ->filter(fn($line) => str_contains($line, '|'))
            ->map(function ($line) {
                [$country, $currencyName, $amount, $currencyCode, $rate] = explode('|', $line);

                $amount = (int) $amount;
                $rate = (float) str_replace(',', '.', $rate);

                if ($amount === 0 || ! is_numeric($rate)) {
                    return null;
                }

                return [$currencyCode => $rate / $amount];
            })
            ->filter()
            ->collapse()
            ->toArray();

        $rates['CZK'] = 1.0;

        return $rates;
    }

    private static function validateCurrencies(string $toCurrency, string $fromCurrency): void
    {
        $supported = Config::get('currencies-conversion.supported_currencies', []);

        foreach ([$toCurrency, $fromCurrency] as $currency) {
            if (! in_array($currency, $supported, true)) {
                throw new InvalidArgumentException("Currency [$currency] is not supported");
            }
        }
    }
}

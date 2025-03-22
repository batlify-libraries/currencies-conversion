<?php

namespace Batlify\CurrenciesConversion;

use Illuminate\Support\ServiceProvider;

class CurrenciesConversionServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/currencies-conversion.php' => config_path('currencies-conversion.php'),
        ], 'config');
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/currencies-conversion.php', 'currencies-conversion'
        );

        $this->app->singleton(CurrencyConverter::class, function () {
            return new CurrencyConverter(config('currencies-conversion'));
        });
    }
}

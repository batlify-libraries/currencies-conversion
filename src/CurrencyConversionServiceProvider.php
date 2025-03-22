<?php

namespace Batlify\CurrencyConversion;

use Illuminate\Support\ServiceProvider;

class CurrencyConversionServiceProvider extends ServiceProvider
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

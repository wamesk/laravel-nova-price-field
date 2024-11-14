<?php

namespace Wame\LaravelNovaPriceField\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;

class FieldServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Nova::serving(static function (ServingNova $event) {
            Nova::script('laravel-nova-price-field', __DIR__.'/../../dist/js/field.js');
            Nova::style('laravel-nova-price-field', __DIR__.'/../../dist/css/field.css');
            Nova::translations(__DIR__.'/../../resources/lang/'.app()->getLocale().'.json');
        });

        //        $this->loadTranslationsFrom(__DIR__ . '/../../resources/lang/', 'laravel-nova-price-field');
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }
}

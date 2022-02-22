<?php

namespace Litvinchuk\LaravelLiqpay;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;
use Litvinchuk\LaravelLiqpay\Http\Middleware\Authorize;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'laravel-liqpay');

        $this->app->booted(function () {
            $this->routes();
        });

        $this->publishes([
            __DIR__.'/../config/laravel-liqpay.php' => config_path('laravel-liqpay.php'),
        ]);

        $this->loadMigrationsFrom(__DIR__.'/../migrations');

        Nova::serving(function (ServingNova $event) {
            //
        });
    }

    /**
     * Register the tool's routes.
     *
     * @return void
     */
    protected function routes()
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        $this->mergeConfigFrom(
            __DIR__.'/../config/laravel-liqpay.php',
            'laravel-liqpay'
        );

        Route::middleware(['nova', Authorize::class])
                ->prefix('nova-vendor/laravel-liqpay')
                ->group(__DIR__.'/../routes/api.php');

        if ($this->app->config['laravel-liqpay.register_public_routes']) {
            LaravelLiqpay::publicRoutes();
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}

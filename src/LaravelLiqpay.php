<?php

namespace Litvinchuk\LaravelLiqpay;

use Laravel\Nova\Nova;
use Laravel\Nova\Tool;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;

class LaravelLiqpay extends Tool
{
    /**
     * Perform any tasks that need to happen when the tool is booted.
     *
     * @return void
     */
    public function boot()
    {
        Nova::script('laravel-liqpay', __DIR__.'/../dist/js/tool.js');
        Nova::style('laravel-liqpay', __DIR__.'/../dist/css/tool.css');
    }

    /**
     * Build the view that renders the navigation links for the tool.
     *
     * @return \Illuminate\View\View
     */
    public function renderNavigation()
    {
        return view('laravel-liqpay::navigation');
    }

    public static function publicRoutes()
    {
        Route::post(
            '/liqpay/status',
            '\Litvinchuk\LaravelLiqpay\Http\Controllers\PaymentsController@status'
        )->name('liqpay.status-update');

        Route::get(
            '/liqpay/pay/{paymentUuid}',
            '\Litvinchuk\LaravelLiqpay\Http\Controllers\PaymentsController@payByLink'
        )->name('liqpay.pay-link');
    }
}

<?php

return [

    'public_key' => env('LIQPAY_PUBLIC_KEY', null),

    'private_key' => env('LIQPAY_PRIVATE_KEY', null),

    // You can overrdide this url in Order model by getPaymentResultUrl($payment) method
    // or by static method \Litvinchuk\LaravelLiqpay\LiqpayPayment::setDefaultResultUrl($url)
    'default_result_url' => '/',

    // for server-server data exchange with liqpay
    // and payment url for users
    // you can disabled it in config and register manually in routes.php
    // Litvinchuk\LaravelLiqpay\LaravelLiqpay::publicRoutes();
    'register_public_routes' => true,

];

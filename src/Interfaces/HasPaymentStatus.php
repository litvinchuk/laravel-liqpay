<?php

namespace Litvinchuk\LaravelLiqpay\Interfaces;

use Litvinchuk\LaravelLiqpay\LiqpayPayment;

interface HasPaymentStatus
{
    public function updatePaymentStatus(LiqpayPayment $payment);
}

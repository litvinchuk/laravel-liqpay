<?php

namespace Litvinchuk\LaravelLiqpay;

use Illuminate\Database\Eloquent\Model;

class LiqpayPaymentLog extends Model
{
    public $fillable = [
        'type',
        'ip',
        'payload',
        'is_successfuly_handled',
        'fail_reason',
    ];

    public $casts = [
        'type' => 'string',
        'ip' => 'string',
        'payload' => 'array',
        'is_successfuly_handled' => 'bool',
        'fail_reason' => 'string',
    ];

    public function markAsFailed($reason)
    {
        $this->is_successfuly_handled = false;
        $this->fail_reason = $reason;
        $this->save();
    }

    public function markAsSuccesfullyHandled()
    {
        $this->is_successfuly_handled = true;
        $this->save();
    }

    public function associatePayment(LiqpayPayment $payment)
    {
        $this->payment()->associate($payment);
        $this->save();

        return $this;
    }

    public function payment()
    {
        return $this->belongsTo(LiqpayPayment::class, 'liqpay_payment_id');
    }
}

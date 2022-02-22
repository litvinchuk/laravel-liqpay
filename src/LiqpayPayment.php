<?php

namespace Litvinchuk\LaravelLiqpay;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;
use Litvinchuk\LaravelLiqpay\Interfaces\HasPaymentStatus;

class LiqpayPayment extends Model
{
    public $fillable = [
        'action',
        'language',
        'description',
        'currency',
        'amount',
        'status',
        'completion_date',
        'err_code',
        'err_description',
        'sender_card_bank',
        'sender_card_country',
        'sender_card_mask2',
        'sender_card_type',
        'sender_commission',
        'sender_first_name',
        'sender_last_name',
        'sender_phone',
    ];

    protected static $defaultResultUrl = null;

    public $casts = [
        'amount' => 'float',
        'is_successful' => 'bool',
    ];

    public static function availableStatuses()
    {
        return collect([

            // primary statuses
            ['id' => 'error', 'label' => 'Неуспешный платеж. Некорректно заполнены данные'],
            ['id' => 'failure', 'label' => 'Неуспешный платеж'],
            ['id' => 'reversed', 'label' => 'Платеж возвращен'],
            ['id' => 'subscribed', 'label' => 'Подписка успешно оформлена'],
            ['id' => 'success', 'label' => 'Успешный платеж'],
            ['id' => 'unsubscribed', 'label' => 'Подписка успешно деактивирована'],

            // verifications
            ['id' => '3ds_verify', 'label' => 'Требуется 3DS верификация.'],
            ['id' => 'captcha_verify', 'label' => 'Ожидается подтверждение captcha'],
            ['id' => 'cvv_verify', 'label' => 'Требуется ввод CVV карты отправителя.'],
            ['id' => 'ivr_verify', 'label' => 'Ожидается подтверждение звонком ivr'],
            ['id' => 'otp_verify', 'label' => 'Требуется OTP подтверждение клиента.'],
            ['id' => 'password_verify', 'label' => 'Ожидается подтверждение пароля приложения Приват24'],
            ['id' => 'phone_verify', 'label' => 'Ожидается ввод телефона клиентом.'],
            ['id' => 'pin_verify', 'label' => 'Ожидается подтверждение pin-code'],
            ['id' => 'receiver_verify', 'label' => 'Требуется ввод данных получателя.'],
            ['id' => 'sender_verify', 'label' => 'Требуется ввод данных отправителя.'],
            ['id' => 'senderapp_verify', 'label' => 'Ожидается подтверждение в приложении SENDER'],
            ['id' => 'wait_qr', 'label' => 'Ожидается сканирование QR-кода клиентом'],
            ['id' => 'wait_sender', 'label' => 'Ожидается подтверждение оплаты клиентом в приложении Privat24/SENDER'],
            ['id' => 'p24_verify', 'label' => 'Ожидается завершение платежа в Приват24'],
            ['id' => 'mp_verify', 'label' => 'Ожидается завершение платежа в кошельке MasterPass'],

            // other
            ['id' => 'cash_wait', 'label' => 'Ожидается оплата наличными в ТСО'],
            ['id' => 'hold_wait', 'label' => 'Сумма успешно заблокирована на счету отправителя'],
            ['id' => 'invoice_wait', 'label' => 'Инвойс создан успешно, ожидается оплата'],
            ['id' => 'prepared', 'label' => 'Платеж создан, ожидается его завершение отправителем'],
            ['id' => 'processing', 'label' => 'Платеж обрабатывается'],
            ['id' => 'wait_accept', 'label' => 'Деньги с клиента списаны, но магазин еще не прошел проверку.'],
            ['id' => 'wait_card', 'label' => 'Не установлен способ возмещения у получателя'],
            ['id' => 'wait_compensation', 'label' => 'Платеж успешный, будет зачислен в ежесуточной проводке'],
            ['id' => 'wait_lc', 'label' => 'Аккредитив. Деньги с клиента списаны, '
                . 'ожидается подтверждение доставки товара'],
            ['id' => 'wait_reserve', 'label' => 'Средства по платежу зарезервированы для'
                . ' проведения возврата по ранее поданной заявке'],
            ['id' => 'wait_secure', 'label' => 'Платеж на проверке'],
            ['id' => 'try_again', 'label' => 'Оплата неуспешна. Клиент может повторить попытку еще раз'],
        ]);
    }

    public function getStatusTitleAttribute()
    {
        return optional(
            static::availableStatuses()->where('id', $this->status)->first()
        )['label'];
    }
    
    public function isSuccess()
    {
        if ($this->status) {
            return $this->status === 'success';
        }
    }
    
    public function isWaitAccept()
    {
        if ($this->status) {
            return $this->status === 'wait_accept';
        }
    }
    
    public function isMoneyReceived()
    {
        return $this->isSuccess() || $this->isWaitAccept();
    }

    public function logs()
    {
        return $this->hasMany(LiqpayPaymentLog::class);
    }

    public function isFailed()
    {
        if ($this->status) {
            return $this->status === 'failure';
        }
    }

    public static function setDefaultResultUrl($url)
    {
        static::$defaultResultUrl = $url;
    }

    public static function detectLanguage()
    {
        $available = ['en', 'uk', 'ru'];

        $appLocale = app()->getLocale();

        if (in_array($appLocale, $available)) {
            return $appLocale;
        }

        return $available[0];
    }

    public function getResultUrl()
    {
        if ($this->order && method_exists($this->order, 'getPaymentResultUrl')) {
            return $this->order->getPaymentResultUrl($this);
        }

        return static::$defaultResultUrl
            ?: url(config('laravel-liqpay.default_result_url'));
    }

    public function order()
    {
        return $this->morphTo();
    }

    public static function createPayment($amount, $currency, $description = '', $language = null)
    {
        $payment = static::makePayment($amount, $currency, $description, $language);

        $payment->save();

        return $payment;
    }

    public static function makePayment($amount, $currency, $description = '', $language = null)
    {
        return new self([
            'action' => 'pay',
            'language' => $language ?: static::detectLanguage(),
            'amount' => $amount,
            'currency' => $currency,
            'description' => $description,
        ]);
    }

    public function renderInline($name = 'default', $mode = 'embed')
    {
        return view('laravel-liqpay::inline', [
            'payment' => $this,
            'mode' => $mode,
            'name' => $name,
        ]);
    }

    public function getPaymentLink()
    {
        return route('liqpay.pay-link', [$this->uuid]);
    }

    public function getSignature()
    {
        $privateKey = config('laravel-liqpay.private_key');

        return base64_encode(
            sha1($privateKey . $this->getEncodedParams() . $privateKey, 1)
        );
    }

    public function getEncodedParams()
    {
        return base64_encode(json_encode($this->getParams()));
    }

    public function getParams()
    {
        $publicKey = config('laravel-liqpay.public_key');

        return array_filter([
            'result_url' => $this->getResultUrl(),
            'server_url' => route('liqpay.status-update'),
            'public_key' => $publicKey,
            'action' => $this->action,
            'language' => $this->language,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'description' => $this->description,
            'order_id' => $this->uuid,
            'version' => '3',
        ]);
    }

    public static function boot()
    {
        parent::boot();

        self::saved(function ($model) {
            if ($model->order instanceof HasPaymentStatus) {
                $model->order->updatePaymentStatus($model);
            }
        });

        self::saving(function ($model) {
            $model->is_successful = $model->isSuccess();
        });

        self::creating(function ($model) {
            $model->uuid = (string) Uuid::uuid4();
        });
    }
}

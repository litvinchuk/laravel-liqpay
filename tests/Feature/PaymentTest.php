<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Litvinchuk\LaravelLiqpay\LiqpayPayment;
use Ramsey\Uuid\Uuid;

class ExampleTest extends TestCase
{
    use DatabaseTransactions;

    public $publicKey;

    public $privateKey;

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicTest()
    {
        $payment = LiqpayPayment::createPayment(100.5, 'USD', 'test decription');

        $serializedData = base64_encode(json_encode([
            'order_id' => $payment->uuid,
            'amount' => $payment->amount,
            'currency' => $payment->currency,
        ]));

        $signature = base64_encode(
            sha1($this->privateKey.$serializedData.$this->privateKey, 1)
        );

        $response = $this->post(route('liqpay.status-update'), [
            'data' => $serializedData,
            'signature' => $signature,
        ]);

        $response->assertStatus(200);
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->publicKey = config('laravel-liqpay.public_key');
        $this->privateKey = config('laravel-liqpay.private_key');
    }
}

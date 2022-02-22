# Installation

Add repository to `composer.json`

```json
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/litvinchuk/laravel-liqpay.git"
    }
]
```

Install library: `composer require litvinchuk/laravel-liqpay`

Register Tool in `NovaServiceProvider`

`App\Providers\NovaServiceProvider.php`

```php
public function tools()
{
    return [
        new \Litvinchuk\LaravelLiqpay\LaravelLiqpay([
            'registerPublicRoutes' => true,
        ]),
    ];
}
```

# Configuration

Add secret and public keys to `.env`

```
LIQPAY_PUBLIC_KEY=public
LIQPAY_PRIVATE_KEY=secret
```

# Create payment

```php
use Litvinchuk\LaravelLiqpay\LiqpayPayment;

$payment = LiqpayPayment::createPayment(10, 'UAH', 'Test payment description');
```

## Pay by link
```php
$payment->getPaymentLink()
```

## Inline pay
### render
```php
$payment->renderInline()
```
### manual
```html
<div id="liqpay_checkout"></div>

<script>
    window.LiqPayCheckoutCallback = function() {
        LiqPayCheckout.init({
            data: "{{ $payment->getEncodedParams() }}",
            signature: "{{ $payment->getSignature() }}",
            embedTo: "#liqpay_checkout",
            language: "{{ $payment->language }}",
            mode: "embed" // embed || popup
        }).on("liqpay.callback", function(data){
             console.log(data.status);
             console.log(data);
         }).on("liqpay.ready", function(data){
             // ready
         }).on("liqpay.close", function(data){
             // close
         });
    };
</script>

<script src="//static.liqpay.ua/libjs/checkout.js" async></script>

```

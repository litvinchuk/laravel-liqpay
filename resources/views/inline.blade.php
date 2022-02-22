<div id="liqpay_checkout_{{ $name }}"></div>

<script>
    window.LiqPayCheckoutEventBus = window.LiqPayCheckoutEventBus || {
        LiqPayCheckoutInstances: {},
        LiqPayCheckoutEvntsQueue: {},
        registerEvents: function (name) {
            if (this.LiqPayCheckoutEvntsQueue[name] && this.LiqPayCheckoutInstances[name]) {
                this.LiqPayCheckoutEvntsQueue[name].forEach(function (data) {
                    this.LiqPayCheckoutInstances[name].on(data.on, data.callback)
                })
                this.LiqPayCheckoutEvntsQueue[name] = []
            }
        },
        on: function (eventName, callback, instanceName) {
            instanceName = instanceName || '{{ $name }}'

            if (this.LiqPayCheckoutInstances[instanceName]) {
                this.LiqPayCheckoutInstances[instanceName].on(eventName, callback)
                return this
            }

            if (! this.LiqPayCheckoutEvntsQueue[instanceName]) {
                this.LiqPayCheckoutEvntsQueue[instanceName] = []
            }

            this.LiqPayCheckoutEvntsQueue[instanceName]
                .push({on: eventName, callback: callback})

            return this
        }
    };

    window.LiqPayCheckoutCallback = function() {
        // window.LiqPayCheckoutEventBus.LiqPayCheckoutInstances['{{ $name }}'] =
        LiqPayCheckout.init({
            data: "{{ $payment->getEncodedParams() }}",
            signature: "{{ $payment->getSignature() }}",
            embedTo: "#liqpay_checkout_{{ $name }}",
            language: "{{ $payment->language }}",
            mode: "{{ isset($mode) ? $mode : 'embed' }}" // embed || popup
        });

        // window.LiqPayCheckoutEventBus.registerEvents('{{ $name }}')

        // .on("liqpay.callback", function(data){
        //     console.log(data.status);
        //     console.log(data);
        // }).on("liqpay.ready", function(data){
        //     // ready
        // }).on("liqpay.close", function(data){
        //     // close
        // });
    };
</script>

<script src="//static.liqpay.ua/libjs/checkout.js" async></script>

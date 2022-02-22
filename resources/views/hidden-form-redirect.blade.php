<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Redirecting...</title>
</head>
<body onload="document.forms[0].submit();">
    <form method="POST" action="{{ $url }}" accept-charset="utf-8">
        <p>Redirecting to payment page...</p>
        <p>
            <input type="hidden" name="data" value="{{ $payment->getEncodedParams() }}"/>
            <input type="hidden" name="signature" value="{{ $payment->getSignature() }}"/>
            <input type="submit" value="Continue" />
        </p>
    </form>
</body>
</html>

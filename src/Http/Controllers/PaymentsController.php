<?php

namespace Litvinchuk\LaravelLiqpay\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Litvinchuk\LaravelLiqpay\Http\Requests\StatusUpdateRequest;
use Litvinchuk\LaravelLiqpay\LiqpayPayment;
use Litvinchuk\LaravelLiqpay\Exceptions\PaymentStatusUpdateException;
use Exception;
use Carbon\Carbon;
use Litvinchuk\LaravelLiqpay\Http\Resources\LiqpayPayment as LiqpayPaymentResource;

class PaymentsController extends BaseController
{
    public function paymentsIndex(Request $request)
    {
        $query = LiqpayPayment::orderBy('id', 'DESC');

        if ($request->from) {
            try {
                $from = Carbon::createFromFormat('d.m.Y', $request->from)->startOfDay();
                $query->where('created_at', '>=', $from);
            } catch (\Exception $e) {
                //
            }
        }

        if ($request->to) {
            try {
                $to = Carbon::createFromFormat('d.m.Y', $request->to)->endOfDay();
                $query->where('created_at', '<=', $to);
            } catch (\Exception $e) {
                //
            }
        }

        if ($request->only_success) {
            $query->where('is_successful', 1);
        }

        return LiqpayPaymentResource::collection(
            $query->paginate()->appends($request->all())
        );
    }

    public function payByLink(Request $request, $paymentUuid)
    {
        $url = 'https://www.liqpay.ua/api/3/checkout';

        $payment = LiqpayPayment::where('uuid', $paymentUuid)->firstOrFail();

        if ($request->expectsJson()) {
            return [
                'isPaid' => $payment->isSuccess(),
                'url' => $url,
                'formData' => [
                    'data' => $payment->getEncodedParams(),
                    'signature' => $payment->getSignature(),
                ],
            ];
        }

        if ($payment->isSuccess()) {
            return redirect($payment->getResultUrl());
        }

        return view('laravel-liqpay::hidden-form-redirect', [
            'url' => $url,
            'payment' => $payment,
        ]);
    }

    public function status(StatusUpdateRequest $request)
    {
        try {
            $data = $request->getDecodedData();

            $this->handlePaymentStatusUpdate(
                LiqpayPayment::where('uuid', $data['order_id'])->firstOrFail(),
                $data,
                $request
            );
        } catch (Exception $e) {
            $request->getLog()->markAsFailed('Excaption: ' . $e->getMessage());

            throw $e;
        }

        $request->getLog()->markAsSuccesfullyHandled();

        return [
            'status' => 'handled',
        ];
    }

    protected function handlePaymentStatusUpdate(LiqpayPayment $payment, $data, $request)
    {
        $request->getLog()
            ->associatePayment($payment);

        if ($payment->currency !== $data['currency']) {
            throw new PaymentStatusUpdateException(
                'Wrong payment currency'
            );
        }

        if ($payment->amount !== (float) $data['amount']) {
            throw new PaymentStatusUpdateException(
                'Wrong payment amount'
            );
        }

        $payment->fill($data);

        $payment->save();
    }
}

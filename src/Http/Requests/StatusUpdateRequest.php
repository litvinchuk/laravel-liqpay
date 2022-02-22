<?php

namespace Litvinchuk\LaravelLiqpay\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Litvinchuk\LaravelLiqpay\LiqpayPaymentLog;
use Illuminate\Contracts\Validation\Validator;

class StatusUpdateRequest extends FormRequest
{
    protected $log;

    protected $rawData;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    public function getDecodedData()
    {
        return $this->data;
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $this->log = LiqpayPaymentLog::create([
            'type' => 'status-update',
            'ip' => $this->getClientIp(),
        ]);

        $this->rawData = $this->data;

        if (! $decodedData = json_decode(base64_decode($this->data), true)) {
            $this->log->update(['payload' => $this->all()]);

            $this->log->markAsFailed('Unable decode data');

            abort(422, ['errors' => ['data' => ['Data is invalid']]], [
                'Content-Type' => 'application/json'
            ]);
        }

        $this->getInputSource()->set('data', $decodedData);

        $this->log->update(['payload' => $this->all()]);
    }

    public function getLog()
    {
        return $this->log;
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function failedValidation(Validator $validator)
    {
        $this->getLog()->markAsFailed('failed validation');

        abort(422, $validator->errors($this->errorBag)->toJson(), [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'data' => ['required', 'array'],
            'data.order_id' => ['required', 'exists:liqpay_payments,uuid'],
            'data.currency' => ['required'],
            'data.amount' => ['required', 'numeric'],
            'signature' => ['required', 'string', function ($attribute, $value, $fail) {
                $privateKey = config('laravel-liqpay.private_key');

                $signature = base64_encode(
                    sha1($privateKey.$this->rawData.$privateKey, 1)
                );

                if ($signature !== $value) {
                    $fail('Invalid signature');
                }
            }],
        ];
    }
}

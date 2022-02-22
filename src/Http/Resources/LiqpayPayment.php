<?php

namespace Litvinchuk\LaravelLiqpay\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LiqpayPayment extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $data = [
            'id' => $this->resource->id,
            'amount' => $this->resource->amount,
            'currency' => $this->resource->currency,
            'status' => $this->resource->status,
            'sender_card_bank' => $this->resource->sender_card_bank,
            'created_at' => $this->resource->created_at->format('d.m.Y H:i'),
            'isSuccess' => $this->resource->is_successful,
            'statusTitle' => $this->resource->statusTitle,
            'order' => null,
            'logs' => LiqpayPaymentLog::collection(
                $this->resource->logs()->limit(12)->get()
            ),
        ];

        if ($this->resource->order) {
            if ($novaResource = \Nova::resourceForModel($this->resource->order)) {
                $data['order'] = [
                    'value' => (new $novaResource($this->resource->order))->title(),
                    'resourceName' => $novaResource::uriKey(),
                    'morphToId' => $this->resource->order->id,
                ];
            }
        }

        return $data;
    }
}

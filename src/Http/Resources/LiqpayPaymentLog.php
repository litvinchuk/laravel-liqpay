<?php

namespace Litvinchuk\LaravelLiqpay\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LiqpayPaymentLog extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->resource->id,
            'type' => $this->resource->type,
            'ip' => $this->resource->ip,
            'payload' => json_encode($this->resource->payload, JSON_PRETTY_PRINT),
            'is_successfuly_handled' => $this->resource->is_successfuly_handled,
            'fail_reason' => $this->resource->fail_reason,
            'created_at' => $this->resource->created_at->format('d.m.Y H:i:s'),
        ];
    }
}

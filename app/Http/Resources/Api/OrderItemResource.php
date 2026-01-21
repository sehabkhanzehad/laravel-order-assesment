<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'order-item',
            'id' => $this->id,
            'attributes' => [
                'productName' => $this->product_name,
                'productSku' => $this->product_sku,
                'quantity' => $this->quantity,
                'unitPrice' => $this->unit_price,
                'subTotal' => $this->sub_total,
            ],
            'relationships' => [
                'order' => new OrderResource($this->whenLoaded('order')),
            ],
        ];
    }
}

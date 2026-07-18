<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentLineResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'description' => $this->description,
            'quantity' => (float) $this->quantity,
            'unit' => $this->unit,
            'unit_price' => (float) $this->unit_price,
            'discount_percent' => (float) $this->discount_percent,
            'tax_rate' => (float) $this->tax_rate,
            'line_total' => (float) $this->line_total,
            'sort_order' => $this->sort_order,
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'name' => $this->name,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'description' => $this->description,
            'unit' => $this->unit,
            'price' => (float) $this->price,
            'cost' => $this->cost !== null ? (float) $this->cost : null,
            'tax_rate' => (float) $this->tax_rate,
            'track_stock' => (bool) $this->track_stock,
            'stock_quantity' => $this->stock_quantity !== null ? (float) $this->stock_quantity : null,
            'stock_alert_threshold' => $this->stock_alert_threshold !== null ? (float) $this->stock_alert_threshold : null,
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

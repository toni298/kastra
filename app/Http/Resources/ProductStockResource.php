<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductStockResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $minimum = $this->product->minimum_stock;
        $value = $this->quantity * $this->product->purchase_price;
        [$status, $label, $variant] = $this->status($minimum);

        return [
            'id' => $this->id,
            'gudang_id' => $this->gudang_id,
            'quantity' => $this->quantity,
            'quantity_display' => "{$this->quantity} {$this->product->unit->name}",
            'minimum_stock' => $minimum,
            'minimum_display' => "{$minimum} {$this->product->unit->name}",
            'inventory_value' => $value,
            'inventory_value_display' => 'Rp '.number_format($value, 0, ',', '.'),
            'status' => $status,
            'status_label' => $label,
            'status_variant' => $variant,
            'product' => ProductResource::make($this->product)->resolve($request),
            'gudang' => $this->gudang->only('id', 'nama'),
        ];
    }

    private function status(int $minimum): array
    {
        if ($this->quantity === 0) {
            return ['out', 'Habis', 'error'];
        }

        if ($this->quantity <= $minimum) {
            return ['low', 'Hampir Habis', 'warning'];
        }

        return ['safe', 'Aman', 'success'];
    }
}

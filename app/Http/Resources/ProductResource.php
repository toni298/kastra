<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'brand_id' => $this->brand_id,
            'unit_id' => $this->unit_id,
            'name' => $this->name,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'purchase_price' => $this->purchase_price,
            'selling_price' => $this->selling_price,
            'minimum_stock' => $this->minimum_stock,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'category' => $this->whenLoaded('category', fn () => $this->category?->only('id', 'name')),
            'brand' => $this->whenLoaded('brand', fn () => $this->brand?->only('id', 'name')),
            'unit' => $this->whenLoaded('unit', fn () => $this->unit?->only('id', 'name')),
            'images' => $this->whenLoaded(
                'images',
                fn () => ProductImageResource::collection($this->images)->resolve($request),
            ),
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockOpnameResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $hasDetails = $this->relationLoaded('details');
        $details = $hasDetails ? $this->details : collect();
        $total = $hasDetails ? $details->count() : (int) ($this->details_count ?? 0);
        $checked = $hasDetails
            ? $details->whereNotNull('physical_quantity')->count()
            : (int) ($this->checked_details_count ?? 0);
        $difference = $hasDetails
            ? $details->sum(fn($detail) => $detail->physical_quantity === null ? 0 : $detail->physical_quantity - $detail->system_quantity)
            : (int) ($this->difference_quantity ?? 0);
        return [
            'id' => $this->id,
            'number' => $this->opname_number,
            'warehouse' => $this->gudang?->nama ?? $this->branch?->name,
            'location' => $this->gudang?->nama ?? $this->branch?->name,
            'source_type' => $this->source_type,
            'source_label' => $this->source_type === 'cabang' ? 'Cabang' : 'Gudang',
            'branch_id' => $this->branch_id,
            'branch' => $this->branch?->only('id', 'name'),
            'warehouse_id' => $this->gudang_id,
            'date' => $this->opname_date?->format('d/m/Y'),
            'status' => match ($this->status) {
                'in_progress' => 'Proses',
                'completed' => 'Selesai',
                'cancelled' => 'Dibatalkan',
                default => 'Draft'
            },
            'status_code' => $this->status,
            'variant' => match ($this->status) {
                'completed' => 'success',
                'cancelled' => 'error',
                'in_progress' => 'warning',
                default => 'info'
            },
            'total_products' => $total,
            'checked_products' => $checked,
            'progress' => $total ? (int) round($checked / $total * 100) : 0,
            'difference' => $difference,
            'difference_label' => $difference > 0 ? '+' . $difference : (string) $difference,
            'details' => $this->when($hasDetails, fn() => $details->map(fn($detail) => [
                'id' => $detail->id,
                'product_stock_id' => $detail->product_stock_id ?? $detail->branch_product_stock_id,
                'product' => ($detail->productStock?->product ?? $detail->branchProductStock?->product)?->name,
                'sku' => ($detail->productStock?->product ?? $detail->branchProductStock?->product)?->sku,
                'unit' => (($detail->productStock?->product ?? $detail->branchProductStock?->product)?->unit)?->name,
                'system_quantity' => $detail->system_quantity,
                'physical_quantity' => $detail->physical_quantity,
                'difference' => $detail->physical_quantity === null ? null : $detail->physical_quantity - $detail->system_quantity,
                'note' => $detail->note,
            ])->values()),
            'note' => $this->note,
        ];
    }
}

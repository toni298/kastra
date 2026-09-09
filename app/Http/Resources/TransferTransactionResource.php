<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransferTransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'number' => $this->transfer_number,
            'source' => $this->sourceGudang->nama,
            'destination' => $this->destination_type === 'branch' ? $this->destinationBranch->name : $this->destinationGudang?->nama,
            'destination_type' => $this->destination_type,
            'workflow_status' => $this->workflow_status,
            'status' => match ($this->workflow_status) {
                'received' => 'Diterima',
                'partially_received' => 'Diterima Sebagian',
                'cancelled' => 'Dibatalkan',
                default => 'Dalam Perjalanan',
            },
            'variant' => match ($this->workflow_status) {
                'received' => 'success',
                'partially_received' => 'warning',
                'cancelled' => 'error',
                default => 'info',
            },
            'date' => $this->transfer_date->format('d/m/Y'),
            'note' => $this->note,
            'details_count' => (int) ($this->details_count ?? 0),
            'details' => $this->when($this->relationLoaded('details'), fn() => $this->details->map(fn($detail) => [
                'product' => $detail->sourceStock->product->name,
                'sku' => $detail->sourceStock->product->sku,
                'quantity' => $detail->quantity,
                'received_quantity' => $detail->received_quantity,
                'source_stock_id' => $detail->source_stock_id,
                'detail_id' => $detail->id,
                'unit' => $detail->sourceStock->product->unit->name,
            ])->values()),
            'timelines' => $this->when($this->relationLoaded('timelines'), fn() => $this->timelines->map(fn($timeline) => ['event' => $timeline->event, 'note' => $timeline->note, 'date' => $timeline->created_at?->format('d/m/Y H:i'), 'user' => $timeline->user?->name])->values()),
        ];
    }
}

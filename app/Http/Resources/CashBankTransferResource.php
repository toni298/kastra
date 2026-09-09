<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CashBankTransferResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'number' => $this->transfer_number,
            'source' => collect([$this->sourceAccount?->name, $this->sourceAccount?->bank_name])->filter()->join(' - '),
            'destination' => collect([$this->destinationAccount?->name, $this->destinationAccount?->bank_name])->filter()->join(' - '),
            'amount' => $this->amount,
            'date' => $this->transfer_date?->format('d/m/Y'),
            'isoDate' => $this->transfer_date?->toDateString(),
            'reference' => $this->reference,
        ];
    }
}

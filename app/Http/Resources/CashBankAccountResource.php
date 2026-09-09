<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CashBankAccountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'name' => $this->name, 'type' => match ($this->type) { 'cash' => 'Kas', 'e_wallet' => 'Dompet Digital', default => 'Bank' }, 'account_type' => $this->type, 'bank' => $this->bank_name, 'number' => $this->account_number ?: '-', 'holder' => $this->account_holder, 'currency' => $this->currency, 'opening_balance' => $this->opening_balance, 'balance' => 'Rp '.number_format($this->current_balance, 0, ',', '.'), 'balance_value' => $this->current_balance, 'status' => $this->is_active ? 'Aktif' : 'Nonaktif', 'variant' => $this->is_active ? 'success' : 'neutral', 'settings' => $this->whenLoaded('settings', fn () => $this->settings->map(fn ($setting) => ['scope' => $setting->branch?->name ?? 'Semua Cabang', 'can_receive_money' => $setting->can_receive_money, 'can_send_money' => $setting->can_send_money, 'is_default_receive' => $setting->is_default_receive, 'is_default_payment' => $setting->is_default_payment, 'is_active' => $setting->is_active])->values())];
    }
}

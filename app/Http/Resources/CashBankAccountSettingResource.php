<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CashBankAccountSettingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'cash_bank_account_id' => $this->cash_bank_account_id,
            'account' => $this->whenLoaded('account', fn () => [
                'id' => $this->account->id,
                'name' => $this->account->name,
                'type' => $this->account->type,
                'bank_name' => $this->account->bank_name,
                'account_number' => $this->account->account_number,
            ]),
            'branch_id' => $this->branch_id,
            'branch' => $this->whenLoaded('branch', fn () => $this->branch ? ['id' => $this->branch->id, 'name' => $this->branch->name] : null),
            'can_receive_money' => $this->can_receive_money,
            'can_send_money' => $this->can_send_money,
            'is_default_receive' => $this->is_default_receive,
            'is_default_payment' => $this->is_default_payment,
            'is_active' => $this->is_active,
        ];
    }
}

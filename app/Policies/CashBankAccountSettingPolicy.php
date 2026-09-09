<?php

namespace App\Policies;

use App\Models\CashBankAccountSetting;
use App\Models\User;
use App\Services\CompanyContext;

class CashBankAccountSettingPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->withinCompanyContext($user) && $user->can('cash_bank.settings.view');
    }

    public function create(User $user): bool
    {
        return $this->withinCompanyContext($user) && $user->can('cash_bank.settings.create');
    }

    public function view(User $user, CashBankAccountSetting $setting): bool
    {
        return $this->withinCompanyContext($user)
            && $user->can('cash_bank.settings.view')
            && (string) $setting->company_id === (string) $user->company_id;
    }

    public function update(User $user, CashBankAccountSetting $setting): bool
    {
        return $this->view($user, $setting) && $user->can('cash_bank.settings.edit');
    }

    public function delete(User $user, CashBankAccountSetting $setting): bool
    {
        return $this->view($user, $setting) && $user->can('cash_bank.settings.delete');
    }

    private function withinCompanyContext(User $user): bool
    {
        return (string) $user->company_id === (string) app(CompanyContext::class)->id();
    }
}

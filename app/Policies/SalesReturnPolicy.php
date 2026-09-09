<?php

namespace App\Policies;

use App\Models\SalesReturn;
use App\Models\User;
use App\Services\CompanyContext;
use App\Services\InertiaAuthorizationService;

class SalesReturnPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->allowsInCurrentCompany($user, 'penjualan.returns.view');
    }

    public function view(User $user, SalesReturn $return): bool
    {
        return $this->allowsInCurrentCompany($user, 'penjualan.returns.view')
            && hash_equals((string) $user->company_id, (string) $return->company_id);
    }

    private function allowsInCurrentCompany(User $user, string $permission): bool
    {
        $companyId = app(CompanyContext::class)->id();

        return $companyId !== null
            && hash_equals((string) $user->company_id, (string) $companyId)
            && app(InertiaAuthorizationService::class)->allows($user, $permission);
    }
}

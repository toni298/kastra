<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;
use App\Services\CompanyContext;
use App\Services\InertiaAuthorizationService;

class CustomerPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->allowsInTenant($user, 'penjualan.customers.view');
    }
    public function view(User $user, Customer $customer): bool
    {
        return $this->allowsInTenant($user, 'penjualan.customers.view') && hash_equals((string) $user->company_id, (string) $customer->company_id);
    }
    public function create(User $user): bool
    {
        return $this->allowsInTenant($user, 'penjualan.customers.create');
    }
    public function update(User $user, Customer $customer): bool
    {
        return $this->allowsInTenant($user, 'penjualan.customers.edit') && hash_equals((string) $user->company_id, (string) $customer->company_id);
    }
    public function delete(User $user, Customer $customer): bool
    {
        return $this->allowsInTenant($user, 'penjualan.customers.delete') && hash_equals((string) $user->company_id, (string) $customer->company_id);
    }

    private function allowsInTenant(User $user, string $permission): bool
    {
        $companyId = app(CompanyContext::class)->id();
        return $companyId !== null && hash_equals((string) $user->company_id, $companyId) && app(InertiaAuthorizationService::class)->allows($user, $permission);
    }
}

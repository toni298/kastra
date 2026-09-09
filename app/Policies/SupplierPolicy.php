<?php

namespace App\Policies;

use App\Models\Supplier;
use App\Models\User;
use App\Services\CompanyContext;
use App\Services\InertiaAuthorizationService;

class SupplierPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->allowsInTenant($user, 'suppliers.view');
    }

    public function create(User $user): bool
    {
        return $this->allowsInTenant($user, 'suppliers.create');
    }

    public function view(User $user, Supplier $supplier): bool
    {
        return $this->viewAny($user) && (string) $user->company_id === (string) $supplier->company_id;
    }

    public function update(User $user, Supplier $supplier): bool
    {
        return $this->allowsInTenant($user, 'suppliers.edit') && $user->company_id === $supplier->company_id;
    }

    public function delete(User $user, Supplier $supplier): bool
    {
        return $this->allowsInTenant($user, 'suppliers.delete') && $user->company_id === $supplier->company_id;
    }

    private function allowsInTenant(User $user, string $permission): bool
    {
        $companyId = app(CompanyContext::class)->id();
        return $companyId !== null && (string) $user->company_id === $companyId && app(InertiaAuthorizationService::class)->allows($user, $permission);
    }
}

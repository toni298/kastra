<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use App\Services\CompanyContext;
use App\Services\InertiaAuthorizationService;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->allowsInCurrentTenant($user, 'products.view');
    }

    public function create(User $user): bool
    {
        return $this->allowsInCurrentTenant($user, 'products.create');
    }

    public function view(User $user, Product $product): bool
    {
        return $this->allowsForTenant($user, $product, 'products.view');
    }

    public function selectMasterData(User $user): bool
    {
        return $this->allowsInCurrentTenant($user, 'products.create')
            || $this->allowsInCurrentTenant($user, 'products.edit');
    }

    public function update(User $user, Product $product): bool
    {
        return $this->allowsForTenant($user, $product, 'products.edit');
    }

    public function delete(User $user, Product $product): bool
    {
        return $this->allowsForTenant($user, $product, 'products.delete');
    }

    private function allowsForTenant(User $user, Product $product, string $permission): bool
    {
        $companyId = app(CompanyContext::class)->id();

        return $this->allowsInCurrentTenant($user, $permission)
            && hash_equals((string) $companyId, (string) $product->company_id);
    }

    private function allowsInCurrentTenant(User $user, string $permission): bool
    {
        $companyId = app(CompanyContext::class)->id();

        return $companyId !== null
            && hash_equals((string) $user->company_id, (string) $companyId)
            && app(InertiaAuthorizationService::class)->allows($user, $permission);
    }
}

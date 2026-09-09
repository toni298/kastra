<?php

namespace App\Policies;

use App\Models\ProductStock;
use App\Models\User;
use App\Services\CompanyContext;
use App\Services\InertiaAuthorizationService;

class ProductStockPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->allowsInTenant($user, 'inventory.stock.view');
    }

    public function create(User $user): bool
    {
        return $this->allowsInTenant($user, 'inventory.stock.create');
    }

    public function update(User $user, ProductStock $stock): bool
    {
        return $this->allowsForStock($user, $stock, 'inventory.stock.edit');
    }

    public function delete(User $user, ProductStock $stock): bool
    {
        return $this->allowsForStock($user, $stock, 'inventory.stock.delete');
    }

    public function view(User $user, ProductStock $stock): bool
    {
        return $this->allowsForStock($user, $stock, 'inventory.stock.view');
    }

    private function allowsForStock(User $user, ProductStock $stock, string $permission): bool
    {
        return $this->allowsInTenant($user, $permission)
            && hash_equals((string) $user->company_id, (string) $stock->company_id);
    }

    private function allowsInTenant(User $user, string $permission): bool
    {
        $companyId = app(CompanyContext::class)->id();

        return $companyId !== null
            && hash_equals((string) $user->company_id, $companyId)
            && app(InertiaAuthorizationService::class)->allows($user, $permission);
    }
}

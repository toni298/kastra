<?php

namespace App\Policies;

use App\Models\StockOpname;
use App\Models\User;
use App\Services\CompanyContext;
use App\Services\InertiaAuthorizationService;

class StockOpnamePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->allowsInTenant($user, 'inventory.opname.view');
    }
    public function view(User $user, StockOpname $opname): bool
    {
        return $this->viewAny($user) && (string) $user->company_id === (string) $opname->company_id;
    }
    public function create(User $user): bool
    {
        return $this->allowsInTenant($user, 'inventory.opname.create');
    }
    public function update(User $user, StockOpname $opname): bool
    {
        return $this->allowsForOpname($user, $opname, 'inventory.opname.edit') && in_array($opname->status, ['draft', 'in_progress'], true);
    }
    public function complete(User $user, StockOpname $opname): bool
    {
        return $this->allowsForOpname($user, $opname, 'inventory.opname.process') && in_array($opname->status, ['draft', 'in_progress'], true);
    }
    public function delete(User $user, StockOpname $opname): bool
    {
        return $this->allowsForOpname($user, $opname, 'inventory.opname.delete') && $opname->status === 'draft';
    }

    private function allowsForOpname(User $user, StockOpname $opname, string $permission): bool
    {
        return $this->allowsInTenant($user, $permission)
            && (string) $user->company_id === (string) $opname->company_id;
    }

    private function allowsInTenant(User $user, string $permission): bool
    {
        $companyId = app(CompanyContext::class)->id();

        return $companyId !== null
            && hash_equals((string) $user->company_id, $companyId)
            && app(InertiaAuthorizationService::class)->allows($user, $permission);
    }
}

<?php

namespace App\Policies;

use App\Models\TransferTransaction;
use App\Models\User;
use App\Services\CompanyContext;
use App\Services\InertiaAuthorizationService;

class TransferTransactionPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->allowsInTenant($user, 'inventory.transfers.view');
    }
    public function create(User $user): bool
    {
        return $this->allowsInTenant($user, 'inventory.transfers.create');
    }
    public function view(User $user, TransferTransaction $transfer): bool
    {
        return $this->allowsForTransfer($user, $transfer, 'inventory.transfers.view');
    }
    public function update(User $user, TransferTransaction $transfer): bool
    {
        return $this->allowsForTransfer($user, $transfer, 'inventory.transfers.edit') && $transfer->workflow_status === 'in_transit';
    }
    public function delete(User $user, TransferTransaction $transfer): bool
    {
        return $this->allowsForTransfer($user, $transfer, 'inventory.transfers.delete') && $transfer->workflow_status === 'in_transit';
    }
    public function receive(User $user, TransferTransaction $transfer): bool
    {
        return $this->allowsForTransfer($user, $transfer, 'inventory.transfers.receive') && $transfer->workflow_status === 'in_transit';
    }

    private function allowsForTransfer(User $user, TransferTransaction $transfer, string $permission): bool
    {
        return $this->allowsInTenant($user, $permission)
            && hash_equals((string) $user->company_id, (string) $transfer->company_id);
    }

    private function allowsInTenant(User $user, string $permission): bool
    {
        $companyId = app(CompanyContext::class)->id();

        return $companyId !== null
            && hash_equals((string) $user->company_id, $companyId)
            && app(InertiaAuthorizationService::class)->allows($user, $permission);
    }
}

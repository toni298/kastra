<?php

namespace App\Policies;

use App\Models\SalesTransaction;
use App\Models\User;
use App\Services\CompanyContext;
use App\Services\InertiaAuthorizationService;

class SalesTransactionPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->allowsInTenant($user, 'penjualan.transactions.view');
    }
    public function view(User $user, SalesTransaction $transaction): bool
    {
        return $this->allowsInTenant($user, 'penjualan.transactions.view') && hash_equals((string) $user->company_id, (string) $transaction->company_id);
    }
    public function create(User $user): bool
    {
        return $this->allowsInTenant($user, 'penjualan.transactions.create');
    }
    public function delete(User $user, SalesTransaction $transaction): bool
    {
        return $this->allowsInTenant($user, 'penjualan.transactions.delete')
            && hash_equals((string) $user->company_id, (string) $transaction->company_id)
            && $transaction->finalized_at === null;
    }
    public function update(User $user, SalesTransaction $transaction): bool
    {
        return $this->allowsInTenant($user, 'penjualan.transactions.edit')
            && hash_equals((string) $user->company_id, (string) $transaction->company_id)
            && $transaction->status !== 'cancelled'
            && $transaction->finalized_at === null;
    }

    public function payment(User $user, SalesTransaction $transaction): bool
    {
        return $this->allowsInTenant($user, 'penjualan.transactions.payment')
            && hash_equals((string) $user->company_id, (string) $transaction->company_id)
            && $transaction->status !== 'cancelled'
            && $transaction->finalized_at === null;
    }

    public function manageReturn(User $user, SalesTransaction $transaction): bool
    {
        return $this->allowsInTenant($user, 'penjualan.transactions.return')
            && hash_equals((string) $user->company_id, (string) $transaction->company_id)
            && in_array($transaction->status, ['completed', 'partial_return'], true)
            && $transaction->finalized_at === null;
            }

    public function finalize(User $user, SalesTransaction $transaction): bool
    {
        return $this->allowsInTenant($user, 'penjualan.transactions.edit')
            && hash_equals((string) $user->company_id, (string) $transaction->company_id)
            && $transaction->status === 'completed'
            && $transaction->payment_status === 'paid'
            && $transaction->finalized_at === null;
    }

    public function print(User $user, SalesTransaction $transaction): bool
    {
        return $this->allowsInTenant($user, 'penjualan.transactions.print')
            && hash_equals((string) $user->company_id, (string) $transaction->company_id);
    }

    private function allowsInTenant(User $user, string $permission): bool
    {
        $companyId = app(CompanyContext::class)->id();

        return $companyId !== null
            && hash_equals((string) $user->company_id, $companyId)
            && app(InertiaAuthorizationService::class)->allows($user, $permission);
    }
}

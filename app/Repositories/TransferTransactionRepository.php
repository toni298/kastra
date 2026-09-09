<?php

namespace App\Repositories;

use App\Models\Gudang;
use App\Models\TransferTransaction;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Support\Collection;
use App\Models\Branch;

class TransferTransactionRepository
{
    public function paginate(string $companyId, array $filters): CursorPaginator
    {
        return TransferTransaction::query()
            ->select([
                'id', 'company_id', 'source_gudang_id', 'destination_gudang_id',
                'destination_type', 'destination_branch_id', 'transfer_number',
                'transfer_date', 'workflow_status', 'note',
            ])
            ->with([
                'sourceGudang:id,nama',
                'destinationGudang:id,nama',
                'destinationBranch:id,name',
            ])
            ->withCount('details')
            ->where('company_id', $companyId)
            ->when($filters['source_gudang_id'] ?? null, fn ($query, $id) => $query->where('source_gudang_id', $id))
            ->when($filters['destination_type'] ?? null, fn ($query, $type) => $query->where('destination_type', $type))
            ->when($filters['destination_gudang_id'] ?? null, fn ($query, $id) => $query->where('destination_gudang_id', $id))
            ->when($filters['destination_branch_id'] ?? null, fn ($query, $id) => $query->where('destination_branch_id', $id))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('workflow_status', $status))
            ->when($filters['date_from'] ?? null, fn ($query, $date) => $query->whereDate('transfer_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, $date) => $query->whereDate('transfer_date', '<=', $date))
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where(function ($nested) use ($search) {
                $nested->where('transfer_number', 'like', "%{$search}%")
                    ->orWhereHas('sourceGudang', fn ($warehouse) => $warehouse->where('nama', 'like', "%{$search}%"))
                    ->orWhereHas('destinationGudang', fn ($warehouse) => $warehouse->where('nama', 'like', "%{$search}%"));
            }))
            ->latest('transfer_date')->latest('id')
            ->cursorPaginate($filters['per_page'] ?? 10)
            ->withQueryString();
    }

    public function findWithDetails(string $companyId, string $id): TransferTransaction
    {
        return TransferTransaction::query()
            ->select([
                'id', 'company_id', 'source_gudang_id', 'destination_gudang_id',
                'destination_type', 'destination_branch_id', 'transfer_number',
                'transfer_date', 'workflow_status', 'note',
            ])
            ->with([
                'sourceGudang:id,nama',
                'destinationGudang:id,nama',
                'destinationBranch:id,name',
                'details:id,transfer_transaction_id,source_stock_id,quantity,received_quantity',
                'details.sourceStock:id,product_id',
                'details.sourceStock.product:id,unit_id,name,sku',
                'details.sourceStock.product.unit:id,name',
                'timelines:id,transfer_transaction_id,user_id,event,note,created_at',
                'timelines.user:id,name',
            ])
            ->where('company_id', $companyId)
            ->findOrFail($id);
    }

    public function warehouses(string $companyId): Collection
    {
        return Gudang::query()->where('company_id', $companyId)->where('aktif', true)->orderBy('nama')->get(['id', 'nama']);
    }

    public function branches(string $companyId): Collection
    {
        return Branch::query()->where('company_id', $companyId)->where('status', 'active')->orderBy('name')->get(['id', 'name']);
    }
}

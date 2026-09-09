<?php

namespace App\Repositories;

use App\Models\Gudang;
use App\Models\Branch;
use App\Models\StockOpname;
use App\Models\StockOpnameDetail;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Support\Collection;

class StockOpnameRepository
{
    public function paginate(string $companyId, array $filters): CursorPaginator
    {
        return StockOpname::query()
            ->select([
                'id', 'company_id', 'source_type', 'gudang_id', 'branch_id',
                'created_by', 'opname_number', 'opname_date', 'status', 'note',
            ])
            ->with([
                'gudang:id,nama',
                'branch:id,name',
                'creator:id,name',
            ])
            ->withCount('details')
            ->withCount([
                'details as checked_details_count' => fn ($query) => $query
                    ->whereNotNull('physical_quantity'),
            ])
            ->selectSub(
                StockOpnameDetail::query()
                    ->selectRaw('COALESCE(SUM(CAST(physical_quantity AS SIGNED) - CAST(system_quantity AS SIGNED)), 0)')
                    ->whereColumn('stock_opname_id', 'stock_opnames.id')
                    ->whereNotNull('physical_quantity'),
                'difference_quantity',
            )
            ->where('company_id', $companyId)
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where(function ($nested) use ($search) {
                $nested->where('opname_number', 'like', "%{$search}%")
                    ->orWhereHas('gudang', fn ($warehouse) => $warehouse->where('nama', 'like', "%{$search}%"))
                    ->orWhereHas('branch', fn ($branch) => $branch->where('name', 'like', "%{$search}%"));
            }))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['gudang_id'] ?? null, fn ($query, $id) => $query->where('gudang_id', $id))
            ->when($filters['branch_id'] ?? null, fn ($query, $id) => $query->where('branch_id', $id))
            ->when($filters['date_from'] ?? null, fn ($query, $date) => $query->whereDate('opname_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, $date) => $query->whereDate('opname_date', '<=', $date))
            ->latest('opname_date')->latest('id')
            ->cursorPaginate($filters['per_page'] ?? 10)->withQueryString();
    }

    public function find(string $companyId, string $id): StockOpname
    {
        return StockOpname::query()
            ->with([
                'gudang:id,nama',
                'branch:id,name',
                'details:id,stock_opname_id,product_stock_id,branch_product_stock_id,system_quantity,physical_quantity,note',
                'details.productStock:id,product_id',
                'details.productStock.product:id,unit_id,name,sku',
                'details.productStock.product.unit:id,name',
                'details.branchProductStock:id,product_id',
                'details.branchProductStock.product:id,unit_id,name,sku',
                'details.branchProductStock.product.unit:id,name',
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
        return Branch::query()->where('company_id', $companyId)->where('status', Branch::STATUS_ACTIVE)->orderBy('name')->get(['id', 'name']);
    }
}

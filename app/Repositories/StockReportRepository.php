<?php

namespace App\Repositories;

use App\Models\Branch;
use App\Models\Gudang;
use App\Models\Product;
use App\Models\ProductBrand;
use App\Models\ProductCategory;
use App\Models\ProductStock;
use App\Models\BranchProductStock;
use App\Models\StockMovement;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StockReportRepository
{
   public function paginate(string $companyId, array $filters): CursorPaginator
   {
      return $this->reportQuery($companyId, $filters)
         ->orderBy('products.created_at')
         ->orderBy('products.id')
         ->cursorPaginate($filters['per_page'] ?? 10)
         ->withQueryString();
   }

   public function export(string $companyId, array $filters): Collection
   {
      return $this->reportQuery($companyId, $filters)
         ->orderBy('products.name')
         ->orderBy('products.id')
         ->get();
   }

   public function summary(string $companyId, array $filters): array
   {
      $query = $this->reportQuery($companyId, $filters);
      $rows = (clone $query)->get(['product_id', 'stock_final', 'valuation', 'is_low_stock']);

      return [
         'product_count' => $rows->count(),
         'total_qty' => (float) $rows->sum('stock_final'),
         'valuation' => (int) $rows->sum('valuation'),
         'low_stock_count' => $rows->where('is_low_stock', true)->count(),
      ];
   }

   public function options(string $companyId): array
   {
      return [
         'categories' => ProductCategory::query()->where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get(['id', 'name']),
         'brands' => ProductBrand::query()->where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get(['id', 'name']),
         'branches' => Branch::query()->where('company_id', $companyId)->where('status', Branch::STATUS_ACTIVE)->orderBy('name')->get(['id', 'name']),
         'warehouses' => Gudang::query()->where('company_id', $companyId)->where('aktif', true)->orderBy('nama')->get(['id', 'nama']),
      ];
   }

   public function ledger(string $companyId, string $productId, array $filters): Collection
   {
      return StockMovement::query()
         ->where('company_id', $companyId)
         ->where('product_id', $productId)
         ->when($filters['branch_id'] ?? null, fn($query, $id) => $query->where('branch_id', $id))
         ->when($filters['gudang_id'] ?? null, fn($query, $id) => $query->where('gudang_id', $id))
         ->when($filters['date_from'] ?? null, fn($query, $date) => $query->whereDate('created_at', '>=', $date))
         ->when($filters['date_to'] ?? null, fn($query, $date) => $query->whereDate('created_at', '<=', $date))
         ->latest('created_at')->latest('id')->limit(100)->get();
   }

   private function reportQuery(string $companyId, array $filters)
   {
      $stockSubquery = $this->stockSubquery($companyId, $filters);
      $movementSubquery = $this->movementSubquery($companyId, $filters);

      return Product::query()
         ->select([
            'products.id as product_id',
            'products.sku',
            'products.name',
            'products.category_id',
            'products.brand_id',
            'products.unit_id',
            'products.purchase_price',
            'products.minimum_stock',
            DB::raw("COALESCE(stock_totals.stock_final, 0) as stock_final"),
            DB::raw("COALESCE(movement_totals.total_in, 0) as total_in"),
            DB::raw("COALESCE(movement_totals.total_out, 0) as total_out"),
            DB::raw("COALESCE(movement_totals.adjustment, 0) as adjustment"),
            'movement_totals.last_movement_at',
            DB::raw("COALESCE(stock_totals.stock_final, 0) - COALESCE(movement_totals.net_change, 0) as stock_initial"),
            DB::raw("COALESCE(stock_totals.stock_final, 0) * products.purchase_price as valuation"),
            DB::raw("COALESCE(stock_totals.stock_final, 0) <= products.minimum_stock as is_low_stock"),
         ])
         ->with(['category:id,name', 'brand:id,name', 'unit:id,name'])
         ->leftJoinSub($stockSubquery, 'stock_totals', fn($join) => $join->on('stock_totals.product_id', '=', 'products.id'))
         ->leftJoinSub($movementSubquery, 'movement_totals', fn($join) => $join->on('movement_totals.product_id', '=', 'products.id'))
         ->where('products.company_id', $companyId)
         ->where('products.is_active', true)
         ->when($filters['search'] ?? null, fn($query, $search) => $query->where(fn($nested) => $nested
            ->where('products.sku', 'like', "%{$search}%")
            ->orWhere('products.name', 'like', "%{$search}%")
            ->orWhere('products.barcode', 'like', "%{$search}%")))
         ->when($filters['category_id'] ?? null, fn($query, $id) => $query->where('products.category_id', $id))
         ->when($filters['brand_id'] ?? null, fn($query, $id) => $query->where('products.brand_id', $id));
   }

   private function stockSubquery(string $companyId, array $filters)
   {
      $queries = [];

      if ($filters['gudang_id'] ?? null) {
         $queries[] = ProductStock::query()
            ->select('product_id')
            ->selectRaw('SUM(quantity) as stock_final')
            ->where('company_id', $companyId)
            ->where('gudang_id', $filters['gudang_id'])
            ->groupBy('product_id');
      } elseif ($filters['branch_id'] ?? null) {
         $queries[] = BranchProductStock::query()
            ->select('product_id')
            ->selectRaw('SUM(quantity) as stock_final')
            ->where('company_id', $companyId)
            ->where('branch_id', $filters['branch_id'])
            ->groupBy('product_id');
      } else {
         $queries[] = ProductStock::query()
            ->select('product_id')
            ->selectRaw('SUM(quantity) as stock_final')
            ->where('company_id', $companyId)
            ->groupBy('product_id');
         $queries[] = BranchProductStock::query()
            ->select('product_id')
            ->selectRaw('SUM(quantity) as stock_final')
            ->where('company_id', $companyId)
            ->groupBy('product_id');
      }

      $stockScope = array_shift($queries);
      foreach ($queries as $query) {
         $stockScope->unionAll($query);
      }

      return DB::query()
         ->fromSub($stockScope, 'stock_scope')
         ->select('product_id')
         ->selectRaw('SUM(stock_final) as stock_final')
         ->groupBy('product_id');
   }

   private function movementSubquery(string $companyId, array $filters)
   {
      return StockMovement::query()->select('product_id')
         ->selectRaw("SUM(CASE WHEN UPPER(movement_type) NOT IN ('ADJUSTMENT', 'OPNAME_ADJUSTMENT') AND type = 'IN' THEN qty ELSE 0 END) as total_in")
         ->selectRaw("SUM(CASE WHEN UPPER(movement_type) NOT IN ('ADJUSTMENT', 'OPNAME_ADJUSTMENT') AND type = 'OUT' THEN qty ELSE 0 END) as total_out")
         ->selectRaw("SUM(CASE WHEN UPPER(movement_type) IN ('ADJUSTMENT', 'OPNAME_ADJUSTMENT') THEN CASE WHEN type = 'OUT' THEN -qty ELSE qty END ELSE 0 END) as adjustment")
         ->selectRaw("SUM(CASE WHEN UPPER(movement_type) IN ('ADJUSTMENT', 'OPNAME_ADJUSTMENT') THEN CASE WHEN type = 'OUT' THEN -qty ELSE qty END WHEN type = 'IN' THEN qty WHEN type = 'OUT' THEN -qty ELSE 0 END) as net_change")
         ->selectRaw('MAX(created_at) as last_movement_at')
         ->where('company_id', $companyId)
         ->when($filters['branch_id'] ?? null, fn($query, $id) => $query->where('branch_id', $id))
         ->when($filters['gudang_id'] ?? null, fn($query, $id) => $query->where('gudang_id', $id))
         ->when($filters['date_from'] ?? null, fn($query, $date) => $query->whereDate('created_at', '>=', $date))
         ->when($filters['date_to'] ?? null, fn($query, $date) => $query->whereDate('created_at', '<=', $date))
         ->when($filters['movement_type'] ?? null, function ($query, $type) {
            return match ($type) {
               'IN' => $query->where('type', 'IN'),
               'OUT' => $query->where('type', 'OUT'),
               'ADJUSTMENT' => $query->whereIn(DB::raw('UPPER(movement_type)'), ['ADJUSTMENT', 'OPNAME_ADJUSTMENT']),
               'TRANSFER' => $query->whereIn(DB::raw('UPPER(movement_type)'), ['TRANSFER_IN', 'TRANSFER_OUT']),
               default => $query,
            };
         })
         ->groupBy('product_id');
   }
}

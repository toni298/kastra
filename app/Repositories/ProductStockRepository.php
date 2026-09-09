<?php

namespace App\Repositories;

use App\Models\ProductStock;
use Illuminate\Contracts\Pagination\CursorPaginator;
use App\Models\Gudang;
use App\Models\ProductCategory;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Collection;

class ProductStockRepository
{
    public function paginate(string $companyId, array $filters): CursorPaginator
    {
        $sort = $filters['sort'] ?? null;
        $direction = $filters['sort_direction'] ?? 'asc';

        return ProductStock::query()
            ->select('product_stocks.*')
            ->join('products', 'products.id', '=', 'product_stocks.product_id')
            ->with([
                'gudang:id,nama',
                'product:id,category_id,brand_id,unit_id,name,sku,barcode,purchase_price,selling_price,minimum_stock,description,is_active,created_at',
                'product.category:id,name',
                'product.brand:id,name',
                'product.unit:id,name',
                'product.images:id,product_id,original_path,webp_path,thumbnail_path,status,sort_order',
            ])
            ->where('product_stocks.company_id', $companyId)
            ->when($filters['search'] ?? null, fn($query, $search) => $query
                ->where(fn($nested) => $nested
                    ->where('products.name', 'like', "%{$search}%")
                    ->orWhere('products.sku', 'like', "%{$search}%")
                    ->orWhere('products.barcode', 'like', "%{$search}%")))
            ->when($filters['gudang_id'] ?? null, fn($query, $id) => $query
                ->where('product_stocks.gudang_id', $id))
            ->when($filters['category_id'] ?? null, fn($query, $id) => $query
                ->where('products.category_id', $id))
            ->when($filters['status'] ?? null, function ($query, $status) {
                if ($status === 'out') {
                    $query->where('product_stocks.quantity', 0);
                } elseif ($status === 'low') {
                    $query->where('product_stocks.quantity', '>', 0)
                        ->whereColumn('product_stocks.quantity', '<=', 'products.minimum_stock');
                } else {
                    $query->whereColumn('product_stocks.quantity', '>', 'products.minimum_stock');
                }
            })
            ->when(
                $sort,
                fn($query) => $query->orderBy($this->sortColumn($sort), $direction)
                    ->orderBy('product_stocks.id', $direction),
                fn($query) => $query->orderByDesc('product_stocks.created_at')
                    ->orderByDesc('product_stocks.id'),
            )
            ->cursorPaginate($filters['per_page'] ?? 10)
            ->withQueryString();
    }

    public function warehouses(string $companyId): Collection
    {
        return Gudang::query()
            ->where('company_id', $companyId)
            ->where('aktif', true)
            ->orderBy('nama')
            ->get(['id', 'nama']);
    }

    public function categories(string $companyId): Collection
    {
        return ProductCategory::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function searchProducts(string $companyId, array $filters): CursorPaginator
    {
        if (empty($filters['gudang_id'])) {
            return Product::query()
                ->select(['products.id', 'products.name', 'products.sku'])
                ->where('company_id', $companyId)
                ->where('is_active', true)
                ->when($filters['branch_id'] ?? null, fn($query, $branchId) => $query->whereHas('branchStocks', fn($stockQuery) => $stockQuery
                    ->where('company_id', $companyId)
                    ->where('branch_id', $branchId)))
                ->when($filters['search'] ?? null, fn($query, $search) => $query
                    ->where(fn($nested) => $nested
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%")))
                ->orderBy('name')
                ->orderBy('products.id')
                ->cursorPaginate(20);
        }

        return Product::query()
            ->select([
                'products.id',
                'products.name',
                'products.sku',
                'product_stocks.id as stock_id',
                'product_stocks.product_id as product_id',
                'product_stocks.quantity',
            ])
            ->leftJoin('product_stocks', function ($join) use ($companyId, $filters) {
                $join->on('product_stocks.product_id', '=', 'products.id')
                    ->where('product_stocks.company_id', $companyId)
                    ->where('product_stocks.gudang_id', $filters['gudang_id']);
            })
            ->where('products.company_id', $companyId)
            ->where('products.is_active', true)
            ->when($filters['search'] ?? null, fn($query, $search) => $query
                ->where(fn($nested) => $nested
                    ->where('products.name', 'like', "%{$search}%")
                    ->orWhere('products.sku', 'like', "%{$search}%")
                    ->orWhere('products.barcode', 'like', "%{$search}%")))
            ->orderBy('products.name')
            ->orderBy('products.id')
            ->cursorPaginate(20);
    }

    public function branchStockPaginate(string $companyId, array $filters): CursorPaginator
    {
        $query = \App\Models\BranchProductStock::query()
            ->select(['branch_product_stocks.*'])
            ->join('products', 'products.id', '=', 'branch_product_stocks.product_id')
            ->with(['branch:id,name', 'product:id,category_id,unit_id,name,sku,minimum_stock,is_active', 'product.category:id,name', 'product.unit:id,name'])
            ->where('branch_product_stocks.company_id', $companyId)
            ->when($filters['search'] ?? null, fn($q, $s) => $q->where(function ($qq) use ($s) {
                $qq->where('products.name', 'like', "%{$s}%")->orWhere('products.sku', 'like', "%{$s}%");
            }))
            ->when($filters['category_id'] ?? null, fn($q, $id) => $q->where('products.category_id', $id))
            ->orderByRaw('branch_product_stocks.branch_id')
            ->orderBy('products.name')
            ->cursorPaginate($filters['per_page'] ?? 20)
            ->withQueryString();

        return $query;
    }

    public function dashboardSummary(string $companyId): array
    {
        $totalProducts = \App\Models\BranchProductStock::query()->where('company_id', $companyId)->whereHas('product', fn($q) => $q->where('is_active', true))->distinct('product_id')->count('product_id');

        $toRestock = \App\Models\BranchProductStock::query()
            ->where('company_id', $companyId)
            ->whereHas('product', fn($q) => $q->whereColumn('branch_product_stocks.quantity', '<=', 'products.minimum_stock'))
            ->distinct('product_id')
            ->count('product_id');

        $today = now()->toDateString();

        // Barang masuk dan keluar dihitung dari ledger stock_movements hari ini.
        $inToday = StockMovement::query()
            ->where('company_id', $companyId)
            ->where('type', 'IN')
            ->whereDate('created_at', $today)
            ->sum('qty');

        $outToday = StockMovement::query()
            ->where('company_id', $companyId)
            ->where('type', 'OUT')
            ->whereDate('created_at', $today)
            ->sum('qty');

        return ['total_products' => $totalProducts, 'to_restock' => $toRestock, 'in_today' => (int) $inToday, 'out_today' => (int) $outToday];
    }

    private function sortColumn(string $sort): string
    {
        return match ($sort) {
            'product_name' => 'products.name',
            'sku' => 'products.sku',
            'quantity' => 'product_stocks.quantity',
            default => 'product_stocks.created_at',
        };
    }

    /**
     * Detail produk untuk drawer inventory overview.
     *
     * Mengambil data BranchProductStock + relasi produk lengkap,
     * serta agregasi performa penjualan (bulan ini & lalu).
     */
    public function productDetail(string $companyId, string $stockId): array
    {
        $stock = \App\Models\BranchProductStock::query()
            ->where('company_id', $companyId)
            ->where('id', $stockId)
            ->with([
                'branch:id,name',
                'product:id,category_id,brand_id,unit_id,name,sku,barcode,purchase_price,selling_price,minimum_stock,description,is_active',
                'product.category:id,name',
                'product.brand:id,name',
                'product.unit:id,name',
            ])
            ->firstOrFail();

        $product = $stock->product;

        // Agregasi penjualan bulan ini & bulan lalu
        $now = now();
        $startThisMonth = $now->copy()->startOfMonth()->toDateString();
        $endThisMonth = $now->copy()->endOfMonth()->toDateString();
        $startLastMonth = $now->copy()->subMonthNoOverflow()->startOfMonth()->toDateString();
        $endLastMonth = $now->copy()->subMonthNoOverflow()->endOfMonth()->toDateString();

        $salesBase = \App\Models\SalesTransactionDetail::query()
            ->where('product_id', $product->id)
            ->whereHas('transaction', fn($q) => $q
                ->where('company_id', $companyId)
                ->whereIn('status', ['completed', 'return']));

        $soldThisMonth = (clone $salesBase)
            ->whereHas('transaction', fn($q) => $q->whereBetween('transaction_date', [$startThisMonth, $endThisMonth]))
            ->sum('quantity');

        $soldLastMonth = (clone $salesBase)
            ->whereHas('transaction', fn($q) => $q->whereBetween('transaction_date', [$startLastMonth, $endLastMonth]))
            ->sum('quantity');

        $revenueThisMonth = (clone $salesBase)
            ->whereHas('transaction', fn($q) => $q->whereBetween('transaction_date', [$startThisMonth, $endThisMonth]))
            ->sum('subtotal');

        $totalSold = (clone $salesBase)->sum('quantity');

        $status = match (true) {
            $stock->quantity === 0 => 'Habis',
            $stock->quantity <= $product->minimum_stock => 'Perlu Direstok',
            default => 'Aman',
        };

        return [
            'id' => $stock->id,
            'branch' => $stock->branch,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'barcode' => $product->barcode,
                'category' => $product->category?->name,
                'brand' => $product->brand?->name,
                'unit' => $product->unit?->name,
                'purchase_price' => $product->purchase_price,
                'selling_price' => $product->selling_price,
                'minimum_stock' => $product->minimum_stock,
                'description' => $product->description,
                'is_active' => $product->is_active,
            ],
            'stock' => [
                'quantity' => $stock->quantity,
                'minimum' => $product->minimum_stock,
                'status' => $status,
                'discount' => $stock->discount ?? 0,
            ],
            'performance' => [
                'sold_this_month' => (int) $soldThisMonth,
                'sold_last_month' => (int) $soldLastMonth,
                'revenue_this_month' => (int) $revenueThisMonth,
                'total_sold' => (int) $totalSold,
            ],
        ];
    }
}

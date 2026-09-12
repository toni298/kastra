<?php

namespace App\Http\Controllers;

use App\Http\Requests\Search\SearchCabangRequest;
use App\Http\Requests\Search\SearchProductMasterRequest;
use App\Http\Requests\Search\SearchInventoryProductRequest;
use App\Http\Requests\Search\SearchGudangRequest;
use App\Http\Requests\Search\SearchBranchProductRequest;
use App\Http\Requests\Search\SearchCustomerRequest;
use App\Http\Requests\Search\SearchSupplierRequest;
use App\Http\Requests\Search\SearchEmployeeRequest;
use App\Http\Requests\Search\SearchPurchaseProductRequest;
use App\Repositories\SalesTransactionRepository;
use App\Models\BranchProductStock;
use App\Models\Branch;
use App\Models\Supplier;
use App\Models\Employee;
use App\Repositories\CabangRepository;
use App\Repositories\ProductMasterRepository;
use App\Repositories\ProductStockRepository;
use App\Repositories\GudangRepository;
use App\Services\CompanyContext;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;

class SearchController extends Controller
{
    public function __construct(
        private CabangRepository $cabangRepository,
        private ProductMasterRepository $productMasterRepository,
        private ProductStockRepository $productStockRepository,
        private GudangRepository $gudangRepository,
        private CompanyContext $companyContext,
        private SalesTransactionRepository $salesTransactionRepository,
    ) {}

    public function cabang(SearchCabangRequest $request): JsonResponse
    {
        $page = $this->cabangRepository->search($this->companyId(), $request->validated());

        return response()->json([
            'results' => collect($page->items())->map(fn(Branch $branch) => [
                'id' => $branch->id,
                'text' => $branch->name,
            ])->values(),
            'pagination' => ['more' => $page->hasMorePages()],
            'next_cursor' => $page->nextCursor()?->encode(),
        ]);
    }

    public function productCategories(SearchProductMasterRequest $request): JsonResponse
    {
        return $this->productMasterResponse('product_categories', $request->validated());
    }

    public function productBrands(SearchProductMasterRequest $request): JsonResponse
    {
        return $this->productMasterResponse('product_brands', $request->validated());
    }

    public function units(SearchProductMasterRequest $request): JsonResponse
    {
        return $this->productMasterResponse('units', $request->validated());
    }

    public function products(SearchInventoryProductRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $page = $this->productStockRepository->searchProducts($this->companyId(), $filters);

        return $this->response($page, fn(Model $stock) => [
            'id' => ($filters['selection'] ?? null) === 'product'
                ? $stock->product_id ?? $stock->getKey()
                : ($stock->stock_id ?? $stock->getKey()),
            'text' => isset($stock->product)
                ? "{$stock->product->name} ({$stock->product->sku})"
                : "{$stock->name} ({$stock->sku})",
            'product_id' => $stock->product_id ?? $stock->getKey(),
            'available_quantity' => (int) ($stock->quantity ?? 0),
            'already_registered' => (bool) ($stock->already_registered ?? false),
        ]);
    }

    public function purchaseProducts(SearchPurchaseProductRequest $request): JsonResponse
    {
        $filters = $request->validated();
        if (!empty($filters['branch_id'])) {
            $page = \App\Models\BranchProductStock::query()->with(['product.unit:id,name'])->where('company_id', $this->companyId())->where('branch_id', $filters['branch_id'])->when($filters['search'] ?? null, fn($query, $search) => $query->whereHas('product', fn($q) => $q->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%")->orWhere('barcode', 'like', "%{$search}%")))->orderBy('id')->cursorPaginate(10);
            return $this->response($page, fn($stock) => ['id' => $stock->product_id, 'name' => $stock->product->name, 'text' => $stock->product->name . ' (' . $stock->product->sku . ')', 'sku' => $stock->product->sku, 'barcode' => $stock->product->barcode, 'price' => (int) $stock->product->purchase_price, 'unit' => $stock->product->unit?->name, 'type' => 'Produk', 'available_quantity' => (int) $stock->quantity]);
        }
        $page = \App\Models\Product::query()->with(['unit:id,name'])->where('company_id', $this->companyId())->where('is_active', true)->when($filters['search'] ?? null, fn($query, $search) => $query->where(fn($q) => $q->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%")->orWhere('barcode', 'like', "%{$search}%")))->orderBy('name')->cursorPaginate(10);
        return $this->response($page, fn(Model $product) => ['id' => $product->id, 'name' => $product->name, 'text' => $product->name . ' (' . $product->sku . ')', 'sku' => $product->sku, 'barcode' => $product->barcode, 'price' => (int) $product->purchase_price, 'unit' => $product->unit?->name, 'type' => 'Produk']);
    }

    public function gudang(SearchGudangRequest $request): JsonResponse
    {
        $page = $this->gudangRepository->search($this->companyId(), $request->validated());

        return $this->response($page, fn(Model $warehouse) => [
            'id' => $warehouse->getKey(),
            'text' => $warehouse->kode ? "{$warehouse->nama} ({$warehouse->kode})" : $warehouse->nama,
        ]);
    }

    public function suppliers(SearchSupplierRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $page = Supplier::query()->select(['id', 'name'])->where('company_id', $this->companyId())->when($filters['search'] ?? null, fn($query, $search) => $query->where(fn($q) => $q->where('name', 'like', "%{$search}%")->orWhere('contact_supplier', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")))->orderBy('name')->cursorPaginate(20);
        return $this->response($page, fn(Model $supplier) => ['id' => $supplier->id, 'text' => $supplier->name]);
    }

    public function employees(SearchEmployeeRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $page = Employee::query()
            ->select(['id', 'name', 'nik'])
            ->where('company_id', $this->companyId())
            ->where('status', 'active')
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where(fn ($nested) => $nested->where('name', 'like', "%{$search}%")->orWhere('nik', 'like', "%{$search}%")))
            ->orderBy('name')
            ->orderBy('id')
            ->cursorPaginate(10);

        return $this->response($page, fn (Employee $employee) => ['id' => $employee->id, 'text' => "{$employee->name} ({$employee->nik})"]);
    }

    public function branchProducts(SearchBranchProductRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $stocks = BranchProductStock::query()->with(['product.unit:id,name'])->where('company_id', $this->companyId())->where('branch_id', $filters['branch_id'])->when($filters['search'] ?? null, fn($query, $search) => $query->whereHas('product', fn($product) => $product->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%")))->where('quantity', '>', 0)->limit(30)->get();
        return response()->json(['results' => $stocks->map(fn($stock) => ['id' => $stock->product_id, 'text' => $stock->product->name . ' (' . $stock->product->sku . ')', 'product_id' => $stock->product_id, 'name' => $stock->product->name, 'sku' => $stock->product->sku, 'price' => $stock->product->selling_price, 'available_quantity' => $stock->quantity, 'unit' => $stock->product->unit?->name])->values(), 'pagination' => ['more' => false]]);
    }

    public function customers(SearchCustomerRequest $request): JsonResponse
    {
        $filters = $request->validated();
        return response()->json(['results' => $this->salesTransactionRepository->customers($this->companyId(), $filters['branch_id'], $filters['search'] ?? null)->map(fn($customer) => ['id' => $customer->id, 'text' => $customer->name . ' ' . ($customer->telp ? '(' . $customer->telp . ')' : '')])->values(), 'pagination' => ['more' => false]]);
    }

    private function productMasterResponse(string $master, array $filters): JsonResponse
    {
        $page = $this->productMasterRepository->search($master, $this->companyId(), $filters);

        return $this->response($page, fn(Model $item) => [
            'id' => $item->getKey(),
            'text' => $item->name,
        ]);
    }

    private function response(CursorPaginator $page, callable $transform): JsonResponse
    {
        return response()->json([
            'results' => collect($page->items())->map($transform)->values(),
            'pagination' => ['more' => $page->hasMorePages()],
            'next_cursor' => $page->nextCursor()?->encode(),
        ]);
    }

    private function companyId(): string
    {
        return (string) $this->companyContext->id();
    }
}

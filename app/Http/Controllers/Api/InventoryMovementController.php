<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\IndexStockMovementRequest;
use App\Http\Resources\StockMovementResource;
use App\Models\BranchProductStock;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\StockMovement;
use App\Models\Branch;
use App\Models\Gudang;
use App\Models\User;
use App\Services\CompanyContext;
use App\Services\InertiaAuthorizationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class InventoryMovementController extends Controller
{
   public function __construct(private CompanyContext $companyContext) {}

   public function index(IndexStockMovementRequest $request): JsonResponse
   {
      $query = $this->baseQuery($request);
      $movements = $query->latest('created_at')->latest('id')->paginate($request->validated('per_page', 25));
      $summary = $this->summary($request);

      return StockMovementResource::collection($movements)->additional(['summary' => $summary])->response();
   }

   public function page(IndexStockMovementRequest $request): Response
   {
      return Inertia::render('Inventory/Index', [
         'activeTab' => 'movements',
         'enabledFeatures' => $this->companyContext->current()?->features ?? [],
         'movementItems' => fn() => StockMovementResource::collection(
            $this->baseQuery($request)->latest('created_at')->latest('id')->paginate($request->validated('per_page', 25))
         )->additional(['summary' => $this->summary($request)]),
         'movementFilters' => $request->validated(),
         'movementSummary' => fn() => $this->summary($request),
         'movementOptions' => fn() => $this->options(),
      ]);
   }

   public function product(IndexStockMovementRequest $request, Product $product): JsonResponse
   {
      abort_unless((string) $product->company_id === (string) $this->companyContext->id(), 404);
      $movements = $this->baseQuery($request)->where('product_id', $product->id)
         ->latest('created_at')->latest('id')->paginate($request->validated('per_page', 25));

      return StockMovementResource::collection($movements)->response();
   }

   public function reconcile(Request $request, Product $product): JsonResponse
   {
      abort_unless($request->user() && (string) $request->user()->company_id === (string) $this->companyContext->id(), 403);
      app(InertiaAuthorizationService::class)->authorize($request->user(), 'inventory.reconcile');

      $result = DB::transaction(function () use ($product, $request): array {
         abort_unless((string) $product->company_id === (string) $this->companyContext->id(), 404);
         $movements = StockMovement::query()->where('company_id', $this->companyContext->id())->where('product_id', $product->id)->whereNotNull('type')->get();
         $stocks = ProductStock::query()->where('company_id', $this->companyContext->id())->where('product_id', $product->id)->lockForUpdate()->get();
         $updated = [];

         foreach ($stocks as $stock) {
            $total = $movements->where('gudang_id', $stock->gudang_id)->sum(fn($movement) => $movement->type === 'IN' ? (float) $movement->qty : -(float) $movement->qty);
            $stock->update(['quantity' => max(0, $total)]);
            $updated[] = ['location' => 'gudang', 'id' => $stock->gudang_id, 'quantity' => max(0, $total)];
         }

         $branchStocks = BranchProductStock::query()->where('company_id', $this->companyContext->id())->where('product_id', $product->id)->lockForUpdate()->get();
         foreach ($branchStocks as $stock) {
            $total = $movements->where('branch_id', $stock->branch_id)->sum(fn($movement) => $movement->type === 'IN' ? (float) $movement->qty : -(float) $movement->qty);
            $stock->update(['quantity' => max(0, $total)]);
            $updated[] = ['location' => 'branch', 'id' => $stock->branch_id, 'quantity' => max(0, $total)];
         }

         return ['product_id' => $product->id, 'reconciled_by' => $request->user()->id, 'stocks' => $updated];
      });

      return response()->json(['data' => $result]);
   }

   private function baseQuery(IndexStockMovementRequest $request)
   {
      $dateFrom = $request->validated('date_from') ?? now()->subDays(29)->startOfDay();
      $dateTo = $request->validated('date_to') ?? now()->endOfDay();

      return StockMovement::query()
         ->where('company_id', $this->companyContext->id())
         ->when($request->validated('product_id'), fn($query, $id) => $query->where('product_id', $id))
         ->when($request->validated('branch_id'), fn($query, $id) => $query->where('branch_id', $id))
         ->when($request->validated('gudang_id'), fn($query, $id) => $query->where('gudang_id', $id))
         ->when($request->validated('user_id'), fn($query, $id) => $query->where('user_id', $id))
         ->when($request->validated('movement_type'), fn($query, $type) => $query->where('movement_type', $type))
         ->whereDate('created_at', '>=', $dateFrom)
         ->whereDate('created_at', '<=', $dateTo)
         ->when($request->validated('search'), fn($query, $search) => $query
            ->where(fn($nested) => $nested
               ->where('product_name', 'like', "%{$search}%")
               ->orWhere('product_sku', 'like', "%{$search}%")));
   }

   private function summary(IndexStockMovementRequest $request): array
   {
      $query = $this->baseQuery($request);
      $adjustmentTypes = ['ADJUSTMENT', 'OPNAME_ADJUSTMENT'];

      return [
         'total_events' => (clone $query)->count(),
         'total_qty_in' => (float) (clone $query)->where('type', 'IN')->whereNotIn(DB::raw('UPPER(movement_type)'), $adjustmentTypes)->sum('qty'),
         'total_qty_out' => (float) (clone $query)->where('type', 'OUT')->whereNotIn(DB::raw('UPPER(movement_type)'), $adjustmentTypes)->sum('qty'),
         'total_adjustment' => (float) (clone $query)->whereIn(DB::raw('UPPER(movement_type)'), $adjustmentTypes)->selectRaw("COALESCE(SUM(CASE WHEN type = 'OUT' THEN -qty ELSE qty END), 0) as total")->value('total'),
      ];
   }

   private function options(): array
   {
      $companyId = $this->companyContext->id();

      return [
         'branches' => Branch::query()->where('company_id', $companyId)->where('status', Branch::STATUS_ACTIVE)->orderBy('name')->get(['id', 'name']),
         'warehouses' => Gudang::query()->where('company_id', $companyId)->where('aktif', true)->orderBy('nama')->get(['id', 'nama']),
         'users' => User::query()->where('company_id', $companyId)->orderBy('name')->get(['id', 'name']),
      ];
   }
}

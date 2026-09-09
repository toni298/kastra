<?php

namespace App\Http\Controllers;

use App\Http\Requests\Reports\IndexStockReportRequest;
use App\Http\Resources\StockMovementResource;
use App\Http\Resources\StockReportResource;
use App\Models\Product;
use App\Repositories\StockReportRepository;
use App\Services\CompanyContext;
use App\Services\InertiaAuthorizationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReportsStockController
{
   public function __construct(
      private StockReportRepository $repository,
      private CompanyContext $companyContext,
      private InertiaAuthorizationService $authorization,
   ) {}

   public function index(IndexStockReportRequest $request): Response
   {
      $this->authorization->authorize($request->user(), 'laporan.view');
      $filters = array_merge([
         'date_from' => now()->startOfMonth()->toDateString(),
         'date_to' => now()->toDateString(),
         'per_page' => 10,
      ], $request->validated());
      $companyId = (string) $this->companyContext->id();

      return Inertia::render('Reports/Stock/StockReport', [
         'activeTab' => 'stock',
         'cashierLayout' => $request->routeIs('cashier.reports.stock'),
         'reportStockItems' => fn() => StockReportResource::collection($this->repository->paginate($companyId, $filters)),
         'reportStockSummary' => fn() => $this->repository->summary($companyId, $filters),
         'reportStockFilters' => $filters,
      ]);
   }

   public function options(Request $request): JsonResponse
   {
      $this->authorization->authorize($request->user(), 'laporan.view');

      return response()->json(
         $this->repository->options((string) $this->companyContext->id())
      );
   }

   public function show(Request $request, string $product): JsonResponse
   {
      $this->authorization->authorize($request->user(), 'laporan.view');
      $companyId = (string) $this->companyContext->id();
      $filters = $request->validate([
         'branch_id' => ['nullable', 'uuid'],
         'gudang_id' => ['nullable', 'uuid'],
         'date_from' => ['nullable', 'date'],
         'date_to' => ['nullable', 'date'],
      ]);
      $productModel = Product::query()
         ->where('company_id', $companyId)
         ->with(['category:id,name', 'brand:id,name'])
         ->findOrFail($product);

      return response()->json([
         'product' => [
            'id' => $productModel->id,
            'sku' => $productModel->sku,
            'name' => $productModel->name,
            'category' => $productModel->category?->name,
            'brand' => $productModel->brand?->name,
         ],
         'ledger' => StockMovementResource::collection($this->repository->ledger($companyId, $product, $filters))->resolve($request),
      ]);
   }

   public function export(IndexStockReportRequest $request): JsonResponse
   {
      $filters = $request->validated();
      $companyId = (string) $this->companyContext->id();
      $rows = StockReportResource::collection($this->repository->export($companyId, $filters))->resolve($request);
      $summary = $this->repository->summary($companyId, $filters);

      return response()->json([
         'title' => 'Laporan Stok',
         'period' => ['from' => $filters['date_from'] ?? '-', 'to' => $filters['date_to'] ?? '-'],
         'columns' => ['Tgl Mutasi', 'SKU', 'Nama Produk', 'Kategori', 'Stok Awal', 'Total Masuk', 'Total Keluar', 'Penyesuaian', 'Stok Akhir', 'Nilai Valuasi'],
         'rows' => collect($rows)->map(fn(array $row) => [
            $row['last_movement_at'] ?? null, $row['sku'], $row['name'], $row['category'], $row['stock_initial'], $row['total_in'], $row['total_out'], $row['adjustment'], $row['stock_final'], $row['valuation'],
            $row['name'],
            $row['category'],
            $row['stock_initial'],
            $row['total_in'],
            $row['total_out'],
            $row['adjustment'],
            $row['stock_final'],
            $row['valuation'],
         ])->values()->all(),
         'summary' => [
            ['Total Jenis Produk', $summary['product_count']],
            ['Total Kuantitas Stok', $summary['total_qty']],
            ['Total Valuasi Stok', $summary['valuation']],
            ['Produk Stok Menipis', $summary['low_stock_count']],
         ],
         'summaryCurrency' => [false, false, true, false],
      ]);
   }
}

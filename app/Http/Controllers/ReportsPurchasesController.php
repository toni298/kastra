<?php

namespace App\Http\Controllers;

use App\Http\Requests\Purchases\IndexPurchaseTransactionRequest;
use App\Http\Resources\PurchaseTransactionResource;
use App\Models\Gudang;
use App\Repositories\PurchaseTransactionRepository;
use App\Services\CompanyContext;
use App\Services\InertiaAuthorizationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReportsPurchasesController
{
   public function __construct(
      private PurchaseTransactionRepository $repository,
      private CompanyContext $companyContext,
      private InertiaAuthorizationService $authorization,
   ) {}

   public function index(IndexPurchaseTransactionRequest $request): Response
   {
      $this->authorization->authorize($request->user(), 'laporan.view');
      $filters = array_merge([
         'date_from' => now()->startOfMonth()->toDateString(),
         'date_to' => now()->toDateString(),
         'per_page' => 10,
      ], $request->validated());
      $companyId = (string) $this->companyContext->id();

      return Inertia::render('Reports/Purchases/Index', [
         'activeTab' => 'purchases',
         'cashierLayout' => $request->routeIs('cashier.reports.purchases'),
         'reportPurchaseItems' => fn() => PurchaseTransactionResource::collection(
            $this->repository->paginateReport($companyId, $filters)
         ),
         'reportPurchaseSummary' => fn() => $this->repository->reportSummary($companyId, $filters),
         'reportPurchaseFilters' => $filters,
         'reportPurchaseOptions' => fn() => [
            'suppliers' => $this->repository->suppliers($companyId),
            'warehouses' => Gudang::query()
               ->where('company_id', $companyId)
               ->where('aktif', true)
               ->orderBy('nama')
               ->get(['id', 'nama']),
         ],
      ]);
   }

   public function show(Request $request, string $transaction): JsonResponse
   {
      $this->authorization->authorize($request->user(), 'laporan.view');
      return PurchaseTransactionResource::make(
         $this->repository->findWithDetails((string) $this->companyContext->id(), $transaction)
      )->response();
   }

   public function export(IndexPurchaseTransactionRequest $request): JsonResponse
   {
      $filters = $request->validated();
      $companyId = (string) $this->companyContext->id();
      $rows = PurchaseTransactionResource::collection(
         $this->repository->exportReport($companyId, $filters)
      )->resolve($request);
      $summary = $this->repository->reportSummary($companyId, $filters);

      return response()->json([
         'title' => 'Laporan Pembelian',
         'period' => [
            'from' => $filters['date_from'] ?? '-',
            'to' => $filters['date_to'] ?? '-',
         ],
         'columns' => ['Tanggal Transaksi', 'No. Ref / Faktur Supplier', 'Nama Supplier', 'Total Qty', 'Total Nominal', 'Status'],
         'rows' => collect($rows)->map(fn(array $row) => [
            $row['date_iso'] ?? '-',
            $row['number'] ?? '-',
            $row['supplier'] ?? 'Pembelian Umum',
            (int) ($row['total_qty'] ?? 0),
            (int) ($row['total'] ?? 0),
            $row['status'] === 'cancelled' ? 'cancelled' : ($row['payment'] ?? '-'),
         ])->values()->all(),
         'summary' => [
            ['Total Pembelian', $summary['total']],
            ['Total Transaksi Pembelian', $summary['transaction_count']],
            ['Total Item Dibeli', $summary['total_qty']],
         ],
         'summaryCurrency' => [true, false, false],
      ]);
   }
}

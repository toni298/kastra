<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\HR\IndexCommissionRequest;
use App\Models\Employee;
use App\Models\SalesTransaction;
use App\Services\CompanyContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Builder;

class CommissionController extends Controller
{
   public function __construct(private CompanyContext $companyContext) {}

   public function index(IndexCommissionRequest $request): JsonResponse
   {
      $filters = $request->validated();
      $query = $this->aggregateQuery($filters);
      $rows = $query->orderBy('employees.name')->orderBy('employees.id')->cursorPaginate($filters['per_page'] ?? 10)->withQueryString();
      $summaryRows = $this->aggregateQuery($filters)->get();
      $totalCommission = $summaryRows->sum(fn ($row) => (float) $row->estimated_commission);
      $totalQty = $summaryRows->sum(fn ($row) => (int) $row->total_qty);
      $top = $summaryRows->sortByDesc(fn ($row) => (float) $row->estimated_commission)->first();

      return response()->json([
         'data' => $rows->through(fn ($row) => $this->presentRow($row)),
         'summary' => [
            'total_commission' => round($totalCommission),
            'top_employee' => $top?->name,
            'total_qty' => $totalQty,
            'unpaid' => round($totalCommission),
         ],
      ]);
   }

   public function details(IndexCommissionRequest $request, Employee $employee): JsonResponse
   {
      abort_unless($employee->company_id === $this->companyContext->id(), 404);
      $filters = $request->validated();
      $query = SalesTransaction::query()
         ->where('sales_transactions.company_id', $this->companyContext->id())
         ->where('sales_transactions.status', 'completed')
         ->whereHas('creator', fn ($user) => $user->where('company_id', $employee->company_id)->where('email', $employee->email))
         ->with('details:id,sales_transaction_id,quantity')
         ->when($filters['from'] ?? null, fn ($q, $from) => $q->whereDate('transaction_date', '>=', $from))
         ->when($filters['to'] ?? null, fn ($q, $to) => $q->whereDate('transaction_date', '<=', $to))
         ->latest('transaction_date')->latest('created_at');

      $items = $query->get()->map(function (SalesTransaction $transaction) use ($employee): array {
         $qty = (int) $transaction->details->sum('quantity');
         $commission = $employee->commission_type === 'percentage'
            ? round(((float) $transaction->total * (float) $employee->commission_value) / 100)
            : round($qty * (float) $employee->commission_value);
         return ['id' => $transaction->id, 'invoice' => $transaction->transaction_number, 'transaction_at' => $transaction->created_at?->format('d/m/Y H:i'), 'qty' => $qty, 'total' => (int) $transaction->total, 'commission' => $commission, 'status' => 'unpaid'];
      });

      return response()->json(['data' => $items]);
   }

   private function aggregateQuery(array $filters): Builder
   {
      return Employee::query()
         ->where('employees.company_id', $this->companyContext->id())
         ->leftJoin('users as commission_users', function ($join): void {
            $join->on('commission_users.email', '=', 'employees.email')->on('commission_users.company_id', '=', 'employees.company_id');
         })
         ->leftJoin('sales_transactions as sales', function ($join) use ($filters): void {
            $join->on('sales.created_by', '=', 'commission_users.id')->where('sales.status', 'completed');
            if ($filters['from'] ?? null) $join->whereDate('sales.transaction_date', '>=', $filters['from']);
            if ($filters['to'] ?? null) $join->whereDate('sales.transaction_date', '<=', $filters['to']);
         })
         ->leftJoin('sales_transaction_details as details', 'details.sales_transaction_id', '=', 'sales.id')
         ->when($filters['search'] ?? null, fn ($query, $search) => $query->where(function ($nested) use ($search): void { $nested->where('employees.name', 'like', "%{$search}%")->orWhere('employees.nik', 'like', "%{$search}%"); }))
         ->when($filters['commission_type'] ?? null, fn ($query, $type) => $query->where('employees.commission_type', $type))
         ->select('employees.id', 'employees.name', 'employees.nik', 'employees.commission_type', 'employees.commission_value')
         ->selectRaw('COALESCE(SUM(sales.total), 0) as total_sales')
         ->selectRaw('COALESCE(SUM(details.quantity), 0) as total_qty')
         ->selectRaw("CASE WHEN employees.commission_type = 'percentage' THEN COALESCE(SUM(sales.total), 0) * employees.commission_value / 100 ELSE COALESCE(SUM(details.quantity), 0) * employees.commission_value END as estimated_commission")
         ->groupBy('employees.id', 'employees.name', 'employees.nik', 'employees.commission_type', 'employees.commission_value');
   }

   private function presentRow(object $row): array
   {
      return ['id' => $row->id, 'name' => $row->name, 'nik' => $row->nik, 'commission_type' => $row->commission_type, 'commission_type_label' => $row->commission_type === 'percentage' ? '% Omzet' : 'Nominal / Qty', 'commission_value' => (float) $row->commission_value, 'total_sales' => (int) $row->total_sales, 'total_qty' => (int) $row->total_qty, 'estimated_commission' => round((float) $row->estimated_commission), 'status' => 'unpaid'];
   }
}

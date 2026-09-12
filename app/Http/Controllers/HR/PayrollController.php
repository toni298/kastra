<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\HR\GeneratePayrollRequest;
use App\Http\Requests\HR\IndexPayrollRequest;
use App\Models\Employee;
use App\Models\Overtime;
use App\Models\Payroll;
use App\Models\PayrollJournal;
use App\Models\SalesTransaction;
use App\Services\CompanyContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PayrollController extends Controller
{
   public function __construct(private CompanyContext $companyContext) {}

   public function index(IndexPayrollRequest $request): JsonResponse
   {
      $filters = $request->validated();
      $query = Payroll::query()->where('company_id', $this->companyContext->id())->withCount('items')->when($filters['period'] ?? null, fn ($q, $period) => $q->where('period', Carbon::createFromFormat('Y-m', $period)->startOfMonth()->toDateString()))->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))->orderByDesc('period')->orderByDesc('id');
      return response()->json(['data' => $query->cursorPaginate($filters['per_page'] ?? 10)->withQueryString()]);
   }

   public function generate(GeneratePayrollRequest $request): JsonResponse
   {
      $data = $request->validated();
      $period = Carbon::createFromFormat('Y-m', $data['period'])->startOfMonth();
      $companyId = (string) $this->companyContext->id();
      $payroll = DB::transaction(function () use ($period, $data, $companyId): Payroll {
         $payroll = Payroll::query()->firstOrCreate(['company_id' => $companyId, 'period' => $period->toDateString()], ['cutoff_date' => $data['cutoff_date'], 'status' => 'draft']);
         abort_if($payroll->status === 'posted', 422, 'Payroll periode ini sudah diposting dan tidak dapat dibuat ulang.');
         $payroll->items()->delete();
         $employees = Employee::query()->where('company_id', $companyId)->where('status', 'active')->get();
         foreach ($employees as $employee) {
            $commission = $this->commission($employee, $period);
            $overtime = Overtime::query()->where('company_id', $companyId)->where('employee_id', $employee->id)->whereBetween('overtime_date', [$period->toDateString(), $period->copy()->endOfMonth()->toDateString()])->where('status', 'approved')->sum('total_amount');
            $net = (int) $employee->base_salary + (int) $employee->allowance + $commission['amount'] + (int) $overtime;
            $payroll->items()->create(['company_id' => $companyId, 'employee_id' => $employee->id, 'basic_salary' => $employee->base_salary, 'allowance' => $employee->allowance, 'commission_amount' => $commission['amount'], 'overtime_amount' => $overtime, 'deduction' => 0, 'net_salary' => $net, 'commission_detail' => $commission['detail'], 'overtime_detail' => []]);
         }
         $payroll->update(['cutoff_date' => $data['cutoff_date'], 'employee_count' => $payroll->items()->count(), 'total_amount' => $payroll->items()->sum('net_salary'), 'status' => 'draft']);
         return $payroll->fresh();
      });
      return response()->json(['data' => $payroll, 'message' => 'Draft payroll berhasil dibuat.'], 201);
   }

   public function postToJournal(Payroll $payroll): JsonResponse
   {
      abort_unless($payroll->company_id === $this->companyContext->id(), 404);
      abort_if($payroll->status === 'posted', 422, 'Payroll sudah diposting.');
      DB::transaction(function () use ($payroll): void {
         PayrollJournal::create(['payroll_id' => $payroll->id, 'company_id' => $payroll->company_id, 'journal_number' => 'PAYROLL-'.$payroll->period->format('Ym').'-'.substr($payroll->id, 0, 8), 'transaction_date' => now()->toDateString(), 'amount' => $payroll->total_amount, 'description' => 'Posting jurnal penggajian periode '.$payroll->period->format('m/Y'), 'posted_by' => Auth::id()]);
         $payroll->update(['status' => 'posted', 'posted_at' => now(), 'posted_by' => Auth::id()]);
         Overtime::query()->where('company_id', $payroll->company_id)->where('status', 'approved')->whereBetween('overtime_date', [$payroll->period->toDateString(), $payroll->period->copy()->endOfMonth()->toDateString()])->update(['status' => 'paid']);
      });
      return response()->json(['data' => $payroll->fresh('journal'), 'message' => 'Payroll berhasil diposting ke jurnal.']);
   }

   private function commission(Employee $employee, Carbon $period): array
   {
      $query = SalesTransaction::query()->where('company_id', $employee->company_id)->where('status', 'completed')->whereBetween('transaction_date', [$period->toDateString(), $period->copy()->endOfMonth()->toDateString()])->whereHas('creator', fn ($user) => $user->where('company_id', $employee->company_id)->where('email', $employee->email))->with('details:id,sales_transaction_id,quantity');
      $transactions = $query->get(); $qty = (int) $transactions->flatMap->details->sum('quantity'); $sales = (int) $transactions->sum('total');
      $amount = $employee->commission_type === 'percentage' ? (int) round($sales * (float) $employee->commission_value / 100) : (int) round($qty * (float) $employee->commission_value);
      return ['amount' => $amount, 'detail' => ['sales' => $sales, 'qty' => $qty, 'transaction_count' => $transactions->count(), 'status' => 'unpaid']];
   }
}

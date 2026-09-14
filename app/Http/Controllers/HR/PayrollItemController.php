<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\HR\IndexPayrollItemsRequest;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Services\CompanyContext;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class PayrollItemController extends Controller
{
   public function __construct(private CompanyContext $companyContext) {}

   public function index(IndexPayrollItemsRequest $request, Payroll $payroll): JsonResponse
   {
      abort_unless($payroll->company_id === $this->companyContext->id(), 404);
      $filters = $request->validated();
      $items = PayrollItem::query()->where('payroll_id', $payroll->id)->with('employee:id,name,nik,role')->when($filters['search'] ?? null, fn($q, $search) => $q->whereHas('employee', fn($employee) => $employee->where('name', 'like', "%{$search}%")->orWhere('nik', 'like', "%{$search}%")))->orderBy('id')->cursorPaginate($filters['per_page'] ?? 10)->withQueryString();
      return response()->json(['data' => $items]);
   }

   public function print(PayrollItem $payrollItem): View
   {
      abort_unless($payrollItem->company_id === $this->companyContext->id(), 404);

      $payrollItem->load([
         'employee.company',
         'payroll',
         'overtimes',
      ]);

      return view('hr.payroll.slip-print', [
         'item' => $payrollItem,
         'company' => $payrollItem->employee->company,
         'terbilang' => $this->terbilang((int) $payrollItem->net_salary),
      ]);
   }

   private function terbilang(int $number): string
   {
      $words = ['nol', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];

      if ($number < 12) return $words[$number];
      if ($number < 20) return $this->terbilang($number - 10) . ' belas';
      if ($number < 100) return $this->terbilang(intdiv($number, 10)) . ' puluh' . ($number % 10 ? ' ' . $this->terbilang($number % 10) : '');
      if ($number < 200) return 'seratus' . ($number - 100 ? ' ' . $this->terbilang($number - 100) : '');
      if ($number < 1000) return $this->terbilang(intdiv($number, 100)) . ' ratus' . ($number % 100 ? ' ' . $this->terbilang($number % 100) : '');
      if ($number < 2000) return 'seribu' . ($number - 1000 ? ' ' . $this->terbilang($number - 1000) : '');
      if ($number < 1000000) return $this->terbilang(intdiv($number, 1000)) . ' ribu' . ($number % 1000 ? ' ' . $this->terbilang($number % 1000) : '');
      if ($number < 1000000000) return $this->terbilang(intdiv($number, 1000000)) . ' juta' . ($number % 1000000 ? ' ' . $this->terbilang($number % 1000000) : '');
      if ($number < 1000000000000) return $this->terbilang(intdiv($number, 1000000000)) . ' miliar' . ($number % 1000000000 ? ' ' . $this->terbilang($number % 1000000000) : '');

      return $this->terbilang(intdiv($number, 1000000000000)) . ' triliun' . ($number % 1000000000000 ? ' ' . $this->terbilang($number % 1000000000000) : '');
   }
}

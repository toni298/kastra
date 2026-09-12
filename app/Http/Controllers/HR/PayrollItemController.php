<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\HR\IndexPayrollItemsRequest;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Services\CompanyContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Barryvdh\DomPDF\Facade\Pdf;

class PayrollItemController extends Controller
{
   public function __construct(private CompanyContext $companyContext) {}

   public function index(IndexPayrollItemsRequest $request, Payroll $payroll): JsonResponse
   {
      abort_unless($payroll->company_id === $this->companyContext->id(), 404);
      $filters = $request->validated();
      $items = PayrollItem::query()->where('payroll_id', $payroll->id)->with('employee:id,name,nik,role')->when($filters['search'] ?? null, fn ($q, $search) => $q->whereHas('employee', fn ($employee) => $employee->where('name', 'like', "%{$search}%")->orWhere('nik', 'like', "%{$search}%")))->orderBy('id')->cursorPaginate($filters['per_page'] ?? 10)->withQueryString();
      return response()->json(['data' => $items]);
   }

   public function printPdf(PayrollItem $item): Response
   {
      abort_unless($item->company_id === $this->companyContext->id(), 404);
      $item->load(['employee', 'payroll']);
      return Pdf::loadView('hr.payroll-slip', ['item' => $item])->setPaper('a4', 'portrait')->download('slip-'.$item->employee->nik.'.pdf');
   }
}

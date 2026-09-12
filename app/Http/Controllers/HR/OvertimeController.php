<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\HR\IndexOvertimeRequest;
use App\Http\Requests\HR\StoreOvertimeRequest;
use App\Http\Requests\HR\UpdateOvertimeStatusRequest;
use App\Models\Overtime;
use App\Services\CompanyContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class OvertimeController extends Controller
{
   public function __construct(private CompanyContext $companyContext) {}

   public function index(IndexOvertimeRequest $request): JsonResponse
   {
      $filters = $request->validated();
      $query = Overtime::query()->where('company_id', $this->companyContext->id())->with('employee:id,name,nik')->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))->when($filters['search'] ?? null, fn ($q, $search) => $q->whereHas('employee', fn ($employee) => $employee->where('name', 'like', "%{$search}%")->orWhere('nik', 'like', "%{$search}%")))->when($filters['from'] ?? null, fn ($q, $from) => $q->whereDate('overtime_date', '>=', $from))->when($filters['to'] ?? null, fn ($q, $to) => $q->whereDate('overtime_date', '<=', $to))->orderByDesc('overtime_date')->orderByDesc('id');
      $monthFrom = now()->startOfMonth()->toDateString();
      $monthTo = now()->endOfMonth()->toDateString();
      $month = Overtime::query()->where('company_id', $this->companyContext->id())->whereBetween('overtime_date', [$monthFrom, $monthTo]);

      return response()->json([
         'data' => $query->cursorPaginate($filters['per_page'] ?? 10)->withQueryString(),
         'summary' => [
            'hours' => (float) (clone $month)->sum('hours'),
            'pending' => (clone $month)->where('status', 'pending')->count(),
            'cost' => (int) (clone $month)->where('status', 'approved')->sum('total_amount'),
            'approved' => (clone $month)->where('status', 'approved')->count(),
         ],
      ]);
   }

   public function store(StoreOvertimeRequest $request): JsonResponse
   {
      $data = $request->validated();
      $overtime = Overtime::create([...$data, 'company_id' => $this->companyContext->id(), 'total_amount' => round((float) $data['hours'] * (int) $data['hourly_rate'])]);
      return response()->json(['data' => $overtime->load('employee:id,name,nik'), 'message' => 'Pengajuan lembur berhasil dicatat.'], 201);
   }

   public function updateStatus(UpdateOvertimeStatusRequest $request, Overtime $overtime): JsonResponse
   {
      abort_unless($overtime->company_id === $this->companyContext->id(), 404);
      $overtime->update(['status' => $request->validated('status'), 'approved_by' => Auth::id(), 'approved_at' => now()]);
      return response()->json(['data' => $overtime->fresh('employee:id,name,nik'), 'message' => 'Status lembur berhasil diperbarui.']);
   }
}

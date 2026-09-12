<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\HR\StoreShiftRequest;
use App\Http\Requests\HR\UpdateShiftRequest;
use App\Http\Requests\HR\IndexShiftEmployeesRequest;
use App\Http\Resources\EmployeeResource;
use App\Models\Employee;
use App\Models\Shift;
use App\Services\CompanyContext;
use Illuminate\Http\JsonResponse;

class ShiftController extends Controller
{
   public function __construct(private CompanyContext $companyContext) {}

   public function index(): JsonResponse
   {
      return response()->json([
         'data' => Shift::query()
            ->where('company_id', $this->companyContext->id())
            ->withCount('employees')
            ->orderBy('start_time')
            ->get(),
      ]);
   }

   public function employees(IndexShiftEmployeesRequest $request, Shift $shift): JsonResponse
   {
      abort_unless($shift->company_id === $this->companyContext->id(), 404);

      $employees = Employee::query()
         ->where('company_id', $this->companyContext->id())
         ->where('shift_id', $shift->id)
         ->with('shift:id,name,start_time,end_time')
         ->when($request->validated('search'), fn($query, $search) => $query->where(function ($nested) use ($search): void {
            $nested->where('name', 'like', "%{$search}%")
               ->orWhere('nik', 'like', "%{$search}%");
         }))
         ->orderBy('name')
         ->orderBy('id')
         ->cursorPaginate($request->validated('per_page', 10))
         ->withQueryString();

      return response()->json(['data' => EmployeeResource::collection($employees)]);
   }

   public function store(StoreShiftRequest $request): JsonResponse
   {
      $shift = Shift::create([...$request->validated(), 'company_id' => $this->companyContext->id()]);
      return response()->json(['data' => $shift, 'message' => 'Shift berhasil dibuat.'], 201);
   }

   public function update(UpdateShiftRequest $request, Shift $shift): JsonResponse
   {
      abort_unless($shift->company_id === $this->companyContext->id(), 404);
      $shift->update($request->validated());
      return response()->json(['data' => $shift, 'message' => 'Shift berhasil diperbarui.']);
   }

   public function destroy(Shift $shift): JsonResponse
   {
      abort_unless($shift->company_id === $this->companyContext->id(), 404);

      if ($shift->employees()->where('status', 'active')->exists()) {
         return response()->json([
            'message' => 'Shift tidak dapat dihapus karena masih memiliki karyawan aktif.',
            'warning' => 'Pindahkan karyawan aktif ke shift lain terlebih dahulu.',
         ], 409);
      }

      $shift->delete();
      return response()->json(['message' => 'Shift berhasil dihapus.']);
   }
}

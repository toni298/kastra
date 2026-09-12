<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\HR\IndexAttendanceRequest;
use App\Http\Requests\HR\UpdateAttendanceRequest;
use App\Http\Resources\AttendanceResource;
use App\Models\Attendance;
use App\Repositories\AttendanceRepository;
use App\Services\AttendanceService;
use App\Services\CompanyContext;
use Illuminate\Http\JsonResponse;

class AttendanceController extends Controller
{
   public function __construct(
      private AttendanceRepository $repository,
      private AttendanceService $service,
      private CompanyContext $companyContext,
   ) {}

   public function index(IndexAttendanceRequest $request): JsonResponse
   {
      $filters = $request->validated();
      $companyId = (string) $this->companyContext->id();
      $date = $filters['date'] ?? now()->toDateString();

      $this->repository->ensureDailyRecords($companyId, $date);

      return response()->json([
         'data' => AttendanceResource::collection($this->repository->paginate($companyId, $filters)),
         'summary' => $this->repository->summary($companyId, $date),
      ]);
   }

   public function update(UpdateAttendanceRequest $request, Attendance $attendance): JsonResponse
   {
      abort_unless($attendance->company_id === $this->companyContext->id(), 404);

      return response()->json([
         'data' => new AttendanceResource($this->service->update($attendance, $request->validated())),
         'message' => 'Log absensi berhasil dikoreksi.',
      ]);
   }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\Kiosk\ClockRequest;
use App\Services\AttendanceService;
use App\Services\CompanyContext;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class KioskAttendanceController extends Controller
{
   public function __construct(private AttendanceService $service, private CompanyContext $companyContext) {}

   public function page(): Response
   {
      $company = $this->companyContext->current();

      return Inertia::render('Kiosk/Attendance', [
         'company' => [
            'name' => $company?->name ?? config('app.name'),
            'logo_url' => $company?->logo_path
               ? rtrim((string) config('filesystems.disks.public.url'), '/') . '/' . ltrim($company->logo_path, '/')
               : null,
         ],
      ]);
   }

   public function clock(ClockRequest $request): JsonResponse
   {
      return response()->json(['data' => $this->service->clock((string) $this->companyContext->id(), $request->validated('pin'))]);
   }
}

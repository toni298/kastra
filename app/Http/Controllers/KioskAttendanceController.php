<?php

namespace App\Http\Controllers;

use App\Http\Requests\Kiosk\ClockRequest;
use App\Models\Company;
use App\Services\AttendanceService;
use App\Services\CompanyContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class KioskAttendanceController extends Controller
{
   public function __construct(private AttendanceService $service, private CompanyContext $companyContext) {}

   public function page(): Response
   {
      $company = Company::query()->find(config('kiosk.company_id'));

      return Inertia::render('Kiosk/Attendance', [
         'company' => [
            'name' => $company?->name ?? config('app.name'),
            'logo_url' => $company?->logo_path
               ? rtrim((string) config('filesystems.disks.public.url'), '/') . '/' . ltrim($company->logo_path, '/')
               : null,
         ],
      ]);
   }

   public function redirectToPage(): RedirectResponse
   {
      return redirect()->route('kiosk.attendance', status: 303);
   }

   public function clock(ClockRequest $request): JsonResponse
   {
      $result = $this->service->clock((string) $this->companyContext->id(), $request->validated());
      return response()->json($result, $result['status'] === 'success' ? 200 : 422);
   }
}

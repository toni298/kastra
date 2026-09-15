<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\Employee\IndexEmployeeRequest;
use App\Services\CompanyContext;
use App\Services\HRSummaryService;
use Inertia\Inertia;
use Inertia\Response;

class HRSummaryController extends Controller
{
   public function __construct(
      private HRSummaryService $summaryService,
      private CompanyContext $companyContext,
   ) {}

   public function __invoke(IndexEmployeeRequest $request): Response
   {
      return Inertia::render('HR/Index', [
         'activeTab' => 'summary',
         'summary' => $this->summaryService->build((string) $this->companyContext->id()),
      ]);
   }
}

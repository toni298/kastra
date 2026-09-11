<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\Employee\IndexEmployeeRequest;
use App\Repositories\EmployeeRepository;
use App\Services\CompanyContext;
use Inertia\Inertia;
use Inertia\Response;

class HRSummaryController extends Controller
{
   public function __construct(
      private EmployeeRepository $repository,
      private CompanyContext $companyContext,
   ) {}

   public function __invoke(IndexEmployeeRequest $request): Response
   {
      return Inertia::render('HR/Index', [
         'activeTab' => 'summary',
         'summary' => $this->repository->summary((string) $this->companyContext->id()),
      ]);
   }
}

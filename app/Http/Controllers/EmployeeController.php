<?php

namespace App\Http\Controllers;

use App\Http\Requests\Employee\IndexEmployeeRequest;
use App\Http\Requests\Employee\ResetEmployeePinRequest;
use App\Http\Requests\Employee\StoreEmployeeRequest;
use App\Http\Resources\EmployeeResource;
use App\Models\Employee;
use App\Repositories\EmployeeRepository;
use App\Services\CompanyContext;
use App\Services\EmployeeService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class EmployeeController extends Controller
{
   public function __construct(
      private EmployeeRepository $repository,
      private EmployeeService $service,
      private CompanyContext $companyContext,
   ) {}

   public function index(IndexEmployeeRequest $request): Response
   {
      return Inertia::render('HR/Index', [
         'employees' => EmployeeResource::collection(
            $this->repository->paginate($this->companyId(), $request->validated())
         ),
         'branches' => $this->repository->branches($this->companyId()),
         'shifts' => $this->repository->shifts($this->companyId()),
         'filters' => $request->validated(),
         'activeTab' => 'employees',
      ]);
   }

   public function store(StoreEmployeeRequest $request): RedirectResponse
   {
      $this->service->create($this->companyId(), $request->validated());

      return to_route('hr.employees.index')->with('success', 'Karyawan berhasil ditambahkan.');
   }

   public function update(StoreEmployeeRequest $request, Employee $employee): RedirectResponse
   {
      $this->service->update($employee, $request->validated());

      return to_route('hr.employees.index')->with('success', 'Data karyawan berhasil diperbarui.');
   }

   public function destroy(\Illuminate\Http\Request $request, Employee $employee): RedirectResponse
   {
      abort_unless($request->user()->can('delete', $employee), 403);
      $this->service->delete($employee);

      return to_route('hr.employees.index')->with('success', 'Karyawan berhasil dihapus.');
   }

   public function resetPin(ResetEmployeePinRequest $request, Employee $employee): RedirectResponse
   {
      $this->service->resetPin($employee, $request->validated('pin'));

      return to_route('hr.employees.index')->with('success', 'PIN karyawan berhasil direset.');
   }

   private function companyId(): string
   {
      return (string) $this->companyContext->id();
   }
}

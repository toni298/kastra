<?php

namespace App\Http\Controllers;

use App\Http\Requests\Role\IndexRoleRequest;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Models\Role;
use App\Repositories\RoleRepository;
use App\Services\CompanyContext;
use App\Services\RbacService;
use App\Services\RoleService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class RoleController extends Controller
{
    public function __construct(
        private RoleRepository $repository,
        private RoleService $service,
        private CompanyContext $companyContext,
        private RbacService $rbacService,
    ) {}

    public function index(IndexRoleRequest $request): Response
    {
        $features = $this->companyContext->current()?->features;
        $availablePermissions = $this->rbacService->permissionsForFeatures($features);

        return Inertia::render('Roles/Index', [
            'roles' => $this->repository->filtered($this->companyId(), $request->validated(), $availablePermissions),
            'permissions' => $this->repository->permissions($availablePermissions)->values()->all(),
            'stats' => $this->repository->stats($this->companyId()),
            'filters' => $request->validated(),
        ]);
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $this->service->create($this->companyId(), $request->validated());

        return back()->with('success', 'Role berhasil ditambahkan.');
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $this->service->update($role, $request->validated());

        return back()->with('success', 'Role berhasil diperbarui.');
    }

    public function destroy(Request $request, Role $role): RedirectResponse
    {
        abort_unless($request->user()->can('delete', $role), 403);
        $this->service->delete($role);

        return back()->with('success', 'Role berhasil dihapus.');
    }

    private function companyId(): string
    {
        return (string) $this->companyContext->id();
    }
}

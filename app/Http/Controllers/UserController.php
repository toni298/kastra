<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\IndexUserRequest;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Services\CompanyContext;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function __construct(
        private UserRepository $repository,
        private UserService $service,
        private CompanyContext $companyContext,
    ) {}

    public function index(IndexUserRequest $request): Response
    {
        return Inertia::render('Users/Index', [
            'users' => $this->repository->paginate($this->companyId(), $request->validated()),
            'roles' => $this->repository->roles($this->companyId()),
            'filters' => $request->validated(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->service->create($this->companyId(), $request->validated());
        return to_route('users.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->service->update($user, $request->validated());
        return to_route('users.index')->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()->can('delete', $user), 403);
        $this->service->delete($user);
        return to_route('users.index')->with('success', 'Pengguna berhasil dihapus.');
    }

    private function companyId(): string
    {
        return (string) $this->companyContext->id();
    }
}

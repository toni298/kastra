<?php

namespace App\Http\Controllers;

use App\Http\Requests\Permission\StorePermissionRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Permission;

class PermissionController extends Controller
{
    /**
     * List semua permissions dengan role count
     */
    public function index(): Response
    {
        Gate::authorize('viewAny', Permission::class);

        $permissions = Permission::query()
            ->with('roles')
            ->withCount('roles')
            ->orderBy('group')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn($perm) => [
                'id' => $perm->id,
                'name' => $perm->name,
                'display_name' => $perm->display_name ?? $perm->name,
                'group' => $perm->group,
                'subgroup' => $perm->subgroup,
                'description' => $perm->description,
                'roles_count' => $perm->roles_count,
            ]);

        return Inertia::render('Permissions/Index', [
            'permissions' => $permissions,
            'totalPermissions' => $permissions->count(),
        ]);
    }

    /**
     * Search permissions (untuk autocomplete/async select)
     */
    public function search(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Permission::class);

        $search = $request->input('q', '');
        $group = $request->input('group', null);

        $query = Permission::query();

        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('display_name', 'like', "%{$search}%");
        }

        if ($group) {
            $query->where('group', $group);
        }

        $permissions = $query
            ->orderBy('group')
            ->orderBy('sort_order')
            ->limit(50)
            ->get(['id', 'name', 'display_name', 'group', 'subgroup']);

        return response()->json([
            'data' => $permissions,
        ]);
    }

    /**
     * Dapatkan permissions grouped by group/subgroup (untuk UI rendering)
     */
    public function grouped(): JsonResponse
    {
        Gate::authorize('viewAny', Permission::class);

        $permissions = Permission::query()
            ->orderBy('group')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $grouped = $permissions->groupBy('group')->map(fn($items) => [
            'group' => $items->first()->group,
            'permissions' => $items->groupBy('subgroup')->map(fn($subItems) => [
                'subgroup' => $subItems->first()->subgroup,
                'permissions' => $subItems->map(fn($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'display_name' => $p->display_name ?? $p->name,
                    'description' => $p->description,
                ])->values(),
            ])->values(),
        ])->values();

        return response()->json([
            'data' => $grouped,
        ]);
    }

    /**
     * Create permission (admin only)
     */
    public function store(StorePermissionRequest $request): RedirectResponse
    {
        Permission::create([...$request->validated(), 'guard_name' => 'web']);
        return back()->with('success', 'Permission berhasil ditambahkan.');
    }
}

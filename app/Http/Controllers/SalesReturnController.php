<?php

namespace App\Http\Controllers;

use App\Http\Requests\Sales\IndexSalesReturnRequest;
use App\Http\Resources\SalesReturnResource;
use App\Models\SalesReturn;
use App\Repositories\SalesReturnRepository;
use App\Services\CompanyContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class SalesReturnController extends Controller
{
    public function __construct(private SalesReturnRepository $repository, private CompanyContext $companyContext) {}

    public function index(IndexSalesReturnRequest $request): Response
    {
        Gate::authorize('viewAny', SalesReturn::class);

        return Inertia::render('Sales/Returns/Index', [
            'returnItems' => fn() => SalesReturnResource::collection(
                $this->repository->paginate((string) $this->companyContext->id(), $request->validated())
            ),
            'returnFilters' => $request->validated(),
        ]);
    }

    public function create(IndexSalesReturnRequest $request): Response
    {
        Gate::authorize('viewAny', SalesReturn::class);

        return $this->index($request);
    }

    public function store(): RedirectResponse
    {
        abort(501, 'CRUD retur penjualan belum tersedia.');
    }

    public function show(SalesReturn $return): JsonResponse
    {
        abort_unless((string) $return->company_id === (string) $this->companyContext->id(), 404);
        Gate::authorize('view', $return);

        return SalesReturnResource::make($this->repository->findWithDetails((string) $this->companyContext->id(), (string) $return->getKey()))->response();
    }

    public function edit(SalesReturn $return): JsonResponse
    {
        return $this->show($return);
    }
    public function update(): RedirectResponse
    {
        abort(501, 'CRUD retur penjualan belum tersedia.');
    }
    public function destroy(): RedirectResponse
    {
        abort(501, 'CRUD retur penjualan belum tersedia.');
    }
}

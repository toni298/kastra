<?php

namespace App\Http\Controllers\CashBank;

use App\Http\Requests\CashBank\StoreCashBankTransferRequest;
use App\Http\Requests\CashBank\IndexCashBankTransferRequest;
use App\Http\Resources\CashBankAccountResource;
use App\Http\Resources\CashBankTransferResource;
use App\Models\CashBankAccount;
use App\Repositories\CashBankTransferRepository;
use App\Repositories\CashBankSummaryRepository;
use App\Services\CashBankTransferService;
use App\Services\CompanyContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CashBankTransfersController
{
    public function __construct(private CashBankTransferRepository $transfers, private CashBankSummaryRepository $summary, private CashBankTransferService $transferService, private CompanyContext $companyContext) {}

    public function index(IndexCashBankTransferRequest $request): Response
    {
        $filters = $request->validated();
        $companyId = (string) $this->companyContext->id();
        $transfers = CashBankTransferResource::collection($this->transfers->paginate($companyId, $filters));
        return Inertia::render('CashBank/Transfers/Index', ['cashBankSummary' => $this->summary->forCompany($companyId), 'cashBankTransfers' => $transfers, 'cashBankTransferFilters' => $filters, 'cashBankAccounts' => CashBankAccountResource::collection(CashBankAccount::query()->where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get())->resolve($request)]);
    }

    public function store(StoreCashBankTransferRequest $request): RedirectResponse
    {
        $this->transferService->create((string) $this->companyContext->id(), (string) $request->user()->id, $request->validated());
        return back()->with('success', 'Transfer dana berhasil disimpan.');
    }
}

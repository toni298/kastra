<?php

namespace App\Http\Controllers\CashBank;

use App\Http\Requests\CashBank\StoreCashBankTransactionRequest;
use App\Http\Requests\CashBank\IndexCashBankTransactionRequest;
use App\Http\Resources\CashBankAccountResource;
use App\Http\Resources\CashBankTransactionResource;
use App\Models\CashBankAccount;
use App\Models\CashBankTransaction;
use App\Repositories\CashBankTransactionRepository;
use App\Repositories\CashBankSummaryRepository;
use App\Services\CompanyContext;
use App\Services\CashBankTransactionService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CashBankTransactionsController
{
    public function __construct(private CashBankTransactionRepository $transactions, private CashBankSummaryRepository $summary, private CashBankTransactionService $service, private CompanyContext $companyContext) {}
    public function index(IndexCashBankTransactionRequest $request): Response
    {
        $filters = $request->validated();
        $companyId = (string) $this->companyContext->id();
        return Inertia::render('CashBank/Transactions/Index', ['cashBankSummary' => $this->summary->forCompany($companyId), 'cashBankTransactions' => CashBankTransactionResource::collection($this->transactions->paginate($companyId, $filters)), 'cashBankTransactionFilters' => $filters, 'cashBankAccounts' => CashBankAccountResource::collection(CashBankAccount::query()->with('settings')->where('company_id',$companyId)->where('is_active',true)->orderBy('name')->get())->resolve($request)]);
    }

    public function store(StoreCashBankTransactionRequest $request): RedirectResponse
    {
        $this->service->create((string) $this->companyContext->id(), (string) $request->user()->id, $request->validated());
        return back()->with('success', 'Transaksi kas dan bank berhasil disimpan.');
    }

    public function update(StoreCashBankTransactionRequest $request, CashBankTransaction $transaction): RedirectResponse
    {
        abort_unless((string) $transaction->company_id === (string) $this->companyContext->id(), 404);
        $this->service->update($transaction, $request->validated());

        return back()->with('success', 'Transaksi kas dan bank berhasil diperbarui.');
    }

    public function destroy(CashBankTransaction $transaction): RedirectResponse
    {
        abort_unless((string) $transaction->company_id === (string) $this->companyContext->id(), 404);
        $this->service->delete($transaction);

        return back()->with('success', 'Transaksi kas dan bank berhasil dihapus.');
    }
}

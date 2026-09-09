<?php

namespace App\Http\Controllers\CashBank;

use App\Http\Requests\CashBank\StoreCashBankAccountRequest;
use App\Http\Resources\CashBankAccountResource;
use App\Models\CashBankAccount;
use App\Repositories\CashBankAccountRepository;
use App\Repositories\CashBankSummaryRepository;
use App\Services\CompanyContext;
use App\Services\CashBankAccountService;
use App\Models\Branch;
use App\Models\CashBankAccountSetting;
use App\Http\Resources\CashBankAccountSettingResource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class CashBankAccountsController
{
    public function __construct(private CashBankAccountRepository $repository, private CashBankSummaryRepository $summary, private CashBankAccountService $service, private CompanyContext $companyContext) {}

    public function index(): Response
    {
        $companyId = (string) $this->companyContext->id();

        return Inertia::render('CashBank/Index', ['activeTab' => 'accounts', 'accountingEnabled' => true, 'cashBankSummary' => $this->summary->forCompany($companyId), 'cashBankAccounts' => $this->accountsPayload(), 'cashBankBranches' => Branch::query()->where('company_id', $companyId)->orderBy('name')->get(['id', 'name']), 'cashBankAccountSettings' => CashBankAccountSettingResource::collection(CashBankAccountSetting::query()->with(['account:id,name,type,bank_name,account_number', 'branch:id,name'])->where('company_id', $companyId)->get())->resolve(request())]);
    }

    public function list(\Illuminate\Http\Request $request): JsonResponse { return response()->json($this->accountsPayload($request->query('cursor'))); }

    public function search(\Illuminate\Http\Request $request): JsonResponse
    {
        $search = $request->string('search')->toString();
        $excludeId = $request->string('exclude_id')->toString();
        $type = $request->string('type')->toString();
        $items = CashBankAccount::query()->where('company_id', (string) $this->companyContext->id())->where('is_active', true)->when($excludeId, fn ($query) => $query->whereKeyNot($excludeId))->when(in_array($type, ['in', 'out'], true), fn ($query) => $query->whereHas('settings', fn ($settings) => $settings->where('is_active', true)->where($type === 'in' ? 'can_receive_money' : 'can_send_money', true)))->when($search, fn ($query) => $query->where('name', 'like', "%{$search}%"))->orderBy('name')->limit(20)->get(['id', 'name', 'bank_name', 'currency', 'current_balance', 'type']);
        return response()->json(['results' => $items->map(fn ($item) => ['id' => $item->id, 'text' => collect([$item->name, $item->bank_name])->filter()->join(' - '), 'currency' => $item->currency, 'balance' => $item->current_balance, 'type' => $item->type]), 'pagination' => ['more' => false], 'next_cursor' => null]);
    }

    public function store(StoreCashBankAccountRequest $request): RedirectResponse
    {
        $this->service->create((string) $this->companyContext->id(), $request->validated());
        return back()->with('success', 'Rekening berhasil ditambahkan.');
    }

    public function update(StoreCashBankAccountRequest $request, CashBankAccount $account): RedirectResponse
    {
        abort_unless((string) $account->company_id === (string) $this->companyContext->id(), 404);
        $this->service->update($account, $request->validated());
        return back()->with('success', 'Rekening berhasil diperbarui.');
    }

    public function deactivate(CashBankAccount $account): RedirectResponse
    {
        abort_unless((string) $account->company_id === (string) $this->companyContext->id(), 404);
        $account->update(['is_active' => false]);
        return back()->with('success', 'Rekening berhasil dinonaktifkan.');
    }

    public function destroy(CashBankAccount $account): RedirectResponse
    {
        abort_unless((string) $account->company_id === (string) $this->companyContext->id(), 404);
        $account->delete();
        return back()->with('success', 'Rekening berhasil dihapus.');
    }

    private function accountsPayload(?string $cursor = null): array
    {
        $accounts = $this->repository->cursorPaginate((string) $this->companyContext->id(), $cursor);
        return ['data' => CashBankAccountResource::collection(collect($accounts->items()))->resolve(request()), 'next_cursor' => $accounts->nextCursor()?->encode()];
    }
}

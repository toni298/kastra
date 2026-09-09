<?php

namespace App\Http\Controllers\CashBank;

use App\Http\Controllers\Controller;
use App\Http\Requests\CashBank\IndexCashBankAccountSettingRequest;
use App\Http\Requests\CashBank\StoreCashBankAccountSettingRequest;
use App\Http\Requests\CashBank\UpdateCashBankAccountSettingRequest;
use App\Http\Resources\CashBankAccountSettingResource;
use App\Models\CashBankAccountSetting;
use App\Repositories\CashBankAccountSettingRepository;
use App\Services\CashBankAccountSettingService;
use App\Services\CompanyContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class CashBankAccountSettingsController extends Controller
{
    public function __construct(
        private CashBankAccountSettingRepository $repository,
        private CashBankAccountSettingService $service,
        private CompanyContext $companyContext,
    ) {
    }

    public function index(IndexCashBankAccountSettingRequest $request): JsonResponse
    {
        $this->authorize('viewAny', CashBankAccountSetting::class);
        $settings = $this->repository->cursorPaginate((string) $this->companyContext->id(), $request->validated());

        return CashBankAccountSettingResource::collection($settings)->response();
    }

    public function store(StoreCashBankAccountSettingRequest $request): JsonResponse
    {
        $this->authorize('create', CashBankAccountSetting::class);
        $setting = $this->service->create((string) $this->companyContext->id(), $request->validated());

        return (new CashBankAccountSettingResource($setting->load(['account:id,name,type,bank_name,account_number', 'branch:id,name'])))
            ->response()
            ->setStatusCode(201);
    }

    public function show(CashBankAccountSetting $account_setting): JsonResponse
    {
        $this->authorize('view', $account_setting);

        return (new CashBankAccountSettingResource($account_setting->load(['account:id,name,type,bank_name,account_number', 'branch:id,name'])))->response();
    }

    public function update(UpdateCashBankAccountSettingRequest $request, CashBankAccountSetting $account_setting): JsonResponse
    {
        $this->authorize('update', $account_setting);
        $setting = $this->service->update($account_setting, $request->validated());

        return (new CashBankAccountSettingResource($setting->load(['account:id,name,type,bank_name,account_number', 'branch:id,name'])))->response();
    }

    public function destroy(CashBankAccountSetting $account_setting): Response
    {
        $this->authorize('delete', $account_setting);
        $this->service->delete($account_setting);

        return response()->noContent();
    }
}

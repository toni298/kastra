<?php

namespace App\Http\Controllers;

use App\Http\Requests\Inventory\UpdateBranchProductStockDiscountRequest;
use App\Models\BranchProductStock;
use App\Services\CompanyContext;
use App\Services\InertiaAuthorizationService;
use Illuminate\Http\JsonResponse;

class InventoryBranchProductStockController extends Controller
{
   public function updateDiscount(UpdateBranchProductStockDiscountRequest $request, BranchProductStock $branchProductStock, CompanyContext $companyContext): JsonResponse
   {
      app(InertiaAuthorizationService::class)->authorize($request->user(), 'inventory.summary.discount');

      abort_unless($branchProductStock->company_id === (string) $companyContext->id(), 404);

      $branchProductStock->update($request->validated());

      return response()->json(['success' => true]);
   }
}

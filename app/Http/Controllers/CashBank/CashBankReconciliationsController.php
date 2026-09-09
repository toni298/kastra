<?php

namespace App\Http\Controllers\CashBank;

use Inertia\Inertia;
use Inertia\Response;

class CashBankReconciliationsController
{
    public function index(): Response
    {
        return Inertia::render('CashBank/Index', ['activeTab' => 'reconciliation', 'accountingEnabled' => true]);
    }
}

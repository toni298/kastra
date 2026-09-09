<?php

namespace App\Http\Controllers\Accounting;

use Inertia\Inertia;
use Inertia\Response;

class ShowAccountingReportsController
{
    public function __invoke(): Response
    {
        return Inertia::render('Accounting/Reports/Index', [
            'cashierLayout' => request()->routeIs('cashier.reports'),
        ]);
    }
}

<?php

namespace App\Http\Controllers\Accounting;

use Inertia\Inertia;
use Inertia\Response;

class ShowAccountingSettingsController
{
    public function __invoke(): Response
    {
        return Inertia::render('Accounting/Settings/Index');
    }
}

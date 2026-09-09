<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class WelcomeController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Welcome', [
            'loginUrl' => Route::has('login') ? route('login') : null,
            'registerUrl' => Route::has('register') ? route('register') : null,
        ])->rootView('landing');
    }
}

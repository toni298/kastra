<?php

namespace App\Http\Controllers;

use App\Http\Requests\Onboarding\CompleteOnboardingRequest;
use App\Services\CompanyContext;
use App\Services\OnboardingService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class OnboardingController extends Controller
{
    public function index(): Response|RedirectResponse
    {
        if (request()->user()->onboarding_completed_at !== null) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('Onboarding/Index');
    }

    public function store(
        CompleteOnboardingRequest $request,
        OnboardingService $service,
        CompanyContext $companyContext,
    ): RedirectResponse {
        $user = $request->user();

        $service->complete($user, $request->validated());
        $companyContext->resolve($user, $request->session());

        return redirect()->route('dashboard')->with('success', 'Setup bisnis berhasil diselesaikan.');
    }
}
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOnboardingCompleted
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() !== null && $request->user()->onboarding_completed_at === null) {
            return redirect()->route('onboarding');
        }

        return $next($request);
    }
}
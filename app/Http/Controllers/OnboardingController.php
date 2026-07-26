<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OnboardingController extends Controller
{
    public function complete(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->update([
            'onboarding_completed'    => true,
            'onboarding_completed_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    public function skip(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->update([
            'onboarding_completed'    => true,
            'onboarding_completed_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }
}

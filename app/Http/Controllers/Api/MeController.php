<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LicenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeController extends Controller
{
    /** GET /api/v1/me — infos utilisateur + société courante + forfait. */
    public function __invoke(Request $request, LicenseService $licenses): JsonResponse
    {
        $user = $request->user();
        $company = $user->currentCompany;
        $license = $licenses->currentFor($user);

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'company' => $company ? [
                'id' => $company->id,
                'name' => $company->name,
                'email' => $company->email,
                'country' => $company->country,
                'currency' => $company->currency,
            ] : null,
            'plan' => [
                'code' => $license?->plan?->code,
                'name' => $license?->plan?->name,
                'license_status' => $license?->status,
                'ends_at' => $license?->effectiveEndsAt()?->toDateString(),
                'api_quota' => $license?->plan?->code === 'enterprise' ? 'unlimited' : '1000/h',
            ],
        ]);
    }
}

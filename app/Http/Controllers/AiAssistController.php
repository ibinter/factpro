<?php

namespace App\Http\Controllers;

use App\Services\AiAssistService;
use App\Services\LicenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiAssistController extends Controller
{
    private const ALLOWED_PLANS = ['business', 'enterprise'];

    public function __construct(
        private AiAssistService $ai,
        private LicenseService $licenses,
    ) {}

    private function hasBusinessPlan(Request $request): bool
    {
        $license = $this->licenses->currentFor($request->user());
        return $license !== null
            && in_array($license->plan?->code, self::ALLOWED_PLANS, true);
    }

    private function guardPlan(Request $request): ?JsonResponse
    {
        if (!$this->hasBusinessPlan($request)) {
            return response()->json(
                ['error' => 'Fonctionnalité IA réservée au forfait BUSINESS+'],
                403
            );
        }
        return null;
    }

    private function guardAvailable(): ?JsonResponse
    {
        if (!$this->ai->isAvailable()) {
            return response()->json(
                ['available' => false, 'message' => 'Clé API Anthropic non configurée'],
                200
            );
        }
        return null;
    }

    public function status(Request $request): JsonResponse
    {
        return response()->json([
            'available' => $this->ai->isAvailable(),
            'plan_ok'   => $this->hasBusinessPlan($request),
        ]);
    }

    public function suggestDescription(Request $request): JsonResponse
    {
        if ($guard = $this->guardPlan($request)) return $guard;
        if ($guard = $this->guardAvailable()) return $guard;

        $request->validate(['name' => 'required|string|max:255', 'category' => 'nullable|string|max:100']);

        $description = $this->ai->suggestProductDescription(
            $request->input('name'),
            $request->input('category', '')
        );

        return response()->json(['description' => $description]);
    }

    public function detectDuplicates(Request $request): JsonResponse
    {
        if ($guard = $this->guardPlan($request)) return $guard;
        if ($guard = $this->guardAvailable()) return $guard;

        $request->validate(['names' => 'required|array', 'names.*' => 'string']);

        $duplicates = $this->ai->detectCustomerDuplicates($request->input('names'));

        return response()->json(['duplicates' => $duplicates]);
    }

    public function summarizeDocument(Request $request): JsonResponse
    {
        if ($guard = $this->guardPlan($request)) return $guard;
        if ($guard = $this->guardAvailable()) return $guard;

        $request->validate([
            'customer_name' => 'required|string',
            'total'         => 'required|numeric',
            'currency'      => 'required|string|max:3',
            'items'         => 'nullable|array',
        ]);

        $summary = $this->ai->summarizeDocument($request->only(['customer_name', 'total', 'currency', 'items']));

        return response()->json(['summary' => $summary]);
    }

    public function suggestPrice(Request $request): JsonResponse
    {
        if ($guard = $this->guardPlan($request)) return $guard;
        if ($guard = $this->guardAvailable()) return $guard;

        $request->validate(['name' => 'required|string|max:255', 'currency' => 'nullable|string|max:3']);

        $price = $this->ai->suggestPrice(
            $request->input('name'),
            $request->input('currency', 'XOF')
        );

        return response()->json(['price' => $price]);
    }
}

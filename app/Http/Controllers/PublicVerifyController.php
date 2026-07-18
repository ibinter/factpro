<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\DocumentIntegrityService;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * Page publique de vérification multi-langue (Phase 16 — §verify.factpro.ibigsoft.com).
 * Accessible sans authentification. Affiche l'authenticité, les infos du document
 * et de l'émetteur en 5 langues (FR/EN/AR/PT/ES).
 */
class PublicVerifyController extends Controller
{
    public function __construct(
        private readonly DocumentIntegrityService $integrity
    ) {}

    /**
     * Page Inertia de vérification publique.
     * URL : /public/verify/{uuid}
     */
    public function show(string $uuid): InertiaResponse
    {
        $document = Document::where('uuid', $uuid)
            ->with(['company', 'customer'])
            ->first();

        if (! $document) {
            return Inertia::render('Public/Verify', [
                'status'   => 'not_found',
                'hash'     => $uuid,
                'document' => null,
                'company'  => null,
            ]);
        }

        return Inertia::render('Public/Verify', [
            'status'   => $this->getVerificationStatus($document),
            'hash'     => $uuid,
            'document' => [
                'number'       => $document->number,
                'type'         => $document->type,
                'type_label'   => $document->type_label,
                'date'         => $document->issue_date?->format('d/m/Y'),
                'total'        => (float) $document->total,
                'currency'     => $document->currency ?? 'XOF',
                'status'       => $document->status,
                'finalized_at' => $document->finalized_at?->format('d/m/Y H:i'),
            ],
            'company' => [
                'name'    => $document->company?->name,
                'logo'    => $document->company?->logo_path
                    ? asset('storage/'.$document->company->logo_path)
                    : null,
                'address' => $document->company?->address,
                'phone'   => $document->company?->phone,
                'email'   => $document->company?->email,
            ],
        ]);
    }

    /**
     * API JSON de vérification (pour intégration externe).
     * GET /api/public/verify/{uuid}
     */
    public function api(string $uuid): JsonResponse
    {
        $document = Document::where('uuid', $uuid)
            ->with(['company'])
            ->first();

        if (! $document) {
            return response()->json([
                'status'  => 'not_found',
                'hash'    => $uuid,
                'message' => 'Document not found or invalid identifier.',
            ], 404);
        }

        $status = $this->getVerificationStatus($document);

        return response()->json([
            'status'  => $status,
            'hash'    => $uuid,
            'document' => [
                'number'       => $document->number,
                'type'         => $document->type,
                'type_label'   => $document->type_label,
                'date'         => $document->issue_date?->format('d/m/Y'),
                'total'        => (float) $document->total,
                'currency'     => $document->currency ?? 'XOF',
                'status'       => $document->status,
                'finalized_at' => $document->finalized_at?->format('d/m/Y H:i'),
            ],
            'company' => [
                'name'    => $document->company?->name,
                'address' => $document->company?->address,
                'phone'   => $document->company?->phone,
                'email'   => $document->company?->email,
            ],
        ]);
    }

    private function getVerificationStatus(Document $document): string
    {
        // Un document non finalisé (sans integrity_hash) n'est pas authentique.
        if (! $document->isFinalized()) {
            return 'draft';
        }

        // Vérification de l'intégrité du hash SHA-256.
        if (! $this->integrity->verify($document)) {
            return 'tampered';
        }

        return match ($document->status) {
            'cancelled'                   => 'cancelled',
            'paid', 'partial'             => 'paid',
            default                       => 'authentic',
        };
    }
}

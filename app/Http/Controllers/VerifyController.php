<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\DocumentIntegrityService;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Page publique de vérification d'authenticité (cahier §5.2).
 * Accessible sans compte : scan du QR → statut AUTHENTIQUE / FALSIFIÉ.
 */
class VerifyController extends Controller
{
    public function __invoke(string $uuid, DocumentIntegrityService $integrity): Response
    {
        $document = Document::with(['company:id,name', 'customer:id,name'])
            ->where('uuid', $uuid)
            ->first();

        if (! $document) {
            return Inertia::render('Verify', ['result' => ['found' => false]]);
        }

        $isAuthentic = $document->isFinalized() && $integrity->verify($document);

        return Inertia::render('Verify', [
            'result' => [
                'found' => true,
                'authentic' => $isAuthentic,
                'issuer' => $document->company?->name,
                'type_label' => $document->type_label,
                'number' => $document->number,
                'issue_date' => $document->issue_date?->format('d/m/Y'),
                'total' => number_format((float) $document->total, 0, ',', ' '),
                'currency' => $document->currency,
                'status' => $document->status,
                'sealed_at' => $document->finalized_at?->format('d/m/Y H:i'),
                'is_trial' => $document->trial_watermark,
                'signed' => $document->signature_path !== null,
                'signed_by' => $document->signed_by_name,
                'signed_at' => $document->signed_at ? \Illuminate\Support\Carbon::parse($document->signed_at)->format('d/m/Y H:i') : null,
            ],
        ]);
    }
}

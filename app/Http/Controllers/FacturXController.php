<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\FacturXService;
use App\Services\LicenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FacturXController extends Controller
{
    public function __construct(
        private FacturXService $facturX,
        private LicenseService $licenses,
    ) {}

    /**
     * Vérifie que l'utilisateur a un plan ENTERPRISE actif.
     */
    private function isEnterprise(Request $request): bool
    {
        $license = $this->licenses->currentFor($request->user());

        return $license !== null
            && $license->isUsable()
            && $license->plan?->code === 'enterprise';
    }

    /**
     * Exporte le XML Factur-X d'une facture finalisée (ENTERPRISE uniquement).
     */
    public function export(Request $request, Document $document): Response
    {
        // Vérifier que le document appartient à la société de l'utilisateur
        abort_unless(
            $document->company_id === $request->user()->currentCompany?->id,
            403
        );

        abort_unless(
            $this->isEnterprise($request),
            403,
            'L\'export Factur-X est réservé au forfait ENTERPRISE.'
        );

        abort_unless(
            $document->isFinalized() && in_array($document->type, ['invoice', 'credit_note'], true),
            422,
            'Le document doit être une facture ou un avoir finalisé pour l\'export Factur-X.'
        );

        $xml = $this->facturX->generateXml($document);

        return response($xml, 200, [
            'Content-Type'        => 'application/xml',
            'Content-Disposition' => 'attachment; filename="factur-x.xml"',
        ]);
    }

    /**
     * Prévisualise le XML Factur-X (JSON) — ENTERPRISE uniquement.
     */
    public function preview(Request $request, Document $document): JsonResponse
    {
        abort_unless(
            $document->company_id === $request->user()->currentCompany?->id,
            403
        );

        abort_unless(
            $this->isEnterprise($request),
            403,
            'L\'export Factur-X est réservé au forfait ENTERPRISE.'
        );

        abort_unless(
            $document->isFinalized() && in_array($document->type, ['invoice', 'credit_note'], true),
            422,
            'Le document doit être une facture ou un avoir finalisé pour l\'export Factur-X.'
        );

        $xml = $this->facturX->generateXml($document);

        return response()->json(['xml' => $xml]);
    }
}

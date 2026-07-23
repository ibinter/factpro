<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Gestion des signatures électroniques sur documents.
 *
 * - __invoke : diffusion sécurisée du PNG de signature (§22.1)
 * - show     : page signature tablette (Module D2)
 * - store    : enregistrement signature base64 depuis tablette
 * - destroy  : suppression signature tablette
 */
class SignatureController extends Controller
{
    /** Diffusion sécurisée du PNG (usage interne — §22.1) */
    public function __invoke(Request $request, Document $document): StreamedResponse
    {
        abort_unless($document->company_id === $request->user()->current_company_id, 404);
        abort_unless($document->signature_path !== null, 404);

        $disk = Storage::disk(config('factpro.proofs.disk'));

        abort_unless($disk->exists($document->signature_path), 404);

        return $disk->response($document->signature_path, 'signature-'.$document->number.'.png', [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'inline; filename="signature-'.$document->number.'.png"',
        ]);
    }

    /** Page de signature (mode tablette) — Module D2 */
    public function show(Request $request, Document $document): Response
    {
        $company = $request->user()->currentCompany;
        if ($document->company_id !== $company->id) abort(403);

        return Inertia::render('Documents/SignaturePad', [
            'document' => $document->load('customer'),
            'company'  => $company->only(['name','logo']),
        ]);
    }

    /** Sauvegarder la signature (PNG base64) — Module D2 */
    public function store(Request $request, Document $document)
    {
        $company = $request->user()->currentCompany;
        if ($document->company_id !== $company->id) abort(403);

        $request->validate([
            'signature' => 'required|string', // base64 PNG
            'signed_by' => 'nullable|string|max:255',
        ]);

        // Décoder et sauvegarder
        $b64 = preg_replace('/^data:image\/\w+;base64,/', '', $request->signature);
        $path = "signatures/{$company->id}/doc_{$document->id}_" . time() . ".png";
        Storage::disk('local')->put($path, base64_decode($b64));

        // Stocker dans meta du document
        $meta = $document->meta ?? [];
        $meta['client_signature'] = [
            'path'      => $path,
            'signed_by' => $request->signed_by ?? $document->customer?->name,
            'signed_at' => now()->toISOString(),
            'ip'        => $request->ip(),
        ];
        $document->update(['meta' => $meta]);

        return back()->with('success', 'Signature enregistrée.');
    }

    /** Supprimer la signature tablette — Module D2 */
    public function destroy(Request $request, Document $document)
    {
        $company = $request->user()->currentCompany;
        if ($document->company_id !== $company->id) abort(403);

        $meta = $document->meta ?? [];
        if (!empty($meta['client_signature']['path'])) {
            Storage::disk('local')->delete($meta['client_signature']['path']);
        }
        unset($meta['client_signature']);
        $document->update(['meta' => $meta]);

        return back()->with('success', 'Signature supprimée.');
    }
}

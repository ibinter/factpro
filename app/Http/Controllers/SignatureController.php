<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Diffusion sécurisée de la signature électronique d'un devis (cahier §22.1).
 * Stockage privé : le PNG n'est jamais exposé publiquement, seulement en flux
 * inline au personnel de la société propriétaire du document.
 */
class SignatureController extends Controller
{
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
}

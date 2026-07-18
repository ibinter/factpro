<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\DocumentService;
use App\Services\QrCodeService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Impression thermique (cahier IBIG §6) : rend un ticket de caisse HTML
 * autonome 58/80/110 mm, imprimable depuis le navigateur vers une
 * imprimante thermique ESC/POS via le driver Windows.
 */
class ThermalController extends Controller
{
    public function __invoke(Request $request, Document $document): View
    {
        abort_unless($document->company_id === $request->user()->current_company_id, 403);

        $width = (int) $request->query('width', 80);
        if (! in_array($width, [58, 80, 110], true)) {
            $width = 80;
        }

        $copies = (int) $request->query('copies', 1);
        $copies = max(1, min(3, $copies));

        $autoprint = $request->boolean('autoprint', true);

        // Le ticket imprimé doit porter le QR scellé : finalisation automatique.
        if (! $document->isFinalized()) {
            app(DocumentService::class)->finalize($document);
        }

        $document->load(['lines', 'customer', 'company', 'payments']);

        $viewName = $width === 110 ? 'thermal.ticket-110mm' : 'thermal.ticket';

        return view($viewName, [
            'document' => $document,
            'company' => $document->company,
            'qrDataUri' => app(QrCodeService::class)->forDocument($document, $width <= 58 ? 3 : 4),
            'width' => $width,
            'copies' => $copies,
            'autoprint' => $autoprint,
            'watermark' => $document->trial_watermark ? config('factpro.trial.watermark_text') : null,
        ]);
    }
}

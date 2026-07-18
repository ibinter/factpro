<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\DocumentService;
use App\Services\QrCodeService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * Étiquettes spéciales (Phase 15 — cahier IBIG §6.3) :
 *  - Sticker de livraison A6
 *  - Étiquette garantie 85×55 mm
 *  - Accès via page Vue SpecialLabels
 */
class SpecialLabelController extends Controller
{
    public function __construct(
        private readonly QrCodeService   $qr,
        private readonly DocumentService $documentService,
    ) {}

    /**
     * Page Vue — aperçu et génération des étiquettes spéciales.
     */
    public function index(Request $request, Document $document): Response
    {
        abort_unless($document->company_id === $request->user()->current_company_id, 403);

        $document->load(['lines', 'customer', 'company']);

        $items = $document->lines->map(fn ($l) => [
            'index'       => $l->getKey(),
            'description' => $l->description,
            'quantity'    => $l->quantity,
            'unit_price'  => $l->unit_price,
        ])->values();

        return Inertia::render('Labels/SpecialLabels', [
            'document'         => $document->only(['id', 'number', 'type_label', 'issue_date', 'currency']),
            'customer'         => $document->customer?->only(['name', 'phone', 'email', 'address', 'city']),
            'items'            => $items,
            'deliveryStickerUrl' => route('documents.delivery-sticker', $document),
            'warrantyBaseUrl'    => route('documents.warranty-label', [$document, 0]),
            'thermal110Url'      => route('documents.thermal', $document).'?width=110',
        ]);
    }

    /**
     * Génère le sticker de livraison (PDF A6) pour un bon de livraison / facture.
     */
    public function deliverySticker(Request $request, Document $document): SymfonyResponse
    {
        abort_unless($document->company_id === $request->user()->current_company_id, 403);

        if (! $document->isFinalized()) {
            $this->documentService->finalize($document);
        }

        $document->load(['customer', 'company']);

        $pdf = Pdf::loadView('labels.delivery-sticker', [
            'document'   => $document,
            'company'    => $document->company,
            'customer'   => $document->customer,
            'qrDataUri'  => $this->qr->forDocument($document, 3),
        ])->setPaper([0, 0, 419.53, 297.64], 'landscape'); // A6 en points

        return $pdf->download('sticker-livraison-'.$document->number.'.pdf');
    }

    /**
     * Génère l'étiquette de garantie pour un article précis du document.
     */
    public function warrantyLabel(Request $request, Document $document, int $itemIndex): SymfonyResponse
    {
        abort_unless($document->company_id === $request->user()->current_company_id, 403);

        if (! $document->isFinalized()) {
            $this->documentService->finalize($document);
        }

        $document->load(['lines', 'customer', 'company']);

        $line = $document->lines->values()->get($itemIndex);
        abort_if($line === null, 404, 'Article introuvable.');

        $warrantyYears = (int) $request->query('years', 2);
        $warrantyYears = max(1, min(10, $warrantyYears));

        $purchaseDate = $document->issue_date ?? now();
        $warrantyEnd  = $purchaseDate->copy()->addYears($warrantyYears);

        // N° de série : on regarde si la description contient "SN:" ou "S/N:"
        $serialNumber = null;
        if (preg_match('/(?:S\/N|SN)\s*[:\-]?\s*([A-Za-z0-9\-]+)/i', $line->description, $m)) {
            $serialNumber = $m[1];
        }

        $pdf = Pdf::loadView('labels.warranty-label', [
            'document'      => $document,
            'company'       => $document->company,
            'productName'   => $line->description,
            'serialNumber'  => $serialNumber,
            'purchaseDate'  => $purchaseDate,
            'warrantyEnd'   => $warrantyEnd,
            'warrantyYears' => $warrantyYears,
            'qrDataUri'     => $this->qr->forDocument($document, 3),
        ])->setPaper([0, 0, 241.89, 155.91]); // 85×55 mm en points

        return $pdf->download('garantie-'.$document->number.'-item'.$itemIndex.'.pdf');
    }
}

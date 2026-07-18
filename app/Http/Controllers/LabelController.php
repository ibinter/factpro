<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\BarcodeService;
use App\Services\LicenseService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * Étiquettes & codes-barres (cahier §6.2) — impression en masse sur
 * planches Avery ou format personnalisé. Réservé BUSINESS/ENTERPRISE (§22.1).
 */
class LabelController extends Controller
{
    /** Plans autorisés à imprimer des étiquettes. */
    private const ALLOWED_PLANS = ['business', 'enterprise'];

    /**
     * Formats de planche pré-configurés (dimensions en mm, page A4 210×297).
     */
    public const FORMATS = [
        'avery-l7160' => [
            'label' => 'Avery L7160',
            'description' => '63,5 × 38,1 mm — 21 étiquettes/page (3 × 7)',
            'width_mm' => 63.5,
            'height_mm' => 38.1,
            'cols' => 3,
            'rows' => 7,
            'margin_top_mm' => 15.1,
            'margin_left_mm' => 7.2,
            'gutter_h_mm' => 2.5,
            'gutter_v_mm' => 0,
        ],
        'avery-l7163' => [
            'label' => 'Avery L7163',
            'description' => '99,1 × 38,1 mm — 14 étiquettes/page (2 × 7)',
            'width_mm' => 99.1,
            'height_mm' => 38.1,
            'cols' => 2,
            'rows' => 7,
            'margin_top_mm' => 15.1,
            'margin_left_mm' => 4.7,
            'gutter_h_mm' => 2.5,
            'gutter_v_mm' => 0,
        ],
        'avery-l7165' => [
            'label' => 'Avery L7165',
            'description' => '99,1 × 67,7 mm — 8 étiquettes/page (2 × 4)',
            'width_mm' => 99.1,
            'height_mm' => 67.7,
            'cols' => 2,
            'rows' => 4,
            'margin_top_mm' => 13.1,
            'margin_left_mm' => 4.7,
            'gutter_h_mm' => 2.5,
            'gutter_v_mm' => 0,
        ],
    ];

    public function __construct(
        private LicenseService $licenses,
        private BarcodeService $barcodes,
    ) {
    }

    /** Le forfait courant donne-t-il accès aux étiquettes ? */
    private function hasAccess(Request $request): bool
    {
        $license = $this->licenses->currentFor($request->user());

        return $license !== null
            && in_array($license->plan?->code, self::ALLOWED_PLANS, true);
    }

    /** Page de composition des étiquettes (ou upsell si forfait insuffisant). */
    public function index(Request $request): Response
    {
        $hasAccess = $this->hasAccess($request);

        $products = [];
        if ($hasAccess) {
            $products = Product::where('company_id', $request->user()->current_company_id)
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'sku', 'barcode', 'price', 'unit', 'stock_quantity', 'track_stock']);
        }

        return Inertia::render('Labels/Index', [
            'hasAccess' => $hasAccess,
            'products' => $products,
            'formats' => collect(self::FORMATS)->map(fn ($f, $key) => [
                'key' => $key,
                'label' => $f['label'],
                'description' => $f['description'],
                'per_page' => $f['cols'] * $f['rows'],
            ])->values(),
            'currency' => $request->user()->currentCompany?->currency ?? 'XOF',
        ]);
    }

    /** Génère le PDF d'étiquettes (A4 portrait) et le streame. */
    public function pdf(Request $request): SymfonyResponse
    {
        abort_unless(
            $this->hasAccess($request),
            403,
            'Les étiquettes sont réservées aux forfaits BUSINESS et ENTERPRISE.'
        );

        $data = $request->validate([
            'format' => ['required', 'in:avery-l7160,avery-l7163,avery-l7165,custom'],
            'width_mm' => ['required_if:format,custom', 'nullable', 'numeric', 'min:20', 'max:210'],
            'height_mm' => ['required_if:format,custom', 'nullable', 'numeric', 'min:15', 'max:297'],
            'cols' => ['required_if:format,custom', 'nullable', 'integer', 'min:1', 'max:6'],
            'rows' => ['required_if:format,custom', 'nullable', 'integer', 'min:1', 'max:15'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => [
                'required',
                'integer',
                \Illuminate\Validation\Rule::exists('products', 'id')
                    ->where('company_id', $request->user()->current_company_id),
            ],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:500'],
            'show_name' => ['sometimes', 'boolean'],
            'show_price' => ['sometimes', 'boolean'],
            'show_barcode' => ['sometimes', 'boolean'],
            'show_qr' => ['sometimes', 'boolean'],
            'show_sku' => ['sometimes', 'boolean'],
            'guides' => ['sometimes', 'boolean'],
        ]);

        // Géométrie de la planche.
        if ($data['format'] === 'custom') {
            $width = (float) $data['width_mm'];
            $height = (float) $data['height_mm'];
            $cols = (int) $data['cols'];
            $rows = (int) $data['rows'];
            $format = [
                'label' => 'Personnalisé',
                'width_mm' => $width,
                'height_mm' => $height,
                'cols' => $cols,
                'rows' => $rows,
                // Centrage de la grille sur la page A4.
                'margin_top_mm' => max(0, (297 - $rows * $height) / 2),
                'margin_left_mm' => max(0, (210 - $cols * $width) / 2),
                'gutter_h_mm' => 0,
                'gutter_v_mm' => 0,
            ];
        } else {
            $format = self::FORMATS[$data['format']];
        }

        $options = [
            'show_name' => (bool) ($data['show_name'] ?? true),
            'show_price' => (bool) ($data['show_price'] ?? true),
            'show_barcode' => (bool) ($data['show_barcode'] ?? true),
            'show_qr' => (bool) ($data['show_qr'] ?? false),
            'show_sku' => (bool) ($data['show_sku'] ?? true),
            'guides' => (bool) ($data['guides'] ?? false),
        ];

        // Charge les produits concernés (déjà validés comme appartenant à la société).
        $productIds = collect($data['items'])->pluck('product_id')->unique();
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        $currency = $request->user()->currentCompany?->currency ?? 'XOF';

        // Génère une seule fois les images par produit unique.
        $rendered = [];
        foreach ($products as $product) {
            $code = $product->barcode ?: ($product->sku ?: 'FP'.$product->id);
            $rendered[$product->id] = [
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => number_format((float) $product->price, 0, ',', ' ').' '.$currency,
                'barcode' => $options['show_barcode'] ? $this->barcodes->barcodePngDataUri((string) $code) : '',
                'barcode_text' => (string) $code,
                'qr' => $options['show_qr']
                    ? $this->barcodes->qrPngDataUri('FACTPRO|'.($product->sku ?: 'FP'.$product->id).'|'.(float) $product->price)
                    : '',
            ];
        }

        // Liste à plat : chaque produit répété quantity fois, ordre préservé.
        $labels = [];
        foreach ($data['items'] as $item) {
            $label = $rendered[$item['product_id']] ?? null;
            if ($label === null) {
                continue;
            }
            for ($i = 0; $i < (int) $item['quantity']; $i++) {
                $labels[] = $label;
            }
        }

        // Découpe en pages de cols × rows.
        $perPage = max(1, $format['cols'] * $format['rows']);
        $pages = array_chunk($labels, $perPage);

        $pdf = Pdf::loadView('pdf.labels', [
            'pages' => $pages,
            'format' => $format,
            'options' => $options,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('etiquettes.pdf');
    }
}

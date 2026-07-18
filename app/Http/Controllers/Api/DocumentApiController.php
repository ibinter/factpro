<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DocumentResource;
use App\Models\Document;
use App\Services\DocumentService;
use App\Services\LicenseService;
use App\Services\QrCodeService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class DocumentApiController extends Controller
{
    public function __construct(
        private DocumentService $documents,
        private LicenseService $licenses,
    ) {
    }

    /** GET /api/v1/documents — liste paginée (?type=, ?status=, ?search=, ?per_page=). */
    public function index(Request $request): AnonymousResourceCollection
    {
        $documents = Document::where('company_id', $request->user()->current_company_id)
            ->when($request->type, fn ($q, $t) => $q->where('type', $t))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->search, fn ($q, $s) => $q->where('number', 'like', "%{$s}%"))
            ->with(['customer', 'lines'])
            ->orderByDesc('issue_date')
            ->orderByDesc('id')
            ->paginate(min((int) $request->integer('per_page', 15) ?: 15, 100))
            ->withQueryString();

        return DocumentResource::collection($documents);
    }

    /** POST /api/v1/documents — crée un document (finalize: true pour sceller). */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        $company = $user->currentCompany;

        // Limite documents/mois du forfait (cahier §22.1)
        $countThisMonth = Document::where('company_id', $company->id)
            ->where('created_at', '>=', now()->startOfMonth())->count();
        if ($this->licenses->limitReached($user, 'documents_per_month', $countThisMonth)) {
            return response()->json([
                'message' => 'Limite de documents mensuelle atteinte pour votre forfait.',
            ], 422);
        }

        [$data, $lines, $finalize] = $this->validateData($request);

        $document = $this->documents->create($company, $user, $data, $lines);

        if ($finalize) {
            $this->documents->finalize($document);

            if ($document->status === 'draft') {
                $document->update(['status' => 'sent', 'sent_at' => now()]);
            }

            $document = $document->fresh(['lines', 'customer']);
        }

        return (new DocumentResource($document))->response()->setStatusCode(201);
    }

    /** GET /api/v1/documents/{uuid} */
    public function show(Request $request, string $uuid): DocumentResource
    {
        $document = $this->find($request, $uuid);
        $document->load(['lines', 'customer']);

        return new DocumentResource($document);
    }

    /**
     * GET /api/v1/documents/{uuid}/pdf — PDF binaire (même génération que le
     * web : template config/pdf_templates.php + QR d'authenticité + filigrane).
     */
    public function pdf(Request $request, string $uuid, QrCodeService $qr)
    {
        $document = $this->find($request, $uuid);
        $document->load(['lines', 'customer', 'company']);

        // Sceller automatiquement à la première génération PDF (comme le web)
        if (! $document->isFinalized()) {
            $this->documents->finalize($document);
        }

        $pdf = Pdf::loadView($this->resolveTemplateView($document), [
            'document' => $document,
            'company' => $document->company,
            'qrDataUri' => $qr->forDocument($document),
            'watermark' => $document->trial_watermark ? config('factpro.trial.watermark_text') : null,
        ])->setPaper('a4');

        return $pdf->download($document->number.'.pdf');
    }

    /** Scope société courante — 404 hors périmètre. */
    private function find(Request $request, string $uuid): Document
    {
        return Document::where('company_id', $request->user()->current_company_id)
            ->where('uuid', $uuid)
            ->firstOrFail();
    }

    /**
     * Résout la vue Blade du modèle visuel (cahier §16) : template du document,
     * sinon modèle par défaut de la société, sinon fallback sur pdf.document.
     */
    private function resolveTemplateView(Document $document): string
    {
        $key = $document->template_key ?: $document->company->default_template;

        if ($key && config("pdf_templates.{$key}") && view()->exists("pdf.templates.{$key}")) {
            return "pdf.templates.{$key}";
        }

        return 'pdf.document';
    }

    /**
     * Validation stricte — mêmes règles que DocumentController::validateData.
     *
     * @return array{0: array, 1: array, 2: bool}
     */
    private function validateData(Request $request): array
    {
        $companyId = $request->user()->current_company_id;

        $data = $request->validate([
            'type' => 'required|in:'.implode(',', array_keys(Document::TYPES)),
            'customer_id' => [
                'nullable',
                Rule::exists('customers', 'id')->where('company_id', $companyId),
            ],
            'reference' => 'nullable|string|max:255',
            'issue_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:issue_date',
            'currency' => 'required|string|size:3',
            'template_key' => [
                'nullable',
                'string',
                'max:40',
                Rule::in(array_keys($this->allowedTemplates($request))),
            ],
            'discount_type' => 'nullable|in:percent,fixed',
            'discount_value' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'finalize' => 'nullable|boolean',
            'lines' => 'required|array|min:1',
            'lines.*.product_id' => [
                'nullable',
                Rule::exists('products', 'id')->where('company_id', $companyId),
            ],
            'lines.*.description' => 'required|string',
            'lines.*.quantity' => 'required|numeric|min:0.01',
            'lines.*.unit' => 'nullable|string|max:20',
            'lines.*.unit_price' => 'required|numeric|min:0',
            'lines.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'lines.*.tax_rate' => 'nullable|numeric|min:0|max:100',
        ], [
            'template_key.in' => 'Ce modèle visuel n\'est pas disponible dans votre forfait actuel.',
        ]);

        $lines = $data['lines'];
        $finalize = (bool) ($data['finalize'] ?? false);
        unset($data['lines'], $data['finalize']);

        return [$data, $lines, $finalize];
    }

    /** Modèles visuels autorisés par le forfait (cf. DocumentController). */
    private function allowedTemplates(Request $request): array
    {
        $registry = config('pdf_templates', []);
        $limit = $this->licenses->currentFor($request->user())?->limit('templates');

        return $limit === null ? $registry : array_slice($registry, 0, $limit, true);
    }
}

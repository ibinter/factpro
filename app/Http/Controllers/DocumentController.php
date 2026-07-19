<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentAuditLog;
use App\Models\PaymentPlan;
use App\Models\User;
use App\Notifications\DocumentFinalized;
use App\Notifications\PaymentReceived;
use App\Services\CacheService;
use App\Services\DocumentService;
use App\Services\LoyaltyService;
use App\Services\LicenseService;
use App\Services\OutgoingWebhookService;
use App\Services\PaymentPlanService;
use App\Services\QrCodeService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class DocumentController extends Controller
{
    public function __construct(
        private DocumentService $documents,
        private LicenseService $licenses,
    ) {
    }

    public function index(Request $request): Response
    {
        $company = $request->user()->currentCompany;
        $period  = $request->get('period', '30');

        $base = Document::where('company_id', $company->id);
        $inv  = (clone $base)->where('type', 'invoice');

        // Stats globales (indépendantes des filtres de recherche)
        $since30   = now()->subDays(30);
        $monthStart = now()->startOfMonth();

        $stats = [
            'ca_month'    => (float) (clone $inv)->where('issue_date', '>=', $monthStart)
                ->whereNotIn('status', ['draft', 'cancelled'])->sum('total'),
            'outstanding' => (float) (clone $inv)->whereIn('status', ['sent', 'viewed', 'partial', 'overdue'])
                ->selectRaw('COALESCE(SUM(total - amount_paid), 0) as due')->value('due'),
            'overdue'     => (clone $inv)->where('status', 'overdue')
                ->orWhere(fn ($q) => $q->whereIn('status', ['sent','viewed','partial'])->where('due_date', '<', now()))
                ->where('company_id', $company->id)->count(),
            'drafts'      => (clone $base)->where('status', 'draft')->count(),
            'total_30d'   => (clone $base)->where('issue_date', '>=', $since30)->count(),
        ];

        // Filtre période
        $periodMap = ['30' => 30, '90' => 90, '180' => 180, '365' => 365];
        $days = $periodMap[$period] ?? null;

        $documents = (clone $base)
            ->when($request->type, fn ($q, $t) => $q->where('type', $t))
            ->when($request->category, function ($q, $cat) {
                $typesInCat = collect(Document::TYPES)->filter(fn ($t) => ($t['category'] ?? '') === $cat)->keys()->all();
                $q->whereIn('type', $typesInCat);
            })
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($days, fn ($q) => $q->where('issue_date', '>=', now()->subDays($days)))
            ->when($request->search, fn ($q, $s) => $q->where(fn ($q) => $q
                ->where('number', 'like', "%{$s}%")
                ->orWhereHas('customer', fn ($q) => $q->where('name', 'like', "%{$s}%"))))
            ->with('customer:id,name')
            ->select(['id','type','number','status','customer_id','issue_date','due_date','total','amount_paid','currency','finalized_at'])
            ->orderByDesc('issue_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Documents/Index', [
            'documents'  => $documents,
            'filters'    => $request->only('type', 'category', 'status', 'search', 'period'),
            'types'      => $this->typesForFront(),
            'categories' => Document::CATEGORIES,
            'stats'      => $stats,
        ]);
    }

    public function create(Request $request): Response
    {
        $company = $request->user()->currentCompany;

        return Inertia::render('Documents/Form', [
            'documentType'    => $request->query('type', 'invoice'),
            'customers'       => $company->customers()->orderBy('name')->get(['id', 'name', 'email', 'currency']),
            'products'        => $company->products()->where('is_active', true)->orderBy('name')
                ->get(['id', 'name', 'description', 'unit', 'price', 'tax_rate']),
            'defaults'        => [
                'currency' => $company->currency,
                'tax_rate' => (float) $company->default_tax_rate,
            ],
            'types'           => $this->typesForFront(),
            'categories'      => Document::CATEGORIES,
            'templates'       => $this->templatesForFront($request->user()),
            'defaultTemplate' => $company->default_template,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $company = $user->currentCompany;

        // Limite documents/mois du forfait (cahier §22.1)
        $countThisMonth = Document::where('company_id', $company->id)
            ->where('created_at', '>=', now()->startOfMonth())->count();
        if ($this->licenses->limitReached($user, 'documents_per_month', $countThisMonth)) {
            return back()->with('error', 'Limite de documents mensuelle atteinte pour votre forfait.');
        }

        [$data, $lines] = $this->validateData($request);

        $document = $this->documents->create($company, $user, $data, $lines);

        CacheService::forgetCompany($company->id);
        DocumentAuditLog::record($document, 'created', $user);

        app(OutgoingWebhookService::class)->dispatch($company, 'document.created', [
            'event' => 'document.created',
            'document_id' => $document->id,
            'type' => $document->type,
            'number' => $document->number,
        ]);

        return redirect()->route('documents.show', $document)
            ->with('success', $document->type_label.' '.$document->number.' créé.');
    }

    public function show(Request $request, Document $document): Response
    {
        $this->authorizeDocument($request, $document);

        $document->load(['lines', 'customer', 'payments', 'parent:id,type,number', 'children:id,parent_id,type,number']);

        // Plan de paiement / acomptes (cahier §12) lié à ce document source
        $paymentPlan = PaymentPlan::where('source_document_id', $document->id)
            ->with(['installments.document:id,type,number,status', 'customer:id,name'])
            ->latest('id')
            ->first();

        $canCreatePlan = $this->hasPaymentPlanAccess($request)
            && in_array($document->type, ['quote', 'invoice'], true)
            && $paymentPlan === null;

        $license = $this->licenses->currentFor($request->user());
        $planCode = $license?->plan?->code;

        return Inertia::render('Documents/Show', [
            'document' => $document,
            'typeLabel' => $document->type_label,
            'verificationUrl' => $document->verificationUrl(),
            'convertTargets' => $this->convertTargets($document),
            'paymentPlan' => $paymentPlan ? $this->presentPaymentPlan($paymentPlan) : null,
            'canCreatePlan' => $canCreatePlan,
            'canFacturX' => ($document->isFinalized() || in_array($document->status, ['paid'], true))
                && in_array($document->type, ['invoice', 'credit_note'], true)
                && $planCode === 'enterprise',
            'hasApprovalAccess' => $license !== null && $license->isUsable(),
        ]);
    }

    /** Le forfait courant donne-t-il accès aux plans de paiement / acomptes ? (PRO et plus) */
    private function hasPaymentPlanAccess(Request $request): bool
    {
        $license = $this->licenses->currentFor($request->user());

        return $license !== null
            && $license->isUsable()
            && $license->plan?->code !== 'starter';
    }

    /** Formate le plan de paiement pour la fiche document. */
    private function presentPaymentPlan(PaymentPlan $plan): array
    {
        return [
            'id' => $plan->id,
            'name' => $plan->name,
            'status' => $plan->status,
            'total_amount' => (float) $plan->total_amount,
            'total_invoiced' => $plan->total_invoiced,
            'remaining' => $plan->remaining,
            'currency' => $plan->currency,
            'installments' => $plan->installments->map(fn ($i) => [
                'id' => $i->id,
                'label' => $i->label,
                'due_date' => $i->due_date?->toDateString(),
                'amount' => (float) $i->amount,
                'percentage' => $i->percentage !== null ? (float) $i->percentage : null,
                'status' => $i->status,
                'document_id' => $i->document_id,
                'document' => $i->document,
            ])->values(),
        ];
    }

    /**
     * Crée un plan de paiement (acomptes échelonnés) depuis un devis/facture.
     * Réservé PRO+ (cahier §12 / §22.1).
     */
    public function createPaymentPlan(Request $request, Document $document, PaymentPlanService $plans): RedirectResponse
    {
        $this->authorizeDocument($request, $document);

        abort_unless(
            $this->hasPaymentPlanAccess($request),
            403,
            'Les plans de paiement (acomptes) sont disponibles dès le forfait PRO.'
        );

        $data = $request->validate([
            'installments' => 'required|array|min:1',
            'installments.*.label' => 'required|string|max:255',
            'installments.*.due_date' => 'required|date',
            'installments.*.amount' => 'nullable|numeric|min:0',
            'installments.*.percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        try {
            $plans->createFromDocument($document, $data['installments'], $request->user());
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Plan de paiement créé.');
    }

    public function edit(Request $request, Document $document): Response
    {
        $this->authorizeDocument($request, $document);

        if ($document->isFinalized()) {
            return $this->show($request, $document);
        }

        $company = $request->user()->currentCompany;
        $document->load(['lines', 'customer']);

        return Inertia::render('Documents/Form', [
            'document'        => $document,
            'documentType'    => $document->type,
            'customers'       => $company->customers()->orderBy('name')->get(['id', 'name', 'email', 'currency']),
            'products'        => $company->products()->where('is_active', true)->orderBy('name')
                ->get(['id', 'name', 'description', 'unit', 'price', 'tax_rate']),
            'defaults'        => [
                'currency' => $company->currency,
                'tax_rate' => (float) $company->default_tax_rate,
            ],
            'types'           => $this->typesForFront(),
            'categories'      => Document::CATEGORIES,
            'templates'       => $this->templatesForFront($request->user()),
            'defaultTemplate' => $company->default_template,
        ]);
    }

    public function update(Request $request, Document $document): RedirectResponse
    {
        $this->authorizeDocument($request, $document);

        if ($document->isFinalized()) {
            return back()->with('error', 'Document finalisé : modification impossible (intégrité protégée).');
        }

        [$data, $lines] = $this->validateData($request);
        unset($data['type']); // le type ne change pas après création

        $this->documents->update($document, $data, $lines);

        CacheService::forgetCompany($document->company_id);
        DocumentAuditLog::record($document, 'updated', $request->user());

        return redirect()->route('documents.show', $document)->with('success', 'Document mis à jour.');
    }

    public function destroy(Request $request, Document $document): RedirectResponse
    {
        $this->authorizeDocument($request, $document);

        if ($document->isFinalized()) {
            return back()->with('error', 'Document finalisé : suppression impossible (archivage légal).');
        }

        $companyId = $document->company_id;
        $document->delete();

        CacheService::forgetCompany($companyId);

        return redirect()->route('documents.index')->with('success', 'Document supprimé.');
    }

    /** Finalise (scelle) le document : hash SHA-256 + horodatage — il devient infalsifiable. */
    public function finalize(Request $request, Document $document): RedirectResponse
    {
        $this->authorizeDocument($request, $document);

        $this->documents->finalize($document);

        DocumentAuditLog::record($document, 'finalized', $request->user());

        if ($document->status === 'draft') {
            $document->update(['status' => 'sent', 'sent_at' => now()]);
        }

        app(OutgoingWebhookService::class)->dispatch($document->company, 'invoice.finalized', [
            'event' => 'invoice.finalized',
            'document_id' => $document->id,
            'type' => $document->type,
            'number' => $document->number,
        ]);

        $request->user()->notify(new DocumentFinalized($document));

        // Phase 14 — Archivage légal immuable
        try {
            app(\App\Services\ArchiveService::class)->archive($document);
        } catch (\Throwable) {
            // L'échec de l'archivage ne bloque pas la finalisation
        }

        return back()->with('success', 'Document finalisé et scellé (QR d\'authenticité actif).');
    }

    /** Conversion devis→facture, facture→avoir, etc. */
    public function convert(Request $request, Document $document): RedirectResponse
    {
        $this->authorizeDocument($request, $document);

        $request->validate(['target_type' => 'required|in:'.implode(',', array_keys(Document::TYPES))]);

        $new = $this->documents->convert($document, $request->target_type, $request->user());

        DocumentAuditLog::record($document, 'converted', $request->user(), ['to_type' => $request->target_type, 'new_document_id' => $new->id]);
        DocumentAuditLog::record($new, 'created', $request->user(), ['from_document_id' => $document->id]);

        return redirect()->route('documents.show', $new)
            ->with('success', $new->type_label.' '.$new->number.' créé à partir de '.$document->number.'.');
    }

    /** Enregistre un paiement sur le document. */
    public function registerPayment(Request $request, Document $document): RedirectResponse
    {
        $this->authorizeDocument($request, $document);

        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|in:cash,mobile_money,card,bank_transfer,cheque,credit',
            'reference' => 'nullable|string|max:100',
            'paid_at' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $data['currency'] = $document->currency;
        $this->documents->registerPayment($document, $data, $request->user());

        DocumentAuditLog::record($document, 'payment_registered', $request->user(), ['amount' => $data['amount'], 'method' => $data['method']]);

        app(OutgoingWebhookService::class)->dispatch($document->company, 'invoice.payment_received', [
            'event' => 'invoice.payment_received',
            'document_id' => $document->id,
            'number' => $document->number,
            'amount' => $data['amount'],
        ]);

        $request->user()->notify(new PaymentReceived($document, (float) $data['amount']));

        app(LoyaltyService::class)->awardPoints($document, (float) $data['amount']);

        return back()->with('success', 'Paiement enregistré.');
    }

    /** Télécharge le PDF (avec QR anti-falsification + filigrane essai le cas échéant). */
    public function pdf(Request $request, Document $document, QrCodeService $qr)
    {
        $this->authorizeDocument($request, $document);

        $document->load(['lines', 'customer', 'company']);

        // Sceller automatiquement à la première génération PDF
        if (! $document->isFinalized()) {
            $this->documents->finalize($document);
        }

        $pdf = Pdf::loadView($this->resolveTemplateView($document), [
            'document' => $document,
            'company' => $document->company,
            'qrDataUri' => $qr->forDocument($document),
            'watermark' => $document->trial_watermark ? config('factpro.trial.watermark_text') : null,
        ])->setPaper('a4');

        return $pdf->stream($document->number.'.pdf');
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
     * Modèles visuels autorisés par le forfait : la limite « templates » de la
     * licence (null = illimité) tronque le registre ordonné config/pdf_templates.php.
     *
     * @return array<string, array>
     */
    private function allowedTemplates(User $user): array
    {
        $registry = config('pdf_templates', []);
        $limit = $this->licenses->currentFor($user)?->limit('templates');

        return $limit === null ? $registry : array_slice($registry, 0, $limit, true);
    }

    /** Liste des modèles autorisés, formatée pour le sélecteur de l'éditeur. */
    private function templatesForFront(User $user): array
    {
        return collect($this->allowedTemplates($user))
            ->map(fn ($t, $key) => [
                'key' => $key,
                'name' => $t['name'],
                'family' => $t['family'],
                'description' => $t['description'] ?? '',
                'primary' => $t['primary'],
                'secondary' => $t['secondary'],
                'accent' => $t['accent'],
            ])
            ->values()
            ->all();
    }

    /** Duplique un document en brouillon avec les mêmes lignes. */
    public function clone(Request $request, Document $document): RedirectResponse
    {
        $this->authorizeDocument($request, $document);

        $user = $request->user();
        $company = $user->currentCompany;

        $document->load('lines');

        $data = [
            'type' => $document->type,
            'customer_id' => $document->customer_id,
            'reference' => $document->reference,
            'issue_date' => now()->toDateString(),
            'due_date' => $document->due_date?->toDateString(),
            'currency' => $document->currency,
            'discount_type' => $document->discount_type,
            'discount_value' => $document->discount_value,
            'notes' => $document->notes,
            'terms' => $document->terms,
            'template_key' => $document->template_key,
        ];

        $lines = $document->lines->map(fn ($l) => [
            'product_id' => $l->product_id,
            'description' => $l->description,
            'quantity' => $l->quantity,
            'unit' => $l->unit,
            'unit_price' => $l->unit_price,
            'discount_percent' => $l->discount_percent,
            'tax_rate' => $l->tax_rate,
        ])->all();

        $newDocument = $this->documents->create($company, $user, $data, $lines);

        return redirect()->route('documents.edit', $newDocument)
            ->with('success', 'Document dupliqué.');
    }

    private function authorizeDocument(Request $request, Document $document): void
    {
        abort_unless($document->company_id === $request->user()->current_company_id, 403);
    }

    private function convertTargets(Document $document): array
    {
        $map = [
            'quote' => ['invoice', 'proforma', 'sales_order', 'deposit_invoice'],
            'proforma' => ['invoice'],
            'sales_order' => ['delivery_note', 'invoice'],
            'delivery_note' => ['invoice'],
            'deposit_invoice' => ['balance_invoice'],
            'invoice' => ['credit_note', 'payment_receipt'],
        ];

        return collect($map[$document->type] ?? [])
            ->map(fn ($t) => ['value' => $t, 'label' => Document::TYPES[$t]['label']])
            ->values()
            ->all();
    }

    /** Télécharge le document au format Word (.docx). */
    public function docx(Request $request, Document $document)
    {
        $this->authorizeDocument($request, $document);
        $document->load(['lines', 'customer', 'company']);

        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(11);

        $section = $phpWord->addSection(['marginTop' => 1200, 'marginBottom' => 1200, 'marginLeft' => 1200, 'marginRight' => 1200]);

        // En-tête société
        $section->addText($document->company->name ?? '', ['bold' => true, 'size' => 16], ['spaceAfter' => 120]);
        if ($document->company->address) {
            $section->addText($document->company->address, ['size' => 10, 'color' => '666666'], ['spaceAfter' => 60]);
        }
        $section->addTextBreak(1);

        // Titre document
        $section->addText(strtoupper($document->type_label.' N° '.$document->number), ['bold' => true, 'size' => 14], ['spaceAfter' => 240]);

        // Infos principales
        $infoStyle = ['size' => 10];
        $section->addText('Date : '.$document->issue_date->format('d/m/Y'), $infoStyle);
        if ($document->due_date) {
            $section->addText('Échéance : '.$document->due_date->format('d/m/Y'), $infoStyle);
        }
        if ($document->customer) {
            $section->addText('Client : '.$document->customer->name, ['size' => 10, 'bold' => true]);
        }
        $section->addTextBreak(1);

        // Table des lignes
        if ($document->lines->isNotEmpty()) {
            $tableStyle = ['borderSize' => 6, 'borderColor' => 'cccccc', 'cellMargin' => 80];
            $phpWord->addTableStyle('docTable', $tableStyle);
            $table = $section->addTable('docTable');

            // En-tête
            $table->addRow(null, ['tblHeader' => true]);
            $headerStyle = ['bold' => true, 'size' => 10, 'color' => 'ffffff'];
            $cellBg = ['bgColor' => '1e3a5f'];
            foreach (['Description', 'Qté', 'P.U. HT', 'Total HT'] as $col) {
                $cell = $table->addCell(null, $cellBg);
                $cell->addText($col, $headerStyle, ['alignment' => 'center']);
            }

            foreach ($document->lines as $line) {
                $table->addRow();
                $table->addCell(4000)->addText($line->description ?? '', ['size' => 10]);
                $table->addCell(1000)->addText(number_format($line->quantity, 2, ',', ' '), ['size' => 10], ['alignment' => 'right']);
                $table->addCell(1500)->addText(number_format($line->unit_price, 0, ',', ' '), ['size' => 10], ['alignment' => 'right']);
                $lineTotal = $line->quantity * $line->unit_price * (1 - ($line->discount_percent ?? 0) / 100);
                $table->addCell(1500)->addText(number_format($lineTotal, 0, ',', ' '), ['size' => 10], ['alignment' => 'right']);
            }
        }

        $section->addTextBreak(1);

        // Totaux
        $fmt = fn ($n) => number_format((float) $n, 0, ',', ' ').' '.$document->currency;
        $section->addText('Sous-total HT : '.$fmt($document->subtotal), ['size' => 11], ['alignment' => 'right']);
        if ($document->discount_amount > 0) {
            $section->addText('Remise : −'.$fmt($document->discount_amount), ['size' => 11, 'color' => 'cc0000'], ['alignment' => 'right']);
        }
        if ($document->tax_amount > 0) {
            $section->addText('TVA : '.$fmt($document->tax_amount), ['size' => 11], ['alignment' => 'right']);
        }
        $section->addText('TOTAL TTC : '.$fmt($document->total), ['size' => 13, 'bold' => true], ['alignment' => 'right', 'spaceAfter' => 240]);

        if ($document->notes) {
            $section->addText('Notes :', ['bold' => true, 'size' => 10]);
            $section->addText($document->notes, ['size' => 10, 'italic' => true]);
        }

        $filename = $document->number.'.docx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        header('Cache-Control: max-age=0');

        $writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save('php://output');
        exit;
    }

    /** Export Excel de la liste des documents (avec les filtres actifs). */
    public function exportExcel(Request $request)
    {
        $company = $request->user()->currentCompany;
        $base    = Document::where('company_id', $company->id);

        $periodMap = ['30' => 30, '90' => 90, '180' => 180, '365' => 365];
        $days = $periodMap[$request->get('period', '')] ?? null;

        $documents = (clone $base)
            ->when($request->type, fn ($q, $t) => $q->where('type', $t))
            ->when($request->category, function ($q, $cat) {
                $types = collect(Document::TYPES)->filter(fn ($t) => ($t['category'] ?? '') === $cat)->keys()->all();
                $q->whereIn('type', $types);
            })
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($days, fn ($q) => $q->where('issue_date', '>=', now()->subDays($days)))
            ->when($request->search, fn ($q, $s) => $q->where(fn ($q) => $q
                ->where('number', 'like', "%{$s}%")
                ->orWhereHas('customer', fn ($q) => $q->where('name', 'like', "%{$s}%"))))
            ->with('customer:id,name')
            ->orderByDesc('issue_date')
            ->get(['id','type','number','status','customer_id','issue_date','due_date','total','amount_paid','currency']);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Documents');

        $headers = ['Type','Numéro','Client','Date émission','Échéance','Total TTC','Payé','Solde','Devise','Statut'];
        $sheet->fromArray($headers, null, 'A1');

        // Style en-tête
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1e3a5f']],
        ];
        $sheet->getStyle('A1:J1')->applyFromArray($headerStyle);

        $row = 2;
        foreach ($documents as $doc) {
            $sheet->fromArray([
                Document::TYPES[$doc->type]['label'] ?? $doc->type,
                $doc->number,
                $doc->customer?->name ?? '—',
                $doc->issue_date ? $doc->issue_date->format('d/m/Y') : '',
                $doc->due_date ? $doc->due_date->format('d/m/Y') : '',
                (float) $doc->total,
                (float) $doc->amount_paid,
                round((float) $doc->total - (float) $doc->amount_paid, 2),
                $doc->currency,
                $doc->status,
            ], null, 'A'.$row);
            $row++;
        }

        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="documents-'.date('Y-m-d').'.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /** Liste enrichie des types pour le front (avec category). */
    private function typesForFront(): array
    {
        return collect(Document::TYPES)
            ->map(fn ($t, $k) => ['value' => $k, 'label' => $t['label'], 'category' => $t['category']])
            ->values()
            ->all();
    }

    /** @return array{0: array, 1: array} */
    private function validateData(Request $request): array
    {
        $companyId = $request->user()->current_company_id;

        $data = $request->validate([
            'type' => 'required|in:'.implode(',', array_keys(Document::TYPES)),
            'customer_id' => [
                'nullable',
                \Illuminate\Validation\Rule::exists('customers', 'id')->where('company_id', $companyId),
            ],
            'reference' => 'nullable|string|max:255',
            'issue_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:issue_date',
            'currency' => 'required|string|size:3',
            'template_key' => [
                'nullable',
                'string',
                'max:40',
                Rule::in(array_keys($this->allowedTemplates($request->user()))),
            ],
            'discount_type' => 'nullable|in:percent,fixed',
            'discount_value' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'meta' => 'nullable|array',
            'lines' => 'nullable|array',
            'lines.*.product_id' => [
                'nullable',
                \Illuminate\Validation\Rule::exists('products', 'id')->where('company_id', $companyId),
            ],
            'lines.*.description' => 'nullable|string',
            'lines.*.quantity' => 'nullable|numeric|min:0',
            'lines.*.unit' => 'nullable|string|max:20',
            'lines.*.unit_price' => 'nullable|numeric|min:0',
            'lines.*.discount_percent' => 'nullable|numeric|min:0',
            'lines.*.line_discount_type' => 'nullable|in:percent,fixed',
            'lines.*.tax_rate' => 'nullable|numeric|min:0|max:100',
        ], [
            'template_key.in' => 'Ce modèle visuel n\'est pas disponible dans votre forfait actuel.',
        ]);

        // Types sans lignes : injecter une ligne synthétique depuis meta
        $noLinesTypes = ['quittance', 'payment_receipt'];
        if (in_array($data['type'] ?? '', $noLinesTypes) || empty($data['lines'])) {
            $data['lines'] = $this->syntheticLines($data);
        }

        $lines = $data['lines'];
        unset($data['lines']);

        return [$data, $lines];
    }

    private function syntheticLines(array $data): array
    {
        $meta = $data['meta'] ?? [];
        $type = $data['type'] ?? '';

        if ($type === 'quittance') {
            $loyer   = (float) ($meta['rent_amount'] ?? 0);
            $charges = (float) ($meta['charges_amount'] ?? 0);
            $lines   = [];
            if ($loyer > 0) {
                $lines[] = ['description' => 'Loyer — ' . ($meta['rental_period'] ?? ''), 'quantity' => 1, 'unit_price' => $loyer, 'tax_rate' => 0, 'discount_percent' => 0, 'line_discount_type' => 'percent'];
            }
            if ($charges > 0) {
                $lines[] = ['description' => 'Charges locatives', 'quantity' => 1, 'unit_price' => $charges, 'tax_rate' => 0, 'discount_percent' => 0, 'line_discount_type' => 'percent'];
            }
            return $lines ?: [['description' => 'Quittance', 'quantity' => 1, 'unit_price' => 0, 'tax_rate' => 0, 'discount_percent' => 0, 'line_discount_type' => 'percent']];
        }

        if ($type === 'payment_receipt') {
            $amount = (float) ($meta['amount_received'] ?? 0);
            return [['description' => ($meta['payment_purpose'] ?? 'Règlement') . ($meta['document_reference'] ? ' — Réf. ' . $meta['document_reference'] : ''), 'quantity' => 1, 'unit_price' => $amount, 'tax_rate' => 0, 'discount_percent' => 0, 'line_discount_type' => 'percent']];
        }

        return [['description' => 'Article', 'quantity' => 1, 'unit_price' => 0, 'tax_rate' => 0, 'discount_percent' => 0, 'line_discount_type' => 'percent']];
    }
}

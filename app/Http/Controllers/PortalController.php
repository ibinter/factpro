<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Document;
use App\Models\PaymentAuditLog;
use App\Services\QrCodeService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Portail client self-service (cahier §11) : chaque client final accède à ses
 * documents via un lien privé à token — consultation, PDF, décision sur devis.
 * Aucun compte requis. Le token n'est jamais journalisé.
 */
class PortalController extends Controller
{
    /** Types de la famille « facture » pris en compte dans les stats du portail. */
    private const INVOICE_TYPES = ['invoice', 'deposit_invoice', 'balance_invoice'];

    /** Page d'accueil du portail : documents finalisés du client + stats. */
    public function show(string $token): Response
    {
        $customer = $this->resolveCustomer($token);
        $company = $customer->company;

        $documents = $customer->documents()
            ->whereNotNull('finalized_at')
            ->where('status', '!=', 'draft')
            ->orderByDesc('issue_date')
            ->orderByDesc('id')
            ->get();

        $invoices = $documents->whereIn('type', self::INVOICE_TYPES);

        return Inertia::render('Portal/Index', [
            'token' => $token,
            'company' => [
                'name' => $company->name,
                'logo_path' => $company->logo_path,
                'phone' => $company->phone,
                'email' => $company->email,
                'address' => $company->address,
                'city' => $company->city,
            ],
            'customer' => [
                'name' => $customer->name,
            ],
            'documents' => $documents->map(fn (Document $d) => [
                'uuid' => $d->uuid,
                'type' => $d->type,
                'type_label' => $d->type_label,
                'number' => $d->number,
                'issue_date' => $d->issue_date?->toDateString(),
                'due_date' => $d->due_date?->toDateString(),
                'status' => $d->status,
                'total' => (float) $d->total,
                'amount_paid' => (float) $d->amount_paid,
                'balance_due' => $d->balance_due,
                'currency' => $d->currency,
                'canDecide' => $d->type === 'quote' && in_array($d->status, ['sent', 'viewed'], true),
                'signed' => $d->signature_path !== null,
                'signed_by_name' => $d->signed_by_name,
                'signed_at' => $d->signed_at ? \Illuminate\Support\Carbon::parse($d->signed_at)->format('d/m/Y H:i') : null,
            ])->values(),
            'stats' => [
                'invoiced' => round((float) $invoices->sum('total'), 2),
                'paid' => round((float) $invoices->sum('amount_paid'), 2),
                'due' => round((float) $invoices->sum('balance_due'), 2),
                'currency' => $documents->first()->currency ?? $customer->currency ?? $company->currency,
            ],
        ]);
    }

    /** Télécharge le PDF d'un document du client (même rendu que côté société). */
    public function pdf(string $token, Document $document, QrCodeService $qr)
    {
        $customer = $this->resolveCustomer($token);

        abort_unless($document->customer_id === $customer->id, 403);
        abort_unless($document->isFinalized(), 404);

        // Un devis envoyé consulté par le client passe en « vu »
        if ($document->type === 'quote' && $document->status === 'sent') {
            $document->update(['status' => 'viewed']);
        }

        $document->load(['lines', 'customer', 'company']);

        $pdf = Pdf::loadView($this->resolveTemplateView($document), [
            'document' => $document,
            'company' => $document->company,
            'qrDataUri' => $qr->forDocument($document),
            'watermark' => $document->trial_watermark ? config('factpro.trial.watermark_text') : null,
        ])->setPaper('a4');

        return $pdf->stream($document->number.'.pdf');
    }

    /** Le client accepte ou refuse un devis (avec commentaire optionnel). */
    public function decision(Request $request, string $token, Document $document): RedirectResponse
    {
        $customer = $this->resolveCustomer($token);

        abort_unless($document->customer_id === $customer->id, 403);
        abort_unless($document->type === 'quote', 403);
        abort_unless(in_array($document->status, ['sent', 'viewed'], true), 403);

        $data = $request->validate([
            'decision' => 'required|in:accept,reject',
            'comment' => 'nullable|string|max:500',
            'signature' => 'nullable|string',
            'signer_name' => 'nullable|string|max:100|required_with:signature',
        ]);

        $oldStatus = $document->status;
        $newStatus = $data['decision'] === 'accept' ? 'accepted' : 'rejected';

        $document->update(['status' => $newStatus]);

        PaymentAuditLog::record(
            'quote_'.$data['decision'],
            'document',
            (string) $document->id,
            ['status' => $oldStatus],
            ['status' => $newStatus],
            $data['comment'] ?? null,
        );

        // Signature électronique (uniquement à l'acceptation, post-scellement,
        // donc HORS hash d'intégrité — cf. DocumentIntegrityService::canonicalPayload).
        if ($data['decision'] === 'accept' && ! empty($data['signature'])) {
            $this->storeSignature($document, $data['signature'], $data['signer_name']);
        }

        return back()->with('success', $data['decision'] === 'accept'
            ? 'Devis '.$document->number.' accepté. Merci pour votre confiance !'
            : 'Devis '.$document->number.' refusé. Votre retour a été transmis.');
    }

    /**
     * Décode et stocke la signature manuscrite (dataURL PNG) en privé, trace
     * l'événement (audit + IP + horodatage) et met à jour le document.
     */
    private function storeSignature(Document $document, string $dataUrl, string $signerName): void
    {
        // Doit être un dataURL PNG en base64.
        if (! preg_match('#^data:image/png;base64,#', $dataUrl)) {
            abort(422, 'Signature invalide : format PNG attendu.');
        }

        $base64 = substr($dataUrl, strlen('data:image/png;base64,'));
        $binary = base64_decode($base64, true);

        // Décodage valide, taille raisonnable (< 300 Ko) et magic bytes PNG.
        if ($binary === false || strlen($binary) > 300 * 1024 || strncmp($binary, "\x89PNG\r\n\x1a\n", 8) !== 0) {
            abort(422, 'Signature invalide.');
        }

        $path = 'private/signatures/'.Str::random(40).'.png';
        Storage::disk(config('factpro.proofs.disk'))->put($path, $binary);

        $document->forceFill([
            'signature_path' => $path,
            'signed_by_name' => $signerName,
            'signed_at' => now(),
            'signature_ip' => request()->ip(),
        ])->save();

        PaymentAuditLog::record(
            'quote_signed',
            'document',
            (string) $document->id,
            null,
            ['signed_by_name' => $signerName],
            null,
        );
    }

    /** (Re)génère le lien privé du portail pour un client (côté société, authentifié). */
    public function generateToken(Request $request, Customer $customer): RedirectResponse
    {
        abort_unless($customer->company_id === $request->user()->current_company_id, 403);

        do {
            $token = Str::random(48);
        } while (Customer::where('portal_token', $token)->exists());

        $customer->forceFill(['portal_token' => $token, 'portal_enabled' => true])->save();

        return back()->with('success', 'Lien du portail client généré pour '.$customer->name.'.');
    }

    /** Résout le client par token de portail actif — 404 sinon (token jamais loggé). */
    private function resolveCustomer(string $token): Customer
    {
        $customer = Customer::where('portal_token', $token)
            ->where('portal_enabled', true)
            ->first();

        abort_unless($customer !== null, 404);

        return $customer;
    }

    /**
     * Résout la vue Blade du modèle visuel (même logique que DocumentController) :
     * template du document, sinon modèle par défaut de la société, sinon pdf.document.
     */
    private function resolveTemplateView(Document $document): string
    {
        $key = $document->template_key ?: $document->company->default_template;

        if ($key && config("pdf_templates.{$key}") && view()->exists("pdf.templates.{$key}")) {
            return "pdf.templates.{$key}";
        }

        return 'pdf.document';
    }
}

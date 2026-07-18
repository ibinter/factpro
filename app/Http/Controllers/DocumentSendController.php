<?php

namespace App\Http\Controllers;

use App\Mail\DocumentMail;
use App\Models\Document;
use App\Models\DocumentAuditLog;
use App\Services\DocumentService;
use App\Services\EmailTrackingService;
use App\Services\OutgoingWebhookService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

/**
 * Envoi d'un document par email au client, PDF scellé en pièce jointe.
 */
class DocumentSendController extends Controller
{
    public function __invoke(Request $request, Document $document): RedirectResponse
    {
        abort_unless($document->company_id === $request->user()->current_company_id, 403);

        $data = $request->validate([
            'recipient' => 'required|email',
            'message' => 'nullable|string|max:2000',
            'cc_self' => 'boolean',
        ]);

        // Le PDF envoyé doit être scellé (hash SHA-256 + QR d'authenticité)
        if (! $document->isFinalized()) {
            app(DocumentService::class)->finalize($document);
        }

        $document->load(['lines', 'customer', 'company']);

        $mail = Mail::to($data['recipient']);
        if ($request->boolean('cc_self')) {
            $mail->cc($request->user()->email);
        }
        $mail->send(new DocumentMail($document, $data['message'] ?? null));

        $updates = ['sent_at' => now()];
        if ($document->status === 'draft') {
            $updates['status'] = 'sent';
        }
        $document->update($updates);

        // Phase 13 — Email tracking
        $tracking = app(EmailTrackingService::class)->createTracking($document, $data['recipient']);

        DocumentAuditLog::record($document, 'sent', $request->user(), ['recipient' => $data['recipient']]);

        app(OutgoingWebhookService::class)->dispatch($document->company, 'document.sent', [
            'event' => 'document.sent',
            'document_id' => $document->id,
            'number' => $document->number,
            'recipient' => $data['recipient'],
        ]);

        return back()->with('success', "Document envoyé à {$data['recipient']}.");
    }
}

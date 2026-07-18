<?php

namespace App\Http\Controllers;

use App\Models\QuoteLink;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use App\Notifications\QuoteLinkActionNotification;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Vue publique d'un lien de partage de devis (sans authentification).
 * Throttle 10 req/min par IP (configuré dans les routes).
 */
class PublicQuoteController extends Controller
{
    /** Affiche la page publique du devis partagé. */
    public function show(string $token, Request $request): Response|\Illuminate\Http\RedirectResponse
    {
        $link = QuoteLink::where('token', $token)
            ->with(['document.lines', 'document.customer', 'document.company'])
            ->first();

        if (! $link) {
            abort(404, 'Lien introuvable.');
        }

        if ($link->isExpired()) {
            abort(410, 'Ce lien a expiré.');
        }

        // Incrémenter vues — viewed_at seulement à la première visite
        $link->increment('views_count');
        if ($link->viewed_at === null) {
            $link->update(['viewed_at' => now()]);
        }

        // Protection par mot de passe : si aucun mot de passe en session, renvoyer la vue de saisie
        if ($link->password !== null && ! $request->session()->get('ql_auth_'.$link->id)) {
            return Inertia::render('QuoteLinks/Show', [
                'requiresPassword' => true,
                'token'            => $token,
                'expired'          => false,
            ]);
        }

        $doc     = $link->document;
        $company = $doc->company;

        return Inertia::render('QuoteLinks/Show', [
            'requiresPassword' => false,
            'token'            => $token,
            'expired'          => false,
            'link'             => [
                'id'               => $link->id,
                'allow_comments'   => $link->allow_comments,
                'allow_decline'    => $link->allow_decline,
                'require_signature'=> $link->require_signature,
                'expires_at'       => $link->expires_at?->toIso8601String(),
                'signed_at'        => $link->signed_at?->toIso8601String(),
                'declined_at'      => $link->declined_at?->toIso8601String(),
                'client_name'      => $link->client_name,
            ],
            'company' => [
                'name'      => $company->name,
                'logo_path' => $company->logo_path,
                'email'     => $company->email,
                'phone'     => $company->phone,
            ],
            'document' => [
                'number'          => $doc->number,
                'type'            => $doc->type,
                'type_label'      => $doc->type_label,
                'issue_date'      => $doc->issue_date?->toDateString(),
                'due_date'        => $doc->due_date?->toDateString(),
                'currency'        => $doc->currency,
                'subtotal'        => (float) $doc->subtotal,
                'discount_amount' => (float) $doc->discount_amount,
                'tax_amount'      => (float) $doc->tax_amount,
                'total'           => (float) $doc->total,
                'customer'        => $doc->customer ? [
                    'name'    => $doc->customer->name,
                    'address' => $doc->customer->address,
                    'city'    => $doc->customer->city,
                    'email'   => $doc->customer->email,
                    'phone'   => $doc->customer->phone,
                ] : null,
                'lines' => $doc->lines->map(fn ($l) => [
                    'id'               => $l->id,
                    'description'      => $l->description,
                    'quantity'         => (float) $l->quantity,
                    'unit'             => $l->unit,
                    'unit_price'       => (float) $l->unit_price,
                    'discount_percent' => (float) $l->discount_percent,
                    'tax_rate'         => (float) $l->tax_rate,
                    'line_total'       => (float) $l->line_total,
                ])->values(),
            ],
        ]);
    }

    /** Vérifie le mot de passe et ouvre la session d'accès. */
    public function checkPassword(string $token, Request $request): JsonResponse
    {
        $link = QuoteLink::where('token', $token)->firstOrFail();

        if ($link->isExpired()) {
            return response()->json(['success' => false, 'message' => 'Lien expiré.'], 410);
        }

        $request->validate(['password' => 'required|string']);

        if ($link->password === null || $link->checkPassword($request->input('password'))) {
            $request->session()->put('ql_auth_'.$link->id, true);

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Mot de passe incorrect.'], 422);
    }

    /** Enregistre la signature (acceptation) du devis. */
    public function sign(string $token, Request $request): JsonResponse
    {
        $link = QuoteLink::where('token', $token)
            ->with('document.company.owner')
            ->firstOrFail();

        if ($link->isExpired()) {
            return response()->json(['success' => false, 'message' => 'Lien expiré.'], 410);
        }

        if (! $link->isActive()) {
            return response()->json(['success' => false, 'message' => 'Ce devis a déjà été traité.'], 422);
        }

        $rules = [
            'client_name'      => 'required|string|max:100',
            'client_email'     => 'nullable|email|max:255',
            'client_comment'   => 'nullable|string|max:2000',
            'signature_data'   => $link->require_signature ? 'required|string' : 'nullable|string',
        ];

        if ($link->allow_comments) {
            $rules['client_email'] = 'required|email|max:255';
        }

        $data = $request->validate($rules);

        $link->update([
            'signed_at'           => now(),
            'client_name'         => $data['client_name'],
            'client_email'        => $data['client_email'] ?? null,
            'client_comment'      => $data['client_comment'] ?? null,
            'client_ip'           => $request->ip(),
            'client_signature_data' => $data['signature_data'] ?? null,
        ]);

        // Notifier le vendeur
        $this->notifyVendor($link, 'signed');

        return response()->json([
            'success' => true,
            'message' => 'Merci ! Votre accord a été enregistré.',
        ]);
    }

    /** Enregistre le refus du devis. */
    public function decline(string $token, Request $request): JsonResponse
    {
        $link = QuoteLink::where('token', $token)
            ->with('document.company.owner')
            ->firstOrFail();

        if ($link->isExpired()) {
            return response()->json(['success' => false, 'message' => 'Lien expiré.'], 410);
        }

        if (! $link->isActive()) {
            return response()->json(['success' => false, 'message' => 'Ce devis a déjà été traité.'], 422);
        }

        if (! $link->allow_decline) {
            return response()->json(['success' => false, 'message' => 'Le refus n\'est pas autorisé sur ce lien.'], 403);
        }

        $data = $request->validate([
            'client_name'   => 'required|string|max:100',
            'decline_reason'=> 'required|string|max:1000',
        ]);

        $link->update([
            'declined_at'    => now(),
            'decline_reason' => $data['decline_reason'],
            'client_name'    => $data['client_name'],
            'client_ip'      => $request->ip(),
        ]);

        $this->notifyVendor($link, 'declined');

        return response()->json(['success' => true]);
    }

    private function notifyVendor(QuoteLink $link, string $event): void
    {
        try {
            $owner = $link->document->company->owner ?? null;
            if ($owner) {
                $owner->notify(new QuoteLinkActionNotification($link, $event));
                $link->update(['notification_sent_at' => now()]);
            }
        } catch (\Throwable) {
            // Notification non bloquante
        }
    }
}

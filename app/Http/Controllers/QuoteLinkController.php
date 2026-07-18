<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\QuoteLink;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Gestion des liens de partage de devis côté vendeur (authentifié).
 */
class QuoteLinkController extends Controller
{
    /** Génère un lien public partageable pour un devis finalisé. */
    public function store(Document $document, Request $request): JsonResponse
    {
        abort_unless(
            $document->company_id === $request->user()->current_company_id,
            403,
        );

        abort_unless(
            in_array($document->type, ['quote', 'proforma'], true),
            422,
            'Seuls les devis et les proformas peuvent être partagés via un lien.',
        );

        abort_unless(
            $document->finalized_at !== null,
            422,
            'Le document doit être finalisé avant de générer un lien de partage.',
        );

        $validated = $request->validate([
            'expires_in_days'    => 'nullable|integer|min:1|max:365',
            'password'           => 'nullable|string|min:4|max:100',
            'allow_comments'     => 'boolean',
            'allow_decline'      => 'boolean',
            'require_signature'  => 'boolean',
        ]);

        $token = Str::random(48);

        $link = QuoteLink::create([
            'document_id'       => $document->id,
            'token'             => $token,
            'expires_at'        => isset($validated['expires_in_days'])
                ? now()->addDays((int) $validated['expires_in_days'])
                : null,
            'password'          => isset($validated['password'])
                ? Hash::make($validated['password'])
                : null,
            'allow_comments'    => $validated['allow_comments'] ?? true,
            'allow_decline'     => $validated['allow_decline'] ?? true,
            'require_signature' => $validated['require_signature'] ?? true,
        ]);

        return response()->json([
            'url'   => $link->publicUrl(),
            'token' => $link->token,
            'link'  => $this->formatLink($link),
        ], 201);
    }

    /** Liste les liens créés pour ce document. */
    public function index(Document $document, Request $request): JsonResponse
    {
        abort_unless(
            $document->company_id === $request->user()->current_company_id,
            403,
        );

        $links = QuoteLink::where('document_id', $document->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (QuoteLink $l) => $this->formatLink($l));

        return response()->json($links);
    }

    /** Révoque (supprime) un lien. */
    public function destroy(QuoteLink $link, Request $request): JsonResponse
    {
        abort_unless(
            $link->document->company_id === $request->user()->current_company_id,
            403,
        );

        $link->delete();

        return response()->json(['success' => true]);
    }

    /** Retourne l'état complet d'un lien (vu, signé, refusé…). */
    public function status(QuoteLink $link, Request $request): JsonResponse
    {
        abort_unless(
            $link->document->company_id === $request->user()->current_company_id,
            403,
        );

        return response()->json($this->formatLink($link));
    }

    private function formatLink(QuoteLink $link): array
    {
        return [
            'id'               => $link->id,
            'url'              => $link->publicUrl(),
            'token'            => $link->token,
            'is_active'        => $link->isActive(),
            'is_expired'       => $link->isExpired(),
            'has_password'     => $link->password !== null,
            'allow_comments'   => $link->allow_comments,
            'allow_decline'    => $link->allow_decline,
            'require_signature'=> $link->require_signature,
            'expires_at'       => $link->expires_at?->toIso8601String(),
            'views_count'      => $link->views_count,
            'viewed_at'        => $link->viewed_at?->toIso8601String(),
            'signed_at'        => $link->signed_at?->toIso8601String(),
            'client_name'      => $link->client_name,
            'client_email'     => $link->client_email,
            'declined_at'      => $link->declined_at?->toIso8601String(),
            'decline_reason'   => $link->decline_reason,
            'client_comment'   => $link->client_comment,
            'created_at'       => $link->created_at?->toIso8601String(),
        ];
    }
}

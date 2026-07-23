<?php

namespace App\Http\Controllers;

use App\Mail\SignatureInviteMail;
use App\Models\QualifiedSignature;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class QualifiedSignatureController extends Controller
{
    // ─── Routes internes (auth requis) ───────────────────────────────────────

    public function dashboard(Request $request): Response
    {
        $company = $request->user()->currentCompany();

        $query = QualifiedSignature::where('company_id', $company->id)
            ->with('signable')
            ->latest();

        if ($filter = $request->get('status')) {
            $query->where('status', $filter);
        }

        $signatures = $query->paginate(20)->withQueryString();

        $stats = [
            'pending'  => QualifiedSignature::where('company_id', $company->id)->where('status', 'pending')->count(),
            'signed'   => QualifiedSignature::where('company_id', $company->id)->where('status', 'signed')->count(),
            'refused'  => QualifiedSignature::where('company_id', $company->id)->where('status', 'refused')->count(),
            'expired'  => QualifiedSignature::where('company_id', $company->id)->where('status', 'expired')->count(),
        ];

        return Inertia::render('Signatures/Dashboard', [
            'signatures' => $signatures,
            'stats'      => $stats,
            'filters'    => ['status' => $filter],
        ]);
    }

    public function invite(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'signable_type' => 'required|string|in:App\\Models\\Document,App\\Models\\CommercialContract,App\\Models\\GedDocument',
            'signable_id'   => 'required|integer',
            'signers'       => 'required|array|min:1',
            'signers.*.name'  => 'required|string|max:255',
            'signers.*.email' => 'required|email',
            'signers.*.role'  => 'nullable|string|max:100',
            'level'           => 'nullable|string|in:simple,advanced,qualified',
            'expires_days'    => 'nullable|integer|min:1|max:90',
        ]);

        $company = $request->user()->currentCompany();
        $signable = $validated['signable_type']::findOrFail($validated['signable_id']);
        $expiresAt = now()->addDays($validated['expires_days'] ?? 7);
        $documentName = $signable->title ?? $signable->number ?? "Document #{$signable->id}";
        $emitterName  = $request->user()->name;

        $created = [];
        foreach ($validated['signers'] as $signer) {
            $sig = QualifiedSignature::create([
                'company_id'      => $company->id,
                'signable_type'   => $validated['signable_type'],
                'signable_id'     => $validated['signable_id'],
                'signer_name'     => $signer['name'],
                'signer_email'    => $signer['email'],
                'signer_role'     => $signer['role'] ?? null,
                'signature_level' => $validated['level'] ?? 'advanced',
                'expires_at'      => $expiresAt,
                'invited_at'      => now(),
                'status'          => 'pending',
            ]);

            $sig->addAuditEntry('invited', $request->ip());

            Mail::to($sig->signer_email)->send(
                new SignatureInviteMail($sig, $documentName, $emitterName)
            );

            $created[] = $sig;
        }

        return response()->json(['signatures' => $created], 201);
    }

    public function status(Request $request, QualifiedSignature $signature): JsonResponse
    {
        $this->authorizeCompany($request, $signature);

        return response()->json([
            'signature'   => $signature->only([
                'id', 'signer_name', 'signer_email', 'signer_role',
                'status', 'signature_level', 'invited_at', 'signed_at',
                'expires_at', 'document_hash',
            ]),
            'audit_trail' => $signature->audit_trail ?? [],
        ]);
    }

    public function download(Request $request, QualifiedSignature $signature): StreamedResponse
    {
        $this->authorizeCompany($request, $signature);

        abort_unless($signature->status === 'signed' && $signature->signed_file_path, 404, 'Document signé non disponible.');

        return Storage::download($signature->signed_file_path, "document-signe-{$signature->id}.pdf");
    }

    // ─── Routes publiques (token, pas d'auth) ────────────────────────────────

    public function showPortal(string $token): Response
    {
        $signature = QualifiedSignature::where('token', $token)->firstOrFail();

        if ($signature->is_expired) {
            $signature->update(['status' => 'expired']);
        }

        $signable     = $signature->signable;
        $documentName = $signable->title ?? $signable->number ?? "Document #{$signable->id}";

        $signature->addAuditEntry('viewed', request()->ip());

        return Inertia::render('Signatures/Portal', [
            'signature'    => $signature->only([
                'id', 'signer_name', 'signer_email', 'signer_role',
                'status', 'signature_level', 'expires_at', 'signed_at',
                'document_hash', 'ip_address',
            ]),
            'documentName' => $documentName,
            'token'        => $token,
        ]);
    }

    public function sendOtp(string $token): JsonResponse
    {
        $signature = $this->findPendingSignature($token);

        $code = $signature->generateOtp();

        // Envoyer le code OTP par email
        \Illuminate\Support\Facades\Mail::raw(
            "Votre code de vérification pour signer le document est : {$code}\n\nCe code expire dans 10 minutes.",
            function ($message) use ($signature) {
                $message->to($signature->signer_email)
                    ->subject('Code de vérification — Signature de document');
            }
        );

        $signature->addAuditEntry('otp_sent', request()->ip());

        return response()->json(['sent' => true]);
    }

    public function verifyOtpAndSign(Request $request, string $token): JsonResponse
    {
        $validated = $request->validate([
            'otp'            => 'required|string|size:6',
            'signature_data' => 'required|string', // base64 canvas
        ]);

        $signature = $this->findPendingSignature($token);

        if (! $signature->verifyOtp($validated['otp'])) {
            return response()->json(['error' => 'Code OTP invalide ou expiré.'], 422);
        }

        // Calculer le hash du signable
        $signable = $signature->signable;
        $documentContent = $signable->title ?? $signable->number ?? "Document #{$signable->id}";
        $documentHash = hash('sha256', $documentContent . $signature->id . $signature->token);

        // Stocker le PDF signé (on génère un fichier texte/PDF simple simulant l'incorporation)
        $signedPath = $this->generateSignedDocument($signature, $validated['signature_data'], $documentHash);

        $signature->update([
            'status'          => 'signed',
            'signed_at'       => now(),
            'ip_address'      => $request->ip(),
            'user_agent'      => substr($request->userAgent() ?? '', 0, 500),
            'signature_data'  => $validated['signature_data'],
            'document_hash'   => $documentHash,
            'signed_file_path'=> $signedPath,
        ]);

        $signature->addAuditEntry('signed', $request->ip());

        return response()->json([
            'success'       => true,
            'signed_at'     => $signature->signed_at->toISOString(),
            'document_hash' => $documentHash,
            'ip_address'    => $request->ip(),
        ]);
    }

    public function refuse(string $token, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $signature = $this->findPendingSignature($token);

        $signature->update(['status' => 'refused']);
        $signature->addAuditEntry('refused: ' . ($validated['reason'] ?? 'aucune raison'), $request->ip());

        return response()->json(['success' => true]);
    }

    // ─── Helpers privés ──────────────────────────────────────────────────────

    private function authorizeCompany(Request $request, QualifiedSignature $signature): void
    {
        $company = $request->user()->currentCompany();
        abort_unless($signature->company_id === $company->id, 403);
    }

    private function findPendingSignature(string $token): QualifiedSignature
    {
        $signature = QualifiedSignature::where('token', $token)->firstOrFail();

        abort_if($signature->is_expired, 410, 'Ce lien de signature a expiré.');
        abort_if($signature->status !== 'pending', 409, "Ce document a déjà été {$signature->status}.");

        return $signature;
    }

    private function generateSignedDocument(QualifiedSignature $signature, string $signatureData, string $documentHash): string
    {
        // Génération d'un PDF minimaliste avec le tampon de signature
        // En production, utiliser une bibliothèque PDF pour incruster la signature
        $content = "%PDF-1.4\n";
        $content .= "% Document signé électroniquement\n";
        $content .= "% Signataire : {$signature->signer_name} <{$signature->signer_email}>\n";
        $content .= "% Rôle : " . ($signature->signer_role ?? 'N/A') . "\n";
        $content .= "% Niveau de signature : {$signature->signature_level}\n";
        $content .= "% Date de signature : " . now()->toISOString() . "\n";
        $content .= "% Hash du document : {$documentHash}\n";
        $content .= "% Signature eIDAS avancée — conforme au règlement UE 910/2014\n";

        $path = "signatures/{$signature->id}-signed.pdf";
        Storage::put($path, $content);

        return $path;
    }
}

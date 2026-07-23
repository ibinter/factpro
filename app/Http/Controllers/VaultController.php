<?php

namespace App\Http\Controllers;

use App\Models\VaultDocument;
use App\Services\VaultService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class VaultController extends Controller
{
    public function __construct(private VaultService $vault) {}

    // -------------------------------------------------------------------------
    // index — liste des documents + stats intégrité
    // -------------------------------------------------------------------------

    public function index(Request $request): Response
    {
        $company = Auth::user()->company;

        $query = VaultDocument::forCompany($company->id)
            ->when($request->type, fn ($q) => $q->where('document_type', $request->type))
            ->when($request->year, fn ($q) => $q->whereYear('archived_at', $request->year))
            ->latest('archived_at');

        $documents = $query->paginate(25)->through(fn ($doc) => [
            'id'               => $doc->id,
            'title'            => $doc->title,
            'document_type'    => $doc->document_type,
            'archived_at'      => $doc->archived_at?->toDateTimeString(),
            'retention_until'  => $doc->retention_until,
            'days_until_expiry'=> $doc->days_until_expiry,
            'integrity_status' => $doc->integrity_status,
            'file_size'        => $doc->file_size,
            'is_sealed'        => $doc->is_sealed,
        ]);

        // Stats rapides sans vérification fichier sur chaque ligne
        $total   = VaultDocument::forCompany($company->id)->count();
        $valid   = VaultDocument::forCompany($company->id)->get()
            ->filter(fn ($d) => $d->integrity_status === 'valid')->count();
        $alerts  = $total - $valid;

        return Inertia::render('Vault/Index', [
            'documents' => $documents,
            'stats'     => compact('total', 'valid', 'alerts'),
            'filters'   => $request->only('type', 'year'),
        ]);
    }

    // -------------------------------------------------------------------------
    // show — détail + résultat verify()
    // -------------------------------------------------------------------------

    public function show(VaultDocument $vault): Response
    {
        $this->authorizeDoc($vault);

        $verification = $this->vault->verify($vault);

        return Inertia::render('Vault/Show', [
            'document'     => $vault,
            'verification' => $verification,
        ]);
    }

    // -------------------------------------------------------------------------
    // download — téléchargement sécurisé
    // -------------------------------------------------------------------------

    public function download(VaultDocument $vault)
    {
        $this->authorizeDoc($vault);

        Log::info('vault.download', [
            'user_id'    => Auth::id(),
            'vault_id'   => $vault->id,
            'company_id' => $vault->company_id,
        ]);

        $absolutePath = Storage::path($vault->file_path);

        if (! file_exists($absolutePath)) {
            abort(404, 'Fichier introuvable dans le coffre.');
        }

        return response()->download($absolutePath, basename($vault->file_path), [
            'Content-Type' => $vault->mime_type,
        ]);
    }

    // -------------------------------------------------------------------------
    // verify — POST, re-vérifie intégrité
    // -------------------------------------------------------------------------

    public function verify(VaultDocument $vault): JsonResponse
    {
        $this->authorizeDoc($vault);

        $result = $this->vault->verify($vault);

        return response()->json($result);
    }

    // -------------------------------------------------------------------------
    // integrityReport — GET, génère rapport complet
    // -------------------------------------------------------------------------

    public function integrityReport(): JsonResponse
    {
        $company = Auth::user()->company;
        $report  = $this->vault->generateIntegrityReport($company);

        return response()->json($report);
    }

    // -------------------------------------------------------------------------
    // purge — DELETE seulement si retention dépassée
    // -------------------------------------------------------------------------

    public function purge(VaultDocument $vault)
    {
        $this->authorizeDoc($vault);

        if (now()->lt($vault->retention_until)) {
            abort(403, 'La durée de rétention légale n\'est pas encore écoulée.');
        }

        Storage::delete($vault->file_path);
        $vault->delete();

        return redirect()->route('vault.index')
            ->with('success', 'Document purgé du coffre.');
    }

    // -------------------------------------------------------------------------
    // Helper
    // -------------------------------------------------------------------------

    private function authorizeDoc(VaultDocument $vault): void
    {
        $companyId = Auth::user()->company_id;
        if ($vault->company_id !== $companyId) {
            abort(403);
        }
    }
}

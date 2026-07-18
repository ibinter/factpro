<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentArchive;
use App\Services\ArchiveService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ArchiveController extends Controller
{
    public function __construct(private ArchiveService $archives) {}

    public function index(Request $request): Response
    {
        $company = $request->user()->currentCompany;

        $query = DocumentArchive::where('company_id', $company->id)
            ->with('document:id,type,number,status,issue_date')
            ->orderByDesc('archived_at');

        if ($request->year) {
            $query->whereYear('archived_at', (int) $request->year);
        }

        if ($request->type) {
            $query->whereHas('document', fn ($q) => $q->where('type', $request->type));
        }

        if ($request->verified === 'verified') {
            $query->where('is_verified', true);
        } elseif ($request->verified === 'unverified') {
            $query->where('is_verified', false);
        }

        $archives = $query->paginate(20)->withQueryString();

        $lastVerified = DocumentArchive::where('company_id', $company->id)
            ->whereNotNull('last_verified_at')
            ->max('last_verified_at');

        return Inertia::render('Archive/Index', [
            'archives'     => $archives,
            'filters'      => $request->only('year', 'type', 'verified'),
            'totalCount'   => DocumentArchive::where('company_id', $company->id)->count(),
            'lastVerified' => $lastVerified,
            'years'        => DocumentArchive::where('company_id', $company->id)
                ->selectRaw('YEAR(archived_at) as year')
                ->distinct()
                ->orderByDesc('year')
                ->pluck('year'),
        ]);
    }

    public function show(DocumentArchive $archive): JsonResponse
    {
        $archive->load(['document.lines', 'document.customer:id,name']);

        return response()->json([
            'archive'    => $archive,
            'audit_trail' => $this->archives->getAuditTrail($archive->document),
        ]);
    }

    public function verify(Request $request, DocumentArchive $archive): JsonResponse
    {
        $this->authorizeArchive($request, $archive);

        $result = $this->archives->verify($archive);

        return response()->json($result);
    }

    public function download(Request $request, DocumentArchive $archive): BinaryFileResponse
    {
        $this->authorizeArchive($request, $archive);

        $filePath = storage_path('app/' . $archive->pdf_path);

        abort_unless(file_exists($filePath), 404, 'Fichier archivé introuvable.');

        return response()->download(
            $filePath,
            ($archive->document->number ?? 'archive') . '_archive.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }

    public function exportZip(Request $request): BinaryFileResponse
    {
        $company = $request->user()->currentCompany;
        $year = (int) $request->input('year', now()->year);

        $zipPath = $this->archives->exportZip($company->id, $year);

        abort_unless(file_exists($zipPath), 500, 'Impossible de générer le ZIP.');

        return response()->download(
            $zipPath,
            "archives_{$company->id}_{$year}.zip",
            ['Content-Type' => 'application/zip']
        )->deleteFileAfterSend(true);
    }

    public function auditTrail(Request $request, Document $document): JsonResponse
    {
        abort_unless($document->company_id === $request->user()->current_company_id, 403);

        return response()->json([
            'audit_trail' => $this->archives->getAuditTrail($document),
        ]);
    }

    private function authorizeArchive(Request $request, DocumentArchive $archive): void
    {
        abort_unless($archive->company_id === $request->user()->current_company_id, 403);
    }
}

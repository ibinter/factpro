<?php

namespace App\Http\Controllers;

use App\Models\AccessLog;
use App\Models\GdprConsent;
use App\Models\GdprRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use ZipArchive;

class GdprComplianceController extends Controller
{
    private function companyId(): int
    {
        return Auth::user()->current_company_id;
    }

    public function dashboard(): Response
    {
        $companyId = $this->companyId();

        $totalConsents   = GdprConsent::where('company_id', $companyId)->count();
        $activeConsents  = GdprConsent::where('company_id', $companyId)->active()->count();
        $revokedConsents = $totalConsents - $activeConsents;

        $pendingRequests = GdprRequest::where('company_id', $companyId)
            ->whereIn('status', ['pending', 'in_progress'])
            ->count();

        $overdueRequests = GdprRequest::where('company_id', $companyId)
            ->whereNull('completed_at')
            ->where('deadline_at', '<', now())
            ->count();

        // Score de conformité : 0-100
        $score = 100;
        if ($totalConsents > 0) {
            $score -= (int) round(($revokedConsents / $totalConsents) * 30);
        }
        if ($overdueRequests > 0) {
            $score -= min(40, $overdueRequests * 10);
        }
        $score = max(0, $score);

        $recentRequests = GdprRequest::where('company_id', $companyId)
            ->with('handler:id,name')
            ->orderByDesc('received_at')
            ->limit(20)
            ->get()
            ->map(fn ($r) => array_merge($r->toArray(), [
                'is_overdue'     => $r->is_overdue,
                'days_remaining' => $r->days_remaining,
            ]));

        return Inertia::render('Gdpr/Dashboard', [
            'stats' => [
                'total_consents'   => $totalConsents,
                'active_consents'  => $activeConsents,
                'revoked_consents' => $revokedConsents,
                'pending_requests' => $pendingRequests,
                'overdue_requests' => $overdueRequests,
                'compliance_score' => $score,
            ],
            'requests' => $recentRequests,
        ]);
    }

    public function requests(Request $request)
    {
        $companyId = $this->companyId();

        $query = GdprRequest::where('company_id', $companyId)
            ->with('handler:id,name')
            ->orderByDesc('received_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $paginatedRequests = $query->paginate(20);

        return Inertia::render('Gdpr/Requests', [
            'requests' => $paginatedRequests->through(fn ($r) => array_merge($r->toArray(), [
                'is_overdue'     => $r->is_overdue,
                'days_remaining' => $r->days_remaining,
            ])),
            'filters' => $request->only('status', 'type'),
        ]);
    }

    public function createRequest(Request $request)
    {
        $validated = $request->validate([
            'type'          => 'required|in:access,rectification,deletion,portability,opposition',
            'subject_name'  => 'required|string|max:255',
            'subject_email' => 'required|email|max:255',
            'subject_type'  => 'nullable|in:customer,employee',
            'subject_id'    => 'nullable|integer',
            'description'   => 'nullable|string',
        ]);

        GdprRequest::create(array_merge($validated, [
            'company_id'  => $this->companyId(),
            'received_at' => now(),
            'deadline_at' => now()->addDays(30),
            'status'      => 'pending',
        ]));

        return back()->with('success', 'Demande RGPD créée. Délai légal : 30 jours.');
    }

    public function updateRequest(Request $request, GdprRequest $gdprRequest)
    {
        abort_unless($gdprRequest->company_id === $this->companyId(), 403);

        $validated = $request->validate([
            'status'   => 'required|in:pending,in_progress,completed,rejected',
            'response' => 'nullable|string',
        ]);

        if ($validated['status'] === 'completed' && $gdprRequest->completed_at === null) {
            $validated['completed_at'] = now();
            $validated['handled_by']   = Auth::id();
        }

        $gdprRequest->update($validated);

        return back()->with('success', 'Demande mise à jour.');
    }

    public function exportData(Request $request)
    {
        $validated = $request->validate([
            'subject_type' => 'required|in:customer,employee',
            'subject_id'   => 'required|integer',
        ]);

        $companyId = $this->companyId();
        $id        = $validated['subject_id'];
        $csvLines  = ['Type,ID,Champ,Valeur'];

        if ($validated['subject_type'] === 'customer') {
            $customer = DB::table('customers')
                ->where('company_id', $companyId)
                ->where('id', $id)
                ->first();

            if ($customer) {
                foreach ((array) $customer as $key => $value) {
                    $csvLines[] = 'customer,' . $id . ',' . $key . ',' . $value;
                }

                $docs = DB::table('documents')
                    ->where('company_id', $companyId)
                    ->where('customer_id', $id)
                    ->select('id', 'number', 'type', 'total', 'status', 'created_at')
                    ->get();

                foreach ($docs as $doc) {
                    $csvLines[] = "document,{$doc->id},number,{$doc->number}";
                    $csvLines[] = "document,{$doc->id},total,{$doc->total}";
                }
            }
        }

        $csv     = implode("\n", $csvLines);
        $zipPath = tempnam(sys_get_temp_dir(), 'gdpr_') . '.zip';
        $zip     = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE);
        $zip->addFromString('export_rgpd.csv', $csv);
        $zip->close();

        AccessLog::record('gdpr_export', true, "Export {$validated['subject_type']} #{$id}");

        return response()->download($zipPath, 'export_rgpd_' . now()->format('Ymd') . '.zip')
            ->deleteFileAfterSend();
    }

    public function deleteSubject(Request $request)
    {
        $validated = $request->validate([
            'subject_type' => 'required|in:customer,employee',
            'subject_id'   => 'required|integer',
        ]);

        $companyId = $this->companyId();
        $id        = $validated['subject_id'];
        $anon      = 'ANONYMIZED-' . $id;

        if ($validated['subject_type'] === 'customer') {
            DB::table('customers')
                ->where('company_id', $companyId)
                ->where('id', $id)
                ->update([
                    'name'    => $anon,
                    'email'   => $anon . '@anonymized.invalid',
                    'phone'   => null,
                    'address' => null,
                ]);
        }

        GdprConsent::where('company_id', $companyId)
            ->where('subject_type', $validated['subject_type'])
            ->where('subject_id', $id)
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now()]);

        AccessLog::record('gdpr_delete_subject', true, "Anonymisation {$validated['subject_type']} #{$id}");

        return back()->with('success', 'Données anonymisées conformément au RGPD (Art. 17).');
    }

    public function generateReport()
    {
        $companyId = $this->companyId();
        $company   = Auth::user()->companies()->find($companyId);

        $totalConsents   = GdprConsent::where('company_id', $companyId)->count();
        $activeConsents  = GdprConsent::where('company_id', $companyId)->active()->count();
        $revokedConsents = $totalConsents - $activeConsents;

        $requestsByType = GdprRequest::where('company_id', $companyId)
            ->select('type', 'status', DB::raw('count(*) as total'))
            ->groupBy('type', 'status')
            ->get();

        $accessSummary = AccessLog::where('company_id', $companyId)
            ->select('action', DB::raw('count(*) as total'), DB::raw('SUM(success) as successes'))
            ->whereDate('created_at', '>=', now()->subDays(30))
            ->groupBy('action')
            ->get();

        $overdueCount = GdprRequest::where('company_id', $companyId)
            ->whereNull('completed_at')
            ->where('deadline_at', '<', now())
            ->count();

        $pdf = Pdf::loadView('gdpr.report', [
            'company'         => $company,
            'generatedAt'     => now(),
            'totalConsents'   => $totalConsents,
            'activeConsents'  => $activeConsents,
            'revokedConsents' => $revokedConsents,
            'requestsByType'  => $requestsByType,
            'accessSummary'   => $accessSummary,
            'overdueCount'    => $overdueCount,
        ]);

        AccessLog::record('gdpr_report_generated', true);

        return $pdf->download('rapport_rgpd_' . now()->format('Ymd') . '.pdf');
    }
}

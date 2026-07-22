<?php

namespace App\Http\Controllers;

use App\Models\PosSession;
use App\Services\PosReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Inertia\Inertia;

class PosZReportController extends Controller
{
    public function __construct(private PosReportService $service) {}

    /**
     * Ouvre une session de caisse avec fonds de caisse initial.
     * (Alternative to PosController::openSession via the new route group)
     */
    public function openSession(Request $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'opening_float' => 'required|numeric|min:0',
            'cashier_name' => 'nullable|string|max:100',
        ]);

        $companyId = $request->user()->current_company_id;

        $exists = PosSession::open()
            ->where('company_id', $companyId)
            ->where('user_id', $request->user()->id)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Une session de caisse est déjà ouverte.');
        }

        $session = PosSession::create([
            'company_id' => $companyId,
            'user_id' => $request->user()->id,
            'status' => 'open',
            'opening_float' => $data['opening_float'],
            'opened_at' => now(),
            'cashier_name' => $data['cashier_name'] ?? null,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['session' => $session->only(['id', 'opening_float', 'opened_at'])]);
        }

        return redirect()->route('pos.index')->with('success', 'Caisse ouverte.');
    }

    /**
     * Rapport X : données intermédiaires sans clôture.
     */
    public function xReport(Request $request, PosSession $session): JsonResponse
    {
        abort_unless($session->company_id === $request->user()->current_company_id, 403);

        return response()->json($this->service->generateXReport($session));
    }

    /**
     * G�n�re le rapport Z (cl�ture irr�versible).
     */
    public function generateZ(Request $request, PosSession $session): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        abort_unless($session->company_id === $request->user()->current_company_id, 403);

        $data = $request->validate([
            'actual_cash' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        try {
            $report = $this->service->generateZReport(
                $session,
                (float) $data['actual_cash'],
                $data['notes'] ?? ''
            );
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['error' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        }

        if ($request->expectsJson()) {
            return response()->json($report);
        }

        return redirect()->route('pos.z-report.pdf', $session)
            ->with('success', 'Rapport Z g�n�r� � '.$report['z_number']);
    }

    /**
     * PDF du rapport Z.
     */
    public function pdfZ(Request $request, PosSession $session): Response
    {
        abort_unless($session->company_id === $request->user()->current_company_id, 403);
        abort_unless($session->z_report_generated_at, 404);

        $session->load('user:id,name', 'company');

        $data = $this->service->generateXReport($session);
        $data['type'] = 'Z';
        $data['z_number'] = $session->z_report_number;
        $data['actual_cash'] = (float) $session->counted_cash;
        $data['expected_cash'] = (float) $session->expected_cash;
        $data['cash_difference'] = (float) $session->difference;
        $data['z_report_generated_at'] = $session->z_report_generated_at;
        $data['currency'] = $session->company->currency ?? 'XOF';

        $pdf = Pdf::loadView('pdf.pos-z-report', $data);
        $pdf->setPaper([0, 0, 226.77, 841.89]); // 80mm de large approx.

        return $pdf->stream('rapport-z-'.$session->z_report_number.'.pdf');
    }

    /**
     * Historique des rapports Z de la company.
     */
    public function history(Request $request): \Inertia\Response
    {
        $companyId = $request->user()->current_company_id;

        $reports = $this->service->getZHistory($companyId);

        return Inertia::render('Pos/ZReport', [
            'reports' => $reports->map(fn ($s) => [
                'id' => $s->id,
                'z_number' => $s->z_report_number,
                'opened_at' => $s->opened_at,
                'z_report_generated_at' => $s->z_report_generated_at,
                'cashier' => $s->cashier_name ?? $s->user?->name,
                'total_sales' => (float) $s->total_sales,
                'tickets_count' => $s->tickets_count,
                'counted_cash' => (float) $s->counted_cash,
                'expected_cash' => (float) $s->expected_cash,
                'difference' => (float) $s->difference,
                'pdf_url' => route('pos.z-report.pdf', $s),
            ]),
            'currency' => $request->user()->currentCompany->currency,
        ]);
    }
}


<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\PrepaidVoucher;
use App\Services\VoucherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Inertia\Inertia;

/**
 * Console superadmin — gestion des codes prépayés revendeurs.
 */
class VoucherAdminController extends Controller
{
    public function __construct(private VoucherService $vouchers) {}

    /** Liste des lots avec stats. */
    public function index(): \Inertia\Response
    {
        $batches = PrepaidVoucher::selectRaw('
                batch_ref,
                MIN(created_at) as created_at,
                COUNT(*) as total,
                SUM(CASE WHEN status = "used" THEN 1 ELSE 0 END) as used_count,
                SUM(CASE WHEN status = "available" THEN 1 ELSE 0 END) as available_count,
                SUM(CASE WHEN status IN ("expired","cancelled") THEN 1 ELSE 0 END) as inactive_count,
                MAX(face_value) as face_value,
                MAX(currency) as currency,
                MAX(reseller_name) as reseller_name,
                MAX(expires_at) as expires_at
            ')
            ->groupBy('batch_ref')
            ->orderByDesc('created_at')
            ->get();

        $plans = Plan::where('is_active', true)->orderBy('sort_order')->get(['id', 'name', 'code']);

        return Inertia::render('Admin/VoucherManager', [
            'batches' => $batches,
            'plans'   => $plans,
        ]);
    }

    /** Génère un nouveau lot. */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'quantity'        => 'required|integer|min:1|max:500',
            'plan_id'         => 'nullable|exists:plans,id',
            'duration_months' => 'required|integer|min:1|max:24',
            'currency'        => 'required|string|max:10',
            'face_value'      => 'required|numeric|min:0',
            'reseller_price'  => 'required|numeric|min:0',
            'reseller_name'   => 'nullable|string|max:150',
            'expires_at'      => 'nullable|date|after:today',
        ]);

        $result = $this->vouchers->generateBatch([
            ...$data,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.vouchers.batch', $result['batch_ref'])
            ->with('success', $data['quantity'] . ' codes générés — lot ' . $result['batch_ref']);
    }

    /** Détail d'un lot. */
    public function show(string $batchRef): \Inertia\Response
    {
        $vouchers = PrepaidVoucher::where('batch_ref', $batchRef)
            ->with(['plan:id,name', 'usedByUser:id,name,email'])
            ->orderBy('id')
            ->get();

        abort_if($vouchers->isEmpty(), 404, 'Lot introuvable.');

        $stats = [
            'total'     => $vouchers->count(),
            'available' => $vouchers->where('status', 'available')->count(),
            'used'      => $vouchers->where('status', 'used')->count(),
            'expired'   => $vouchers->where('status', 'expired')->count(),
            'cancelled' => $vouchers->where('status', 'cancelled')->count(),
        ];

        return Inertia::render('Admin/VoucherBatch', [
            'batchRef' => $batchRef,
            'vouchers' => $vouchers,
            'stats'    => $stats,
        ]);
    }

    /** Télécharge le CSV du lot. */
    public function exportCsv(string $batchRef): Response
    {
        $csv = $this->vouchers->exportBatchCsv($batchRef);

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"vouchers-{$batchRef}.csv\"",
        ]);
    }

    /** Annule un code individuel. */
    public function destroy(PrepaidVoucher $voucher): RedirectResponse
    {
        try {
            $this->vouchers->cancel($voucher);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['voucher' => $e->getMessage()]);
        }

        return redirect()->route('admin.vouchers.index')->with('success', "Code {$voucher->code} annulé.");
    }
}

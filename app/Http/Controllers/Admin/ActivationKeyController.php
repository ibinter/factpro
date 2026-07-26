<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivationKey;
use App\Models\PaymentAuditLog;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Inertia\Inertia;

/**
 * Console superadmin — gestion des clés d'activation formule (cahier §19.6).
 */
class ActivationKeyController extends Controller
{
    // ── Helpers ────────────────────────────────────────────────────────────────

    /** Génère un code unique IBFP-XXXX-XXXX-XXXX (aléatoire, non prédictible). */
    private function generateCode(): string
    {
        do {
            $code = 'IBFP-' . implode('-', [
                strtoupper(Str::random(4)),
                strtoupper(Str::random(4)),
                strtoupper(Str::random(4)),
            ]);
        } while (ActivationKey::where('code', $code)->exists());

        return $code;
    }

    /** Génère un identifiant de lot unique. */
    private function generateBatch(): string
    {
        $prefix = 'LOT-' . date('Y') . '-';
        $last = ActivationKey::where('batch', 'like', $prefix . '%')
            ->orderByDesc('batch')
            ->value('batch');

        if ($last) {
            $n = (int) substr($last, strlen($prefix));
            return $prefix . str_pad($n + 1, 3, '0', STR_PAD_LEFT);
        }

        return $prefix . '001';
    }

    // ── Actions ────────────────────────────────────────────────────────────────

    /** Liste des clés avec filtres. */
    public function index(Request $request): \Inertia\Response
    {
        $query = ActivationKey::with(['plan', 'usedByCompany', 'generatedBy'])
            ->latest();

        if ($request->filled('batch')) {
            $query->where('batch', $request->batch);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('plan_id')) {
            $query->where('plan_id', $request->plan_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $keys = $query->paginate(50)->withQueryString();

        $plans = Plan::where('is_active', true)->orderBy('sort_order')->get(['id', 'name', 'code']);

        $batches = ActivationKey::selectRaw('batch, COUNT(*) as total, MIN(created_at) as created_at')
            ->groupBy('batch')
            ->orderByDesc('created_at')
            ->get();

        return Inertia::render('Admin/ActivationKeys', [
            'keys'    => $keys,
            'plans'   => $plans,
            'batches' => $batches,
            'filters' => $request->only(['batch', 'status', 'plan_id', 'date_from', 'date_to']),
        ]);
    }

    /** Génère un lot de clés. */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'plan_id'       => 'required|exists:plans,id',
            'quantity'      => 'required|integer|min:1|max:500',
            'duration_days' => 'required|integer|min:1|max:3650',
            'expires_at'    => 'nullable|date|after:today',
        ]);

        $batch = $this->generateBatch();
        $adminId = $request->user()->id;
        $keys = [];

        for ($i = 0; $i < $data['quantity']; $i++) {
            $keys[] = [
                'code'          => $this->generateCode(),
                'plan_id'       => $data['plan_id'],
                'batch'         => $batch,
                'duration_days' => $data['duration_days'],
                'expires_at'    => $data['expires_at'] ?? null,
                'status'        => 'available',
                'generated_by'  => $adminId,
                'created_at'    => now(),
                'updated_at'    => now(),
            ];
        }

        ActivationKey::insert($keys);

        PaymentAuditLog::record('activation_keys_generated', 'activation_key', null, null, [
            'batch'         => $batch,
            'quantity'      => $data['quantity'],
            'plan_id'       => $data['plan_id'],
            'duration_days' => $data['duration_days'],
            'expires_at'    => $data['expires_at'] ?? null,
        ], adminId: $adminId);

        return back()->with('success', "Lot {$batch} : {$data['quantity']} clé(s) générée(s).");
    }

    /** Révoque une clé avec motif obligatoire. */
    public function revoke(Request $request, ActivationKey $activationKey): RedirectResponse
    {
        $data = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        if ($activationKey->status === 'revoked') {
            return back()->withErrors(['key' => 'Cette clé est déjà révoquée.']);
        }
        if ($activationKey->status === 'used') {
            return back()->withErrors(['key' => 'Impossible de révoquer une clé déjà utilisée.']);
        }

        $activationKey->update([
            'status'            => 'revoked',
            'revoked_at'        => now(),
            'revoked_by'        => $request->user()->id,
            'revocation_reason' => $data['reason'],
        ]);

        PaymentAuditLog::record('activation_key_revoked', 'activation_key', $activationKey->id, null, [
            'code'   => $activationKey->code,
            'reason' => $data['reason'],
        ], adminId: $request->user()->id);

        return back()->with('success', 'Clé révoquée.');
    }

    /** Export CSV d'un lot. */
    public function exportCsv(Request $request, string $batch): Response
    {
        $keys = ActivationKey::with(['plan', 'usedByCompany'])
            ->where('batch', $batch)
            ->orderBy('id')
            ->get();

        $csv = "Code,Plan,Durée (jours),Expire le,Statut,Société utilisatrice,Utilisé le\n";

        foreach ($keys as $key) {
            $csv .= implode(',', [
                $key->code,
                '"' . ($key->plan?->name ?? '') . '"',
                $key->duration_days,
                $key->expires_at?->format('Y-m-d') ?? '',
                $key->status,
                '"' . ($key->usedByCompany?->name ?? '') . '"',
                $key->used_at?->format('Y-m-d H:i:s') ?? '',
            ]) . "\n";
        }

        $filename = "activation-keys-{$batch}.csv";

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /** Suppression d'une clé (uniquement si disponible). */
    public function destroy(ActivationKey $activationKey): RedirectResponse
    {
        if ($activationKey->status !== 'available') {
            return back()->withErrors(['key' => 'Seules les clés disponibles peuvent être supprimées.']);
        }

        $activationKey->delete();

        return back()->with('success', 'Clé supprimée.');
    }
}

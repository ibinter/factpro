<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\License;
use App\Models\PaymentAuditLog;
use App\Models\PaymentTransaction;
use App\Models\Plan;
use App\Services\LicenseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Console Superadmin — gestion des licences (script §16.2).
 * Toute action sensible exige un motif obligatoire + journal d'audit.
 */
class LicenseAdminController extends Controller
{
    public function index(Request $request): Response
    {
        $licenses = License::with(['user:id,name,email', 'plan:id,code,name'])
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->plan_code, fn ($q, $code) => $q->whereHas('plan', fn ($p) => $p->where('code', $code)))
            ->when($request->search, function ($q, $search) {
                $q->where(fn ($sub) => $sub
                    ->where('license_key', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($u) => $u
                        ->where('email', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")));
            })
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (License $l) => [
                'id' => $l->id,
                'license_key' => $l->license_key,
                'type' => $l->type,
                'status' => $l->status,
                'starts_at' => $l->starts_at?->format('d/m/Y'),
                'ends_at' => $l->ends_at?->format('d/m/Y'),
                'days_remaining' => $l->daysRemaining(),
                'user' => $l->user?->only(['id', 'name', 'email']),
                'plan' => $l->plan?->only(['id', 'code', 'name']),
            ]);

        $stats = [
            'active' => License::whereIn('status', ['active', 'grace_period'])->count(),
            'trials' => License::where('status', 'trial')->count(),
            'suspended' => License::where('status', 'suspended')->count(),
            'expiring_7d' => License::whereIn('status', ['trial', 'provisional', 'active', 'grace_period'])
                ->whereNotNull('ends_at')
                ->whereBetween('ends_at', [now(), now()->addDays(7)])
                ->count(),
        ];

        return Inertia::render('Admin/Licenses', [
            'licenses' => $licenses,
            'stats' => $stats,
            'plans' => Plan::orderBy('sort_order')->get(['id', 'code', 'name']),
            'statuses' => License::STATUSES,
            'filters' => $request->only(['status', 'plan_code', 'search']),
        ]);
    }

    /** Prolonge une licence de N mois (réactive si expirée/suspendue). */
    public function extend(Request $request, License $license): RedirectResponse
    {
        $data = $request->validate([
            'months' => 'required|integer|min:1|max:24',
            'reason' => 'required|string|max:500',
        ]);

        $this->guardNotRevoked($license);

        $old = $this->snapshot($license);

        $base = $license->ends_at && $license->ends_at->isFuture() ? $license->ends_at->copy() : now();

        $updates = ['ends_at' => $base->addMonths((int) $data['months'])];

        if (in_array($license->status, ['expired', 'suspended', 'grace_period', 'pending'])) {
            $updates['status'] = 'active';
            $updates['grace_period_ends_at'] = null;
        }

        if ($license->type === 'trial') {
            $updates['type'] = 'paid';
            $updates['status'] = 'active';
        }

        $license->update($updates);

        PaymentAuditLog::record(
            'license_extended',
            'license',
            $license->id,
            $old,
            $this->snapshot($license->fresh()),
            $data['reason'],
            $request->user()->id,
        );

        return redirect()->route('admin.licenses.index')->with('success', "Licence prolongée de {$data['months']} mois (fin : {$license->fresh()->ends_at->format('d/m/Y')}).");
    }

    /** Suspend une licence (motif obligatoire). */
    public function suspend(Request $request, License $license): RedirectResponse
    {
        $data = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $this->guardNotRevoked($license);

        $old = $this->snapshot($license);
        $license->update(['status' => 'suspended']);

        PaymentAuditLog::record(
            'license_suspended',
            'license',
            $license->id,
            $old,
            $this->snapshot($license->fresh()),
            $data['reason'],
            $request->user()->id,
        );

        return redirect()->route('admin.licenses.index')->with('success', 'Licence suspendue.');
    }

    /** Réactive une licence suspendue dont la période est encore valide. */
    public function reactivate(Request $request, License $license): RedirectResponse
    {
        $data = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $this->guardNotRevoked($license);

        if (! $license->ends_at || $license->ends_at->isPast()) {
            throw ValidationException::withMessages([
                'reason' => 'Licence expirée : utilisez « Prolonger ».',
            ]);
        }

        $old = $this->snapshot($license);
        $license->update(['status' => 'active']);

        PaymentAuditLog::record(
            'license_reactivated',
            'license',
            $license->id,
            $old,
            $this->snapshot($license->fresh()),
            $data['reason'],
            $request->user()->id,
        );

        return redirect()->route('admin.licenses.index')->with('success', 'Licence réactivée.');
    }

    /** Révoque définitivement une licence (irréversible). */
    public function revoke(Request $request, License $license): RedirectResponse
    {
        $data = $request->validate([
            'reason' => 'required|string|max:500',
            'confirmation' => 'required|accepted',
        ]);

        $this->guardNotRevoked($license);

        $old = $this->snapshot($license);
        $license->update(['status' => 'revoked']);

        PaymentAuditLog::record(
            'license_revoked',
            'license',
            $license->id,
            $old,
            $this->snapshot($license->fresh()),
            $data['reason'],
            $request->user()->id,
        );

        return redirect()->route('admin.licenses.index')->with('success', 'Licence révoquée définitivement.');
    }

    /**
     * Vue gestionnaire de licences étendue (LicenseManager.vue) avec onglet provisoires.
     */
    public function manager(Request $request): Response
    {
        $query = License::with(['user:id,name,email', 'plan:id,code,name', 'order:id,order_number,status'])
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->plan_code, fn ($q, $code) => $q->whereHas('plan', fn ($p) => $p->where('code', $code)))
            ->when($request->search, function ($q, $search) {
                $q->where(fn ($sub) => $sub
                    ->where('license_key', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($u) => $u
                        ->where('email', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")));
            })
            ->when($request->expiring, function ($q, $days) {
                $q->whereIn('status', ['active', 'trial', 'provisional', 'grace_period'])
                    ->whereNotNull('ends_at')
                    ->whereBetween('ends_at', [now(), now()->addDays((int) $days)]);
            });

        $licenses = $query->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString()
            ->through(fn (License $l) => [
                'id' => $l->id,
                'license_key' => $l->license_key,
                'type' => $l->type,
                'status' => $l->status,
                'starts_at' => $l->starts_at?->format('d/m/Y'),
                'ends_at' => $l->ends_at?->format('d/m/Y'),
                'ends_at_iso' => $l->ends_at?->toISOString(),
                'days_remaining' => $l->daysRemaining(),
                'user' => $l->user?->only(['id', 'name', 'email']),
                'plan' => $l->plan?->only(['id', 'code', 'name']),
                'order_number' => $l->order?->order_number,
                'metadata' => $l->metadata,
            ]);

        $provisional = License::with(['user:id,name,email', 'plan:id,code,name'])
            ->where('status', 'provisional')
            ->orderBy('ends_at')
            ->get()
            ->map(fn (License $l) => [
                'id' => $l->id,
                'license_key' => $l->license_key,
                'ends_at' => $l->ends_at?->format('d/m/Y H:i'),
                'ends_at_iso' => $l->ends_at?->toISOString(),
                'days_remaining' => $l->daysRemaining(),
                'user' => $l->user?->only(['id', 'name', 'email']),
                'plan' => $l->plan?->only(['code', 'name']),
                'motif' => $l->metadata['provisional_reason'] ?? null,
            ]);

        $stats = [
            'active' => License::whereIn('status', ['active', 'grace_period'])->count(),
            'trials' => License::where('status', 'trial')->count(),
            'provisional' => License::where('status', 'provisional')->count(),
            'suspended' => License::where('status', 'suspended')->count(),
            'expiring_30d' => License::whereIn('status', ['trial', 'provisional', 'active', 'grace_period'])
                ->whereNotNull('ends_at')
                ->whereBetween('ends_at', [now(), now()->addDays(30)])
                ->count(),
        ];

        return Inertia::render('Admin/LicenseManager', [
            'licenses' => $licenses,
            'provisional' => $provisional,
            'stats' => $stats,
            'plans' => Plan::orderBy('sort_order')->get(['id', 'code', 'name']),
            'statuses' => License::STATUSES,
            'filters' => $request->only(['status', 'plan_code', 'search', 'expiring']),
        ]);
    }

    /** Convertit une licence provisoire en définitive. */
    public function confirmProvisional(Request $request, License $license): RedirectResponse
    {
        $data = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        if ($license->status !== 'provisional') {
            throw ValidationException::withMessages([
                'reason' => "Cette licence n'est pas en statut provisoire.",
            ]);
        }

        $transaction = PaymentTransaction::where('id', $license->transaction_id)->first()
            ?? PaymentTransaction::where('order_id', $license->order_id)->latest()->first();

        if (! $transaction) {
            throw ValidationException::withMessages([
                'reason' => 'Aucune transaction associée à cette licence.',
            ]);
        }

        app(LicenseService::class)->confirmProvisional($license, $transaction, $request->user());

        PaymentAuditLog::record(
            'provisional_confirmed',
            'license',
            $license->id,
            ['status' => 'provisional'],
            ['status' => 'active'],
            $data['reason'],
            $request->user()->id,
        );

        return redirect()->route('admin.licenses.index')->with('success', 'Licence provisoire convertie en licence active.');
    }

    /** Une licence révoquée ne peut plus être modifiée (jamais supprimée). */
    private function guardNotRevoked(License $license): void
    {
        if ($license->status === 'revoked') {
            throw ValidationException::withMessages([
                'reason' => 'Licence révoquée : aucune action possible.',
            ]);
        }
    }

    /** Photographie l'état sensible d'une licence pour le journal d'audit. */
    private function snapshot(License $license): array
    {
        return [
            'status' => $license->status,
            'type' => $license->type,
            'ends_at' => $license->ends_at?->toDateTimeString(),
            'grace_period_ends_at' => $license->grace_period_ends_at?->toDateTimeString(),
        ];
    }
}

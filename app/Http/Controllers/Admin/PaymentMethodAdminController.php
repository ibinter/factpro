<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentAuditLog;
use App\Models\PaymentMethodConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Console Superadmin — configuration des moyens de paiement manuels (script §16.3).
 */
class PaymentMethodAdminController extends Controller
{
    private const TYPES = ['mobile_money', 'bank_national', 'bank_international', 'transfer_service'];

    public function index(Request $request): Response
    {
        $methods = PaymentMethodConfig::orderBy('sort_order')
            ->orderBy('label')
            ->get()
            ->map(fn (PaymentMethodConfig $m) => $this->present($m));

        return Inertia::render('Admin/Methods', [
            'methods' => $methods,
            'types' => self::TYPES,
        ]);
    }

    /** Vue de configuration étendue des méthodes de paiement (PaymentMethodSettings.vue). */
    public function settings(Request $request): Response
    {
        $methods = PaymentMethodConfig::orderBy('sort_order')
            ->orderBy('label')
            ->get()
            ->map(fn (PaymentMethodConfig $m) => $this->present($m));

        $grouped = [
            'mobile_money' => $methods->where('type', 'mobile_money')->values(),
            'bank_national' => $methods->where('type', 'bank_national')->values(),
            'bank_international' => $methods->where('type', 'bank_international')->values(),
            'transfer_service' => $methods->where('type', 'transfer_service')->values(),
            'cash' => $methods->where('type', 'cash')->values(),
        ];

        return Inertia::render('Admin/PaymentMethodSettings', [
            'methods' => $methods,
            'grouped' => $grouped,
            'types' => self::TYPES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $method = PaymentMethodConfig::create($data);

        PaymentAuditLog::record(
            'payment_method_created',
            'config',
            (string) $method->id,
            null,
            $method->only(array_keys($data)),
            null,
            $request->user()->id,
        );

        return redirect()->route('admin.payment-methods.index')->with('success', "Moyen de paiement « {$method->label} » ajouté.");
    }

    public function update(Request $request, PaymentMethodConfig $method): RedirectResponse
    {
        $data = $this->validated($request);

        $old = $method->only(array_keys($data));
        $method->update($data);

        PaymentAuditLog::record(
            'payment_method_updated',
            'config',
            (string) $method->id,
            $old,
            $method->fresh()->only(array_keys($data)),
            null,
            $request->user()->id,
        );

        return redirect()->route('admin.payment-methods.index')->with('success', "Moyen de paiement « {$method->label} » mis à jour.");
    }

    public function destroy(Request $request, PaymentMethodConfig $method): RedirectResponse
    {
        $old = $this->present($method);
        $label = $method->label;

        $method->delete();

        PaymentAuditLog::record(
            'payment_method_deleted',
            'config',
            (string) $old['id'],
            $old,
            null,
            null,
            $request->user()->id,
        );

        return redirect()->route('admin.payment-methods.index')->with('success', "Moyen de paiement « {$label} » supprimé.");
    }

    public function toggle(Request $request, PaymentMethodConfig $method): RedirectResponse
    {
        $old = ['is_active' => $method->is_active];
        $method->update(['is_active' => ! $method->is_active]);

        PaymentAuditLog::record(
            'payment_method_toggled',
            'config',
            (string) $method->id,
            $old,
            ['is_active' => $method->is_active],
            null,
            $request->user()->id,
        );

        return redirect()->route('admin.payment-methods.index')->with('success', $method->is_active
            ? "« {$method->label} » activé."
            : "« {$method->label} » désactivé.");
    }

    /** Règles de validation communes (création + mise à jour). */
    private function validated(Request $request): array
    {
        return $request->validate([
            'type' => ['required', 'string', 'in:'.implode(',', self::TYPES)],
            'label' => ['required', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'size:2'],
            'operator' => ['nullable', 'string', 'max:60'],
            'account_number' => ['nullable', 'string', 'max:255'],
            'account_holder' => ['nullable', 'string', 'max:255'],
            'iban' => ['nullable', 'string', 'max:40'],
            'swift_bic' => ['nullable', 'string', 'max:15'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'currency' => ['required', 'string', 'size:3'],
            'instructions' => ['nullable', 'string', 'max:1000'],
            'min_amount' => ['nullable', 'numeric', 'min:0'],
            'max_amount' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:255'],
        ]);
    }

    private function present(PaymentMethodConfig $m): array
    {
        return [
            'id' => $m->id,
            'type' => $m->type,
            'country' => $m->country,
            'label' => $m->label,
            'operator' => $m->operator,
            'account_number' => $m->account_number,
            'account_holder' => $m->account_holder,
            'iban' => $m->iban,
            'swift_bic' => $m->swift_bic,
            'bank_name' => $m->bank_name,
            'currency' => $m->currency,
            'instructions' => $m->instructions,
            'min_amount' => $m->min_amount !== null ? (float) $m->min_amount : null,
            'max_amount' => $m->max_amount !== null ? (float) $m->max_amount : null,
            'is_active' => (bool) $m->is_active,
            'sort_order' => (int) $m->sort_order,
        ];
    }
}

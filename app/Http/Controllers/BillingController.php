<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\CryptoWallet;
use App\Models\Order;
use App\Models\PaymentMethodConfig;
use App\Models\PaymentProof;
use App\Models\PaymentTransaction;
use App\Models\Plan;
use App\Services\CouponService;
use App\Services\DeliveryPaymentService;
use App\Services\LicenseService;
use App\Services\PaymentService;
use App\Services\VoucherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Espace client Â« Abonnement & Facturation Â» (script Â§15).
 */
class BillingController extends Controller
{
    public function __construct(
        private PaymentService $payments,
        private LicenseService $licenses,
        private CouponService $coupons,
    ) {
    }

    /** Page des forfaits (comparatif â€” cahier Â§22). */
    public function plans(Request $request): Response
    {
        $rates = config('factpro.exchange_rates_xof');

        $plans = Plan::where('is_active', true)->orderBy('sort_order')->get()
            ->map(fn (Plan $plan) => [
                'id' => $plan->id,
                'code' => $plan->code,
                'name' => $plan->name,
                'short_description' => $plan->short_description,
                'price_monthly' => (float) $plan->price_monthly,
                'price_yearly' => $plan->priceFor(12),
                'price_eur' => round((float) $plan->price_monthly / $rates['EUR'], 2),
                'price_usd' => round((float) $plan->price_monthly / $rates['USD'], 2),
                'currency' => $plan->currency,
                'features' => $plan->features,
                'limits' => $plan->limits,
            ]);

        $license = $this->licenses->currentFor($request->user());

        return Inertia::render('Billing/Plans', [
            'plans' => $plans,
            'currentPlanCode' => $license?->plan?->code,
            'isTrial' => (bool) $license?->isTrial(),
        ]);
    }

    /** CrÃ©e la commande et redirige vers la page de paiement (script Â§2). */
    public function subscribe(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'plan_code' => 'required|exists:plans,code',
            'months'    => ['required', Rule::in([1, 3, 6, 12])],
            'org_type'  => ['nullable', 'string', Rule::in(['ong', 'school'])],
        ]);

        $plan = Plan::where('code', $data['plan_code'])->firstOrFail();

        $order = $this->payments->createOrder(
            $request->user(),
            $plan,
            (int) $data['months'],
            $request->user()->country ?? 'CI',
        );

        // Offres spÃ©ciales ONG / Ã‰cole : applique le coupon systÃ¨me automatiquement
        if (! empty($data['org_type'])) {
            $systemCouponCode = match ($data['org_type']) {
                'ong'    => 'ONG50',
                'school' => 'SCHOOL40',
                default  => null,
            };

            if ($systemCouponCode) {
                $order->loadMissing('plan');
                $result = $this->coupons->validateFor(
                    $systemCouponCode,
                    $request->user(),
                    $order->plan,
                    (float) $order->amount,
                );

                if ($result['valid']) {
                    $this->coupons->redeem($result['coupon'], $request->user(), $order);
                    $order->update([
                        'discount_amount' => $result['discount'],
                        'total_amount'    => round((float) $order->amount - $result['discount'], 2),
                        'metadata'        => array_merge($order->metadata ?? [], [
                            'coupon_code' => $result['coupon']->code,
                        ]),
                    ]);
                }
            }
        }

        // Programme ambassadeur : rÃ©compense le parrain si applicable
        app(\App\Services\ReferralService::class)->rewardReferrer($request->user());

        return redirect()->route('billing.checkout', $order);
    }

    /** Page de paiement : rÃ©capitulatif + cartes de moyens de paiement (script Â§5). */
    public function checkout(Request $request, Order $order): Response|RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        if ($order->status === 'paid') {
            return redirect()->route('billing.index')->with('success', 'Cette commande est dÃ©jÃ  payÃ©e.');
        }

        $methods = PaymentMethodConfig::where('is_active', true)
            ->where(fn ($q) => $q->whereNull('country')->orWhere('country', $order->country))
            ->orderBy('sort_order')
            ->get();

        $couponCode = $order->metadata['coupon_code'] ?? null;

        $activeWallets = CryptoWallet::active()->orderBy('display_order')->get();

        return Inertia::render('Billing/Checkout', [
            'order' => $order->load('plan:id,code,name'),
            'manualMethods' => $methods,
            'monerooEnabled' => (bool) config('factpro.moneroo.secret_key'),
            'appliedCoupon' => $couponCode ? [
                'code' => $couponCode,
                'discount' => (float) $order->discount_amount,
            ] : null,
            'activeWallets' => $activeWallets,
            'codEnabled' => (bool) config('services.cod.enabled', false),
        ]);
    }

    /**
     * Applique un code promo Ã  une commande et recalcule le total (cahier Â§22.2).
     *
     * Choix d'implÃ©mentation : la redemption est enregistrÃ©e dÃ¨s l'application du
     * code (la commande n'est pas encore payÃ©e). C'est acceptable pour ce module
     * self-contained ; removeCoupon() l'annule proprement si le code est retirÃ©.
     */
    public function applyCoupon(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        if (! $order->isPayable()) {
            return back()->with('error', 'Cette commande ne peut plus Ãªtre modifiÃ©e.');
        }

        $data = $request->validate([
            'code' => 'required|string|max:50',
        ]);

        $order->loadMissing('plan');

        $result = $this->coupons->validateFor(
            $data['code'],
            $request->user(),
            $order->plan,
            (float) $order->amount,
        );

        if (! $result['valid']) {
            return back()->with('error', $result['message']);
        }

        // Retire un Ã©ventuel coupon dÃ©jÃ  appliquÃ© avant d'en poser un nouveau.
        $this->clearCoupon($order);

        $coupon = $result['coupon'];
        $discount = $result['discount'];

        $this->coupons->redeem($coupon, $request->user(), $order);

        $order->update([
            'discount_amount' => $discount,
            'total_amount' => round((float) $order->amount - $discount, 2),
            'metadata' => array_merge($order->metadata ?? [], ['coupon_code' => $coupon->code]),
        ]);

        return redirect()->route('billing.index')->with('success', "Code promo Â« {$coupon->code} Â» appliquÃ©.");
    }

    /** Retire le code promo appliquÃ© et restaure le montant d'origine. */
    public function removeCoupon(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        if (! $order->isPayable()) {
            return back()->with('error', 'Cette commande ne peut plus Ãªtre modifiÃ©e.');
        }

        $this->clearCoupon($order);

        $order->update([
            'discount_amount' => 0,
            'total_amount' => (float) $order->amount,
            'metadata' => array_diff_key($order->metadata ?? [], ['coupon_code' => null]),
        ]);

        return redirect()->route('billing.index')->with('success', 'Code promo retirÃ©.');
    }

    /** Annule la redemption liÃ©e au coupon actuellement posÃ© sur la commande. */
    private function clearCoupon(Order $order): void
    {
        $current = $order->metadata['coupon_code'] ?? null;

        if (! $current) {
            return;
        }

        $coupon = Coupon::whereRaw('UPPER(code) = ?', [strtoupper($current)])->first();

        if ($coupon) {
            $this->coupons->cancelForOrder($coupon, $order);
        }
    }

    /** DÃ©claration d'un paiement manuel avec preuve (script Â§4.2). */
    public function submitProof(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        if (! $order->isPayable() && $order->status !== 'proof_submitted') {
            return back()->with('error', 'Cette commande ne peut plus recevoir de paiement.');
        }

        $maxKb = config('factpro.proofs.max_size_mb', 10) * 1024;

        $data = $request->validate([
            'provider' => 'required|in:orange_money,mtn_momo,wave,moov,bank_transfer_national,bank_transfer_international,international_transfer,transfer_service,cash',
            'sender_name' => 'required|string|max:255',
            'sender_number' => 'nullable|string|max:40',
            'provider_reference' => 'nullable|string|max:100',
            'amount_declared' => 'required|numeric|min:1',
            'sender_country' => 'nullable|string|max:100',
            'sender_city' => 'nullable|string|max:100',
            'transfer_service' => 'nullable|string|max:100',
            'comment' => 'nullable|string|max:500',
            'proof' => "nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:{$maxKb}",
        ]);

        if ($data['provider'] !== 'cash') {
            $request->validate([
                'proof' => "required|file|mimes:jpg,jpeg,png,webp,pdf|max:{$maxKb}",
            ]);
        }

        $this->payments->submitManualPayment($order, $data['provider'], $data, $request->file('proof'));

        return redirect()->route('billing.proof-status', $order->id)
            ->with('success', 'Votre dÃ©claration a Ã©tÃ© reÃ§ue et sera vÃ©rifiÃ©e par notre Ã©quipe.');
    }

    /** Page de suivi de preuve aprÃ¨s soumission. */
    public function proofStatus(Request $request, Order $order): Response
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        $order->load(['plan:id,code,name', 'transactions' => function ($q) {
            $q->orderByDesc('created_at')->with('proofs');
        }]);

        return Inertia::render('Billing/ProofStatus', [
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'total_amount' => (float) $order->total_amount,
                'currency' => $order->currency,
                'plan' => $order->plan?->name,
                'duration_months' => $order->duration_months,
            ],
            'transactions' => $order->transactions->map(fn ($t) => [
                'id' => $t->id,
                'status' => $t->status,
                'payment_provider' => $t->payment_provider,
                'internal_reference' => $t->internal_reference,
                'amount_declared' => (float) $t->amount_declared,
                'created_at' => $t->created_at->format('d/m/Y H:i'),
                'proofs_count' => $t->proofs->count(),
            ]),
        ]);
    }

    /** TÃ©lÃ©chargement reÃ§u pour commande payÃ©e. */
    public function downloadReceipt(Request $request, Order $order): HttpResponse|RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        if ($order->status !== 'paid') {
            return redirect()->route('billing.index')
                ->with('error', 'Le reÃ§u n\'est disponible que pour les commandes payÃ©es.');
        }

        $order->load('plan');
        $content = "REÃ‡U DE PAIEMENT\n\nCommande : {$order->order_number}\nForfait : {$order->plan?->name}\nMontant : {$order->total_amount} {$order->currency}\nDate : {$order->paid_at}\n";

        return response($content, 200, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => 'attachment; filename="recu-' . $order->order_number . '.txt"',
        ]);
    }

    /** Ajoute un complÃ©ment de preuve (documents supplÃ©mentaires). */
    public function addComplement(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        if (! in_array($order->status, ['proof_submitted', 'under_review', 'missing_info'])) {
            return back()->with('error', 'Impossible d\'ajouter un complÃ©ment dans l\'Ã©tat actuel de la commande.');
        }

        $maxKb = config('factpro.proofs.max_size_mb', 10) * 1024;

        $data = $request->validate([
            'comment' => 'nullable|string|max:1000',
            'proof' => "required|file|mimes:jpg,jpeg,png,webp,pdf|max:{$maxKb}",
        ]);

        $transaction = $order->transactions()->orderByDesc('created_at')->firstOrFail();

        $disk = config('factpro.proofs.disk', 'local');
        $file = $request->file('proof');
        $uuid = (string) Str::uuid();
        $ext = $file->getClientOriginalExtension();

        $file->storeAs('private/proofs', "{$uuid}.{$ext}", $disk);

        $storedName = "{$uuid}.{$ext}";
        PaymentProof::create([
            'transaction_id'      => $transaction->id,
            'uploaded_by'         => $request->user()->id,
            'file_path'           => "private/proofs/{$storedName}",
            'original_filename'   => $file->getClientOriginalName(),
            'stored_filename'     => $storedName,
            'file_hash'           => hash_file('sha256', $file->getRealPath()),
            'file_size'           => $file->getSize(),
            'mime_type'           => $file->getMimeType(),
            'verification_status' => 'pending',
            'internal_comment'    => $data['comment'] ?? null,
        ]);

        if ($order->status === 'missing_info') {
            $order->update(['status' => 'under_review']);
        }

        return redirect()->route('billing.index')->with('success', 'Votre complÃ©ment a Ã©tÃ© ajoutÃ© et sera examinÃ© par notre Ã©quipe.');
    }

    /** DÃ©claration de paiement par chÃ¨que bancaire. */
    public function initiateCheque(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'order_id'        => 'required|exists:orders,id',
            'cheque_number'   => 'required|string|max:50',
            'issuing_bank'    => 'required|string|max:100',
            'account_holder'  => 'required|string|max:100',
            'declared_amount' => 'required|numeric|min:0',
            'cheque_date'     => 'required|date',
            'comment'         => 'nullable|string|max:500',
            'proof'           => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $order = Order::findOrFail($data['order_id']);

        abort_unless($order->user_id === $request->user()->id, 403);

        if (! $order->isPayable() && $order->status !== 'proof_submitted') {
            return back()->with('error', 'Cette commande ne peut plus recevoir de paiement.');
        }

        $this->payments->submitManualPayment($order, 'cheque', [
            'amount_declared'    => $data['declared_amount'],
            'provider_reference' => $data['cheque_number'],
            'sender_name'        => $data['account_holder'],
            'sender_number'      => null,
            'cheque_number'      => $data['cheque_number'],
            'issuing_bank'       => $data['issuing_bank'],
            'account_holder'     => $data['account_holder'],
            'cheque_date'        => $data['cheque_date'],
            'comment'            => $data['comment'] ?? null,
        ], $request->hasFile('proof') ? $request->file('proof') : null);

        return redirect()->route('billing.proof-status', $order)
            ->with('success', 'Votre dÃ©claration de chÃ¨que a Ã©tÃ© enregistrÃ©e. Nous vous contacterons aprÃ¨s encaissement.');
    }

    /** DÃ©claration d'un paiement en cryptomonnaie (tx hash + preuve optionnelle). */
    public function initiateCrypto(Request $request): RedirectResponse
    {
        $request->validate([
            'order_id'        => 'required|exists:orders,id',
            'wallet_id'       => 'required|exists:crypto_wallets,id',
            'tx_hash'         => 'required|string|max:200',
            'declared_amount' => 'required|numeric|min:0',
            'tx_date'         => 'required|date',
            'proof'           => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $order = Order::findOrFail($request->order_id);

        abort_unless($order->user_id === $request->user()->id, 403);

        $wallet = CryptoWallet::findOrFail($request->wallet_id);

        // Anti-doublon : rejeter un hash dÃ©jÃ  soumis et non rejetÃ©
        $exists = PaymentTransaction::where('provider_reference', $request->tx_hash)
            ->whereIn('status', ['pending', 'under_review', 'manually_validated', 'succeeded'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['tx_hash' => 'Ce hash de transaction a dÃ©jÃ  Ã©tÃ© soumis.']);
        }

        $proofFile = $request->hasFile('proof') ? $request->file('proof') : null;

        $transaction = $this->payments->submitManualPayment(
            $order,
            'crypto',
            [
                'provider_reference' => $request->tx_hash,
                'amount_declared'    => $request->declared_amount,
                'sender_name'        => $request->user()->name,
            ],
            $proofFile,
        );

        // Stocke les dÃ©tails crypto dans la colonne metadata
        $transaction->update([
            'metadata' => [
                'wallet_id'      => $wallet->id,
                'currency'       => $wallet->currency,
                'network'        => $wallet->network,
                'wallet_address' => $wallet->wallet_address,
                'tx_hash'        => $request->tx_hash,
                'tx_date'        => $request->tx_date,
            ],
        ]);

        return redirect()->route('billing.proof-status', $order)
            ->with('success', 'Votre transaction crypto a Ã©tÃ© soumise. VÃ©rification en cours.');
    }

    // â”€â”€ Voucher / Code prÃ©payÃ© â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    /**
     * VÃ©rifie un code prÃ©payÃ© sans l'activer â€” retourne les infos du code.
     */
    public function verifyVoucher(Request $request): JsonResponse
    {
        $request->validate(['code' => 'required|string|max:30']);
        $result = app(VoucherService::class)->verify($request->code);

        return response()->json($result);
    }

    /**
     * Active un code prÃ©payÃ© â†’ licence instantanÃ©e.
     */
    public function redeemVoucher(Request $request): RedirectResponse
    {
        $request->validate(['code' => 'required|string|max:30']);

        try {
            $user    = auth()->user();
            $company = $user->currentCompany ?? $user->companies()->first();

            abort_unless($company !== null, 403, 'Aucune sociÃ©tÃ© associÃ©e Ã  ce compte.');

            app(VoucherService::class)->redeem($request->code, $company->id, $user->id);

            return redirect()->route('billing.index')
                ->with('success', 'Licence activÃ©e ! Bon dÃ©marrage avec IBIG FactPro.');
        } catch (\RuntimeException $e) {
            return back()->withErrors(['code' => $e->getMessage()]);
        }
    }

    /** Tableau de bord abonnement + historique (script Â§15). */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $license = $this->licenses->currentFor($user);

        $orders = Order::where('user_id', $user->id)
            ->with(['plan:id,code,name', 'transactions:id,order_id,status,payment_provider,internal_reference,amount_declared,created_at'])
            ->orderByDesc('created_at')
            ->paginate(10);

        return Inertia::render('Billing/Index', [
            'license' => $license ? [
                'key' => $license->license_key,
                'plan' => $license->plan?->name,
                'type' => $license->type,
                'status' => $license->status,
                'starts_at' => $license->starts_at?->format('d/m/Y'),
                'ends_at' => $license->ends_at?->format('d/m/Y'),
                'days_remaining' => $license->daysRemaining(),
                'limits' => $license->limits,
            ] : null,
            'orders' => $orders,
        ]);
    }

    // â”€â”€ Paiement Ã  la livraison (COD) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    /** Le client soumet une commande avec livraison physique. */
    public function initiateDelivery(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'order_id'     => 'required|exists:orders,id',
            'contact_name' => 'required|string|max:150',
            'phone'        => 'required|string|max:30',
            'address'      => 'required|string|max:300',
            'city'         => 'required|string|max:100',
            'country'      => 'nullable|string|max:10',
            'notes'        => 'nullable|string|max:300',
        ]);

        $order = Order::findOrFail($data['order_id']);
        abort_unless($order->user_id === $request->user()->id, 403);

        if (! $order->isPayable()) {
            return back()->with('error', 'Cette commande ne peut plus recevoir de paiement.');
        }

        app(DeliveryPaymentService::class)->createDeliveryOrder($order, $data);

        return redirect()->route('billing.delivery-status', $order)
            ->with('success', 'Votre commande de livraison a Ã©tÃ© enregistrÃ©e. Un agent vous contactera sous 24h.');
    }

    /** Page de suivi de la livraison. */
    public function deliveryStatus(Request $request, Order $order): Response
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        $delivery = $order->deliveryOrder()->with('agent')->first();

        return Inertia::render('Billing/DeliveryStatus', [
            'order' => [
                'id'             => $order->id,
                'order_number'   => $order->order_number,
                'status'         => $order->status,
                'total_amount'   => (float) $order->total_amount,
                'currency'       => $order->currency,
                'plan'           => $order->plan?->name,
            ],
            'delivery' => $delivery ? [
                'id'                   => $delivery->id,
                'status'               => $delivery->status,
                'cod_amount'           => (float) $delivery->cod_amount,
                'cod_currency'         => $delivery->cod_currency,
                'contact_name'         => $delivery->contact_name,
                'contact_phone'        => $delivery->contact_phone,
                'delivery_address'     => $delivery->delivery_address,
                'delivery_city'        => $delivery->delivery_city,
                'delivery_country'     => $delivery->delivery_country,
                'delivery_notes'       => $delivery->delivery_notes,
                'assigned_at'          => $delivery->assigned_at?->format('d/m/Y H:i'),
                'payment_confirmed_at' => $delivery->payment_confirmed_at?->format('d/m/Y H:i'),
                'agent'                => $delivery->agent ? [
                    'name'  => $delivery->agent->name,
                    'phone' => $delivery->agent->phone,
                ] : null,
            ] : null,
        ]);
    }
}


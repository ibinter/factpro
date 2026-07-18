<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed, reactive } from 'vue';

const props = defineProps({
    order: Object,
    manualMethods: Array,
    monerooEnabled: Boolean,
    appliedCoupon: Object,
    activeWallets: { type: Array, default: () => [] },
    codEnabled: { type: Boolean, default: false },
});

const fmt = (n) => new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(n ?? 0);

// Active accordion panel: null | 'moneroo' | 'mobile_money' | 'bank' | 'international' | 'cash'
const activePanel = ref(null);
const toggle = (panel) => { activePanel.value = activePanel.value === panel ? null : panel; };

// ── Mobile Money ──────────────────────────────────────────────────────────────
const mobileOperators = [
    { value: 'wave', label: 'Wave', color: 'bg-sky-500' },
    { value: 'orange_money', label: 'Orange Money', color: 'bg-orange-500' },
    { value: 'mtn_momo', label: 'MTN MoMo', color: 'bg-yellow-400' },
    { value: 'moov', label: 'Moov Money', color: 'bg-blue-700' },
];
const selectedOperator = ref(null);

const mobileForm = useForm({
    provider: null,
    sender_name: '',
    sender_number: '',
    provider_reference: '',
    amount_declared: Number(props.order.total_amount),
    proof: null,
});

const selectOperator = (op) => {
    selectedOperator.value = op;
    mobileForm.provider = op.value;
};

const submitMobile = () => {
    mobileForm.post(route('billing.proof', props.order.id), { forceFormData: true });
};

// Coordonnées opérateurs depuis PaymentMethodConfig
const configFor = (operatorValue) =>
    (props.manualMethods ?? []).filter((m) =>
        m.type === 'mobile_money' && (m.operator ?? '').toLowerCase().includes(operatorValue.split('_')[0]),
    );

// ── Virement bancaire national ─────────────────────────────────────────────
const bankConfigs = computed(() =>
    (props.manualMethods ?? []).filter((m) => m.type === 'bank_transfer_national' || m.type?.startsWith('bank')),
);

const bankForm = useForm({
    provider: 'bank_transfer_national',
    sender_name: '',
    sender_number: '',
    provider_reference: '',
    amount_declared: Number(props.order.total_amount),
    proof: null,
});

const submitBank = () => {
    bankForm.post(route('billing.proof', props.order.id), { forceFormData: true });
};

// ── Transfert international ───────────────────────────────────────────────
const intlServices = ['Western Union', 'MoneyGram', 'Ria', 'Sendwave', 'WorldRemit', 'SWIFT', 'Autre'];
const intlForm = useForm({
    provider: 'international_transfer',
    sender_name: '',
    sender_country: '',
    sender_city: '',
    transfer_service: '',
    amount_declared: Number(props.order.total_amount),
    provider_reference: '',
    comment: '',
    proof: null,
});

const submitIntl = () => {
    intlForm.post(route('billing.proof', props.order.id), { forceFormData: true });
};

// ── Espèces ───────────────────────────────────────────────────────────────
const cashForm = useForm({
    provider: 'cash',
    sender_name: '',
    amount_declared: Number(props.order.total_amount),
    comment: '',
    proof: null,
});

const submitCash = () => {
    cashForm.post(route('billing.proof', props.order.id), { forceFormData: true });
};

// ── Chèque bancaire ───────────────────────────────────────────────────────
const chequeMethod = computed(() =>
    (props.manualMethods ?? []).find((m) => m.type === 'cheque'),
);

const chequeForm = useForm({
    order_id:        props.order.id,
    cheque_number:   '',
    issuing_bank:    '',
    account_holder:  '',
    declared_amount: Number(props.order.total_amount),
    cheque_date:     '',
    comment:         '',
    proof:           null,
});

const submitCheque = () => {
    chequeForm.post(route('billing.initiate.cheque'), { forceFormData: true });
};

// ── Cryptomonnaie ────────────────────────────────────────────────────────
const selectedWallet = ref(null);
const cryptoSubmitting = ref(false);
const cryptoForm = ref({
    tx_hash: '',
    declared_amount: Number(props.order.total_amount),
    tx_date: '',
    proof: null,
});

const copyAddress = (address) => {
    navigator.clipboard?.writeText(address);
};

const submitCrypto = () => {
    if (!selectedWallet.value) return;
    cryptoSubmitting.value = true;

    const fd = new FormData();
    fd.append('order_id', props.order.id);
    fd.append('wallet_id', selectedWallet.value.id);
    fd.append('tx_hash', cryptoForm.value.tx_hash);
    fd.append('declared_amount', cryptoForm.value.declared_amount);
    fd.append('tx_date', cryptoForm.value.tx_date);
    if (cryptoForm.value.proof) fd.append('proof', cryptoForm.value.proof);

    router.post(route('billing.initiate.crypto'), fd, {
        forceFormData: true,
        onFinish: () => { cryptoSubmitting.value = false; },
    });
};

// ── Moneroo ───────────────────────────────────────────────────────────────
const monerooProcessing = ref(false);
const payWithMoneroo = () => {
    monerooProcessing.value = true;
    router.post(route('billing.moneroo.initiate', props.order.id), {}, {
        onFinish: () => { monerooProcessing.value = false; },
    });
};

// ── Voucher / Code prépayé ────────────────────────────────────────────────
const voucherCode = ref('');
const verifying = ref(false);
const submitting = ref(false);
const voucherResult = ref(null);

const verifyVoucher = async () => {
    verifying.value = true;
    voucherResult.value = null;
    try {
        const res = await fetch(route('billing.voucher.verify'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '' },
            body: JSON.stringify({ code: voucherCode.value }),
        });
        voucherResult.value = await res.json();
    } catch {
        voucherResult.value = { valid: false, error: 'Erreur de vérification. Réessayez.' };
    } finally {
        verifying.value = false;
    }
};

const redeemVoucher = () => {
    submitting.value = true;
    router.post(route('billing.voucher.redeem'), { code: voucherCode.value }, {
        onFinish: () => { submitting.value = false; },
    });
};

// ── Coupon ────────────────────────────────────────────────────────────────
const couponForm = useForm({ code: '' });
const applyCoupon = () => {
    couponForm.post(route('billing.coupon.apply', props.order.id), {
        preserveScroll: true,
        onSuccess: () => (couponForm.code = ''),
    });
};
const removeCoupon = () => {
    router.delete(route('billing.coupon.remove', props.order.id), { preserveScroll: true });
};

// ── Paiement à la livraison (COD) ────────────────────────────────────────
const codForm = useForm({
    order_id:     props.order.id,
    contact_name: '',
    phone:        '',
    address:      '',
    city:         '',
    country:      'CI',
    notes:        '',
});

const submitCod = () => {
    codForm.post(route('billing.initiate.delivery'));
};
</script>

<template>
    <Head title="Paiement" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Paiement sécurisé — <span class="text-brand-600">{{ order.order_number }}</span>
            </h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-3xl space-y-5 px-4 sm:px-6 lg:px-8">

                <!-- Récapitulatif -->
                <div class="rounded-xl bg-gradient-to-r from-brand-900 to-brand-700 p-6 text-white shadow">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <div class="text-sm opacity-75">Forfait {{ order.plan?.name }} — {{ order.duration_months }} mois</div>
                            <div class="text-3xl font-extrabold">
                                {{ fmt(order.total_amount) }}
                                <span class="text-lg font-normal">{{ order.currency }}</span>
                            </div>
                            <div v-if="appliedCoupon" class="mt-1 text-sm text-green-300">
                                Remise appliquée : −{{ fmt(appliedCoupon.discount) }} {{ order.currency }}
                            </div>
                        </div>
                        <div class="text-right text-xs opacity-75">
                            Commande valable jusqu'au<br>
                            <b>{{ new Date(order.expires_at).toLocaleString('fr-FR') }}</b>
                        </div>
                    </div>
                </div>

                <!-- Code promo -->
                <div class="rounded-lg bg-white p-5 shadow">
                    <h3 class="mb-3 font-semibold text-gray-800">🎟 Code promo</h3>
                    <div v-if="appliedCoupon" class="flex flex-wrap items-center justify-between gap-3 rounded-md bg-green-50 px-4 py-3">
                        <div class="text-sm text-green-800">
                            Code <b class="font-mono">{{ appliedCoupon.code }}</b> appliqué :
                            <b>−{{ fmt(appliedCoupon.discount) }} {{ order.currency }}</b>
                        </div>
                        <button @click="removeCoupon" class="text-sm font-semibold text-red-600 hover:underline">Retirer</button>
                    </div>
                    <form v-else @submit.prevent="applyCoupon" class="flex flex-wrap items-end gap-3">
                        <div class="flex-1">
                            <InputLabel value="Vous avez un code promo ?" />
                            <TextInput v-model="couponForm.code" type="text" class="mt-1 block w-full font-mono uppercase" placeholder="IBIGSTART" />
                            <InputError :message="couponForm.errors.code" class="mt-1" />
                        </div>
                        <PrimaryButton :disabled="couponForm.processing || !couponForm.code" @click="applyCoupon">
                            Appliquer
                        </PrimaryButton>
                    </form>
                    <p v-if="$page.props.flash?.error" class="mt-2 text-xs text-red-600">{{ $page.props.flash.error }}</p>
                </div>

                <!-- Alerte sécurité -->
                <div class="rounded-md bg-amber-50 px-4 py-3 text-xs text-amber-800">
                    🔒 IBIG ne vous demandera <b>jamais</b> votre code secret Mobile Money, votre mot de passe ou votre code confidentiel.
                </div>

                <!-- ═══════ MÉTHODE 1 : Moneroo en ligne ═══════ -->
                <div v-if="monerooEnabled" class="overflow-hidden rounded-xl border-2 border-brand-600 bg-white shadow-sm">
                    <button
                        @click="toggle('moneroo')"
                        class="flex w-full items-center justify-between gap-4 p-5 text-left"
                    >
                        <div class="flex items-center gap-4">
                            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-brand-600 text-xl text-white flex-shrink-0">💳</span>
                            <div>
                                <div class="font-semibold text-gray-800">Payer en ligne (Moneroo)</div>
                                <div class="text-xs text-gray-500">CB, Mobile Money, Wallets · <span class="text-green-600 font-semibold">Activation automatique immédiate</span></div>
                            </div>
                        </div>
                        <span class="text-gray-400 text-lg">{{ activePanel === 'moneroo' ? '▲' : '▼' }}</span>
                    </button>
                    <div v-show="activePanel === 'moneroo'" class="border-t bg-gray-50 p-5">
                        <p class="mb-4 text-sm text-gray-600">
                            Vous serez redirigé vers Moneroo, notre partenaire de paiement sécurisé, puis ramené automatiquement.
                        </p>
                        <PrimaryButton :disabled="monerooProcessing" @click="payWithMoneroo" class="w-full justify-center">
                            {{ monerooProcessing ? 'Redirection…' : `Payer maintenant — ${fmt(order.total_amount)} ${order.currency}` }}
                        </PrimaryButton>
                    </div>
                </div>

                <!-- ═══════ MÉTHODE 2 : Mobile Money manuel ═══════ -->
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                    <button @click="toggle('mobile_money')" class="flex w-full items-center justify-between gap-4 p-5 text-left hover:bg-gray-50">
                        <div class="flex items-center gap-4">
                            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-sky-100 text-xl flex-shrink-0">📱</span>
                            <div>
                                <div class="font-semibold text-gray-800">Mobile Money manuel</div>
                                <div class="text-xs text-gray-500">Wave, Orange Money, MTN, Moov · Vérification 24–48h</div>
                            </div>
                        </div>
                        <span class="text-gray-400 text-lg">{{ activePanel === 'mobile_money' ? '▲' : '▼' }}</span>
                    </button>
                    <div v-show="activePanel === 'mobile_money'" class="border-t p-5 space-y-5">
                        <!-- Sélecteur opérateur -->
                        <div>
                            <p class="mb-3 text-sm font-medium text-gray-700">1. Choisissez votre opérateur</p>
                            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                                <button
                                    v-for="op in mobileOperators" :key="op.value"
                                    @click="selectOperator(op)"
                                    class="flex flex-col items-center gap-2 rounded-lg border-2 p-3 text-center text-sm font-semibold transition"
                                    :class="selectedOperator?.value === op.value ? 'border-brand-600 bg-brand-50 text-brand-700' : 'border-gray-200 hover:border-brand-300'"
                                >
                                    <span class="flex h-8 w-8 items-center justify-center rounded-full text-white text-xs" :class="op.color">
                                        {{ op.label.charAt(0) }}
                                    </span>
                                    {{ op.label }}
                                </button>
                            </div>
                        </div>

                        <!-- Coordonnées opérateur sélectionné -->
                        <div v-if="selectedOperator">
                            <p class="mb-2 text-sm font-medium text-gray-700">2. Effectuez le transfert</p>
                            <template v-if="configFor(selectedOperator.value).length">
                                <div v-for="cfg in configFor(selectedOperator.value)" :key="cfg.id" class="mb-2 rounded-md bg-brand-50 p-4 text-sm">
                                    <div class="font-semibold text-brand-800">{{ cfg.label }}</div>
                                    <div v-if="cfg.account_number" class="mt-1">Numéro : <b class="text-brand-700 text-base">{{ cfg.account_number }}</b></div>
                                    <div v-if="cfg.account_holder">Titulaire : <b>{{ cfg.account_holder }}</b></div>
                                    <p v-if="cfg.instructions" class="mt-2 text-xs text-gray-500">{{ cfg.instructions }}</p>
                                </div>
                            </template>
                            <div v-else class="rounded-md bg-gray-50 p-4 text-sm text-gray-600">
                                Envoyez <b>{{ fmt(order.total_amount) }} {{ order.currency }}</b> via {{ selectedOperator.label }},
                                puis déclarez votre paiement ci-dessous.
                            </div>

                            <div class="mt-3 rounded-md bg-red-50 px-3 py-2 text-xs text-red-700">
                                ⚠️ Ne communiquez jamais votre code secret Mobile Money à quiconque, même à notre équipe.
                            </div>
                        </div>

                        <!-- Formulaire déclaration -->
                        <form v-if="selectedOperator" @submit.prevent="submitMobile" class="space-y-4">
                            <p class="text-sm font-medium text-gray-700">3. Déclarez votre paiement</p>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <InputLabel value="Nom de l'expéditeur *" />
                                    <TextInput v-model="mobileForm.sender_name" class="mt-1 block w-full" required />
                                    <InputError :message="mobileForm.errors.sender_name" class="mt-1" />
                                </div>
                                <div>
                                    <InputLabel value="Numéro expéditeur *" />
                                    <TextInput v-model="mobileForm.sender_number" class="mt-1 block w-full" placeholder="+225 07 00 00 00 00" />
                                    <InputError :message="mobileForm.errors.sender_number" class="mt-1" />
                                </div>
                                <div>
                                    <InputLabel value="Référence transaction *" />
                                    <TextInput v-model="mobileForm.provider_reference" class="mt-1 block w-full" placeholder="Ex : MP2607XXXXXX" required />
                                    <InputError :message="mobileForm.errors.provider_reference" class="mt-1" />
                                </div>
                                <div>
                                    <InputLabel value="Montant envoyé *" />
                                    <TextInput v-model="mobileForm.amount_declared" type="number" step="1" min="1" class="mt-1 block w-full" required />
                                    <InputError :message="mobileForm.errors.amount_declared" class="mt-1" />
                                </div>
                                <div class="sm:col-span-2">
                                    <InputLabel value="Capture d'écran / Reçu * (JPG, PNG, PDF — max 10 Mo)" />
                                    <input
                                        type="file" accept=".jpg,.jpeg,.png,.webp,.pdf"
                                        @input="mobileForm.proof = $event.target.files[0]"
                                        class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:rounded-md file:border-0 file:bg-brand-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-brand-700 hover:file:bg-brand-100"
                                        required
                                    />
                                    <InputError :message="mobileForm.errors.proof" class="mt-1" />
                                </div>
                            </div>
                            <div class="flex justify-end">
                                <PrimaryButton :disabled="mobileForm.processing">
                                    {{ mobileForm.processing ? 'Envoi…' : 'Envoyer ma déclaration' }}
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- ═══════ MÉTHODE 3 : Virement bancaire national ═══════ -->
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                    <button @click="toggle('bank')" class="flex w-full items-center justify-between gap-4 p-5 text-left hover:bg-gray-50">
                        <div class="flex items-center gap-4">
                            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-xl flex-shrink-0">🏦</span>
                            <div>
                                <div class="font-semibold text-gray-800">Virement bancaire national</div>
                                <div class="text-xs text-gray-500">Activation après vérification · 1–5 jours ouvrés</div>
                            </div>
                        </div>
                        <span class="text-gray-400 text-lg">{{ activePanel === 'bank' ? '▲' : '▼' }}</span>
                    </button>
                    <div v-show="activePanel === 'bank'" class="border-t p-5 space-y-5">
                        <!-- Coordonnées bancaires -->
                        <div>
                            <p class="mb-3 text-sm font-medium text-gray-700">Coordonnées bancaires IBIG Soft</p>
                            <template v-if="bankConfigs.length">
                                <div v-for="cfg in bankConfigs" :key="cfg.id" class="mb-2 rounded-md bg-gray-50 p-4 text-sm">
                                    <div class="font-semibold text-gray-800">{{ cfg.label }}</div>
                                    <div v-if="cfg.bank_name" class="mt-1">Banque : <b>{{ cfg.bank_name }}</b></div>
                                    <div v-if="cfg.account_holder">Titulaire : <b>{{ cfg.account_holder }}</b></div>
                                    <div v-if="cfg.account_number">N° compte : <b class="font-mono">{{ cfg.account_number }}</b></div>
                                    <div v-if="cfg.iban">IBAN : <b class="font-mono">{{ cfg.iban }}</b></div>
                                    <p v-if="cfg.instructions" class="mt-2 text-xs text-gray-500">{{ cfg.instructions }}</p>
                                </div>
                            </template>
                            <div v-else class="rounded-md bg-gray-50 p-4 text-sm text-gray-600">
                                Les coordonnées bancaires seront communiquées par email après création de votre commande. Montant à virer : <b>{{ fmt(order.total_amount) }} {{ order.currency }}</b>
                            </div>
                        </div>

                        <form @submit.prevent="submitBank" class="space-y-4">
                            <p class="text-sm font-medium text-gray-700">Déclarez votre virement</p>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <InputLabel value="Nom de l'émetteur *" />
                                    <TextInput v-model="bankForm.sender_name" class="mt-1 block w-full" required />
                                    <InputError :message="bankForm.errors.sender_name" class="mt-1" />
                                </div>
                                <div>
                                    <InputLabel value="Référence du virement *" />
                                    <TextInput v-model="bankForm.provider_reference" class="mt-1 block w-full" placeholder="N° virement ou ordre" required />
                                    <InputError :message="bankForm.errors.provider_reference" class="mt-1" />
                                </div>
                                <div>
                                    <InputLabel value="Montant viré *" />
                                    <TextInput v-model="bankForm.amount_declared" type="number" step="1" min="1" class="mt-1 block w-full" required />
                                    <InputError :message="bankForm.errors.amount_declared" class="mt-1" />
                                </div>
                                <div class="sm:col-span-2">
                                    <InputLabel value="Justificatif de virement * (JPG, PNG, PDF — max 10 Mo)" />
                                    <input
                                        type="file" accept=".jpg,.jpeg,.png,.webp,.pdf"
                                        @input="bankForm.proof = $event.target.files[0]"
                                        class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:rounded-md file:border-0 file:bg-brand-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-brand-700 hover:file:bg-brand-100"
                                        required
                                    />
                                    <InputError :message="bankForm.errors.proof" class="mt-1" />
                                </div>
                            </div>
                            <div class="flex justify-end">
                                <PrimaryButton :disabled="bankForm.processing">
                                    {{ bankForm.processing ? 'Envoi…' : 'Envoyer ma déclaration' }}
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- ═══════ MÉTHODE 4 : Transfert international ═══════ -->
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                    <button @click="toggle('international')" class="flex w-full items-center justify-between gap-4 p-5 text-left hover:bg-gray-50">
                        <div class="flex items-center gap-4">
                            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-indigo-100 text-xl flex-shrink-0">🌍</span>
                            <div>
                                <div class="font-semibold text-gray-800">Virement international / Transfert</div>
                                <div class="text-xs text-gray-500">Western Union, MoneyGram, Ria, Sendwave, WorldRemit, SWIFT</div>
                            </div>
                        </div>
                        <span class="text-gray-400 text-lg">{{ activePanel === 'international' ? '▲' : '▼' }}</span>
                    </button>
                    <div v-show="activePanel === 'international'" class="border-t p-5 space-y-5">
                        <div class="rounded-md bg-indigo-50 p-4 text-sm text-indigo-800">
                            <p class="font-semibold mb-1">Instructions</p>
                            <p>Effectuez votre transfert vers <b>IBIG Soft — Abidjan, Côte d'Ivoire</b> en utilisant le service de votre choix.
                            Après transfert, remplissez le formulaire ci-dessous et joignez votre reçu.</p>
                        </div>

                        <form @submit.prevent="submitIntl" class="space-y-4">
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="sm:col-span-2">
                                    <InputLabel value="Service utilisé *" />
                                    <select
                                        v-model="intlForm.transfer_service"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500"
                                        required
                                    >
                                        <option value="">Choisissez le service…</option>
                                        <option v-for="s in intlServices" :key="s" :value="s">{{ s }}</option>
                                    </select>
                                </div>
                                <div>
                                    <InputLabel value="Nom complet de l'expéditeur *" />
                                    <TextInput v-model="intlForm.sender_name" class="mt-1 block w-full" required />
                                </div>
                                <div>
                                    <InputLabel value="Pays d'envoi *" />
                                    <TextInput v-model="intlForm.sender_country" class="mt-1 block w-full" placeholder="Ex : France" required />
                                </div>
                                <div>
                                    <InputLabel value="Ville d'envoi" />
                                    <TextInput v-model="intlForm.sender_city" class="mt-1 block w-full" placeholder="Ex : Paris" />
                                </div>
                                <div>
                                    <InputLabel value="Montant envoyé *" />
                                    <TextInput v-model="intlForm.amount_declared" type="number" step="0.01" min="1" class="mt-1 block w-full" required />
                                </div>
                                <div>
                                    <InputLabel value="Référence / Code de retrait *" />
                                    <TextInput v-model="intlForm.provider_reference" class="mt-1 block w-full" placeholder="Ex : WU-XXXXXX" required />
                                </div>
                                <div>
                                    <InputLabel value="Commentaire (optionnel)" />
                                    <TextInput v-model="intlForm.comment" class="mt-1 block w-full" />
                                </div>
                                <div class="sm:col-span-2">
                                    <InputLabel value="Reçu de transfert * (JPG, PNG, PDF — max 10 Mo)" />
                                    <input
                                        type="file" accept=".jpg,.jpeg,.png,.webp,.pdf"
                                        @input="intlForm.proof = $event.target.files[0]"
                                        class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:rounded-md file:border-0 file:bg-brand-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-brand-700 hover:file:bg-brand-100"
                                        required
                                    />
                                    <InputError :message="intlForm.errors.proof" class="mt-1" />
                                </div>
                            </div>
                            <div class="flex justify-end">
                                <PrimaryButton :disabled="intlForm.processing">
                                    {{ intlForm.processing ? 'Envoi…' : 'Envoyer ma déclaration' }}
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- ═══════ MÉTHODE 5 : Espèces ═══════ -->
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                    <button @click="toggle('cash')" class="flex w-full items-center justify-between gap-4 p-5 text-left hover:bg-gray-50">
                        <div class="flex items-center gap-4">
                            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-green-100 text-xl flex-shrink-0">💵</span>
                            <div>
                                <div class="font-semibold text-gray-800">Paiement en espèces</div>
                                <div class="text-xs text-gray-500">Au siège IBIG Soft — Sur rendez-vous</div>
                            </div>
                        </div>
                        <span class="text-gray-400 text-lg">{{ activePanel === 'cash' ? '▲' : '▼' }}</span>
                    </button>
                    <div v-show="activePanel === 'cash'" class="border-t p-5 space-y-5">
                        <!-- Adresse et horaires -->
                        <div class="rounded-md bg-green-50 p-4 text-sm text-green-900">
                            <p class="font-semibold mb-2">Adresse IBIG Soft</p>
                            <p>📍 Abidjan, Plateau — Zone 4C (prendre rendez-vous avant)</p>
                            <p class="mt-1">🕘 Lun–Ven : 08h–17h · Sam : 09h–13h</p>
                            <p class="mt-1">📞 <a href="tel:+2250000000000" class="underline">+225 00 00 00 00 00</a></p>
                        </div>

                        <form @submit.prevent="submitCash" class="space-y-4">
                            <div class="grid gap-4">
                                <div>
                                    <InputLabel value="Votre nom complet *" />
                                    <TextInput v-model="cashForm.sender_name" class="mt-1 block w-full" required />
                                </div>
                                <div>
                                    <InputLabel value="Montant à payer" />
                                    <TextInput v-model="cashForm.amount_declared" type="number" step="1" min="1" class="mt-1 block w-full" required />
                                </div>
                                <div>
                                    <InputLabel value="Commentaire / Date de passage prévue" />
                                    <TextInput v-model="cashForm.comment" class="mt-1 block w-full" placeholder="Ex : Je passerai lundi 21 juillet à 10h" />
                                </div>
                            </div>
                            <div class="flex justify-end">
                                <PrimaryButton :disabled="cashForm.processing">
                                    {{ cashForm.processing ? 'Envoi…' : 'Confirmer mon intention de paiement' }}
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- ═══════ MÉTHODE 6 : Chèque bancaire ═══════ -->
                <div v-if="chequeMethod" class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                    <button @click="toggle('cheque')" class="flex w-full items-center justify-between gap-4 p-5 text-left hover:bg-gray-50">
                        <div class="flex items-center gap-4">
                            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-yellow-100 text-xl flex-shrink-0">🏷️</span>
                            <div>
                                <div class="font-semibold text-gray-800">Chèque bancaire</div>
                                <div class="text-xs text-gray-500">Envoi par courrier — {{ chequeMethod.processing_time }}</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-xs bg-yellow-100 text-yellow-700 px-2 py-1 rounded">Vérification manuelle</span>
                            <span class="text-gray-400 text-lg">{{ activePanel === 'cheque' ? '▲' : '▼' }}</span>
                        </div>
                    </button>
                    <div v-show="activePanel === 'cheque'" class="border-t p-5 space-y-5">
                        <!-- Coordonnées -->
                        <div class="rounded-md bg-white border p-4 text-sm">
                            <h4 class="font-semibold text-gray-700 mb-3">📋 Instructions</h4>
                            <div class="space-y-1">
                                <div v-if="chequeMethod.account_name">
                                    <span class="text-gray-500">À l'ordre de :</span> <strong>{{ chequeMethod.account_name }}</strong>
                                </div>
                                <div v-if="chequeMethod.bank_name">
                                    <span class="text-gray-500">Banque :</span> {{ chequeMethod.bank_name }}
                                </div>
                                <div v-if="chequeMethod.address">
                                    <span class="text-gray-500">Adresse d'envoi :</span> {{ chequeMethod.address }}
                                </div>
                            </div>
                            <div v-if="chequeMethod.instructions" class="mt-3 p-3 bg-blue-50 rounded text-xs text-blue-800">
                                {{ chequeMethod.instructions }}
                            </div>
                        </div>

                        <!-- Formulaire déclaration -->
                        <form @submit.prevent="submitCheque" class="space-y-4">
                            <p class="text-sm font-medium text-gray-700">Déclarez votre chèque</p>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <InputLabel value="Numéro du chèque *" />
                                    <TextInput v-model="chequeForm.cheque_number" class="mt-1 block w-full font-mono" placeholder="Ex: 0012345" required />
                                    <InputError :message="chequeForm.errors.cheque_number" class="mt-1" />
                                </div>
                                <div>
                                    <InputLabel value="Banque émettrice *" />
                                    <TextInput v-model="chequeForm.issuing_bank" class="mt-1 block w-full" placeholder="Ex: Société Générale" required />
                                    <InputError :message="chequeForm.errors.issuing_bank" class="mt-1" />
                                </div>
                                <div>
                                    <InputLabel value="Nom du titulaire du compte *" />
                                    <TextInput v-model="chequeForm.account_holder" class="mt-1 block w-full" required />
                                    <InputError :message="chequeForm.errors.account_holder" class="mt-1" />
                                </div>
                                <div>
                                    <InputLabel value="Montant inscrit sur le chèque *" />
                                    <TextInput v-model="chequeForm.declared_amount" type="number" step="0.01" min="0" class="mt-1 block w-full" required />
                                    <InputError :message="chequeForm.errors.declared_amount" class="mt-1" />
                                </div>
                                <div>
                                    <InputLabel value="Date du chèque *" />
                                    <TextInput v-model="chequeForm.cheque_date" type="date" class="mt-1 block w-full" required />
                                    <InputError :message="chequeForm.errors.cheque_date" class="mt-1" />
                                </div>
                                <div>
                                    <InputLabel value="Commentaire (optionnel)" />
                                    <TextInput v-model="chequeForm.comment" class="mt-1 block w-full" placeholder="Date d'envoi par courrier, numéro de suivi..." />
                                </div>
                                <div class="sm:col-span-2">
                                    <InputLabel value="Photo du chèque (optionnel — JPG, PNG, PDF — max 5 Mo)" />
                                    <input
                                        type="file" accept=".jpg,.jpeg,.png,.pdf"
                                        @input="chequeForm.proof = $event.target.files[0]"
                                        class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:rounded-md file:border-0 file:bg-brand-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-brand-700 hover:file:bg-brand-100"
                                    />
                                    <InputError :message="chequeForm.errors.proof" class="mt-1" />
                                </div>
                            </div>
                            <div class="rounded-lg bg-amber-50 border border-amber-200 p-3 text-xs text-amber-800">
                                ⚠️ L'activation de votre licence sera effectuée après encaissement du chèque (3 à 5 jours ouvrables).
                            </div>
                            <div class="flex justify-end">
                                <PrimaryButton :disabled="chequeForm.processing">
                                    {{ chequeForm.processing ? 'Envoi…' : 'Confirmer ma déclaration' }}
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- ═══════ MÉTHODE 6 : Cryptomonnaie ═══════ -->
                <div v-if="activeWallets.length > 0" class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                    <button @click="toggle('crypto')" class="flex w-full items-center justify-between gap-4 p-5 text-left hover:bg-gray-50">
                        <div class="flex items-center gap-4">
                            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-orange-100 text-xl flex-shrink-0">₿</span>
                            <div>
                                <div class="font-semibold text-gray-800">Cryptomonnaie</div>
                                <div class="text-xs text-gray-500">USDT, Bitcoin, Ethereum — Vérification manuelle</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs bg-orange-100 text-orange-700 px-2 py-1 rounded font-medium">Vérification manuelle</span>
                            <span class="text-gray-400 text-lg">{{ activePanel === 'crypto' ? '▲' : '▼' }}</span>
                        </div>
                    </button>

                    <div v-show="activePanel === 'crypto'" class="border-t p-5 space-y-5">
                        <!-- Sélecteur crypto -->
                        <div>
                            <label class="text-sm font-medium text-gray-700 mb-2 block">Choisissez votre cryptomonnaie</label>
                            <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                                <button
                                    v-for="wallet in activeWallets" :key="wallet.id"
                                    type="button"
                                    @click="selectedWallet = wallet"
                                    :class="['p-3 rounded-lg border-2 text-left transition', selectedWallet?.id === wallet.id ? 'border-brand-600 bg-brand-50' : 'border-gray-200 hover:border-gray-300']"
                                >
                                    <div class="font-bold text-sm">{{ wallet.currency }}</div>
                                    <div class="text-xs text-gray-500">{{ wallet.network }}</div>
                                </button>
                            </div>
                        </div>

                        <div v-if="selectedWallet" class="space-y-4">
                            <!-- Adresse wallet -->
                            <div class="bg-white p-4 rounded-lg border">
                                <p class="text-xs text-gray-500 mb-1">Adresse {{ selectedWallet.display_label ?? selectedWallet.label }}</p>
                                <div class="flex items-center gap-2">
                                    <code class="flex-1 text-xs font-mono bg-gray-50 p-2 rounded break-all">{{ selectedWallet.wallet_address }}</code>
                                    <button type="button" @click="copyAddress(selectedWallet.wallet_address)"
                                        class="text-brand-600 text-xs font-medium shrink-0 hover:underline">Copier</button>
                                </div>
                                <div v-if="selectedWallet.qr_code_url" class="mt-3 flex justify-center">
                                    <img :src="selectedWallet.qr_code_url" alt="QR Code" class="w-32 h-32">
                                </div>
                                <div class="mt-2 text-xs text-orange-700 bg-orange-50 p-2 rounded">
                                    ⚠️ Envoyez UNIQUEMENT des {{ selectedWallet.currency }} sur le réseau {{ selectedWallet.network }}.
                                    Toute erreur de réseau entraîne la perte définitive des fonds.
                                </div>
                            </div>

                            <!-- Formulaire déclaration -->
                            <form @submit.prevent="submitCrypto" class="space-y-3">
                                <div>
                                    <label class="text-sm font-medium text-gray-700">Hash de la transaction (TX ID) *</label>
                                    <input v-model="cryptoForm.tx_hash" type="text" required
                                        class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono focus:outline-none focus:ring-1 focus:ring-brand-500"
                                        placeholder="0x1234...abcd">
                                    <p class="text-xs text-gray-400 mt-1">Le hash se trouve dans l'historique de votre wallet</p>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="text-sm font-medium text-gray-700">Montant envoyé *</label>
                                        <input v-model="cryptoForm.declared_amount" type="number" step="0.000001" required
                                            class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-brand-500">
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-700">Devise</label>
                                        <input :value="selectedWallet.currency" disabled
                                            class="w-full mt-1 px-3 py-2 border border-gray-200 rounded-lg text-sm bg-gray-50 cursor-not-allowed">
                                    </div>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-700">Date de la transaction *</label>
                                    <input v-model="cryptoForm.tx_date" type="datetime-local" required
                                        class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-brand-500">
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-700">Capture d'écran (optionnel)</label>
                                    <input type="file" @change="cryptoForm.proof = $event.target.files[0]"
                                        accept=".jpg,.jpeg,.png,.pdf"
                                        class="w-full mt-1 text-sm text-gray-500 file:mr-4 file:rounded-md file:border-0 file:bg-brand-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-brand-700 hover:file:bg-brand-100">
                                </div>
                                <div class="text-xs text-gray-500 bg-gray-100 p-3 rounded">
                                    🔍 Notre équipe vérifiera votre transaction sur l'explorateur blockchain.
                                    Ne soumettez pas de faux hash — cela entraînera le rejet permanent de votre compte.
                                </div>
                                <button type="submit" :disabled="cryptoSubmitting"
                                    class="mt-2 w-full bg-brand-600 text-white py-3 rounded-xl font-semibold hover:bg-brand-700 disabled:opacity-60 transition">
                                    {{ cryptoSubmitting ? 'Envoi...' : 'Soumettre ma transaction' }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- ═══════ MÉTHODE : Voucher / Code prépayé ═══════ -->
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                    <button @click="toggle('voucher')" class="flex w-full items-center justify-between gap-4 p-5 text-left hover:bg-gray-50">
                        <div class="flex items-center gap-4">
                            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-green-100 text-xl flex-shrink-0">🎫</span>
                            <div>
                                <div class="font-semibold text-gray-800">Code prépayé / Voucher</div>
                                <div class="text-xs text-gray-500">
                                    Activation instantanée
                                    <span class="ml-1 inline-block rounded bg-green-100 px-1.5 py-0.5 text-green-700 font-semibold">⚡ Instantané</span>
                                </div>
                            </div>
                        </div>
                        <span class="text-gray-400 text-lg">{{ activePanel === 'voucher' ? '▲' : '▼' }}</span>
                    </button>

                    <div v-show="activePanel === 'voucher'" class="border-t p-5 space-y-4">
                        <p class="text-sm text-gray-600">
                            Entrez votre code prépayé acheté auprès d'un revendeur agréé IBIG Soft.
                            Format : <code class="rounded bg-gray-100 px-1 font-mono">IBIG-XXXX-XXXX-XXXX</code>
                        </p>

                        <!-- Saisie + Vérification -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Votre code prépayé *</label>
                            <div class="flex gap-2">
                                <input
                                    v-model="voucherCode"
                                    type="text"
                                    @input="voucherCode = voucherCode.toUpperCase(); voucherResult = null"
                                    class="flex-1 rounded-lg border border-gray-300 px-3 py-2 font-mono tracking-wider text-sm focus:border-brand-500 focus:ring-brand-500"
                                    placeholder="IBIG-XXXX-XXXX-XXXX"
                                    maxlength="19"
                                />
                                <button
                                    type="button"
                                    @click="verifyVoucher"
                                    :disabled="voucherCode.length < 10 || verifying"
                                    class="rounded-lg bg-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-300 disabled:opacity-50"
                                >
                                    {{ verifying ? '...' : 'Vérifier' }}
                                </button>
                            </div>
                        </div>

                        <!-- Résultat vérification -->
                        <div v-if="voucherResult" :class="['rounded-lg p-3 text-sm border', voucherResult.valid ? 'bg-green-50 text-green-800 border-green-200' : 'bg-red-50 text-red-800 border-red-200']">
                            <div v-if="voucherResult.valid">
                                ✅ Code valide —
                                <b>{{ voucherResult.plan?.name ?? 'Abonnement' }}</b>
                                — {{ voucherResult.duration_months }} mois
                            </div>
                            <div v-else>❌ {{ voucherResult.error }}</div>
                        </div>

                        <!-- Bouton activation -->
                        <button
                            v-if="voucherResult?.valid"
                            @click="redeemVoucher"
                            :disabled="submitting"
                            class="w-full rounded-xl bg-green-600 py-3 font-semibold text-white hover:bg-green-700 disabled:opacity-50"
                        >
                            {{ submitting ? 'Activation en cours...' : '⚡ Activer maintenant' }}
                        </button>
                    </div>
                </div>

                <!-- ═══════ MÉTHODE : Paiement à la livraison (COD) ═══════ -->
                <div v-if="codEnabled" class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                    <button @click="toggle('cod')" class="flex w-full items-center justify-between gap-4 p-5 text-left hover:bg-gray-50">
                        <div class="flex items-center gap-4">
                            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-purple-100 text-xl flex-shrink-0">🚚</span>
                            <div>
                                <div class="font-semibold text-gray-800">Paiement à la livraison</div>
                                <div class="text-xs text-gray-500">Payez en espèces à la réception — 24–72h · <span class="text-purple-600 font-semibold">Livraison physique</span></div>
                            </div>
                        </div>
                        <span class="text-gray-400 text-lg">{{ activePanel === 'cod' ? '▲' : '▼' }}</span>
                    </button>
                    <div v-show="activePanel === 'cod'" class="border-t p-5 space-y-4">
                        <p class="text-sm text-gray-600">
                            Un agent IBIG Soft vous livrera le produit physique (clé USB, carte d'activation).
                            Préparez <strong>{{ fmt(order.total_amount) }} {{ order.currency }}</strong> en espèces à la réception.
                        </p>
                        <form @submit.prevent="submitCod" class="space-y-3">
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <InputLabel value="Nom complet *" />
                                    <TextInput v-model="codForm.contact_name" type="text" class="mt-1 block w-full" required />
                                    <InputError :message="codForm.errors.contact_name" class="mt-1" />
                                </div>
                                <div>
                                    <InputLabel value="Téléphone *" />
                                    <TextInput v-model="codForm.phone" type="tel" class="mt-1 block w-full" placeholder="+225..." required />
                                    <InputError :message="codForm.errors.phone" class="mt-1" />
                                </div>
                            </div>
                            <div>
                                <InputLabel value="Adresse de livraison *" />
                                <TextInput v-model="codForm.address" type="text" class="mt-1 block w-full" placeholder="Quartier, rue, bâtiment..." required />
                                <InputError :message="codForm.errors.address" class="mt-1" />
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <InputLabel value="Ville *" />
                                    <TextInput v-model="codForm.city" type="text" class="mt-1 block w-full" required />
                                    <InputError :message="codForm.errors.city" class="mt-1" />
                                </div>
                                <div>
                                    <InputLabel value="Pays" />
                                    <select v-model="codForm.country" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 text-sm">
                                        <option value="CI">Côte d'Ivoire</option>
                                        <option value="SN">Sénégal</option>
                                        <option value="CM">Cameroun</option>
                                        <option value="BJ">Bénin</option>
                                        <option value="TG">Togo</option>
                                        <option value="BF">Burkina Faso</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <InputLabel value="Instructions pour le livreur (optionnel)" />
                                <textarea v-model="codForm.notes" rows="2"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 text-sm"
                                    placeholder="Point de repère, horaires disponibles..."></textarea>
                            </div>
                            <div class="rounded-lg bg-amber-50 p-3 text-xs text-amber-800 border border-amber-200">
                                💰 Préparez <strong>{{ fmt(order.total_amount) }} {{ order.currency }}</strong> en espèces.
                                La licence sera activée après confirmation de réception du paiement par notre agent.
                            </div>
                            <PrimaryButton type="submit" :disabled="codForm.processing" class="w-full justify-center">
                                {{ codForm.processing ? 'Enregistrement...' : '🚚 Confirmer ma commande' }}
                            </PrimaryButton>
                        </form>
                    </div>
                </div>

                <!-- Pied de page sécurité -->
                <div class="rounded-lg bg-gray-50 p-4 text-center text-xs text-gray-500">
                    🔒 Paiement 100% sécurisé — Vos données sont protégées<br>
                    IBIG ne vous demandera jamais votre code secret Mobile Money, votre mot de passe ou votre code confidentiel.
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

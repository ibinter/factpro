<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import QuoteLinkManager from '@/Pages/Documents/QuoteLinkManager.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    document: Object,
    typeLabel: String,
    verificationUrl: String,
    convertTargets: Array,
    paymentPlan: { type: Object, default: null },
    canCreatePlan: { type: Boolean, default: false },
    canFacturX: { type: Boolean, default: false },
});

const fmt = (n) => new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(n ?? 0);

const submittingApproval = ref(false);
const submitForApproval = () => {
    if (submittingApproval.value) return;
    submittingApproval.value = true;
    router.post(route('approval.submit', props.document.id), {}, {
        onFinish: () => { submittingApproval.value = false; },
    });
};

const statusLabels = {
    draft: 'Brouillon', sent: 'Envoyé', viewed: 'Vu', accepted: 'Accepté',
    rejected: 'Refusé', partial: 'Partiellement payé', paid: 'Payé', overdue: 'En retard',
    cancelled: 'Annulé', converted: 'Converti',
};
const statusColors = {
    draft: 'bg-gray-100 text-gray-700', sent: 'bg-blue-100 text-blue-700',
    viewed: 'bg-indigo-100 text-indigo-700', accepted: 'bg-green-100 text-green-700',
    rejected: 'bg-red-100 text-red-700', partial: 'bg-amber-100 text-amber-700',
    paid: 'bg-green-100 text-green-700', overdue: 'bg-red-100 text-red-700',
    cancelled: 'bg-gray-100 text-gray-500', converted: 'bg-purple-100 text-purple-700',
};

const balanceDue = computed(() => Number(props.document.total) - Number(props.document.amount_paid));
const isPayable = computed(() =>
    ['invoice', 'deposit_invoice', 'balance_invoice', 'pos_ticket'].includes(props.document.type) && balanceDue.value > 0,
);

const showPaymentModal = ref(false);
const paymentForm = useForm({
    amount: balanceDue.value,
    method: 'cash',
    reference: '',
    paid_at: new Date().toISOString().slice(0, 10),
    notes: '',
});

const submitPayment = () => {
    paymentForm.post(route('documents.payments', props.document.id), {
        preserveScroll: true,
        onSuccess: () => { showPaymentModal.value = false; },
    });
};

const showConvertModal = ref(false);
const convertForm = useForm({ target_type: props.convertTargets[0]?.value ?? null });
const submitConvert = () => {
    convertForm.post(route('documents.convert', props.document.id));
};

const finalize = () => {
    router.post(route('documents.finalize', props.document.id), {}, { preserveScroll: true });
};

const openThermal = () => {
    window.open(route('documents.thermal', props.document.id) + '?width=80', '_blank');
};

const showSendModal = ref(false);
const sendForm = useForm({
    recipient: props.document.customer?.email ?? '',
    message: '',
    cc_self: false,
});
const submitSend = () => {
    sendForm.post(route('documents.send', props.document.id), {
        preserveScroll: true,
        onSuccess: () => {
            showSendModal.value = false;
            sendForm.reset('message');
        },
    });
};

const methodLabels = {
    cash: 'Espèces', mobile_money: 'Mobile Money', card: 'Carte bancaire',
    bank_transfer: 'Virement', cheque: 'Chèque', credit: 'Crédit client',
};

/* -------------------------------------------------------------------------
 * Plan de paiement / acomptes (cahier §12)
 * ---------------------------------------------------------------------- */
const installmentStatusLabels = { pending: 'À venir', invoiced: 'Facturé', paid: 'Payé' };
const installmentStatusColors = {
    pending: 'bg-gray-100 text-gray-600',
    invoiced: 'bg-blue-100 text-blue-700',
    paid: 'bg-green-100 text-green-700',
};
const planStatusLabels = { active: 'Actif', completed: 'Terminé', cancelled: 'Annulé' };

const documentTotal = computed(() => Number(props.document.total) || 0);

const showPlanModal = ref(false);
const today = new Date().toISOString().slice(0, 10);
const plusDays = (n) => new Date(Date.now() + n * 86400000).toISOString().slice(0, 10);

const planForm = useForm({ installments: [] });

const presets = {
    '30/70': [
        { label: 'Acompte 30%', percentage: 30, days: 0 },
        { label: 'Solde 70%', percentage: 70, days: 30 },
    ],
    '40/30/30': [
        { label: 'Acompte 40%', percentage: 40, days: 0 },
        { label: 'Échéance 30%', percentage: 30, days: 30 },
        { label: 'Solde 30%', percentage: 30, days: 60 },
    ],
    '3× égales': [
        { label: '1ʳᵉ échéance', percentage: 33.34, days: 0 },
        { label: '2ᵉ échéance', percentage: 33.33, days: 30 },
        { label: 'Solde', percentage: 33.33, days: 60 },
    ],
};

const applyPreset = (key) => {
    planForm.installments = presets[key].map((p) => ({
        label: p.label,
        percentage: p.percentage,
        amount: Math.round((documentTotal.value * p.percentage) / 100),
        due_date: plusDays(p.days),
        mode: 'percentage',
    }));
};

const addInstallment = () => {
    planForm.installments.push({ label: '', percentage: null, amount: 0, due_date: today, mode: 'amount' });
};

const removeInstallment = (i) => planForm.installments.splice(i, 1);

const openPlanModal = () => {
    applyPreset('30/70');
    showPlanModal.value = true;
};

const rowAmount = (row) =>
    row.mode === 'percentage' && row.percentage != null
        ? Math.round((documentTotal.value * Number(row.percentage)) / 100)
        : Number(row.amount) || 0;

const planSum = computed(() => planForm.installments.reduce((s, r) => s + rowAmount(r), 0));
const planBalanced = computed(() => Math.abs(planSum.value - documentTotal.value) <= 0.05);

const submitPlan = () => {
    planForm
        .transform((data) => ({
            installments: data.installments.map((r) => ({
                label: r.label,
                due_date: r.due_date,
                ...(r.mode === 'percentage' && r.percentage != null
                    ? { percentage: r.percentage }
                    : { amount: rowAmount(r) }),
            })),
        }))
        .post(route('payment-plans.create', props.document.id), {
            preserveScroll: true,
            onSuccess: () => { showPlanModal.value = false; },
        });
};

const invoiceInstallment = (installment) => {
    router.post(route('payment-plans.installment.invoice', installment.id), {}, { preserveScroll: true });
};

const cancelPlan = () => {
    if (props.paymentPlan) {
        router.post(route('payment-plans.cancel', props.paymentPlan.id), {}, { preserveScroll: true });
    }
};

const planProgress = computed(() => {
    if (!props.paymentPlan || !props.paymentPlan.total_amount) return 0;
    return Math.min(100, Math.round((props.paymentPlan.total_invoiced / props.paymentPlan.total_amount) * 100));
});
</script>

<template>
    <Head :title="typeLabel + ' ' + document.number" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">
                        {{ typeLabel }} <span class="text-brand-600">{{ document.number }}</span>
                        <span class="ml-2 rounded-full px-2.5 py-0.5 text-xs font-semibold" :class="statusColors[document.status]">
                            {{ statusLabels[document.status] ?? document.status }}
                        </span>
                        <span v-if="document.finalized_at" class="ml-1" title="Document scellé">🔒</span>
                    </h2>
                    <p v-if="document.parent" class="mt-1 text-xs text-gray-400">
                        Créé à partir de
                        <Link :href="route('documents.show', document.parent.id)" class="text-brand-600 hover:underline">
                            {{ document.parent.number }}
                        </Link>
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a
                        :href="route('documents.pdf', document.id)"
                        target="_blank"
                        class="rounded-md bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700"
                    >
                        📄 PDF
                    </a>
                    <a
                        :href="route('documents.docx', document.id)"
                        class="rounded-md border border-blue-300 bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-100"
                        title="Télécharger en Word (.docx)"
                    >
                        📝 Word
                    </a>
                    <button
                        v-if="document.type !== 'purchase_order'"
                        @click="openThermal"
                        class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                    >
                        🎫 Ticket
                    </button>
                    <button
                        @click="showSendModal = true"
                        class="rounded-md bg-brand-900 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-800"
                    >
                        ✉ Envoyer
                    </button>
                    <button
                        v-if="!document.finalized_at"
                        @click="finalize"
                        class="rounded-md bg-gold-400 px-4 py-2 text-sm font-semibold text-brand-900 hover:bg-gold-300"
                    >
                        🔒 Finaliser & sceller
                    </button>
                    <Link
                        v-if="!document.finalized_at"
                        :href="route('documents.edit', document.id)"
                        class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                    >
                        Modifier
                    </Link>
                    <button
                        v-if="convertTargets.length"
                        @click="showConvertModal = true"
                        class="rounded-md border border-brand-600 px-4 py-2 text-sm font-semibold text-brand-600 hover:bg-brand-50"
                    >
                        Convertir →
                    </button>
                    <button
                        v-if="isPayable"
                        @click="showPaymentModal = true"
                        class="rounded-md bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700"
                    >
                        + Paiement
                    </button>
                    <a
                        v-if="canFacturX && ['invoice','credit_note'].includes(document.type)"
                        :href="route('documents.facturx', document.id)"
                        class="rounded-md border border-indigo-600 bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-100"
                    >
                        📄 Factur-X XML
                    </a>
                </div>
                <!-- Lien de partage devis interactif (Phase 12) -->
                <QuoteLinkManager
                    v-if="['quote','proforma'].includes(document.type) && document.finalized_at"
                    :document="document"
                />
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <div class="grid gap-6 lg:grid-cols-3">
                    <!-- Infos -->
                    <div class="rounded-lg bg-white p-6 shadow">
                        <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-gray-400">Informations</h3>
                        <dl class="space-y-2 text-sm">
                            <div class="flex justify-between"><dt class="text-gray-500">Émis le</dt><dd class="font-semibold">{{ document.issue_date?.slice(0, 10) }}</dd></div>
                            <div v-if="document.due_date" class="flex justify-between"><dt class="text-gray-500">Échéance</dt><dd class="font-semibold">{{ document.due_date?.slice(0, 10) }}</dd></div>
                            <div v-if="document.reference" class="flex justify-between"><dt class="text-gray-500">Référence</dt><dd>{{ document.reference }}</dd></div>
                            <div class="flex justify-between"><dt class="text-gray-500">Devise</dt><dd>{{ document.currency }}</dd></div>
                            <div v-if="document.finalized_at" class="flex justify-between"><dt class="text-gray-500">Scellé le</dt><dd>{{ new Date(document.finalized_at).toLocaleString('fr-FR') }}</dd></div>
                        </dl>
                    </div>

                    <!-- Client -->
                    <div class="rounded-lg bg-white p-6 shadow">
                        <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-gray-400">Client</h3>
                        <div v-if="document.customer">
                            <div class="font-semibold text-gray-800">{{ document.customer.name }}</div>
                            <div class="mt-1 text-sm text-gray-500">
                                <div v-if="document.customer.address">{{ document.customer.address }}</div>
                                <div>{{ document.customer.city }} {{ document.customer.country }}</div>
                                <div v-if="document.customer.phone">{{ document.customer.phone }}</div>
                                <div v-if="document.customer.email">{{ document.customer.email }}</div>
                            </div>
                        </div>
                        <div v-else class="text-sm text-gray-400">Aucun client associé</div>
                    </div>

                    <!-- Authenticité QR -->
                    <div class="rounded-lg border-2 border-gold-400/50 bg-white p-6 shadow">
                        <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-gold-600">🔒 Anti-Falsification</h3>
                        <div v-if="document.finalized_at" class="text-sm">
                            <p class="text-gray-600">Document scellé par hash SHA-256, vérifiable publiquement :</p>
                            <a :href="verificationUrl" target="_blank" class="mt-2 block break-all text-xs text-brand-600 hover:underline">
                                {{ verificationUrl }}
                            </a>
                            <p class="mt-2 break-all font-mono text-[10px] text-gray-400">
                                {{ document.integrity_hash }}
                            </p>
                        </div>
                        <p v-else class="text-sm text-gray-500">
                            Ce document est encore en <b>brouillon</b>. Finalisez-le pour activer le sceau
                            cryptographique et le QR de vérification.
                        </p>
                        <p v-if="document.trial_watermark" class="mt-2 rounded bg-red-50 px-2 py-1 text-xs text-red-600">
                            ⚠ Filigrane « VERSION ESSAI FACTPRO » appliqué (compte en essai)
                        </p>
                    </div>
                </div>

                <!-- Signature électronique du client (cahier §22.1) -->
                <div v-if="document.signature_path" class="rounded-lg border-2 border-green-200 bg-green-50/50 p-6 shadow">
                    <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-green-700">✍ Signé électroniquement</h3>
                    <div class="flex flex-wrap items-center gap-6">
                        <img
                            :src="route('documents.signature', document.id)"
                            alt="Signature du client"
                            class="h-16 w-auto rounded border border-gray-200 bg-white p-1"
                        />
                        <p class="text-sm text-gray-600">
                            Signé par <b class="text-gray-800">{{ document.signed_by_name }}</b>
                            <template v-if="document.signed_at">
                                le {{ new Date(document.signed_at).toLocaleString('fr-FR') }}
                            </template>
                            <span v-if="document.signature_ip" class="text-gray-400"> (IP {{ document.signature_ip }})</span>
                        </p>
                    </div>
                </div>

                <!-- Lignes -->
                <div class="overflow-hidden rounded-lg bg-white shadow">
                    <table class="w-full text-sm">
                        <thead class="bg-brand-900 text-left text-xs uppercase tracking-wide text-white">
                            <tr>
                                <th class="px-6 py-3">Désignation</th>
                                <th class="px-6 py-3 text-right">Qté</th>
                                <th class="px-6 py-3">Unité</th>
                                <th class="px-6 py-3 text-right">P.U. HT</th>
                                <th class="px-6 py-3 text-right">Rem. %</th>
                                <th class="px-6 py-3 text-right">TVA %</th>
                                <th class="px-6 py-3 text-right">Total HT</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="line in document.lines" :key="line.id">
                                <td class="px-6 py-3">{{ line.description }}</td>
                                <td class="px-6 py-3 text-right">{{ Number(line.quantity) }}</td>
                                <td class="px-6 py-3 text-gray-500">{{ line.unit }}</td>
                                <td class="px-6 py-3 text-right">{{ fmt(line.unit_price) }}</td>
                                <td class="px-6 py-3 text-right">{{ Number(line.discount_percent) }}</td>
                                <td class="px-6 py-3 text-right">{{ Number(line.tax_rate) }}</td>
                                <td class="px-6 py-3 text-right font-semibold">{{ fmt(line.line_total) }}</td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-gray-50 text-sm">
                            <tr>
                                <td colspan="6" class="px-6 py-2 text-right text-gray-500">Sous-total HT</td>
                                <td class="px-6 py-2 text-right font-semibold">{{ fmt(document.subtotal) }} {{ document.currency }}</td>
                            </tr>
                            <tr v-if="Number(document.discount_amount) > 0">
                                <td colspan="6" class="px-6 py-2 text-right text-gray-500">Remise</td>
                                <td class="px-6 py-2 text-right text-red-600">−{{ fmt(document.discount_amount) }} {{ document.currency }}</td>
                            </tr>
                            <tr>
                                <td colspan="6" class="px-6 py-2 text-right text-gray-500">TVA</td>
                                <td class="px-6 py-2 text-right font-semibold">{{ fmt(document.tax_amount) }} {{ document.currency }}</td>
                            </tr>
                            <tr class="border-t-2 border-brand-900">
                                <td colspan="6" class="px-6 py-3 text-right font-bold text-brand-900">TOTAL TTC</td>
                                <td class="px-6 py-3 text-right text-lg font-bold text-brand-900">{{ fmt(document.total) }} {{ document.currency }}</td>
                            </tr>
                            <tr v-if="Number(document.amount_paid) > 0">
                                <td colspan="6" class="px-6 py-2 text-right text-green-600">Payé</td>
                                <td class="px-6 py-2 text-right font-semibold text-green-600">{{ fmt(document.amount_paid) }} {{ document.currency }}</td>
                            </tr>
                            <tr v-if="Number(document.amount_paid) > 0 && balanceDue > 0">
                                <td colspan="6" class="px-6 py-2 text-right text-red-600">Reste à payer</td>
                                <td class="px-6 py-2 text-right font-bold text-red-600">{{ fmt(balanceDue) }} {{ document.currency }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Paiements -->
                <div v-if="document.payments?.length" class="overflow-hidden rounded-lg bg-white shadow">
                    <div class="border-b px-6 py-4"><h3 class="font-semibold text-gray-800">Paiements reçus</h3></div>
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-6 py-3">Date</th>
                                <th class="px-6 py-3">Moyen</th>
                                <th class="px-6 py-3">Référence</th>
                                <th class="px-6 py-3 text-right">Montant</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="payment in document.payments" :key="payment.id">
                                <td class="px-6 py-3">{{ payment.paid_at?.slice(0, 10) }}</td>
                                <td class="px-6 py-3">{{ methodLabels[payment.method] ?? payment.method }}</td>
                                <td class="px-6 py-3 text-gray-500">{{ payment.reference ?? '—' }}</td>
                                <td class="px-6 py-3 text-right font-semibold text-green-600">{{ fmt(payment.amount) }} {{ payment.currency }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Plan de paiement / Acomptes (cahier §12) -->
                <div v-if="canCreatePlan || paymentPlan" class="rounded-lg bg-white p-6 shadow">
                    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                        <h3 class="font-semibold text-gray-800">📅 Plan de paiement</h3>
                        <div class="flex items-center gap-2">
                            <span v-if="paymentPlan"
                                class="rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                :class="{
                                    'bg-green-100 text-green-700': paymentPlan.status === 'active',
                                    'bg-brand-100 text-brand-700': paymentPlan.status === 'completed',
                                    'bg-gray-100 text-gray-500': paymentPlan.status === 'cancelled',
                                }">
                                {{ planStatusLabels[paymentPlan.status] ?? paymentPlan.status }}
                            </span>
                            <button v-if="canCreatePlan" @click="openPlanModal"
                                class="rounded-md bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                                + Créer un plan d'acompte
                            </button>
                        </div>
                    </div>

                    <p v-if="canCreatePlan && !paymentPlan" class="text-sm text-gray-500">
                        Découpez le total de ce document en échéances (acompte + solde) et générez
                        automatiquement les factures d'acompte correspondantes.
                    </p>

                    <div v-if="paymentPlan">
                        <!-- Barre de progression -->
                        <div class="mb-4">
                            <div class="mb-1 flex justify-between text-xs text-gray-500">
                                <span>Facturé : {{ fmt(paymentPlan.total_invoiced) }} / {{ fmt(paymentPlan.total_amount) }} {{ paymentPlan.currency }}</span>
                                <span>{{ planProgress }}%</span>
                            </div>
                            <div class="h-2 w-full overflow-hidden rounded-full bg-gray-100">
                                <div class="h-full rounded-full bg-brand-600" :style="{ width: planProgress + '%' }"></div>
                            </div>
                        </div>

                        <table class="w-full text-sm">
                            <thead class="text-left text-xs uppercase tracking-wide text-gray-400">
                                <tr>
                                    <th class="py-2">Échéance</th>
                                    <th class="py-2">Date</th>
                                    <th class="py-2 text-right">Montant</th>
                                    <th class="py-2 text-center">Statut</th>
                                    <th class="py-2 text-right">Facture</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr v-for="inst in paymentPlan.installments" :key="inst.id">
                                    <td class="py-2 font-medium text-gray-700">{{ inst.label }}</td>
                                    <td class="py-2 text-gray-500">{{ inst.due_date }}</td>
                                    <td class="py-2 text-right font-semibold">{{ fmt(inst.amount) }} {{ paymentPlan.currency }}</td>
                                    <td class="py-2 text-center">
                                        <span class="rounded-full px-2 py-0.5 text-xs font-semibold" :class="installmentStatusColors[inst.status]">
                                            {{ installmentStatusLabels[inst.status] ?? inst.status }}
                                        </span>
                                    </td>
                                    <td class="py-2 text-right">
                                        <Link v-if="inst.document" :href="route('documents.show', inst.document.id)"
                                            class="text-sm font-semibold text-brand-600 hover:underline">
                                            {{ inst.document.number }}
                                        </Link>
                                        <button v-else-if="paymentPlan.status !== 'cancelled'"
                                            @click="invoiceInstallment(inst)"
                                            class="rounded-md border border-brand-600 px-3 py-1 text-xs font-semibold text-brand-600 hover:bg-brand-50">
                                            Générer la facture
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="mt-4 flex justify-between">
                            <Link :href="route('payment-plans.show', paymentPlan.id)" class="text-sm text-brand-600 hover:underline">
                                Voir le plan complet →
                            </Link>
                            <button v-if="paymentPlan.status === 'active' && paymentPlan.total_invoiced === 0"
                                @click="cancelPlan"
                                class="text-sm text-red-600 hover:underline">
                                Annuler le plan
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Engagement email (Phase 13) -->
                <div class="rounded-lg bg-white p-6 shadow">
                    <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-gray-400">Engagement Email</h3>
                    <div v-if="!document.email_tracking" class="text-sm text-gray-400">
                        Aucun tracking disponible. Envoyez ce document par email pour activer le suivi.
                    </div>
                    <div v-else class="space-y-3">
                        <div class="flex flex-wrap gap-4 text-sm">
                            <div>
                                <span class="text-gray-500">Destinataire :</span>
                                <span class="ml-1 font-medium text-gray-700">{{ document.email_tracking.recipient_email }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500">Envoyé le :</span>
                                <span class="ml-1 font-medium text-gray-700">{{ document.email_tracking.sent_at?.slice(0,10) }}</span>
                            </div>
                            <div v-if="document.email_tracking.opened_at">
                                <span class="text-gray-500">1re ouverture :</span>
                                <span class="ml-1 font-medium text-green-700">{{ new Date(document.email_tracking.opened_at).toLocaleString('fr-FR') }}</span>
                            </div>
                        </div>

                        <!-- Barre ouverture -->
                        <div>
                            <div class="mb-1 flex justify-between text-xs text-gray-500">
                                <span>Ouvertures</span>
                                <span>{{ document.email_tracking.opens_count }}</span>
                            </div>
                            <div class="h-2 w-full overflow-hidden rounded-full bg-gray-100">
                                <div class="h-full rounded-full bg-green-500 transition-all"
                                    :style="{ width: Math.min(100, document.email_tracking.opens_count * 20) + '%' }"></div>
                            </div>
                        </div>

                        <!-- Barre clic -->
                        <div>
                            <div class="mb-1 flex justify-between text-xs text-gray-500">
                                <span>Clics PDF</span>
                                <span>{{ document.email_tracking.clicks_count }}</span>
                            </div>
                            <div class="h-2 w-full overflow-hidden rounded-full bg-gray-100">
                                <div class="h-full rounded-full bg-indigo-500 transition-all"
                                    :style="{ width: Math.min(100, document.email_tracking.clicks_count * 20) + '%' }"></div>
                            </div>
                        </div>

                        <div class="flex gap-3 text-xs">
                            <span v-if="document.email_tracking.opened_at" class="rounded-full bg-green-100 px-2 py-0.5 font-semibold text-green-700">Ouvert</span>
                            <span v-else class="rounded-full bg-red-100 px-2 py-0.5 font-semibold text-red-700">Non ouvert</span>
                            <span v-if="document.email_tracking.clicked_at" class="rounded-full bg-indigo-100 px-2 py-0.5 font-semibold text-indigo-700">PDF consulté</span>
                        </div>
                    </div>
                </div>

                <!-- Circuit de validation (Phase 13) -->
                <div v-if="document.approval_status && document.approval_status !== 'none'" class="rounded-lg bg-white p-6 shadow">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="font-semibold text-gray-800">🔄 Circuit de validation</h3>
                        <span
                            class="rounded-full px-2.5 py-0.5 text-xs font-semibold"
                            :class="{
                                'bg-amber-100 text-amber-700': document.approval_status === 'pending_approval',
                                'bg-green-100 text-green-700': document.approval_status === 'approved',
                                'bg-red-100 text-red-700': document.approval_status === 'rejected',
                            }"
                        >
                            {{
                                document.approval_status === 'pending_approval' ? 'En attente' :
                                document.approval_status === 'approved' ? 'Approuvé' :
                                document.approval_status === 'rejected' ? 'Rejeté' :
                                document.approval_status
                            }}
                        </span>
                    </div>

                    <!-- Timeline des étapes -->
                    <div v-if="document.approval_steps?.length" class="space-y-3">
                        <div
                            v-for="step in document.approval_steps"
                            :key="step.id"
                            class="flex items-start gap-3 rounded-md bg-gray-50 p-3"
                        >
                            <div class="mt-0.5 flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full text-xs font-bold"
                                :class="{
                                    'bg-amber-200 text-amber-800': step.status === 'pending',
                                    'bg-green-200 text-green-800': step.status === 'approved',
                                    'bg-red-200 text-red-800': step.status === 'rejected',
                                    'bg-gray-200 text-gray-600': step.status === 'delegated',
                                }">
                                {{ step.step_number }}
                            </div>
                            <div class="flex-1 text-sm">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-gray-700">
                                        {{ step.approver?.name ?? 'Approbateur #' + step.approver_id }}
                                    </span>
                                    <span class="text-xs text-gray-400">
                                        {{ step.status === 'pending' ? 'En attente' : step.status === 'approved' ? '✅ Approuvé' : step.status === 'rejected' ? '❌ Rejeté' : '→ Délégué' }}
                                    </span>
                                </div>
                                <div v-if="step.comment" class="mt-1 text-gray-500 italic">"{{ step.comment }}"</div>
                                <div v-if="step.decided_at" class="mt-0.5 text-xs text-gray-400">
                                    {{ new Date(step.decided_at).toLocaleString('fr-FR') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Soumettre à validation (si status = none et document draft) -->
                <div v-else-if="document.approval_status === 'none' && document.status === 'draft'" class="rounded-lg border border-dashed border-gray-300 bg-gray-50 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-semibold text-gray-700">Circuit de validation</h3>
                            <p class="mt-1 text-sm text-gray-500">Ce document n'est pas encore soumis à validation.</p>
                        </div>
                        <button
                            class="rounded-md bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 disabled:opacity-50"
                            :disabled="submittingApproval"
                            @click="submitForApproval"
                        >
                            {{ submittingApproval ? 'Envoi…' : 'Soumettre à validation' }}
                        </button>
                    </div>
                </div>

                <!-- Documents liés -->
                <div v-if="document.children?.length" class="rounded-lg bg-white p-6 shadow">
                    <h3 class="mb-2 font-semibold text-gray-800">Documents générés à partir de celui-ci</h3>
                    <div class="flex flex-wrap gap-2">
                        <Link
                            v-for="child in document.children"
                            :key="child.id"
                            :href="route('documents.show', child.id)"
                            class="rounded-full bg-brand-50 px-3 py-1 text-sm font-semibold text-brand-700 hover:bg-brand-100"
                        >
                            {{ child.number }}
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modale envoi par email -->
        <Modal :show="showSendModal" @close="showSendModal = false">
            <div class="p-6">
                <h3 class="mb-1 text-lg font-semibold text-gray-800">Envoyer {{ typeLabel }} {{ document.number }}</h3>
                <p class="mb-4 text-sm text-gray-500">
                    Le PDF scellé (QR d'authenticité inclus) sera joint à l'email.
                    <span v-if="!document.finalized_at" class="text-amber-600">Le document sera finalisé automatiquement avant l'envoi.</span>
                </p>
                <div class="space-y-4">
                    <div>
                        <InputLabel value="Email du destinataire *" />
                        <TextInput v-model="sendForm.recipient" type="email" class="mt-1 block w-full" placeholder="client@exemple.com" />
                        <InputError :message="sendForm.errors.recipient" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel value="Message (optionnel)" />
                        <textarea
                            v-model="sendForm.message"
                            rows="4"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                            placeholder="Un mot d'accompagnement pour votre client…"
                        ></textarea>
                        <InputError :message="sendForm.errors.message" class="mt-1" />
                    </div>
                    <label class="flex items-center gap-2 text-sm text-gray-600">
                        <input
                            v-model="sendForm.cc_self"
                            type="checkbox"
                            class="rounded border-gray-300 text-brand-600 shadow-sm focus:ring-brand-500"
                        />
                        M'envoyer une copie
                    </label>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="showSendModal = false">Annuler</SecondaryButton>
                    <PrimaryButton :disabled="sendForm.processing" @click="submitSend">
                        {{ sendForm.processing ? 'Envoi en cours…' : '✉ Envoyer' }}
                    </PrimaryButton>
                </div>
            </div>
        </Modal>

        <!-- Modale paiement -->
        <Modal :show="showPaymentModal" @close="showPaymentModal = false">
            <div class="p-6">
                <h3 class="mb-4 text-lg font-semibold text-gray-800">Enregistrer un paiement</h3>
                <div class="space-y-4">
                    <div>
                        <InputLabel value="Montant *" />
                        <TextInput v-model="paymentForm.amount" type="number" step="0.01" min="0.01" class="mt-1 block w-full" />
                        <InputError :message="paymentForm.errors.amount" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel value="Moyen de paiement" />
                        <select v-model="paymentForm.method" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
                            <option v-for="(label, value) in methodLabels" :key="value" :value="value">{{ label }}</option>
                        </select>
                    </div>
                    <div>
                        <InputLabel value="Date *" />
                        <TextInput v-model="paymentForm.paid_at" type="date" class="mt-1 block w-full" />
                    </div>
                    <div>
                        <InputLabel value="Référence" />
                        <TextInput v-model="paymentForm.reference" class="mt-1 block w-full" placeholder="N° transaction, chèque…" />
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="showPaymentModal = false">Annuler</SecondaryButton>
                    <PrimaryButton :disabled="paymentForm.processing" @click="submitPayment">Enregistrer</PrimaryButton>
                </div>
            </div>
        </Modal>

        <!-- Modale conversion -->
        <Modal :show="showConvertModal" @close="showConvertModal = false">
            <div class="p-6">
                <h3 class="mb-4 text-lg font-semibold text-gray-800">Convertir ce document</h3>
                <p class="mb-4 text-sm text-gray-500">
                    Un nouveau document sera créé avec les mêmes lignes, lié à {{ document.number }}.
                </p>
                <InputLabel value="Convertir en" />
                <select v-model="convertForm.target_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
                    <option v-for="t in convertTargets" :key="t.value" :value="t.value">{{ t.label }}</option>
                </select>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="showConvertModal = false">Annuler</SecondaryButton>
                    <PrimaryButton :disabled="convertForm.processing" @click="submitConvert">Convertir</PrimaryButton>
                </div>
            </div>
        </Modal>

        <!-- Modale création plan de paiement / acomptes -->
        <Modal :show="showPlanModal" max-width="2xl" @close="showPlanModal = false">
            <div class="p-6">
                <h3 class="mb-1 text-lg font-semibold text-gray-800">Créer un plan d'acompte</h3>
                <p class="mb-4 text-sm text-gray-500">
                    Total à répartir : <b class="text-gray-800">{{ fmt(documentTotal) }} {{ document.currency }}</b>.
                    La somme des échéances doit égaler ce total.
                </p>

                <div class="mb-4 flex flex-wrap gap-2">
                    <button v-for="key in Object.keys(presets)" :key="key" @click="applyPreset(key)"
                        class="rounded-full border border-gray-300 px-3 py-1 text-xs font-semibold text-gray-600 hover:bg-gray-50">
                        {{ key }}
                    </button>
                    <button @click="addInstallment"
                        class="rounded-full border border-brand-600 px-3 py-1 text-xs font-semibold text-brand-600 hover:bg-brand-50">
                        + Ligne personnalisée
                    </button>
                </div>

                <div class="space-y-2">
                    <div v-for="(row, i) in planForm.installments" :key="i"
                        class="grid grid-cols-12 items-center gap-2">
                        <input v-model="row.label" placeholder="Libellé"
                            class="col-span-4 rounded-md border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500" />
                        <input v-model="row.due_date" type="date"
                            class="col-span-3 rounded-md border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500" />
                        <select v-model="row.mode"
                            class="col-span-2 rounded-md border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                            <option value="amount">Montant</option>
                            <option value="percentage">%</option>
                        </select>
                        <input v-if="row.mode === 'percentage'" v-model.number="row.percentage" type="number" step="0.01" min="0" max="100"
                            class="col-span-2 rounded-md border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500" />
                        <input v-else v-model.number="row.amount" type="number" step="0.01" min="0"
                            class="col-span-2 rounded-md border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500" />
                        <button @click="removeInstallment(i)" class="col-span-1 text-center text-red-500 hover:text-red-700">✕</button>
                    </div>
                </div>

                <div class="mt-3 flex justify-between rounded-md px-3 py-2 text-sm"
                    :class="planBalanced ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700'">
                    <span>Somme des échéances</span>
                    <span class="font-semibold">
                        {{ fmt(planSum) }} / {{ fmt(documentTotal) }} {{ document.currency }}
                        <span v-if="!planBalanced"> — écart {{ fmt(planSum - documentTotal) }}</span>
                    </span>
                </div>
                <InputError :message="planForm.errors.installments" class="mt-1" />

                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="showPlanModal = false">Annuler</SecondaryButton>
                    <PrimaryButton :disabled="planForm.processing || !planBalanced || !planForm.installments.length"
                        @click="submitPlan">
                        Créer le plan
                    </PrimaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

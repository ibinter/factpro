<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
    order: Object,
    transactions: Array,
});

const fmt = (n) => new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(n ?? 0);

const providerLabels = {
    orange_money: 'Orange Money',
    mtn_momo: 'MTN Mobile Money',
    wave: 'Wave',
    moov: 'Moov Money',
    bank_transfer_national: 'Virement bancaire national',
    bank_transfer_international: 'Virement international',
    international_transfer: 'Transfert international',
    cash: 'Paiement espèces',
};

const orderStatusLabels = {
    pending_payment: 'En attente de paiement',
    proof_submitted: 'Preuve soumise',
    under_review: 'En cours de vérification',
    missing_info: 'Complément demandé',
    paid: 'Payé — Licence activée',
    rejected: 'Rejeté',
};

const statusIcon = {
    pending_payment: '⏳',
    proof_submitted: '📤',
    under_review: '🔍',
    missing_info: '⚠️',
    paid: '✅',
    rejected: '❌',
};

const statusColor = {
    pending_payment: 'bg-amber-50 border-amber-300 text-amber-800',
    proof_submitted: 'bg-indigo-50 border-indigo-300 text-indigo-800',
    under_review: 'bg-blue-50 border-blue-300 text-blue-800',
    missing_info: 'bg-orange-50 border-orange-300 text-orange-800',
    paid: 'bg-green-50 border-green-300 text-green-800',
    rejected: 'bg-red-50 border-red-300 text-red-800',
};

// Timeline steps
const timelineSteps = [
    { key: 'received', label: 'Preuve reçue', done: true },
    { key: 'review', label: 'Vérification en cours', done: ['under_review', 'missing_info', 'paid'].includes(props.order.status) },
    { key: 'validated', label: 'Paiement validé — Licence activée', done: props.order.status === 'paid' },
];

// Complement form
const showComplementForm = ref(false);
const complementForm = useForm({ comment: '', proof: null });

const submitComplement = () => {
    complementForm.post(route('billing.proof.complement', props.order.id), {
        forceFormData: true,
        onSuccess: () => {
            showComplementForm.value = false;
            complementForm.reset();
        },
    });
};

// Polling every 30s to refresh order status
let pollInterval = null;
onMounted(() => {
    if (!['paid', 'rejected'].includes(props.order.status)) {
        pollInterval = setInterval(() => {
            // Inertia soft reload to refresh props
            if (typeof window !== 'undefined' && window.Inertia) {
                window.Inertia.reload({ only: ['order', 'transactions'] });
            }
        }, 30000);
    }
});
onBeforeUnmount(() => {
    if (pollInterval) clearInterval(pollInterval);
});
</script>

<template>
    <Head title="Suivi de paiement" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Suivi — commande <span class="text-brand-600">{{ order.order_number }}</span>
                </h2>
                <Link :href="route('billing.index')" class="text-sm text-brand-600 hover:underline">
                    ← Mon abonnement
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-3xl space-y-6 px-4 sm:px-6 lg:px-8">

                <!-- Bannière statut -->
                <div
                    class="rounded-xl border-2 p-5 font-semibold text-lg"
                    :class="statusColor[order.status] ?? 'bg-gray-50 border-gray-200 text-gray-700'"
                >
                    <span class="mr-2 text-2xl">{{ statusIcon[order.status] ?? 'ℹ️' }}</span>
                    {{ orderStatusLabels[order.status] ?? order.status }}
                    <div class="mt-1 text-sm font-normal opacity-80">
                        {{ order.plan }} — {{ order.duration_months }} mois —
                        <b>{{ fmt(order.total_amount) }} {{ order.currency }}</b>
                    </div>
                </div>

                <!-- Timeline -->
                <div class="rounded-xl bg-white p-6 shadow">
                    <h3 class="mb-5 font-semibold text-gray-800">Avancement</h3>
                    <ol class="relative border-l-2 border-gray-200 space-y-6 ml-3">
                        <li v-for="step in timelineSteps" :key="step.key" class="ml-6">
                            <span
                                class="absolute -left-3.5 flex h-7 w-7 items-center justify-center rounded-full ring-4 ring-white text-sm"
                                :class="step.done ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-400'"
                            >
                                {{ step.done ? '✓' : '○' }}
                            </span>
                            <p class="text-sm font-medium" :class="step.done ? 'text-gray-800' : 'text-gray-400'">
                                {{ step.label }}
                            </p>
                        </li>
                    </ol>
                    <p v-if="!['paid', 'rejected'].includes(order.status)" class="mt-4 text-xs text-gray-400">
                        Cette page se rafraîchit automatiquement toutes les 30 secondes.
                    </p>
                </div>

                <!-- Résumé transaction -->
                <div v-if="transactions.length" class="rounded-xl bg-white p-6 shadow">
                    <h3 class="mb-4 font-semibold text-gray-800">Déclaration soumise</h3>
                    <div v-for="tx in transactions" :key="tx.id" class="mb-3 rounded-lg bg-gray-50 p-4 text-sm">
                        <div class="flex flex-wrap justify-between gap-2">
                            <div>
                                <div class="font-semibold text-gray-800">{{ providerLabels[tx.payment_provider] ?? tx.payment_provider }}</div>
                                <div class="text-xs text-gray-500">Réf. interne : {{ tx.internal_reference }}</div>
                                <div class="text-xs text-gray-500">Soumis le {{ tx.created_at }}</div>
                            </div>
                            <div class="text-right">
                                <div class="font-bold text-gray-800">{{ fmt(tx.amount_declared) }} {{ order.currency }}</div>
                                <div class="text-xs text-gray-500">{{ tx.proofs_count }} preuve(s) jointe(s)</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Formulaire complément -->
                <div v-if="['proof_submitted', 'under_review', 'missing_info'].includes(order.status)" class="rounded-xl bg-white p-6 shadow">
                    <h3 class="mb-2 font-semibold text-gray-800">
                        {{ order.status === 'missing_info' ? '⚠️ Complément demandé par notre équipe' : 'Ajouter un document complémentaire' }}
                    </h3>
                    <p class="mb-4 text-sm text-gray-500">
                        Si notre équipe vous demande un document supplémentaire, soumettez-le ici.
                    </p>

                    <button
                        v-if="!showComplementForm"
                        @click="showComplementForm = true"
                        class="rounded-md border border-brand-600 px-4 py-2 text-sm font-semibold text-brand-600 hover:bg-brand-50"
                    >
                        Ajouter un complément
                    </button>

                    <form v-else @submit.prevent="submitComplement" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Commentaire (optionnel)</label>
                            <textarea
                                v-model="complementForm.comment"
                                rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500"
                                placeholder="Expliquez le document joint…"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Document complémentaire *</label>
                            <input
                                type="file"
                                accept=".jpg,.jpeg,.png,.webp,.pdf"
                                @input="complementForm.proof = $event.target.files[0]"
                                class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:rounded-md file:border-0 file:bg-brand-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-brand-700 hover:file:bg-brand-100"
                                required
                            />
                            <p v-if="complementForm.errors.proof" class="mt-1 text-xs text-red-600">{{ complementForm.errors.proof }}</p>
                        </div>
                        <div class="flex gap-3">
                            <button
                                type="submit"
                                :disabled="complementForm.processing"
                                class="rounded-md bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 disabled:opacity-50"
                            >
                                {{ complementForm.processing ? 'Envoi…' : 'Envoyer le complément' }}
                            </button>
                            <button type="button" @click="showComplementForm = false" class="text-sm text-gray-500 hover:text-gray-700">
                                Annuler
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Reçu disponible si payé -->
                <div v-if="order.status === 'paid'" class="rounded-xl bg-green-50 p-5 shadow">
                    <p class="mb-3 font-semibold text-green-800">Votre licence est active !</p>
                    <div class="flex flex-wrap gap-3">
                        <Link
                            :href="route('billing.receipt.download', order.id)"
                            class="rounded-md bg-green-700 px-4 py-2 text-sm font-semibold text-white hover:bg-green-800"
                        >
                            Télécharger le reçu
                        </Link>
                        <Link
                            :href="route('dashboard')"
                            class="rounded-md border border-green-700 px-4 py-2 text-sm font-semibold text-green-700 hover:bg-green-100"
                        >
                            Aller au tableau de bord
                        </Link>
                    </div>
                </div>

                <!-- Support -->
                <div class="rounded-lg bg-gray-50 p-4 text-center text-sm text-gray-500">
                    Une question ? <a href="mailto:support@ibigsoft.com" class="font-semibold text-brand-600 hover:underline">Contacter le support</a>
                    <br>
                    <span class="text-xs">🔒 IBIG ne vous demandera jamais votre code secret Mobile Money, votre mot de passe ou votre code confidentiel.</span>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

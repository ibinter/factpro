<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    license: Object,
    orders: Object,
});

const activeTab = ref('subscription');

const fmt = (n) => new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(n ?? 0);

const orderStatusLabels = {
    draft: 'Brouillon', pending_payment: 'En attente de paiement', payment_initiated: 'Paiement lancé',
    proof_submitted: 'Preuve soumise', under_review: 'En cours de vérification',
    missing_info: 'Complément demandé', paid: 'Payée', expired: 'Expirée',
    cancelled: 'Annulée', rejected: 'Rejetée', refunded: 'Remboursée',
};
const orderStatusColors = {
    pending_payment: 'bg-amber-100 text-amber-700', payment_initiated: 'bg-blue-100 text-blue-700',
    proof_submitted: 'bg-indigo-100 text-indigo-700', under_review: 'bg-indigo-100 text-indigo-700',
    missing_info: 'bg-orange-100 text-orange-700', paid: 'bg-green-100 text-green-700',
    expired: 'bg-gray-100 text-gray-500', cancelled: 'bg-gray-100 text-gray-500',
    rejected: 'bg-red-100 text-red-700', refunded: 'bg-purple-100 text-purple-700',
};

const licenseStatusLabels = {
    trial: 'ESSAI', active: 'ACTIF', provisional: 'PROVISOIRE',
    grace_period: 'PÉRIODE DE GRÂCE', suspended: 'SUSPENDU', expired: 'EXPIRÉ',
};

const bannerClass = computed(() => {
    const s = props.license?.status;
    if (s === 'active') return 'from-green-700 to-green-600';
    if (s === 'trial') return 'from-brand-900 to-brand-600';
    if (s === 'grace_period') return 'from-orange-600 to-orange-500';
    if (s === 'expired' || s === 'suspended') return 'from-red-700 to-red-600';
    return 'from-brand-900 to-brand-600';
});

// Progress circle SVG
const progressPercent = computed(() => {
    if (!props.license) return 0;
    const d = props.license.days_remaining;
    // Assume a max visible span of 365 days
    return Math.min(100, Math.round((d / 365) * 100));
});

const circumference = 2 * Math.PI * 36; // r=36
const dashOffset = computed(() => circumference * (1 - progressPercent.value / 100));

const tabs = [
    { key: 'subscription', label: 'Mon abonnement' },
    { key: 'payments', label: 'Paiements' },
    { key: 'methods', label: 'Moyens de paiement' },
];

const paymentMethods = [
    { icon: '💳', title: 'Paiement en ligne (Moneroo)', desc: 'Carte bancaire, Mobile Money, Wallets — Activation automatique immédiate', delay: 'Immédiat' },
    { icon: '📱', title: 'Mobile Money manuel', desc: 'Wave, Orange Money, MTN MoMo, Moov Money — Envoi de preuve requis', delay: '2–24h' },
    { icon: '🏦', title: 'Virement bancaire national', desc: 'Coordonnées bancaires fournies à l\'étape de paiement', delay: '1–5 jours' },
    { icon: '🌍', title: 'Transfert international', desc: 'Western Union, MoneyGram, Ria, Sendwave, WorldRemit, SWIFT', delay: '2–5 jours' },
    { icon: '💵', title: 'Paiement en espèces', desc: 'Au siège IBIG Soft — Sur rendez-vous', delay: 'Immédiat (sur place)' },
];
</script>

<template>
    <Head title="Abonnement & Facturation" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Abonnement & Facturation</h2>
                <Link
                    :href="route('billing.plans')"
                    class="rounded-md bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700"
                >
                    {{ license?.type === 'trial' ? 'Choisir un forfait' : 'Changer de forfait' }}
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">

                <!-- Bannière licence -->
                <div v-if="license" class="rounded-xl bg-gradient-to-r p-6 text-white shadow-lg" :class="bannerClass">
                    <div class="flex flex-wrap items-center justify-between gap-6">
                        <div class="flex items-center gap-5">
                            <!-- Cercle de progression SVG -->
                            <div class="relative flex-shrink-0">
                                <svg width="80" height="80" viewBox="0 0 80 80">
                                    <circle cx="40" cy="40" r="36" fill="none" stroke="rgba(255,255,255,0.2)" stroke-width="6" />
                                    <circle
                                        cx="40" cy="40" r="36"
                                        fill="none"
                                        stroke="rgba(255,255,255,0.9)"
                                        stroke-width="6"
                                        stroke-linecap="round"
                                        :stroke-dasharray="circumference"
                                        :stroke-dashoffset="dashOffset"
                                        transform="rotate(-90 40 40)"
                                    />
                                </svg>
                                <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                                    <span class="text-lg font-extrabold leading-none">{{ license.days_remaining }}</span>
                                    <span class="text-[10px] opacity-80">jours</span>
                                </div>
                            </div>
                            <div>
                                <div class="flex items-center gap-3">
                                    <span class="text-2xl font-extrabold">PLAN {{ license.plan }}</span>
                                    <span
                                        class="rounded-full px-2.5 py-0.5 text-xs font-bold"
                                        :class="license.status === 'active' ? 'bg-green-400 text-green-950' : license.status === 'trial' ? 'bg-gold-400 text-brand-900' : 'bg-red-400 text-red-950'"
                                    >
                                        ● {{ licenseStatusLabels[license.status] ?? license.status }}
                                    </span>
                                </div>
                                <div class="mt-1 text-sm opacity-80">
                                    {{ license.starts_at }} → {{ license.ends_at }}
                                </div>
                                <div class="mt-0.5 font-mono text-xs opacity-60">Clé : {{ license.key }}</div>
                            </div>
                        </div>
                        <div v-if="license.limits" class="space-y-1 text-xs opacity-90">
                            <div v-if="license.limits.documents_per_month">
                                📄 {{ license.limits.documents_per_month === 'unlimited' ? 'Documents illimités' : license.limits.documents_per_month + ' documents/mois' }}
                            </div>
                            <div>👥 {{ license.limits.users === 'unlimited' ? 'Utilisateurs illimités' : license.limits.users + ' utilisateur(s)' }}</div>
                            <div>🏢 {{ license.limits.companies === 'unlimited' ? 'Sociétés illimitées' : license.limits.companies + ' société(s)' }}</div>
                        </div>
                    </div>
                </div>

                <div v-else class="rounded-xl border-2 border-dashed border-brand-200 bg-brand-50 p-8 text-center">
                    <p class="text-gray-600">Aucun abonnement actif. Choisissez un forfait pour commencer.</p>
                    <Link :href="route('billing.plans')" class="mt-4 inline-block rounded-md bg-brand-600 px-6 py-2.5 text-sm font-bold text-white hover:bg-brand-700">
                        Voir les forfaits
                    </Link>
                </div>

                <!-- Onglets -->
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex gap-6 overflow-x-auto">
                        <button
                            v-for="tab in tabs"
                            :key="tab.key"
                            @click="activeTab = tab.key"
                            class="whitespace-nowrap border-b-2 px-1 pb-3 text-sm font-medium transition"
                            :class="activeTab === tab.key ? 'border-brand-600 text-brand-700' : 'border-transparent text-gray-500 hover:text-gray-700'"
                        >
                            {{ tab.label }}
                        </button>
                    </nav>
                </div>

                <!-- Onglet : Historique des paiements -->
                <div v-if="activeTab === 'payments'" class="overflow-hidden rounded-lg bg-white shadow">
                    <div class="border-b px-6 py-4">
                        <h3 class="font-semibold text-gray-800">Historique des commandes</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                                <tr>
                                    <th class="px-6 py-3">Commande</th>
                                    <th class="px-6 py-3">Forfait</th>
                                    <th class="px-6 py-3">Durée</th>
                                    <th class="px-6 py-3 text-right">Montant</th>
                                    <th class="px-6 py-3">Statut</th>
                                    <th class="px-6 py-3 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr v-for="order in orders.data" :key="order.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-3">
                                        <div class="font-semibold text-gray-800">{{ order.order_number }}</div>
                                        <div class="text-xs text-gray-400">{{ new Date(order.created_at).toLocaleDateString('fr-FR') }}</div>
                                    </td>
                                    <td class="px-6 py-3">{{ order.plan?.name }}</td>
                                    <td class="px-6 py-3">{{ order.duration_months }} mois</td>
                                    <td class="px-6 py-3 text-right font-semibold">{{ fmt(order.total_amount) }} {{ order.currency }}</td>
                                    <td class="px-6 py-3">
                                        <span class="rounded-full px-2 py-0.5 text-xs font-semibold" :class="orderStatusColors[order.status] ?? 'bg-gray-100 text-gray-600'">
                                            {{ orderStatusLabels[order.status] ?? order.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 text-right">
                                        <Link
                                            v-if="['pending_payment', 'payment_initiated', 'missing_info'].includes(order.status)"
                                            :href="route('billing.checkout', order.id)"
                                            class="text-sm font-semibold text-brand-600 hover:underline"
                                        >
                                            Payer →
                                        </Link>
                                        <Link
                                            v-else-if="['proof_submitted', 'under_review'].includes(order.status)"
                                            :href="route('billing.proof-status', order.id)"
                                            class="text-sm font-semibold text-indigo-600 hover:underline"
                                        >
                                            Suivi →
                                        </Link>
                                        <Link
                                            v-else-if="order.status === 'paid'"
                                            :href="route('billing.receipt.download', order.id)"
                                            class="text-sm font-semibold text-green-600 hover:underline"
                                        >
                                            Reçu ↓
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-if="!orders.data.length">
                                    <td colspan="6" class="px-6 py-10 text-center text-gray-400">Aucune commande pour l'instant.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-if="orders.links?.length > 3" class="flex flex-wrap gap-1 px-4 py-3">
                        <template v-for="link in orders.links" :key="link.label">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                v-html="link.label"
                                class="rounded px-3 py-1.5 text-sm"
                                :class="link.active ? 'bg-brand-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'"
                            />
                            <span v-else v-html="link.label" class="px-3 py-1.5 text-sm text-gray-400" />
                        </template>
                    </div>
                </div>

                <!-- Onglet : Abonnement (par défaut) -->
                <div v-if="activeTab === 'subscription'" class="space-y-4">
                    <div class="rounded-lg bg-white p-6 shadow">
                        <h3 class="mb-4 font-semibold text-gray-800">Actions rapides</h3>
                        <div class="flex flex-wrap gap-3">
                            <Link :href="route('billing.plans')" class="rounded-md bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                                {{ license?.status === 'trial' ? 'Choisir un forfait' : 'Renouveler / Changer' }}
                            </Link>
                        </div>
                    </div>

                    <!-- Commandes récentes -->
                    <div v-if="orders.data.length" class="rounded-lg bg-white p-6 shadow">
                        <h3 class="mb-4 font-semibold text-gray-800">Dernières commandes</h3>
                        <div class="space-y-2">
                            <div v-for="order in orders.data.slice(0, 3)" :key="order.id"
                                class="flex items-center justify-between rounded-md bg-gray-50 px-4 py-3 text-sm">
                                <div>
                                    <span class="font-semibold text-gray-700">{{ order.order_number }}</span>
                                    <span class="ml-2 text-gray-400">{{ order.plan?.name }} · {{ order.duration_months }} mois</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="font-semibold">{{ fmt(order.total_amount) }} {{ order.currency }}</span>
                                    <span class="rounded-full px-2 py-0.5 text-xs font-semibold" :class="orderStatusColors[order.status] ?? 'bg-gray-100 text-gray-600'">
                                        {{ orderStatusLabels[order.status] ?? order.status }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <button @click="activeTab = 'payments'" class="mt-3 text-sm text-brand-600 hover:underline">
                            Voir tout l'historique →
                        </button>
                    </div>
                </div>

                <!-- Onglet : Moyens de paiement -->
                <div v-if="activeTab === 'methods'" class="space-y-3">
                    <div v-for="method in paymentMethods" :key="method.title"
                        class="flex items-start gap-4 rounded-lg bg-white p-5 shadow">
                        <span class="text-3xl flex-shrink-0">{{ method.icon }}</span>
                        <div>
                            <div class="font-semibold text-gray-800">{{ method.title }}</div>
                            <div class="text-sm text-gray-500">{{ method.desc }}</div>
                            <div class="mt-1 text-xs text-brand-600 font-medium">Délai : {{ method.delay }}</div>
                        </div>
                    </div>
                    <p class="rounded-md bg-amber-50 px-4 py-3 text-xs text-amber-800">
                        🔒 IBIG ne vous demandera <b>jamais</b> votre code secret Mobile Money, votre mot de passe ou votre code confidentiel.
                    </p>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

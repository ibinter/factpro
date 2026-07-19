<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';

const props = defineProps({
    documents: Object,
    filters: { type: Object, default: () => ({}) },
    types: Array,
    categories: { type: Object, default: () => ({}) },
    stats: { type: Object, default: () => ({}) },
});

// ── Filtres ───────────────────────────────────────────────────────────────────
const search   = ref(props.filters.search   ?? '');
const type     = ref(props.filters.type     ?? '');
const category = ref(props.filters.category ?? '');
const status   = ref(props.filters.status   ?? '');
const period   = ref(props.filters.period   ?? '');

let timeout = null;
const applyFilters = () => {
    router.get(
        route('documents.index'),
        {
            search:   search.value   || undefined,
            type:     type.value     || undefined,
            category: category.value || undefined,
            status:   status.value   || undefined,
            period:   period.value   || undefined,
        },
        { preserveState: true, replace: true },
    );
};

watch(search, () => {
    clearTimeout(timeout);
    timeout = setTimeout(applyFilters, 350);
});
watch([type, category, status, period], applyFilters);

// Quand on change de catégorie, réinitialiser le type si incompatible
watch(category, (newCat) => {
    if (newCat && type.value) {
        const found = props.types.find(t => t.value === type.value && t.category === newCat);
        if (!found) type.value = '';
    }
});

const clearFilters = () => {
    search.value   = '';
    type.value     = '';
    category.value = '';
    status.value   = '';
    period.value   = '';
};

const hasFilters = computed(() =>
    search.value || type.value || category.value || status.value || period.value
);

// Types filtrés par catégorie sélectionnée (pour les pills)
const filteredTypes = computed(() =>
    category.value ? props.types.filter(t => t.category === category.value) : props.types
);

// URL export Excel avec les filtres actifs
const excelExportUrl = computed(() => {
    const params = new URLSearchParams();
    if (search.value)   params.set('search',   search.value);
    if (type.value)     params.set('type',     type.value);
    if (category.value) params.set('category', category.value);
    if (status.value)   params.set('status',   status.value);
    if (period.value)   params.set('period',   period.value);
    const qs = params.toString();
    return route('documents.export.excel') + (qs ? '?' + qs : '');
});

// ── Formatters ────────────────────────────────────────────────────────────────
const fmt = (n) => new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(n ?? 0);

// ── Statuts ───────────────────────────────────────────────────────────────────
const statusLabels = {
    draft: 'Brouillon', sent: 'Envoyé', viewed: 'Vu', accepted: 'Accepté',
    rejected: 'Refusé', partial: 'Partiel', paid: 'Payé', overdue: 'En retard',
    cancelled: 'Annulé', converted: 'Converti',
};
const statusColors = {
    draft:     'bg-gray-100 text-gray-600 ring-gray-200',
    sent:      'bg-blue-50  text-blue-700  ring-blue-200',
    viewed:    'bg-indigo-50 text-indigo-700 ring-indigo-200',
    accepted:  'bg-emerald-50 text-emerald-700 ring-emerald-200',
    rejected:  'bg-red-50   text-red-700   ring-red-200',
    partial:   'bg-amber-50  text-amber-700  ring-amber-200',
    paid:      'bg-emerald-50 text-emerald-700 ring-emerald-200',
    overdue:   'bg-red-50   text-red-600   ring-red-300',
    cancelled: 'bg-gray-100 text-gray-400  ring-gray-200',
    converted: 'bg-purple-50 text-purple-700 ring-purple-200',
};
const statusDot = {
    draft: 'bg-gray-400', sent: 'bg-blue-500', viewed: 'bg-indigo-500',
    accepted: 'bg-emerald-500', rejected: 'bg-red-500', partial: 'bg-amber-500',
    paid: 'bg-emerald-500', overdue: 'bg-red-600', cancelled: 'bg-gray-300',
    converted: 'bg-purple-500',
};

// ── Types ─────────────────────────────────────────────────────────────────────
const typeConfig = {
    invoice:         { label: 'Facture',              bg: 'bg-blue-600',    text: 'text-blue-600'    },
    quote:           { label: 'Devis',                bg: 'bg-amber-500',   text: 'text-amber-600'   },
    proforma:        { label: 'Facture Proforma',     bg: 'bg-violet-500',  text: 'text-violet-600'  },
    sales_order:     { label: 'Bon de Commande',      bg: 'bg-sky-500',     text: 'text-sky-600'     },
    purchase_order:  { label: 'Commande Fournisseur', bg: 'bg-indigo-500',  text: 'text-indigo-600'  },
    delivery_note:   { label: 'Bon de Livraison',     bg: 'bg-teal-500',    text: 'text-teal-600'    },
    credit_note:     { label: 'Avoir',                bg: 'bg-rose-500',    text: 'text-rose-600'    },
    payment_receipt: { label: 'Reçu de Paiement',     bg: 'bg-emerald-500', text: 'text-emerald-600' },
    deposit_invoice: { label: "Facture d'Acompte",    bg: 'bg-cyan-500',    text: 'text-cyan-600'    },
    balance_invoice: { label: 'Facture de Solde',     bg: 'bg-blue-700',    text: 'text-blue-700'    },
    work_order:      { label: 'Bon de Travaux',       bg: 'bg-orange-500',  text: 'text-orange-600'  },
    pos_ticket:      { label: 'Ticket de Caisse',     bg: 'bg-gray-500',    text: 'text-gray-600'    },
    quittance:       { label: 'Quittance',            bg: 'bg-lime-500',    text: 'text-lime-600'    },
    rma:             { label: 'Bon de Retour RMA',    bg: 'bg-pink-500',    text: 'text-pink-600'    },
    remittance:      { label: 'Bordereau de Remise',  bg: 'bg-purple-500',  text: 'text-purple-600'  },
};
const getTypeConf = (t) => typeConfig[t] ?? { label: t, bg: 'bg-gray-400', text: 'text-gray-600', icon: '📄' };

// ── Date helpers ──────────────────────────────────────────────────────────────
const isOverdue = (doc) =>
    doc.due_date && !['paid','cancelled','converted'].includes(doc.status) && new Date(doc.due_date) < new Date();

const daysDue = (doc) => {
    if (!doc.due_date) return null;
    const diff = Math.ceil((new Date() - new Date(doc.due_date)) / 86400000);
    return diff;
};

const formatDate = (d) => {
    if (!d) return '—';
    const [y, m, day] = d.slice(0, 10).split('-');
    return `${day}/${m}/${y}`;
};

// ── Paiement partiel barre ────────────────────────────────────────────────────
const paymentPct = (doc) => {
    if (!doc.total || !doc.amount_paid) return 0;
    return Math.min(100, Math.round((doc.amount_paid / doc.total) * 100));
};

// ── Actions rapides ───────────────────────────────────────────────────────────
const quickActions = [
    { type: 'invoice',       label: 'Facture',     cls: 'bg-brand-600 text-white hover:bg-brand-700' },
    { type: 'quote',         label: 'Devis',       cls: 'border border-amber-400 text-amber-700 hover:bg-amber-50' },
    { type: 'proforma',      label: 'Proforma',    cls: 'border border-violet-300 text-violet-700 hover:bg-violet-50' },
    { type: 'delivery_note', label: 'Bon de livr.', cls: 'border border-teal-300 text-teal-700 hover:bg-teal-50' },
    { type: 'credit_note',   label: 'Avoir',       cls: 'border border-rose-300 text-rose-700 hover:bg-rose-50' },
];
</script>

<template>
    <Head title="Documents commerciaux" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-50 pb-12">

            <!-- ── Page header ────────────────────────────────────────────── -->
            <div class="bg-white border-b border-gray-100 px-6 py-4">
                <div class="mx-auto max-w-7xl flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">Documents commerciaux</h1>
                        <p class="text-xs text-gray-400 mt-0.5">{{ documents.total }} document{{ documents.total > 1 ? 's' : '' }} au total</p>
                    </div>
                    <!-- Quick create buttons -->
                    <div class="flex flex-wrap gap-2">
                        <Link v-for="a in quickActions" :key="a.type"
                            :href="route('documents.create', { type: a.type })"
                            class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-xs font-semibold transition-colors"
                            :class="a.cls">
                            + {{ a.label }}
                        </Link>
                    </div>
                </div>
            </div>

            <div class="mx-auto max-w-7xl px-4 sm:px-6 py-6 space-y-5">

                <!-- ── KPI cards ──────────────────────────────────────────── -->
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <!-- CA du mois -->
                    <div class="rounded-xl bg-white p-4 shadow-sm border border-gray-100">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-blue-50 text-blue-600 text-sm">💰</span>
                            <span class="text-xs font-medium text-gray-500">CA ce mois</span>
                        </div>
                        <p class="text-xl font-bold text-gray-900">{{ fmt(stats.ca_month) }}</p>
                        <p class="text-[10px] text-gray-400 mt-0.5">XOF — factures émises</p>
                    </div>
                    <!-- Impayés -->
                    <div class="rounded-xl bg-white p-4 shadow-sm border border-gray-100">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-amber-50 text-amber-600 text-sm">⏳</span>
                            <span class="text-xs font-medium text-gray-500">À encaisser</span>
                        </div>
                        <p class="text-xl font-bold text-gray-900">{{ fmt(stats.outstanding) }}</p>
                        <p class="text-[10px] text-gray-400 mt-0.5">XOF — impayées</p>
                    </div>
                    <!-- En retard -->
                    <div class="rounded-xl bg-white p-4 shadow-sm border border-gray-100">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-red-50 text-red-600 text-sm">⚠️</span>
                            <span class="text-xs font-medium text-gray-500">En retard</span>
                        </div>
                        <p class="text-xl font-bold" :class="stats.overdue > 0 ? 'text-red-600' : 'text-gray-900'">{{ stats.overdue }}</p>
                        <p class="text-[10px] text-gray-400 mt-0.5">facture{{ stats.overdue > 1 ? 's' : '' }} échue{{ stats.overdue > 1 ? 's' : '' }}</p>
                    </div>
                    <!-- Brouillons -->
                    <div class="rounded-xl bg-white p-4 shadow-sm border border-gray-100">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-gray-100 text-gray-500 text-sm">📝</span>
                            <span class="text-xs font-medium text-gray-500">Brouillons</span>
                        </div>
                        <p class="text-xl font-bold text-gray-900">{{ stats.drafts }}</p>
                        <p class="text-[10px] text-gray-400 mt-0.5">document{{ stats.drafts > 1 ? 's' : '' }} non finalisé{{ stats.drafts > 1 ? 's' : '' }}</p>
                    </div>
                </div>

                <!-- ── Filtres ─────────────────────────────────────────────── -->
                <div class="rounded-xl bg-white p-4 shadow-sm border border-gray-100">
                    <div class="flex flex-wrap items-center gap-3">
                        <!-- Recherche -->
                        <div class="relative min-w-[200px] flex-1">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <input
                                v-model="search"
                                type="search"
                                placeholder="Rechercher un N° ou un client…"
                                class="block w-full rounded-lg border-gray-300 pl-10 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500"
                            />
                        </div>

                        <!-- Catégorie -->
                        <select v-model="category" class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                            <option value="">Toutes catégories</option>
                            <option v-for="(label, key) in categories" :key="key" :value="key">{{ label }}</option>
                        </select>

                        <!-- Type (filtré par catégorie) -->
                        <select v-model="type" class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                            <option value="">Tous les types</option>
                            <option v-for="t in filteredTypes" :key="t.value" :value="t.value">{{ t.label }}</option>
                        </select>

                        <!-- Statut -->
                        <select v-model="status" class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                            <option value="">Tous les statuts</option>
                            <option v-for="(label, val) in statusLabels" :key="val" :value="val">{{ label }}</option>
                        </select>

                        <!-- Période -->
                        <select v-model="period" class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                            <option value="">Toute la période</option>
                            <option value="30">30 derniers jours</option>
                            <option value="90">3 derniers mois</option>
                            <option value="180">6 derniers mois</option>
                            <option value="365">12 derniers mois</option>
                        </select>

                        <!-- Export Excel -->
                        <a :href="excelExportUrl" title="Exporter en Excel"
                            class="flex items-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-100 transition-colors">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Excel
                        </a>

                        <!-- Clear -->
                        <button v-if="hasFilters" type="button" @click="clearFilters"
                            class="flex items-center gap-1 rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-500 hover:bg-gray-50 transition-colors">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            Effacer
                        </button>
                    </div>

                    <!-- Catégorie pills -->
                    <div class="mt-3 flex flex-wrap gap-1.5 border-b border-gray-50 pb-2.5">
                        <button type="button" @click="category = ''; type = ''"
                            class="rounded-full px-3 py-1 text-xs font-medium transition-colors"
                            :class="!category ? 'bg-brand-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'">
                            Tout
                        </button>
                        <button v-for="(label, key) in categories" :key="key" type="button" @click="category = key; type = ''"
                            class="rounded-full px-3 py-1 text-xs font-medium transition-colors"
                            :class="category === key ? 'bg-brand-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'">
                            {{ label }}
                        </button>
                    </div>

                    <!-- Type pills (filtrés par catégorie) -->
                    <div class="mt-2.5 flex flex-wrap gap-1.5">
                        <button type="button" @click="type = ''"
                            class="rounded-full px-2.5 py-0.5 text-[11px] font-medium transition-colors"
                            :class="!type ? 'bg-gray-800 text-white shadow-sm' : 'bg-gray-50 text-gray-500 hover:bg-gray-100'">
                            Tous types
                        </button>
                        <button v-for="t in filteredTypes" :key="t.value" type="button" @click="type = t.value"
                            class="rounded-full px-2.5 py-0.5 text-[11px] font-medium transition-colors"
                            :class="type === t.value ? 'bg-gray-800 text-white shadow-sm' : 'bg-gray-50 text-gray-500 hover:bg-gray-100'">
                            {{ t.label }}
                        </button>
                    </div>
                </div>

                <!-- ── Table ───────────────────────────────────────────────── -->
                <div class="overflow-hidden rounded-xl bg-white shadow-sm border border-gray-100">
                    <!-- Résultats info -->
                    <div class="flex items-center justify-between border-b border-gray-50 px-5 py-3">
                        <p class="text-xs text-gray-500">
                            <span class="font-semibold text-gray-800">{{ documents.total }}</span>
                            résultat{{ documents.total > 1 ? 's' : '' }}
                            <span v-if="hasFilters" class="text-gray-400"> (filtré{{ documents.total > 1 ? 's' : '' }})</span>
                        </p>
                        <p class="text-xs text-gray-400">
                            Page {{ documents.current_page }} / {{ documents.last_page }}
                        </p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="border-b border-gray-100 bg-gray-50/60">
                                <tr>
                                    <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-gray-500" style="width:10%">Type</th>
                                    <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-gray-500" style="width:12%">Numéro</th>
                                    <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-gray-500" style="width:20%">Client</th>
                                    <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-gray-500" style="width:12%">Émis le</th>
                                    <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-gray-500" style="width:12%">Échéance</th>
                                    <th class="px-5 py-3 text-right text-[11px] font-semibold uppercase tracking-wide text-gray-500" style="width:14%">Montant TTC</th>
                                    <th class="px-5 py-3 text-center text-[11px] font-semibold uppercase tracking-wide text-gray-500" style="width:12%">Statut</th>
                                    <th class="px-4 py-3 text-center text-[11px] font-semibold uppercase tracking-wide text-gray-500" style="width:8%">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <tr v-for="doc in documents.data" :key="doc.id"
                                    class="group hover:bg-blue-50/40 transition-colors cursor-pointer"
                                    @click="router.visit(route('documents.show', doc.id))">

                                    <!-- Type badge -->
                                    <td class="px-5 py-3.5">
                                        <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold"
                                            :class="[getTypeConf(doc.type).text, 'bg-opacity-10 ring-1 ring-inset', 'bg-gray-100']"
                                            :style="{ backgroundColor: 'transparent' }">
                                            <span class="h-1.5 w-1.5 rounded-full flex-shrink-0"
                                                :class="{
                                                    'bg-blue-500': doc.type === 'invoice',
                                                    'bg-amber-500': doc.type === 'quote',
                                                    'bg-violet-500': doc.type === 'proforma',
                                                    'bg-teal-500': doc.type === 'delivery_note',
                                                    'bg-rose-500': doc.type === 'credit_note',
                                                    'bg-indigo-500': doc.type === 'purchase_order',
                                                    'bg-emerald-500': doc.type === 'receipt',
                                                    'bg-gray-400': !typeConfig[doc.type],
                                                }"></span>
                                            {{ getTypeConf(doc.type).label }}
                                        </span>
                                    </td>

                                    <!-- Numéro -->
                                    <td class="px-5 py-3.5">
                                        <span class="font-mono text-xs font-bold text-brand-700 group-hover:text-brand-900">
                                            {{ doc.number }}
                                        </span>
                                        <span v-if="doc.finalized_at" class="ml-1 text-[10px] text-gray-400" title="Document scellé">🔒</span>
                                    </td>

                                    <!-- Client -->
                                    <td class="px-5 py-3.5">
                                        <span class="truncate font-medium text-gray-800 max-w-[160px] block">
                                            {{ doc.customer?.name ?? '—' }}
                                        </span>
                                    </td>

                                    <!-- Date émission -->
                                    <td class="px-5 py-3.5 text-xs text-gray-500">
                                        {{ formatDate(doc.issue_date) }}
                                    </td>

                                    <!-- Échéance + retard -->
                                    <td class="px-5 py-3.5 text-xs">
                                        <template v-if="doc.due_date">
                                            <span :class="isOverdue(doc) ? 'text-red-600 font-semibold' : 'text-gray-500'">
                                                {{ formatDate(doc.due_date) }}
                                            </span>
                                            <span v-if="isOverdue(doc)"
                                                class="ml-1 rounded-full bg-red-100 px-1.5 py-0.5 text-[10px] font-bold text-red-700">
                                                +{{ daysDue(doc) }}j
                                            </span>
                                        </template>
                                        <span v-else class="text-gray-300">—</span>
                                    </td>

                                    <!-- Montant + barre partiel -->
                                    <td class="px-5 py-3.5 text-right">
                                        <span class="font-bold text-gray-900 text-sm">{{ fmt(doc.total) }}</span>
                                        <span class="text-[10px] text-gray-400 ml-1">{{ doc.currency }}</span>
                                        <!-- Barre paiement partiel -->
                                        <div v-if="doc.status === 'partial' && doc.amount_paid > 0" class="mt-1">
                                            <div class="h-1 w-full rounded-full bg-gray-100">
                                                <div class="h-1 rounded-full bg-amber-400" :style="{ width: paymentPct(doc) + '%' }"></div>
                                            </div>
                                            <span class="text-[9px] text-amber-600">{{ paymentPct(doc) }}% encaissé</span>
                                        </div>
                                    </td>

                                    <!-- Statut -->
                                    <td class="px-5 py-3.5 text-center">
                                        <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[11px] font-semibold ring-1 ring-inset"
                                            :class="statusColors[doc.status] ?? 'bg-gray-100 text-gray-600 ring-gray-200'">
                                            <span class="h-1.5 w-1.5 rounded-full flex-shrink-0"
                                                :class="statusDot[doc.status] ?? 'bg-gray-400'"></span>
                                            {{ statusLabels[doc.status] ?? doc.status }}
                                        </span>
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-4 py-3.5" @click.stop>
                                        <div class="flex items-center justify-center gap-1">
                                            <Link :href="route('documents.show', doc.id)"
                                                class="rounded-lg p-1.5 text-gray-400 hover:bg-white hover:text-brand-600 hover:shadow-sm transition-all"
                                                title="Voir">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </Link>
                                            <Link :href="route('documents.edit', doc.id)"
                                                class="rounded-lg p-1.5 text-gray-400 hover:bg-white hover:text-gray-700 hover:shadow-sm transition-all"
                                                title="Modifier">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </Link>
                                            <a :href="route('documents.pdf', doc.id)" target="_blank" @click.stop
                                                class="rounded-lg p-1.5 text-gray-400 hover:bg-white hover:text-red-600 hover:shadow-sm transition-all"
                                                title="Télécharger PDF">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Empty state -->
                                <tr v-if="!documents.data.length">
                                    <td colspan="8" class="py-16 text-center">
                                        <div class="flex flex-col items-center gap-3">
                                            <span class="text-4xl">📋</span>
                                            <p class="text-base font-semibold text-gray-600">Aucun document trouvé</p>
                                            <p v-if="hasFilters" class="text-sm text-gray-400">Essayez de modifier ou supprimer vos filtres</p>
                                            <p v-else class="text-sm text-gray-400">Créez votre premier document commercial</p>
                                            <div class="mt-2 flex gap-2">
                                                <button v-if="hasFilters" type="button" @click="clearFilters"
                                                    class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">
                                                    Effacer les filtres
                                                </button>
                                                <Link :href="route('documents.create', { type: type || 'invoice' })"
                                                    class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                                                    + {{ type ? getTypeConf(type).label : 'Nouvelle facture' }}
                                                </Link>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ── Pagination ──────────────────────────────────────────── -->
                <div v-if="documents.last_page > 1" class="flex items-center justify-between">
                    <p class="text-xs text-gray-500">
                        {{ documents.from }}–{{ documents.to }} sur {{ documents.total }} résultats
                    </p>
                    <div class="flex flex-wrap gap-1">
                        <template v-for="link in documents.links" :key="link.label">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                v-html="link.label"
                                class="rounded-lg px-3 py-1.5 text-xs font-medium transition-colors"
                                :class="link.active
                                    ? 'bg-brand-600 text-white shadow-sm'
                                    : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'"
                            />
                            <span v-else v-html="link.label" class="px-2 py-1.5 text-xs text-gray-400" />
                        </template>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

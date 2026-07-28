<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import OnboardingChecklist from '@/Components/OnboardingChecklist.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';

const props = defineProps({
    currency:           { type: String,  default: 'XOF' },
    stats:              { type: Object,  default: () => ({}) },
    statusBreakdown:    Object,
    recentDocuments:    Array,
    chart:              Array,
    monthlyRevenue:     Array,
    topCustomers:       Array,
    topProducts:        Array,
    alerts:             Array,
    conversionRate:     Number,
    checklistProgress:  { type: Object,  default: () => null },
    checklistDismissed: { type: Boolean, default: false },
});

// ── Formatters ───────────────────────────────────────────────────────────────
const fmt = (n) => new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(n ?? 0);
const fmtMoney = (n) => fmt(n) + ' ' + props.currency;

// ── Animated counter ─────────────────────────────────────────────────────────
const animatedValues = ref({ revenue: 0, outstanding: 0, invoices: 0, quotes: 0 });
onMounted(() => {
    const targets = {
        revenue:     props.stats?.revenue_month  ?? 0,
        outstanding: props.stats?.outstanding    ?? 0,
        invoices:    props.stats?.invoices_month ?? 0,
        quotes:      props.stats?.quotes_pending ?? 0,
    };
    const steps = 40; let step = 0;
    const timer = setInterval(() => {
        step++;
        const ease = 1 - Math.pow(1 - step / steps, 3);
        animatedValues.value.revenue     = Math.round(targets.revenue     * ease);
        animatedValues.value.outstanding = Math.round(targets.outstanding * ease);
        animatedValues.value.invoices    = Math.round(targets.invoices    * ease);
        animatedValues.value.quotes      = Math.round(targets.quotes      * ease);
        if (step >= steps) clearInterval(timer);
    }, 1200 / steps);
});

// ── 12-month area chart SVG ───────────────────────────────────────────────────
const chartW = 700; const chartH = 160; const padT = 10;
const chartData = computed(() => props.monthlyRevenue ?? props.chart ?? []);
const maxVal    = computed(() => Math.max(...chartData.value.map(d => d.revenue ?? d.total ?? 0), 1));

const points = computed(() => {
    const data = chartData.value;
    if (data.length < 2) return [];
    return data.map((d, i) => {
        const x = (i / (data.length - 1)) * chartW;
        const val = d.revenue ?? d.total ?? 0;
        const y = padT + (chartH - padT) - (val / maxVal.value) * (chartH - padT);
        return { x, y, val, label: d.month, count: d.invoices_count };
    });
});
const polyline = computed(() => points.value.map(p => `${p.x},${p.y}`).join(' '));
const areaPath = computed(() => {
    if (!points.value.length) return '';
    const first = points.value[0]; const last = points.value[points.value.length - 1];
    return `M${first.x},${chartH} ${points.value.map(p => `L${p.x},${p.y}`).join(' ')} L${last.x},${chartH} Z`;
});
const hoveredPoint = ref(null);

// ── Donut chart ──────────────────────────────────────────────────────────────
const statusColors  = { paid: '#10b981', sent: '#3b82f6', draft: '#94a3b8', overdue: '#ef4444', partial: '#f59e0b', cancelled: '#6b7280', viewed: '#8b5cf6', converted: '#06b6d4', accepted: '#10b981', rejected: '#ef4444' };
const statusLabels  = { paid: 'Payée', sent: 'Envoyée', draft: 'Brouillon', overdue: 'En retard', partial: 'Partielle', cancelled: 'Annulée', viewed: 'Vue', converted: 'Convertie', accepted: 'Accepté', rejected: 'Refusé' };

const donutTotal = computed(() => Object.values(props.statusBreakdown ?? {}).reduce((a, b) => a + b, 0));
const donutData  = computed(() => {
    const bd = props.statusBreakdown ?? {}; const total = donutTotal.value;
    if (!total) return [];
    let startAngle = -Math.PI / 2;
    return Object.entries(bd).map(([status, count]) => {
        const pct = count / total; const angle = pct * 2 * Math.PI; const endAngle = startAngle + angle;
        const r = 60; const cx = 80; const cy = 80;
        const x1 = cx + r * Math.cos(startAngle); const y1 = cy + r * Math.sin(startAngle);
        const x2 = cx + r * Math.cos(endAngle);   const y2 = cy + r * Math.sin(endAngle);
        const largeArc = angle > Math.PI ? 1 : 0;
        const path = `M${cx},${cy} L${x1},${y1} A${r},${r} 0 ${largeArc},1 ${x2},${y2} Z`;
        const result = { status, count, pct, path, color: statusColors[status] ?? '#94a3b8', label: statusLabels[status] ?? status };
        startAngle = endAngle;
        return result;
    });
});

// ── Progress bar maxes ────────────────────────────────────────────────────────
const topCustomerMax = computed(() => Math.max(...(props.topCustomers ?? []).map(c => c.total), 1));
const topProductMax  = computed(() => Math.max(...(props.topProducts  ?? []).map(p => p.revenue), 1));

// ── Doc table helpers ─────────────────────────────────────────────────────────
const typeLabel = { invoice: 'Facture', quote: 'Devis', proforma: 'Proforma', delivery_note: 'Bon de livraison', credit_note: 'Avoir', purchase_order: 'Commande', receipt: 'Reçu', sales_order: 'Bon commande' };
const typeColor  = { invoice: 'bg-blue-100 text-blue-700', quote: 'bg-amber-100 text-amber-700', proforma: 'bg-purple-100 text-purple-700', delivery_note: 'bg-green-100 text-green-700', credit_note: 'bg-red-100 text-red-700', purchase_order: 'bg-indigo-100 text-indigo-700', receipt: 'bg-teal-100 text-teal-700', sales_order: 'bg-sky-100 text-sky-700' };
const statusColor = { paid: 'bg-emerald-100 text-emerald-700', sent: 'bg-blue-100 text-blue-700', draft: 'bg-gray-100 text-gray-600', overdue: 'bg-red-100 text-red-700', partial: 'bg-amber-100 text-amber-700', cancelled: 'bg-gray-100 text-gray-400', viewed: 'bg-violet-100 text-violet-700', converted: 'bg-cyan-100 text-cyan-700', accepted: 'bg-emerald-100 text-emerald-700', rejected: 'bg-red-100 text-red-700' };
const statusLbl   = { paid: 'Payée', sent: 'Envoyée', draft: 'Brouillon', overdue: 'En retard', partial: 'Partielle', cancelled: 'Annulée', viewed: 'Vue', converted: 'Convertie', accepted: 'Accepté', rejected: 'Refusé' };
const formatDate  = (d) => d ? new Date(d).toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' }) : '—';

// ── Alerts ────────────────────────────────────────────────────────────────────
const dismissedAlerts = ref(new Set());
const dismissAlert = (type) => dismissedAlerts.value.add(type);
const visibleAlerts = computed(() => (props.alerts ?? []).filter(a => !dismissedAlerts.value.has(a.type)));

const alertStyle = {
    danger:  { bar: 'bg-red-500',    bg: 'bg-red-50 dark:bg-red-900/20',    border: 'border-red-200 dark:border-red-800',    text: 'text-red-800 dark:text-red-300',    icon: '🔴' },
    warning: { bar: 'bg-amber-500',  bg: 'bg-amber-50 dark:bg-amber-900/20',  border: 'border-amber-200 dark:border-amber-800',  text: 'text-amber-800 dark:text-amber-300',  icon: '⚠️' },
    info:    { bar: 'bg-blue-500',   bg: 'bg-blue-50 dark:bg-blue-900/20',   border: 'border-blue-200 dark:border-blue-800',   text: 'text-blue-800 dark:text-blue-300',   icon: 'ℹ️' },
};

// ── Accès rapides ─────────────────────────────────────────────────────────────
const quickModules = [
    { label: 'Clients',        icon: '👥', route: 'customers.index',          color: 'text-blue-600 bg-blue-50 dark:bg-blue-900/30' },
    { label: 'Produits',       icon: '📦', route: 'products.index',           color: 'text-emerald-600 bg-emerald-50 dark:bg-emerald-900/30' },
    { label: 'Stock',          icon: '🏪', route: 'warehouses.index',         color: 'text-indigo-600 bg-indigo-50 dark:bg-indigo-900/30' },
    { label: 'Achats',         icon: '🛒', route: 'purchases.index',          color: 'text-orange-600 bg-orange-50 dark:bg-orange-900/30' },
    { label: 'Abonnements',    icon: '🔄', route: 'subscriptions.index',      color: 'text-violet-600 bg-violet-50 dark:bg-violet-900/30' },
    { label: 'SAV',            icon: '🔧', route: 'sav.repairs.index',        color: 'text-red-600 bg-red-50 dark:bg-red-900/30' },
    { label: 'Comptabilité',   icon: '📊', route: 'accounting.accounts',      color: 'text-teal-600 bg-teal-50 dark:bg-teal-900/30' },
    { label: 'CRM',            icon: '🎯', route: 'crm.prospects.index',      color: 'text-pink-600 bg-pink-50 dark:bg-pink-900/30' },
    { label: 'WhatsApp',       icon: '💬', route: 'settings.whatsapp',        color: 'text-green-600 bg-green-50 dark:bg-green-900/30' },
    { label: 'Immobilier',     icon: '🏠', route: 'real-estate.properties.index', color: 'text-cyan-600 bg-cyan-50 dark:bg-cyan-900/30' },
    { label: 'Caisse (POS)',   icon: '🖥️', route: 'pos.index',               color: 'text-amber-600 bg-amber-50 dark:bg-amber-900/30' },
    { label: 'Relances',       icon: '📬', route: 'reminders.index',          color: 'text-slate-600 bg-slate-50 dark:bg-slate-900/30' },
];

// safe route helper (route can fail if not defined)
const safeRoute = (name) => {
    try { return route(name); } catch { return '#'; }
};
</script>

<template>
    <Head title="Tableau de bord" />
    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-gray-900 pb-12">

            <!-- Header -->
            <div class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 px-6 py-4 sticky top-0 z-10 shadow-sm">
                <div class="mx-auto max-w-7xl flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Tableau de bord</h1>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">Activité en temps réel de votre entreprise</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <Link :href="route('documents.create', { type: 'invoice' })"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-brand-600 px-3 py-2 text-xs font-semibold text-white shadow-sm hover:bg-brand-700 transition-colors">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Facture
                        </Link>
                        <Link :href="route('documents.create', { type: 'quote' })"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-brand-300 dark:border-brand-700 bg-white dark:bg-gray-700 px-3 py-2 text-xs font-semibold text-brand-700 dark:text-brand-400 hover:bg-brand-50 dark:hover:bg-brand-900/30 transition-colors">
                            Devis
                        </Link>
                        <Link :href="route('documents.create', { type: 'proforma' })"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-xs font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                            Proforma
                        </Link>
                        <Link :href="route('documents.create', { type: 'delivery_note' })"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-xs font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                            Bon livraison
                        </Link>
                        <Link :href="route('documents.create', { type: 'credit_note' })"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-xs font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                            Avoir
                        </Link>
                    </div>
                </div>
            </div>

            <div class="mx-auto max-w-7xl px-4 sm:px-6 py-6 space-y-6">

                <!-- Onboarding checklist -->
                <OnboardingChecklist />

                <!-- Alertes -->
                <div v-if="visibleAlerts.length" class="space-y-2">
                    <div v-for="a in visibleAlerts" :key="a.type"
                        class="flex items-center gap-3 rounded-xl border overflow-hidden"
                        :class="[alertStyle[a.severity]?.bg, alertStyle[a.severity]?.border]">
                        <div class="w-1 self-stretch flex-shrink-0" :class="alertStyle[a.severity]?.bar"></div>
                        <span class="text-base flex-shrink-0">{{ alertStyle[a.severity]?.icon }}</span>
                        <a :href="a.link" class="flex-1 py-2.5 text-xs font-medium hover:underline"
                            :class="alertStyle[a.severity]?.text">
                            {{ a.message }}
                        </a>
                        <a :href="a.link"
                            class="mr-2 rounded-lg px-2 py-1 text-[10px] font-semibold transition-colors flex-shrink-0"
                            :class="alertStyle[a.severity]?.text + ' hover:bg-black/5'">
                            Voir →
                        </a>
                        <button @click="dismissAlert(a.type)"
                            class="mr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 flex-shrink-0" title="Fermer">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>

                <!-- KPI Cards -->
                <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                    <!-- CA du mois -->
                    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-brand-500 to-brand-700 p-5 text-white shadow-md">
                        <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-white/10"></div>
                        <p class="text-xs font-medium text-brand-100">CA du mois</p>
                        <p class="mt-1 text-2xl font-bold tracking-tight">{{ fmt(animatedValues.revenue) }}</p>
                        <p class="text-[10px] text-brand-200">{{ currency }}</p>
                        <div v-if="stats.revenue_trend !== null && stats.revenue_trend !== undefined"
                            class="mt-2 flex items-center gap-1 text-xs font-medium">
                            <span v-if="stats.revenue_trend >= 0" class="text-green-300">▲ +{{ stats.revenue_trend }}%</span>
                            <span v-else class="text-red-300">▼ {{ stats.revenue_trend }}%</span>
                            <span class="text-brand-200">vs mois dernier</span>
                        </div>
                    </div>

                    <!-- Impayés -->
                    <div class="relative overflow-hidden rounded-2xl bg-white dark:bg-gray-800 p-5 shadow-sm border border-gray-100 dark:border-gray-700">
                        <div class="absolute right-3 top-3 h-8 w-8 rounded-full bg-red-50 dark:bg-red-900/30 flex items-center justify-center">
                            <svg class="h-4 w-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                            </svg>
                        </div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">À encaisser</p>
                        <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-gray-100 tracking-tight">{{ fmt(animatedValues.outstanding) }}</p>
                        <p class="text-[10px] text-gray-400 dark:text-gray-500">{{ currency }}</p>
                        <Link :href="route('documents.index', { type: 'invoice', status: 'sent' })"
                            class="mt-2 inline-block text-xs text-red-500 font-medium hover:underline">
                            Voir les impayées →
                        </Link>
                    </div>

                    <!-- Factures du mois -->
                    <div class="relative overflow-hidden rounded-2xl bg-white dark:bg-gray-800 p-5 shadow-sm border border-gray-100 dark:border-gray-700">
                        <div class="absolute right-3 top-3 h-8 w-8 rounded-full bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center">
                            <svg class="h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Factures ce mois</p>
                        <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-gray-100 tracking-tight">{{ animatedValues.invoices }}</p>
                        <p class="text-[10px] text-gray-400 dark:text-gray-500">documents</p>
                        <div v-if="stats.invoices_trend !== null && stats.invoices_trend !== undefined"
                            class="mt-2 flex items-center gap-1 text-xs font-medium">
                            <span v-if="stats.invoices_trend >= 0" class="text-emerald-600">▲ +{{ stats.invoices_trend }}%</span>
                            <span v-else class="text-red-500">▼ {{ stats.invoices_trend }}%</span>
                            <span class="text-gray-400 dark:text-gray-500">vs mois dernier</span>
                        </div>
                    </div>

                    <!-- Devis en attente -->
                    <div class="relative overflow-hidden rounded-2xl bg-white dark:bg-gray-800 p-5 shadow-sm border border-gray-100 dark:border-gray-700">
                        <div class="absolute right-3 top-3 h-8 w-8 rounded-full bg-amber-50 dark:bg-amber-900/30 flex items-center justify-center">
                            <svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Devis en attente</p>
                        <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-gray-100 tracking-tight">{{ animatedValues.quotes }}</p>
                        <p class="text-[10px] text-gray-400 dark:text-gray-500">devis</p>
                        <p class="mt-2 text-xs text-amber-600 dark:text-amber-400 font-medium">{{ conversionRate }}% convertis (30j)</p>
                    </div>
                </div>

                <!-- Graphique + Donut -->
                <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                    <!-- Area chart -->
                    <div class="lg:col-span-2 rounded-2xl bg-white dark:bg-gray-800 p-5 shadow-sm border border-gray-100 dark:border-gray-700">
                        <div class="mb-4 flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Évolution du chiffre d'affaires</h3>
                                <p class="text-xs text-gray-400 dark:text-gray-500">12 derniers mois — factures encaissées</p>
                            </div>
                            <div class="flex items-center gap-1 rounded-full bg-brand-50 dark:bg-brand-900/30 px-2.5 py-1 text-xs font-medium text-brand-700 dark:text-brand-400">
                                <span class="h-1.5 w-1.5 rounded-full bg-brand-500"></span>
                                CA mensuel
                            </div>
                        </div>

                        <!-- Labels mois -->
                        <div class="flex justify-between mb-1">
                            <span v-for="(d, i) in chartData" :key="i"
                                class="text-[9px] text-gray-400 dark:text-gray-500 text-center"
                                :style="{ width: (100 / chartData.length) + '%' }">
                                {{ d.month?.split(' ')[0] }}
                            </span>
                        </div>

                        <!-- SVG -->
                        <div class="relative" @mouseleave="hoveredPoint = null">
                            <svg :viewBox="`0 0 ${chartW} ${chartH}`" class="w-full" style="height:150px" preserveAspectRatio="none">
                                <defs>
                                    <linearGradient id="areaGrad" x1="0" y1="0" x2="0" y2="1">
                                        <stop offset="0%" stop-color="#6366f1" stop-opacity="0.30"/>
                                        <stop offset="100%" stop-color="#6366f1" stop-opacity="0.02"/>
                                    </linearGradient>
                                </defs>
                                <line v-for="n in [0.25, 0.5, 0.75]" :key="n"
                                    x1="0" :y1="chartH * (1 - n)" :x2="chartW" :y2="chartH * (1 - n)"
                                    stroke="#e2e8f0" stroke-width="1"/>
                                <path :d="areaPath" fill="url(#areaGrad)"/>
                                <polyline :points="polyline" fill="none" stroke="#6366f1" stroke-width="2.5"
                                    stroke-linejoin="round" stroke-linecap="round"/>
                                <g v-for="(p, i) in points" :key="i" @mouseenter="hoveredPoint = p" class="cursor-pointer">
                                    <circle :cx="p.x" :cy="p.y" r="16" fill="transparent"/>
                                    <circle :cx="p.x" :cy="p.y" r="4"
                                        :fill="hoveredPoint === p ? '#6366f1' : '#fff'"
                                        :stroke="hoveredPoint === p ? '#fff' : '#6366f1'"
                                        stroke-width="2.5"/>
                                </g>
                            </svg>
                            <!-- Tooltip -->
                            <div v-if="hoveredPoint"
                                class="pointer-events-none absolute z-10 rounded-xl bg-gray-900 dark:bg-gray-700 px-3 py-2 text-xs text-white shadow-xl -translate-x-1/2 -top-1"
                                :style="{ left: (hoveredPoint.x / chartW * 100) + '%' }">
                                <p class="font-bold">{{ fmtMoney(hoveredPoint.val) }}</p>
                                <p class="text-gray-400">{{ hoveredPoint.label }}</p>
                                <p v-if="hoveredPoint.count" class="text-gray-400">{{ hoveredPoint.count }} facture(s)</p>
                            </div>
                        </div>

                        <!-- Footer stats -->
                        <div class="mt-3 flex flex-wrap gap-6 border-t border-gray-50 dark:border-gray-700 pt-3">
                            <div>
                                <p class="text-[10px] text-gray-400 dark:text-gray-500">Total 12 mois</p>
                                <p class="text-sm font-bold text-gray-800 dark:text-gray-100">{{ fmtMoney(chartData.reduce((s, d) => s + (d.revenue ?? d.total ?? 0), 0)) }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] text-gray-400 dark:text-gray-500">Moyenne mensuelle</p>
                                <p class="text-sm font-bold text-gray-800 dark:text-gray-100">{{ fmtMoney(Math.round(chartData.reduce((s, d) => s + (d.revenue ?? d.total ?? 0), 0) / (chartData.length || 1))) }}</p>
                            </div>
                            <div v-if="stats.paid_count !== undefined && stats.total_invoices">
                                <p class="text-[10px] text-gray-400 dark:text-gray-500">Taux recouvrement</p>
                                <p class="text-sm font-bold text-emerald-600">{{ Math.round((stats.paid_count / (stats.total_invoices || 1)) * 100) }}%</p>
                            </div>
                        </div>
                    </div>

                    <!-- Donut statuts -->
                    <div class="rounded-2xl bg-white dark:bg-gray-800 p-5 shadow-sm border border-gray-100 dark:border-gray-700">
                        <h3 class="mb-1 text-sm font-semibold text-gray-800 dark:text-gray-100">Statuts (30 jours)</h3>
                        <p class="mb-4 text-xs text-gray-400 dark:text-gray-500">Répartition des documents émis</p>
                        <div v-if="donutData.length" class="flex flex-col items-center gap-4">
                            <svg width="160" height="160" viewBox="0 0 160 160">
                                <circle cx="80" cy="80" r="38" fill="white" class="dark:fill-gray-800"/>
                                <path v-for="slice in donutData" :key="slice.status" :d="slice.path" :fill="slice.color" opacity="0.9"/>
                                <text x="80" y="76" text-anchor="middle" fill="#1e293b" class="dark:fill-gray-100" style="font-size:22px;font-weight:700">{{ donutTotal }}</text>
                                <text x="80" y="92" text-anchor="middle" fill="#94a3b8" style="font-size:9px">documents</text>
                            </svg>
                            <div class="w-full space-y-1.5">
                                <div v-for="slice in donutData" :key="slice.status" class="flex items-center justify-between text-xs">
                                    <div class="flex items-center gap-1.5">
                                        <span class="h-2 w-2 rounded-full flex-shrink-0" :style="{ backgroundColor: slice.color }"></span>
                                        <span class="text-gray-600 dark:text-gray-400">{{ slice.label }}</span>
                                    </div>
                                    <span class="font-semibold text-gray-800 dark:text-gray-200">
                                        {{ slice.count }}
                                        <span class="text-gray-400 dark:text-gray-500 font-normal">({{ Math.round(slice.pct * 100) }}%)</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div v-else class="flex h-32 items-center justify-center text-sm text-gray-400 dark:text-gray-500">
                            Aucun document ce mois
                        </div>
                    </div>
                </div>

                <!-- Top Clients + Top Produits -->
                <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                    <div class="rounded-2xl bg-white dark:bg-gray-800 p-5 shadow-sm border border-gray-100 dark:border-gray-700">
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Top clients (12 mois)</h3>
                            <Link :href="route('customers.index')" class="text-xs text-brand-600 dark:text-brand-400 hover:underline">Voir tous →</Link>
                        </div>
                        <div v-if="topCustomers && topCustomers.length" class="space-y-3">
                            <div v-for="(c, i) in topCustomers" :key="i" class="flex items-center gap-3">
                                <span class="w-5 text-center text-xs font-bold text-gray-300 dark:text-gray-600">{{ i + 1 }}</span>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="truncate text-xs font-medium text-gray-800 dark:text-gray-200">{{ c.name }}</span>
                                        <span class="ml-2 flex-shrink-0 text-xs font-semibold text-gray-700 dark:text-gray-300">{{ fmt(c.total) }}</span>
                                    </div>
                                    <div class="h-1.5 w-full rounded-full bg-gray-100 dark:bg-gray-700">
                                        <div class="h-1.5 rounded-full bg-gradient-to-r from-brand-400 to-brand-600 transition-all duration-700"
                                            :style="{ width: ((c.total / topCustomerMax) * 100) + '%' }"></div>
                                    </div>
                                </div>
                                <span class="flex-shrink-0 rounded-full bg-gray-100 dark:bg-gray-700 px-1.5 py-0.5 text-[10px] text-gray-500 dark:text-gray-400">{{ c.invoices_count }}×</span>
                            </div>
                        </div>
                        <div v-else class="flex h-24 items-center justify-center text-sm text-gray-400 dark:text-gray-500">Aucune donnée</div>
                    </div>

                    <div class="rounded-2xl bg-white dark:bg-gray-800 p-5 shadow-sm border border-gray-100 dark:border-gray-700">
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Top produits / services (12 mois)</h3>
                            <Link :href="route('products.index')" class="text-xs text-brand-600 dark:text-brand-400 hover:underline">Voir tous →</Link>
                        </div>
                        <div v-if="topProducts && topProducts.length" class="space-y-3">
                            <div v-for="(p, i) in topProducts" :key="i" class="flex items-center gap-3">
                                <span class="w-5 text-center text-xs font-bold text-gray-300 dark:text-gray-600">{{ i + 1 }}</span>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="truncate text-xs font-medium text-gray-800 dark:text-gray-200">{{ p.name }}</span>
                                        <span class="ml-2 flex-shrink-0 text-xs font-semibold text-gray-700 dark:text-gray-300">{{ fmt(p.revenue) }}</span>
                                    </div>
                                    <div class="h-1.5 w-full rounded-full bg-gray-100 dark:bg-gray-700">
                                        <div class="h-1.5 rounded-full bg-gradient-to-r from-emerald-400 to-emerald-600 transition-all duration-700"
                                            :style="{ width: ((p.revenue / topProductMax) * 100) + '%' }"></div>
                                    </div>
                                </div>
                                <span class="flex-shrink-0 rounded-full bg-gray-100 dark:bg-gray-700 px-1.5 py-0.5 text-[10px] text-gray-500 dark:text-gray-400">{{ Math.round(p.quantity) }} u.</span>
                            </div>
                        </div>
                        <div v-else class="flex h-24 items-center justify-center text-sm text-gray-400 dark:text-gray-500">Aucune donnée</div>
                    </div>
                </div>

                <!-- Accès rapides aux modules -->
                <div class="rounded-2xl bg-white dark:bg-gray-800 p-5 shadow-sm border border-gray-100 dark:border-gray-700">
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Accès rapides</h3>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">Tous vos modules en un clic</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-6 gap-3">
                        <a v-for="mod in quickModules" :key="mod.route"
                            :href="safeRoute(mod.route)"
                            class="flex flex-col items-center gap-2 rounded-xl p-3 transition-all hover:scale-105 hover:shadow-md cursor-pointer"
                            :class="mod.color">
                            <span class="text-2xl">{{ mod.icon }}</span>
                            <span class="text-[10px] font-semibold text-center leading-tight">{{ mod.label }}</span>
                        </a>
                    </div>
                </div>

                <!-- Documents récents -->
                <div class="rounded-2xl bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-50 dark:border-gray-700">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Documents récents</h3>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">8 derniers documents créés</p>
                        </div>
                        <Link :href="route('documents.index')" class="text-xs text-brand-600 dark:text-brand-400 hover:underline">Voir tous →</Link>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs">
                            <thead>
                                <tr class="border-b border-gray-50 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-700/30">
                                    <th class="px-4 py-2.5 text-left font-medium text-gray-500 dark:text-gray-400">N°</th>
                                    <th class="px-4 py-2.5 text-left font-medium text-gray-500 dark:text-gray-400">Type</th>
                                    <th class="px-4 py-2.5 text-left font-medium text-gray-500 dark:text-gray-400 hidden sm:table-cell">Client</th>
                                    <th class="px-4 py-2.5 text-left font-medium text-gray-500 dark:text-gray-400 hidden md:table-cell">Date</th>
                                    <th class="px-4 py-2.5 text-right font-medium text-gray-500 dark:text-gray-400">Montant</th>
                                    <th class="px-4 py-2.5 text-center font-medium text-gray-500 dark:text-gray-400">Statut</th>
                                    <th class="px-4 py-2.5 text-center font-medium text-gray-500 dark:text-gray-400">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
                                <tr v-for="doc in recentDocuments" :key="doc.id"
                                    class="hover:bg-gray-50/70 dark:hover:bg-gray-700/50 transition-colors">
                                    <td class="px-4 py-3 font-mono text-gray-700 dark:text-gray-300 font-medium">{{ doc.number }}</td>
                                    <td class="px-4 py-3">
                                        <span class="rounded-md px-2 py-0.5 text-[10px] font-semibold"
                                            :class="typeColor[doc.type] ?? 'bg-gray-100 text-gray-600'">
                                            {{ typeLabel[doc.type] ?? doc.type }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400 hidden sm:table-cell max-w-[160px] truncate">
                                        {{ doc.customer?.name ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-400 dark:text-gray-500 hidden md:table-cell">{{ formatDate(doc.issue_date) }}</td>
                                    <td class="px-4 py-3 text-right font-semibold text-gray-800 dark:text-gray-200 tabular-nums">{{ fmt(doc.total) }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold"
                                            :class="statusColor[doc.status] ?? 'bg-gray-100 text-gray-500'">
                                            {{ statusLbl[doc.status] ?? doc.status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <Link :href="route('documents.show', doc.id)"
                                                class="rounded p-1 text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-700 dark:hover:text-gray-200" title="Voir">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </Link>
                                            <Link :href="route('documents.edit', doc.id)"
                                                class="rounded p-1 text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-700 dark:hover:text-gray-200" title="Modifier">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </Link>
                                            <a :href="route('documents.pdf', doc.id)" target="_blank"
                                                class="rounded p-1 text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-red-600" title="PDF">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                                </svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!recentDocuments || !recentDocuments.length">
                                    <td colspan="7" class="px-4 py-10 text-center text-gray-400 dark:text-gray-500">
                                        <p class="text-2xl mb-2">📋</p>
                                        <p>Aucun document pour l'instant</p>
                                        <Link :href="route('documents.create', { type: 'invoice' })"
                                            class="mt-2 inline-block text-brand-600 dark:text-brand-400 text-xs hover:underline">
                                            Créer votre première facture →
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

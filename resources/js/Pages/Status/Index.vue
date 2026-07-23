<script setup>
import { computed, ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import PublicNav from '@/Pages/Public/Partials/PublicNav.vue';
import PublicFooter from '@/Pages/Public/Partials/PublicFooter.vue';

const props = defineProps({
    appName:   { type: String,  default: 'IBIG FactPro' },
    canLogin:  { type: Boolean, default: true },
    canRegister: { type: Boolean, default: true },
    services:  { type: Array,  default: () => [] },
    stats:     { type: Object, default: () => ({}) },
    incidents: { type: Array,  default: () => [] },
});

const updatedAt = ref('');
onMounted(() => {
    updatedAt.value = new Date().toLocaleString('fr-FR');
});

const allOperational = computed(() =>
    props.services.every((s) => s.status === 'operational'),
);

const ICONS = {
    web:      '🌐',
    database: '🗄️',
    pdf:      '📄',
    email:    '📧',
    api:      '⚡',
    storage:  '💾',
};

const STATUS_LABEL = {
    operational: 'Opérationnel',
    degraded:    'Dégradé',
    outage:      'Panne',
};

const IMPACT_LABEL = {
    minor:       'Mineur',
    major:       'Majeur',
    maintenance: 'Maintenance',
};

function statusClasses(status) {
    if (status === 'operational') return 'bg-green-100 text-green-800';
    if (status === 'degraded')    return 'bg-orange-100 text-orange-800';
    return 'bg-red-100 text-red-800';
}

function impactClasses(impact) {
    if (impact === 'maintenance') return 'bg-blue-100 text-blue-800';
    if (impact === 'major')       return 'bg-red-100 text-red-800';
    return 'bg-yellow-100 text-yellow-800';
}

function incidentStatusClasses(status) {
    return status === 'resolved'
        ? 'bg-green-100 text-green-800'
        : 'bg-orange-100 text-orange-800';
}

function incidentStatusLabel(status) {
    return status === 'resolved' ? 'Résolu' : 'En cours d\'investigation';
}

function formatDate(dateStr) {
    return new Date(dateStr).toLocaleDateString('fr-FR', {
        day: '2-digit', month: 'long', year: 'numeric',
    });
}

const fmt = (n) => Number(n || 0).toLocaleString('fr-FR');
</script>

<template>
    <Head title="Statut des services — IBIG FactPro" />

    <PublicNav :can-login="canLogin" :can-register="canRegister" />

    <!-- ── Hero ───────────────────────────────────────────────── -->
    <section
        class="py-16 px-4 text-white text-center"
        style="background: linear-gradient(135deg, #001d3d 0%, #002D5B 100%);"
    >
        <p class="text-3xl font-bold mb-1">{{ appName }}</p>
        <h1 class="text-4xl font-extrabold mb-4">Statut des services</h1>

        <!-- Badge global -->
        <div class="inline-flex items-center gap-2 px-5 py-2 rounded-full text-sm font-semibold mt-2"
             :class="allOperational
                 ? 'bg-green-500/20 text-green-300 border border-green-500/40'
                 : 'bg-orange-500/20 text-orange-300 border border-orange-500/40'">
            <span>{{ allOperational ? '✓' : '⚠' }}</span>
            <span>{{ allOperational
                ? 'Tous les systèmes sont opérationnels'
                : 'Certains systèmes rencontrent des perturbations' }}</span>
        </div>

        <p v-if="updatedAt" class="mt-4 text-sm text-blue-200/70">
            Dernière mise à jour : {{ updatedAt }}
        </p>
    </section>

    <main class="max-w-5xl mx-auto px-4 py-12 space-y-16">

        <!-- ── Services ──────────────────────────────────────── -->
        <section>
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Services</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div
                    v-for="svc in services"
                    :key="svc.key"
                    class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex items-start gap-4"
                >
                    <span class="text-3xl leading-none mt-0.5">{{ ICONS[svc.key] ?? '🔧' }}</span>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2 flex-wrap">
                            <p class="font-semibold text-gray-900">{{ svc.name }}</p>
                            <span
                                class="px-2.5 py-0.5 rounded-full text-xs font-semibold"
                                :class="statusClasses(svc.status)"
                            >
                                {{ STATUS_LABEL[svc.status] ?? svc.status }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-500 mt-1">
                            Disponibilité : <strong>{{ svc.uptime }}</strong>
                            <span v-if="svc.latency" class="ml-3">Latence : <strong>{{ svc.latency }}</strong></span>
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── Statistiques ───────────────────────────────────── -->
        <section
            class="rounded-3xl px-8 py-10"
            style="background: #F0C040;"
        >
            <h2 class="text-2xl font-bold mb-8 text-center" style="color: #001d3d;">
                Chiffres clés
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 text-center">
                <div>
                    <p class="text-4xl font-extrabold" style="color: #001d3d;">{{ fmt(stats.users) }}</p>
                    <p class="mt-1 text-sm font-medium" style="color: #002D5B;">Utilisateurs enregistrés</p>
                </div>
                <div>
                    <p class="text-4xl font-extrabold" style="color: #001d3d;">{{ stats.uptime }}</p>
                    <p class="mt-1 text-sm font-medium" style="color: #002D5B;">Disponibilité globale</p>
                </div>
                <div>
                    <p class="text-4xl font-extrabold" style="color: #001d3d;">{{ fmt(stats.invoices) }}</p>
                    <p class="mt-1 text-sm font-medium" style="color: #002D5B;">Factures générées</p>
                </div>
            </div>
        </section>

        <!-- ── Historique des incidents ───────────────────────── -->
        <section>
            <h2 class="text-2xl font-bold text-gray-800 mb-6">
                Historique des incidents <span class="text-base font-normal text-gray-400">(30 derniers jours)</span>
            </h2>

            <div v-if="incidents.length === 0" class="text-gray-500 text-sm">
                Aucun incident signalé sur cette période.
            </div>

            <ul v-else class="space-y-4">
                <li
                    v-for="(inc, i) in incidents"
                    :key="i"
                    class="bg-white border border-gray-100 rounded-2xl shadow-sm p-5"
                >
                    <div class="flex items-start justify-between gap-3 flex-wrap">
                        <div>
                            <p class="font-semibold text-gray-900">{{ inc.title }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ formatDate(inc.date) }}</p>
                        </div>
                        <div class="flex gap-2 flex-wrap">
                            <span
                                class="px-2.5 py-0.5 rounded-full text-xs font-semibold"
                                :class="incidentStatusClasses(inc.status)"
                            >
                                {{ incidentStatusLabel(inc.status) }}
                            </span>
                            <span
                                class="px-2.5 py-0.5 rounded-full text-xs font-semibold"
                                :class="impactClasses(inc.impact)"
                            >
                                {{ IMPACT_LABEL[inc.impact] ?? inc.impact }}
                            </span>
                        </div>
                    </div>
                    <p class="text-sm text-gray-500 mt-3">{{ inc.detail }}</p>
                </li>
            </ul>
        </section>

    </main>

    <PublicFooter />
</template>

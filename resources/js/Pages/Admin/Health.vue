<script setup>
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AdminTabs from '@/Components/AdminTabs.vue';

const props = defineProps({
    users: { type: Array, default: () => [] },
    stats: { type: Object, default: () => ({}) },
});

// Filtre actif : 'all' | 'churning' | 'at_risk' | 'healthy'
const activeFilter = ref('all');

const filtered = computed(() => {
    if (activeFilter.value === 'all') return props.users;
    return props.users.filter(u => u.risk === activeFilter.value);
});

const riskLabel = {
    healthy:  { text: 'Sain',      color: 'bg-green-100 text-green-800' },
    at_risk:  { text: 'À risque',  color: 'bg-orange-100 text-orange-800' },
    churning: { text: 'En danger', color: 'bg-red-100 text-red-800' },
};

const planColor = (plan) => {
    const p = (plan ?? '').toLowerCase();
    if (p.includes('business')) return 'bg-purple-100 text-purple-800';
    if (p.includes('pro'))      return 'bg-blue-100 text-blue-800';
    if (p.includes('starter'))  return 'bg-sky-100 text-sky-800';
    return 'bg-gray-100 text-gray-600';
};

const scoreBarColor = (score) => {
    if (score >= 70) return 'bg-green-500';
    if (score >= 40) return 'bg-orange-400';
    return 'bg-red-500';
};

const scoreTextColor = (score) => {
    if (score >= 70) return 'text-green-700';
    if (score >= 40) return 'text-orange-600';
    return 'text-red-600';
};

function exportCSV() {
    const rows = [['Entreprise', 'Email', 'Plan', 'Score', 'Docs 30j', 'Dernière activité', 'Risque']];
    filtered.value.forEach(u =>
        rows.push([u.company, u.email, u.plan, u.score, u.docs_30, u.last_doc, u.risk])
    );
    const csv = rows.map(r => r.map(c => `"${c}"`).join(',')).join('\n');
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'clients-sante.csv';
    a.click();
    URL.revokeObjectURL(url);
}
</script>

<template>
    <Head title="Santé des clients" />

    <div class="min-h-screen bg-gray-50">
        <!-- Admin Tabs -->
        <div class="bg-white border-b border-gray-200 px-6 py-3 shadow-sm">
            <AdminTabs />
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Santé des clients</h1>
                    <p class="text-gray-500 mt-1">Détectez les comptes à risque avant qu'ils ne partent</p>
                </div>
                <button
                    @click="exportCSV"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-gray-800 text-white text-sm font-medium hover:bg-gray-700 transition"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Exporter CSV
                </button>
            </div>

            <!-- KPI Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <!-- Total -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <div class="text-sm text-gray-500 font-medium mb-1">Total comptes</div>
                    <div class="text-4xl font-extrabold text-blue-600">{{ stats.total }}</div>
                    <div class="text-xs text-gray-400 mt-1">avec licence</div>
                </div>

                <!-- Sains -->
                <div class="bg-white rounded-2xl shadow-sm border border-green-100 p-5">
                    <div class="text-sm text-gray-500 font-medium mb-1">💚 Clients sains</div>
                    <div class="text-4xl font-extrabold text-green-600">{{ stats.healthy }}</div>
                    <div class="text-xs text-gray-400 mt-1">score ≥ 70</div>
                </div>

                <!-- À risque -->
                <div class="bg-white rounded-2xl shadow-sm border border-orange-100 p-5">
                    <div class="text-sm text-gray-500 font-medium mb-1">⚠️ À risque</div>
                    <div class="text-4xl font-extrabold text-orange-500">{{ stats.at_risk }}</div>
                    <div class="text-xs text-gray-400 mt-1">score 40 – 69</div>
                </div>

                <!-- En danger -->
                <div class="bg-white rounded-2xl shadow-sm border border-red-100 p-5">
                    <div class="text-sm text-gray-500 font-medium mb-1">🚨 En danger</div>
                    <div class="text-4xl font-extrabold text-red-600">{{ stats.churning }}</div>
                    <div class="text-xs text-gray-400 mt-1">score &lt; 40</div>
                </div>
            </div>

            <!-- Score moyen -->
            <div class="flex justify-center mb-8">
                <div class="bg-white rounded-2xl shadow-sm border border-yellow-100 px-10 py-5 text-center">
                    <div class="text-sm text-gray-500 font-medium mb-1">Score santé moyen</div>
                    <div class="text-6xl font-black text-yellow-500">{{ stats.avg_score }}</div>
                    <div class="text-xs text-gray-400 mt-1">sur 100</div>
                </div>
            </div>

            <!-- Filtres -->
            <div class="flex flex-wrap gap-2 mb-5">
                <button
                    v-for="f in [
                        { key: 'all',      label: 'Tous (' + stats.total + ')' },
                        { key: 'churning', label: '🚨 En danger (' + stats.churning + ')' },
                        { key: 'at_risk',  label: '⚠️ À risque (' + stats.at_risk + ')' },
                        { key: 'healthy',  label: '💚 Sains (' + stats.healthy + ')' },
                    ]"
                    :key="f.key"
                    @click="activeFilter = f.key"
                    :class="[
                        'px-4 py-2 rounded-full text-sm font-semibold transition',
                        activeFilter === f.key
                            ? 'bg-gray-800 text-white shadow'
                            : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'
                    ]"
                >
                    {{ f.label }}
                </button>
            </div>

            <!-- Tableau -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 text-sm">
                        <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
                            <tr>
                                <th class="px-4 py-3 text-left">Entreprise / Email</th>
                                <th class="px-4 py-3 text-left">Plan</th>
                                <th class="px-4 py-3 text-left w-48">Score santé</th>
                                <th class="px-4 py-3 text-center">Docs 30j</th>
                                <th class="px-4 py-3 text-left">Dernière activité</th>
                                <th class="px-4 py-3 text-center">Statut</th>
                                <th class="px-4 py-3 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-if="filtered.length === 0">
                                <td colspan="7" class="px-4 py-10 text-center text-gray-400">
                                    Aucun compte dans cette catégorie.
                                </td>
                            </tr>
                            <tr
                                v-for="u in filtered"
                                :key="u.id"
                                class="hover:bg-gray-50 transition"
                            >
                                <!-- Entreprise + email -->
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-gray-900">{{ u.company }}</div>
                                    <div class="text-xs text-gray-400">{{ u.email }}</div>
                                    <div class="text-xs text-gray-300">Depuis {{ u.member_since }}</div>
                                </td>

                                <!-- Plan -->
                                <td class="px-4 py-3">
                                    <span
                                        :class="['inline-block px-2 py-0.5 rounded text-xs font-semibold', planColor(u.plan)]"
                                    >
                                        {{ u.plan }}
                                    </span>
                                    <span
                                        v-if="u.trial"
                                        class="ml-1 inline-block px-1.5 py-0.5 rounded text-xs bg-yellow-100 text-yellow-700"
                                    >
                                        trial
                                    </span>
                                </td>

                                <!-- Score -->
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 bg-gray-100 rounded-full h-2 overflow-hidden">
                                            <div
                                                :class="['h-2 rounded-full transition-all', scoreBarColor(u.score)]"
                                                :style="{ width: u.score + '%' }"
                                            />
                                        </div>
                                        <span :class="['text-xs font-bold w-7 text-right', scoreTextColor(u.score)]">
                                            {{ u.score }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Docs 30j -->
                                <td class="px-4 py-3 text-center">
                                    <span :class="[
                                        'font-semibold',
                                        u.docs_30 === 0 ? 'text-red-500' :
                                        u.docs_30 < 5  ? 'text-orange-500' : 'text-green-600'
                                    ]">
                                        {{ u.docs_30 }}
                                    </span>
                                </td>

                                <!-- Dernière activité -->
                                <td class="px-4 py-3 text-gray-500 text-xs">{{ u.last_doc }}</td>

                                <!-- Statut risque -->
                                <td class="px-4 py-3 text-center">
                                    <span
                                        v-if="riskLabel[u.risk]"
                                        :class="['inline-block px-2 py-0.5 rounded text-xs font-semibold', riskLabel[u.risk].color]"
                                    >
                                        {{ riskLabel[u.risk].text }}
                                    </span>
                                </td>

                                <!-- Action -->
                                <td class="px-4 py-3 text-center">
                                    <a
                                        :href="`mailto:${u.email}?subject=Votre compte FactPro`"
                                        class="inline-block px-3 py-1 rounded-lg bg-brand-600 text-white text-xs font-semibold hover:bg-brand-700 transition"
                                    >
                                        Contacter
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</template>

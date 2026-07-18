<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    stats: Object,
    trackings: Array,
});

const filter = ref('all');

const filtered = computed(() => {
    if (filter.value === 'opened') return props.trackings.filter(t => t.opened_at);
    if (filter.value === 'unopened') return props.trackings.filter(t => !t.opened_at);
    if (filter.value === 'clicked') return props.trackings.filter(t => t.clicked_at);
    return props.trackings;
});

const fmt = (n) => new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 1 }).format(n ?? 0);

const daysSince = (dateStr) => {
    if (!dateStr) return null;
    const diff = Date.now() - new Date(dateStr).getTime();
    return Math.floor(diff / 86400000);
};

// SVG chart: ouvertures par jour sur 30 jours
const chartData = computed(() => {
    const days = [];
    const today = new Date();
    for (let i = 29; i >= 0; i--) {
        const d = new Date(today);
        d.setDate(d.getDate() - i);
        const key = d.toISOString().slice(0, 10);
        days.push({ label: key.slice(5), value: props.stats.opens_by_day?.[key] ?? 0 });
    }
    return days;
});

const maxVal = computed(() => Math.max(1, ...chartData.value.map(d => d.value)));

const barHeight = (val) => Math.round((val / maxVal.value) * 80);
</script>

<template>
    <Head title="Tracking Email" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Engagement Email — 30 derniers jours</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">

                <!-- KPI Cards -->
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <div class="rounded-lg bg-white p-5 shadow text-center">
                        <div class="text-3xl font-bold text-brand-600">{{ stats.total_sent }}</div>
                        <div class="mt-1 text-xs text-gray-500 uppercase tracking-wide">Emails envoyés</div>
                    </div>
                    <div class="rounded-lg bg-white p-5 shadow text-center">
                        <div class="text-3xl font-bold text-green-600">{{ stats.open_rate }}%</div>
                        <div class="mt-1 text-xs text-gray-500 uppercase tracking-wide">Taux d'ouverture</div>
                    </div>
                    <div class="rounded-lg bg-white p-5 shadow text-center">
                        <div class="text-3xl font-bold text-indigo-600">{{ stats.click_rate }}%</div>
                        <div class="mt-1 text-xs text-gray-500 uppercase tracking-wide">Taux de clic PDF</div>
                    </div>
                    <div class="rounded-lg bg-white p-5 shadow text-center">
                        <div class="text-3xl font-bold text-red-600">{{ stats.unopened_3days }}</div>
                        <div class="mt-1 text-xs text-gray-500 uppercase tracking-wide">Non ouverts &gt;3j</div>
                    </div>
                </div>

                <!-- Graphique ouvertures par jour -->
                <div class="rounded-lg bg-white p-6 shadow">
                    <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-400">Ouvertures par jour</h3>
                    <svg viewBox="0 0 600 100" xmlns="http://www.w3.org/2000/svg" class="w-full" style="height:120px;">
                        <g v-for="(d, i) in chartData" :key="d.label">
                            <rect
                                :x="i * 20 + 2"
                                :y="90 - barHeight(d.value)"
                                width="16"
                                :height="barHeight(d.value)"
                                fill="#4f46e5"
                                opacity="0.7"
                                rx="2"
                            />
                            <title>{{ d.label }} : {{ d.value }} ouverture(s)</title>
                        </g>
                    </svg>
                    <div class="mt-1 flex justify-between text-xs text-gray-400">
                        <span>{{ chartData[0]?.label }}</span>
                        <span>{{ chartData[29]?.label }}</span>
                    </div>
                </div>

                <!-- Tableau -->
                <div class="rounded-lg bg-white shadow overflow-hidden">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b px-6 py-4">
                        <h3 class="font-semibold text-gray-800">Suivi par document</h3>
                        <div class="flex gap-2">
                            <button v-for="f in [['all','Tous'],['opened','Ouverts'],['unopened','Non ouverts'],['clicked','Cliqués']]"
                                :key="f[0]"
                                @click="filter = f[0]"
                                :class="filter === f[0] ? 'bg-brand-600 text-white' : 'border border-gray-300 text-gray-600 hover:bg-gray-50'"
                                class="rounded-full px-3 py-1 text-xs font-semibold">
                                {{ f[1] }}
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-400">
                                <tr>
                                    <th class="px-4 py-3">Document</th>
                                    <th class="px-4 py-3">Destinataire</th>
                                    <th class="px-4 py-3">Envoyé le</th>
                                    <th class="px-4 py-3 text-center">Ouvert</th>
                                    <th class="px-4 py-3 text-center">Nb ouv.</th>
                                    <th class="px-4 py-3 text-center">Cliqué</th>
                                    <th class="px-4 py-3">Alerte</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr v-for="t in filtered" :key="t.id" class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-semibold text-brand-700">
                                        {{ t.document?.number ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-600">{{ t.recipient_email }}</td>
                                    <td class="px-4 py-3 text-gray-500">{{ t.sent_at?.slice(0, 10) }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span v-if="t.opened_at" class="text-green-600">✅</span>
                                        <span v-else class="text-red-500">❌</span>
                                    </td>
                                    <td class="px-4 py-3 text-center">{{ t.opens_count }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span v-if="t.clicked_at" class="text-indigo-600">✅</span>
                                        <span v-else class="text-gray-300">—</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span
                                            v-if="!t.opened_at && daysSince(t.sent_at) >= 3"
                                            class="rounded-full bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-700">
                                            Non ouvert depuis {{ daysSince(t.sent_at) }}j
                                        </span>
                                        <span v-else-if="t.alert_sent_at" class="text-xs text-gray-400">Alerte envoyée</span>
                                    </td>
                                </tr>
                                <tr v-if="!filtered.length">
                                    <td colspan="7" class="px-4 py-6 text-center text-gray-400">Aucun résultat</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

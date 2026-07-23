<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdminTabs from '@/Components/AdminTabs.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    stats: Object,
    by_source: Array,
    by_medium: Array,
    by_campaign: Array,
    signups_by_day: Array,
    top_referrers: Array,
});

// ─── Formatage ────────────────────────────────────────────────────────────────
const fmt = (n) => new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(n ?? 0);

// ─── Graphique barres 30j ─────────────────────────────────────────────────────
const chartHeight = 160;
const chartPad    = 8;

const chartMax = computed(() => Math.max(...(props.signups_by_day ?? []).map(d => d.count), 1));

const bars = computed(() => {
    const data  = props.signups_by_day ?? [];
    const total = data.length || 1;
    const barW  = 100 / total;
    return data.map((d, i) => {
        const h  = (d.count / chartMax.value) * (chartHeight - chartPad * 2);
        const x  = i * barW;
        const y  = chartHeight - chartPad - h;
        return { ...d, h, x, y, barW, midX: x + barW / 2 };
    });
});

// ─── Barres de progression ────────────────────────────────────────────────────
const sourceMax   = computed(() => Math.max(...(props.by_source ?? []).map(s => s.count), 1));
const mediumMax   = computed(() => Math.max(...(props.by_medium ?? []).map(m => m.count), 1));
const campaignMax = computed(() => Math.max(...(props.by_campaign ?? []).map(c => c.count), 1));
</script>

<template>
    <Head title="Acquisition & Conversions" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200 leading-tight">
                Acquisition & Conversions
            </h2>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

                <!-- Tabs -->
                <AdminTabs />

                <!-- ─── KPI Cards ─────────────────────────────────────────────── -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Inscriptions 30j -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow p-5 border-t-4 border-blue-600">
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Inscriptions</p>
                        <p class="mt-1 text-3xl font-extrabold text-blue-700 dark:text-blue-400">{{ fmt(stats.total_signups) }}</p>
                        <p class="mt-1 text-xs text-gray-400">30 derniers jours</p>
                    </div>

                    <!-- Conversions payant -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow p-5 border-t-4 border-green-500">
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Convertis payant</p>
                        <p class="mt-1 text-3xl font-extrabold text-green-600 dark:text-green-400">{{ fmt(stats.conversions) }}</p>
                        <p class="mt-1 text-xs text-gray-400">essai → abonnement actif</p>
                    </div>

                    <!-- Taux de conversion -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow p-5 border-t-4 border-yellow-400">
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Taux de conversion</p>
                        <p class="mt-1 text-3xl font-extrabold text-yellow-500 dark:text-yellow-400">{{ stats.conversion_rate }}%</p>
                        <p class="mt-1 text-xs text-gray-400">sur la période</p>
                    </div>

                    <!-- Direct / Inconnu -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow p-5 border-t-4 border-gray-400">
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Direct / Inconnu</p>
                        <p class="mt-1 text-3xl font-extrabold text-gray-600 dark:text-gray-300">{{ fmt(stats.direct_count) }}</p>
                        <p class="mt-1 text-xs text-gray-400">sans UTM tracé</p>
                    </div>
                </div>

                <!-- ─── Courbe inscriptions 30j ───────────────────────────────── -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow p-6">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-4">Inscriptions — 30 derniers jours</h3>
                    <div class="overflow-x-auto">
                        <svg
                            :viewBox="`0 0 100 ${chartHeight + 20}`"
                            preserveAspectRatio="none"
                            class="w-full"
                            :style="`height: ${chartHeight + 20}px; min-width: 400px`"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <!-- Grille -->
                            <line v-for="i in 4" :key="i"
                                  x1="0" :y1="chartPad + ((chartHeight - chartPad * 2) / 4) * (i - 1)"
                                  x2="100" :y2="chartPad + ((chartHeight - chartPad * 2) / 4) * (i - 1)"
                                  stroke="#e5e7eb" stroke-width="0.3" />

                            <!-- Barres -->
                            <g v-for="b in bars" :key="b.date">
                                <rect
                                    :x="b.x + b.barW * 0.1"
                                    :y="b.y"
                                    :width="b.barW * 0.8"
                                    :height="b.h"
                                    rx="0.6"
                                    fill="#1a56db"
                                    opacity="0.85"
                                />
                                <text
                                    v-if="b.count > 0"
                                    :x="b.midX"
                                    :y="b.y - 1.5"
                                    text-anchor="middle"
                                    font-size="2.8"
                                    fill="#374151"
                                >{{ b.count }}</text>
                            </g>

                            <!-- Dates (premier et dernier) -->
                            <text v-if="bars.length"
                                  :x="bars[0].midX"
                                  :y="chartHeight + 12"
                                  text-anchor="middle"
                                  font-size="3"
                                  fill="#6b7280"
                            >{{ bars[0].date }}</text>
                            <text v-if="bars.length > 1"
                                  :x="bars[bars.length - 1].midX"
                                  :y="chartHeight + 12"
                                  text-anchor="middle"
                                  font-size="3"
                                  fill="#6b7280"
                            >{{ bars[bars.length - 1].date }}</text>
                        </svg>
                    </div>
                    <p v-if="!signups_by_day?.length" class="text-center text-gray-400 text-sm py-8">
                        Aucune inscription sur la période.
                    </p>
                </div>

                <!-- ─── 3 colonnes UTM ─────────────────────────────────────────── -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

                    <!-- Top Sources -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow p-5">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-4">Top Sources UTM</h3>
                        <div v-if="by_source?.length" class="space-y-3">
                            <div v-for="item in by_source" :key="item.utm_source">
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-gray-700 dark:text-gray-300 font-medium truncate max-w-[70%]">{{ item.utm_source }}</span>
                                    <span class="text-gray-500 font-semibold">{{ item.count }}</span>
                                </div>
                                <div class="h-1.5 bg-gray-100 dark:bg-gray-700 rounded-full">
                                    <div class="h-1.5 bg-blue-500 rounded-full"
                                         :style="`width: ${Math.round((item.count / sourceMax) * 100)}%`"></div>
                                </div>
                            </div>
                        </div>
                        <p v-else class="text-gray-400 text-sm">Pas encore de données UTM source.</p>
                    </div>

                    <!-- Top Mediums -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow p-5">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-4">Top Mediums UTM</h3>
                        <div v-if="by_medium?.length" class="space-y-3">
                            <div v-for="item in by_medium" :key="item.utm_medium">
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-gray-700 dark:text-gray-300 font-medium truncate max-w-[70%]">{{ item.utm_medium }}</span>
                                    <span class="text-gray-500 font-semibold">{{ item.count }}</span>
                                </div>
                                <div class="h-1.5 bg-gray-100 dark:bg-gray-700 rounded-full">
                                    <div class="h-1.5 bg-green-500 rounded-full"
                                         :style="`width: ${Math.round((item.count / mediumMax) * 100)}%`"></div>
                                </div>
                            </div>
                        </div>
                        <p v-else class="text-gray-400 text-sm">Pas encore de données UTM medium.</p>
                    </div>

                    <!-- Top Campagnes -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow p-5">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-4">Top Campagnes UTM</h3>
                        <div v-if="by_campaign?.length" class="space-y-3">
                            <div v-for="item in by_campaign" :key="item.utm_campaign">
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-gray-700 dark:text-gray-300 font-medium truncate max-w-[70%]">{{ item.utm_campaign }}</span>
                                    <span class="text-gray-500 font-semibold">{{ item.count }}</span>
                                </div>
                                <div class="h-1.5 bg-gray-100 dark:bg-gray-700 rounded-full">
                                    <div class="h-1.5 bg-yellow-400 rounded-full"
                                         :style="`width: ${Math.round((item.count / campaignMax) * 100)}%`"></div>
                                </div>
                            </div>
                        </div>
                        <p v-else class="text-gray-400 text-sm">Pas encore de données UTM campagne.</p>
                    </div>
                </div>

                <!-- ─── Table référents ────────────────────────────────────────── -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow p-6">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-4">Top Référents</h3>
                    <div v-if="top_referrers?.length" class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide border-b border-gray-100 dark:border-gray-700">
                                    <th class="pb-3 pr-4">Domaine</th>
                                    <th class="pb-3 pr-4">URL complète</th>
                                    <th class="pb-3 text-right">Inscriptions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
                                <tr v-for="ref in top_referrers" :key="ref.url" class="hover:bg-gray-50 dark:hover:bg-gray-750">
                                    <td class="py-2.5 pr-4 font-medium text-gray-800 dark:text-gray-100">{{ ref.domain }}</td>
                                    <td class="py-2.5 pr-4 text-gray-500 dark:text-gray-400 truncate max-w-xs">
                                        <a :href="ref.url" target="_blank" rel="noopener" class="hover:text-blue-600 hover:underline">
                                            {{ ref.url }}
                                        </a>
                                    </td>
                                    <td class="py-2.5 text-right font-bold text-gray-700 dark:text-gray-200">{{ ref.count }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p v-else class="text-gray-400 text-sm">Aucun référent détecté sur la période.</p>
                </div>

                <!-- ─── Note d'utilisation ─────────────────────────────────────── -->
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4 text-sm text-blue-700 dark:text-blue-300">
                    <p class="font-semibold mb-1">Comment fonctionnent les UTM ?</p>
                    <p>Les paramètres UTM sont capturés automatiquement depuis les URLs de vos campagnes marketing lors de l'inscription.</p>
                    <p class="mt-2 font-mono text-xs bg-blue-100 dark:bg-blue-900/40 rounded px-2 py-1 inline-block">
                        /register?utm_source=google&amp;utm_medium=cpc&amp;utm_campaign=africa-q3
                    </p>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

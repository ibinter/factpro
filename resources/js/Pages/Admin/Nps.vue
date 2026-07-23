<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdminTabs from '@/Components/AdminTabs.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    responses: Array,
    stats:     Object,
});

function scoreLabel(score) {
    if (score >= 9) return 'promoter';
    if (score >= 7) return 'passive';
    return 'detractor';
}

function scoreBadgeClass(score) {
    if (score >= 9) return 'bg-green-100 text-green-800';
    if (score >= 7) return 'bg-yellow-100 text-yellow-800';
    return 'bg-red-100 text-red-700';
}
</script>

<template>
    <Head title="NPS — Admin" />
    <AuthenticatedLayout>
        <div class="mx-auto max-w-6xl px-4 py-8">
            <AdminTabs />

            <h1 class="mt-6 text-2xl font-bold text-gray-800">📣 Net Promoter Score</h1>

            <!-- KPI cards -->
            <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
                <!-- NPS global -->
                <div class="rounded-2xl bg-white p-5 shadow">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Score NPS</p>
                    <p
                        class="mt-2 text-5xl font-extrabold"
                        :class="stats.nps >= 0 ? 'text-blue-600' : 'text-red-500'"
                    >
                        {{ stats.nps > 0 ? '+' : '' }}{{ stats.nps }}
                    </p>
                    <p class="mt-1 text-xs text-gray-400">sur {{ stats.total }} réponses</p>
                </div>

                <!-- Promoteurs -->
                <div class="rounded-2xl bg-white p-5 shadow">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Promoteurs</p>
                    <p class="mt-2 text-4xl font-extrabold text-green-600">{{ stats.promoters }}</p>
                    <p class="mt-1 text-xs text-gray-400">Score 9-10</p>
                </div>

                <!-- Détracteurs -->
                <div class="rounded-2xl bg-white p-5 shadow">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Détracteurs</p>
                    <p class="mt-2 text-4xl font-extrabold text-red-500">{{ stats.detractors }}</p>
                    <p class="mt-1 text-xs text-gray-400">Score 0-6</p>
                </div>

                <!-- Total -->
                <div class="rounded-2xl bg-white p-5 shadow">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Total réponses</p>
                    <p class="mt-2 text-4xl font-extrabold text-gray-700">{{ stats.total }}</p>
                    <p class="mt-1 text-xs text-gray-400">Toutes périodes</p>
                </div>
            </div>

            <!-- Tableau -->
            <div class="mt-8 overflow-hidden rounded-2xl bg-white shadow">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 text-sm">
                        <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-400">
                            <tr>
                                <th class="px-4 py-3 text-left">Score</th>
                                <th class="px-4 py-3 text-left">Utilisateur</th>
                                <th class="px-4 py-3 text-left">Email</th>
                                <th class="px-4 py-3 text-left">Commentaire</th>
                                <th class="px-4 py-3 text-left">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr
                                v-for="r in responses"
                                :key="r.id"
                                class="hover:bg-gray-50"
                            >
                                <td class="px-4 py-3">
                                    <span
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold"
                                        :class="scoreBadgeClass(r.score)"
                                    >
                                        {{ r.score }}/10
                                    </span>
                                </td>
                                <td class="px-4 py-3 font-medium text-gray-800">{{ r.user ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ r.email ?? '—' }}</td>
                                <td class="max-w-xs px-4 py-3 text-gray-600">
                                    <span class="line-clamp-2">{{ r.comment || '—' }}</span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-gray-400">{{ r.created_at }}</td>
                            </tr>
                            <tr v-if="!responses?.length">
                                <td colspan="5" class="px-4 py-8 text-center text-gray-400">
                                    Aucune réponse NPS pour l'instant.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

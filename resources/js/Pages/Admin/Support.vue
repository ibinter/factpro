<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdminTabs from '@/Components/AdminTabs.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    tickets: Array,
    stats: Object,
});

const activeFilter = ref('all');

const filteredTickets = computed(() => {
    if (activeFilter.value === 'all') return props.tickets;
    return props.tickets.filter(t => t.status === activeFilter.value);
});

const priorityColors = {
    urgent: 'bg-red-100 text-red-700',
    high:   'bg-orange-100 text-orange-700',
    normal: 'bg-blue-100 text-blue-700',
    low:    'bg-gray-100 text-gray-500',
};
const statusColors = {
    open:         'bg-green-100 text-green-700',
    in_progress:  'bg-blue-100 text-blue-700',
    waiting_user: 'bg-amber-100 text-amber-700',
    resolved:     'bg-gray-100 text-gray-500',
    closed:       'bg-gray-200 text-gray-400',
};
const statusLabels = {
    open: 'Ouvert', in_progress: 'En cours', waiting_user: 'En attente',
    resolved: 'Résolu', closed: 'Fermé',
};
const priorityLabels = { low: 'Basse', normal: 'Normale', high: 'Haute', urgent: 'Urgent' };
const categoryLabels = {
    general: 'Général', billing: 'Facturation', technical: 'Technique',
    feature: 'Fonctionnalité', other: 'Autre',
};

const filterTabs = [
    { key: 'all', label: 'Tous' },
    { key: 'open', label: 'Ouverts' },
    { key: 'in_progress', label: 'En cours' },
    { key: 'waiting_user', label: 'En attente' },
    { key: 'resolved', label: 'Résolus' },
    { key: 'closed', label: 'Fermés' },
];
</script>

<template>
    <Head title="Admin — Support" />
    <AuthenticatedLayout>
        <template #header>
            <div class="space-y-4">
                <h2 class="text-xl font-semibold text-gray-800">Administration</h2>
                <AdminTabs />
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">

                <!-- KPI cards -->
                <div class="grid grid-cols-3 gap-4">
                    <div class="rounded-2xl bg-white p-5 shadow border-l-4 border-red-500">
                        <div class="text-xs font-semibold uppercase tracking-wide text-red-400">Ouverts</div>
                        <div class="mt-1 text-3xl font-bold text-red-600">{{ stats.open }}</div>
                    </div>
                    <div class="rounded-2xl bg-white p-5 shadow border-l-4 border-orange-400">
                        <div class="text-xs font-semibold uppercase tracking-wide text-orange-400">En cours</div>
                        <div class="mt-1 text-3xl font-bold text-orange-500">{{ stats.in_progress }}</div>
                    </div>
                    <div class="rounded-2xl bg-white p-5 shadow border-l-4 border-green-500">
                        <div class="text-xs font-semibold uppercase tracking-wide text-green-400">Résolus</div>
                        <div class="mt-1 text-3xl font-bold text-green-600">{{ stats.resolved }}</div>
                    </div>
                </div>

                <!-- Filtres onglets -->
                <div class="flex gap-2 flex-wrap">
                    <button
                        v-for="tab in filterTabs"
                        :key="tab.key"
                        @click="activeFilter = tab.key"
                        class="rounded-full px-4 py-1.5 text-sm font-medium transition"
                        :class="activeFilter === tab.key
                            ? 'bg-brand-600 text-white shadow'
                            : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'"
                    >
                        {{ tab.label }}
                    </button>
                </div>

                <!-- Tableau -->
                <div class="overflow-hidden rounded-2xl bg-white shadow">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Ticket</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Sujet</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Client</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Priorité</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Statut</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Rép.</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Date</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                <tr v-if="!filteredTickets.length">
                                    <td colspan="7" class="py-10 text-center text-sm text-gray-400">Aucun ticket</td>
                                </tr>
                                <tr v-for="ticket in filteredTickets" :key="ticket.id" class="hover:bg-gray-50 transition">
                                    <td class="px-5 py-3">
                                        <Link :href="route('admin.support.show', ticket.id)" class="font-mono text-sm font-semibold text-brand-600 hover:underline">
                                            {{ ticket.ticket_number }}
                                        </Link>
                                    </td>
                                    <td class="px-5 py-3">
                                        <Link :href="route('admin.support.show', ticket.id)" class="text-sm text-gray-800 hover:text-brand-600">
                                            {{ ticket.subject }}
                                        </Link>
                                        <div class="text-xs text-gray-400">{{ categoryLabels[ticket.category] ?? ticket.category }}</div>
                                    </td>
                                    <td class="px-5 py-3">
                                        <div class="text-sm text-gray-700">{{ ticket.user }}</div>
                                        <div class="text-xs text-gray-400">{{ ticket.email }}</div>
                                    </td>
                                    <td class="px-5 py-3">
                                        <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold" :class="priorityColors[ticket.priority]">
                                            {{ priorityLabels[ticket.priority] ?? ticket.priority }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3">
                                        <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold" :class="statusColors[ticket.status]">
                                            {{ statusLabels[ticket.status] ?? ticket.status }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-sm text-gray-500 text-center">{{ ticket.replies_count }}</td>
                                    <td class="px-5 py-3 text-xs text-gray-400">{{ ticket.created_at }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

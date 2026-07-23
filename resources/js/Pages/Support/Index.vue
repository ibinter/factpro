<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    tickets: Array,
});

const categoryLabels = {
    general: 'Général', billing: 'Facturation', technical: 'Technique',
    feature: 'Fonctionnalité', other: 'Autre',
};

const priorityLabels = { low: 'Basse', normal: 'Normale', high: 'Haute', urgent: 'Urgent' };
const priorityColors = {
    urgent: 'bg-red-100 text-red-700',
    high:   'bg-orange-100 text-orange-700',
    normal: 'bg-blue-100 text-blue-700',
    low:    'bg-gray-100 text-gray-500',
};

const statusLabels = {
    open: 'Ouvert', in_progress: 'En cours', waiting_user: 'En attente',
    resolved: 'Résolu', closed: 'Fermé',
};
const statusColors = {
    open:         'bg-green-100 text-green-700',
    in_progress:  'bg-blue-100 text-blue-700',
    waiting_user: 'bg-amber-100 text-amber-700',
    resolved:     'bg-gray-100 text-gray-500',
    closed:       'bg-gray-200 text-gray-400',
};
</script>

<template>
    <Head title="Mes tickets de support" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-800">Mes tickets de support</h2>
                <Link
                    :href="route('support.create')"
                    class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-brand-700 transition"
                >
                    + Nouveau ticket
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

                <!-- État vide -->
                <div v-if="!tickets.length" class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-gray-200 bg-white py-20 text-center">
                    <span class="text-6xl">🎫</span>
                    <h3 class="mt-4 text-lg font-semibold text-gray-700">Aucun ticket de support</h3>
                    <p class="mt-1 text-sm text-gray-500">Vous n'avez pas encore soumis de ticket. Notre équipe est là pour vous aider.</p>
                    <Link
                        :href="route('support.create')"
                        class="mt-6 rounded-lg bg-brand-600 px-6 py-2.5 text-sm font-semibold text-white shadow hover:bg-brand-700 transition"
                    >
                        Créer mon premier ticket
                    </Link>
                </div>

                <!-- Tableau -->
                <div v-else class="overflow-hidden rounded-2xl bg-white shadow">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Ticket</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Sujet</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Catégorie</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Priorité</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Statut</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Réponses</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Date</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                <tr v-for="ticket in tickets" :key="ticket.id" class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4">
                                        <Link :href="route('support.show', ticket.id)" class="font-mono text-sm font-semibold text-brand-600 hover:underline">
                                            {{ ticket.ticket_number }}
                                        </Link>
                                    </td>
                                    <td class="px-6 py-4">
                                        <Link :href="route('support.show', ticket.id)" class="text-sm text-gray-800 hover:text-brand-600">
                                            {{ ticket.subject }}
                                        </Link>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600">
                                            {{ categoryLabels[ticket.category] ?? ticket.category }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold" :class="priorityColors[ticket.priority]">
                                            {{ priorityLabels[ticket.priority] ?? ticket.priority }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold" :class="statusColors[ticket.status]">
                                            {{ statusLabels[ticket.status] ?? ticket.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 text-center">{{ ticket.replies_count }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-400">{{ ticket.created_at }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

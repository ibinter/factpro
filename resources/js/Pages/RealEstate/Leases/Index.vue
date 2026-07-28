<script setup>
import { computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    leases: Object,
})

const statusColors = {
    active:     'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
    expired:    'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
    terminated: 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
}

const statusLabels = {
    active:     'Actif',
    expired:    'Expiré',
    terminated: 'Résilié',
}

function isExpiringSoon(lease) {
    if (!lease.end_date || lease.is_open_ended || lease.status !== 'active') return false
    const end = new Date(lease.end_date)
    const now = new Date()
    const diffDays = (end - now) / (1000 * 60 * 60 * 24)
    return diffDays >= 0 && diffDays <= 90
}

function formatDate(date) {
    if (!date) return '—'
    return new Date(date).toLocaleDateString('fr-FR')
}

function formatCurrency(amount, currency = 'XOF') {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency }).format(amount)
}
</script>

<template>
    <Head title="Baux" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Baux locatifs</h2>
                <Link :href="route('real-estate.leases.create')"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
                    + Nouveau bail
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <!-- Alertes baux expirant bientôt -->
                <div v-if="leases.data.some(l => isExpiringSoon(l))"
                    class="mb-6 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 rounded-xl p-4">
                    <p class="text-sm font-semibold text-red-800 dark:text-red-300 mb-2">
                        Baux expirant dans les 90 prochains jours :
                    </p>
                    <ul class="space-y-1">
                        <li v-for="lease in leases.data.filter(l => isExpiringSoon(l))" :key="lease.id"
                            class="text-sm text-red-700 dark:text-red-400">
                            <Link :href="route('real-estate.leases.show', lease.id)" class="hover:underline font-medium">
                                {{ lease.property?.name }}
                            </Link>
                            — {{ lease.tenant?.name }} — expire le {{ formatDate(lease.end_date) }}
                        </li>
                    </ul>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl shadow overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Bien</th>
                                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Locataire</th>
                                    <th class="text-center px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Début</th>
                                    <th class="text-center px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Fin</th>
                                    <th class="text-right px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Loyer/mois</th>
                                    <th class="text-center px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Statut</th>
                                    <th class="px-6 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <tr v-for="lease in leases.data" :key="lease.id"
                                    :class="['hover:bg-gray-50 dark:hover:bg-gray-700/50 transition', isExpiringSoon(lease) ? 'bg-red-50 dark:bg-red-900/10' : '']">
                                    <td class="px-6 py-4">
                                        <p class="font-medium text-gray-900 dark:text-white">{{ lease.property?.name }}</p>
                                        <p v-if="lease.property?.city" class="text-xs text-gray-400">{{ lease.property.city }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-gray-900 dark:text-white">{{ lease.tenant?.name }}</p>
                                        <p v-if="lease.tenant?.email" class="text-xs text-gray-400">{{ lease.tenant.email }}</p>
                                    </td>
                                    <td class="px-6 py-4 text-center text-gray-700 dark:text-gray-300">{{ formatDate(lease.start_date) }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <span v-if="lease.is_open_ended" class="text-gray-400 text-xs">Indéterminé</span>
                                        <span v-else :class="['text-sm', isExpiringSoon(lease) ? 'font-semibold text-red-700 dark:text-red-400' : 'text-gray-700 dark:text-gray-300']">
                                            {{ formatDate(lease.end_date) }}
                                            <span v-if="isExpiringSoon(lease)" class="ml-1">⚠️</span>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right font-medium text-gray-900 dark:text-white">
                                        {{ formatCurrency(lease.monthly_rent, lease.property?.currency) }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span :class="['px-2 py-1 rounded-full text-xs font-medium', statusColors[lease.status]]">
                                            {{ statusLabels[lease.status] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <Link :href="route('real-estate.leases.show', lease.id)"
                                            class="text-indigo-600 dark:text-indigo-400 hover:underline text-xs">
                                            Voir
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="leases.data.length === 0" class="text-center py-12 text-gray-400 dark:text-gray-500">
                        <p class="text-4xl mb-3">📋</p>
                        <p>Aucun bail enregistré.</p>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="leases.last_page > 1" class="mt-4 flex justify-center gap-2">
                    <Link
                        v-for="link in leases.links" :key="link.label"
                        :href="link.url || '#'"
                        :class="['px-3 py-1 rounded text-sm', link.active ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-100', !link.url && 'opacity-50 cursor-not-allowed']"
                        v-html="link.label"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

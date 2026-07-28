<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    subscriptions: Object,
    filters: Object,
})

const currentStatus = ref(props.filters?.status ?? '')

const statusOptions = [
    { value: '', label: 'Tous les statuts' },
    { value: 'active',    label: 'Actif' },
    { value: 'paused',    label: 'Pausé' },
    { value: 'cancelled', label: 'Annulé' },
    { value: 'expired',   label: 'Expiré' },
]

const statusBadgeClass = (status) => {
    const map = {
        active:    'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        paused:    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        cancelled: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
        expired:   'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
    }
    return map[status] ?? 'bg-gray-100 text-gray-800'
}

const statusLabel = (status) => {
    const map = { active: 'Actif', paused: 'Pausé', cancelled: 'Annulé', expired: 'Expiré' }
    return map[status] ?? status
}

const frequencyLabel = (f) => {
    const map = { weekly: 'Hebdomadaire', monthly: 'Mensuel', quarterly: 'Trimestriel', biannual: 'Semestriel', annual: 'Annuel' }
    return map[f] ?? f
}

function filterByStatus() {
    router.get(route('subscriptions.index'), { status: currentStatus.value || undefined }, { preserveState: true, replace: true })
}

function pause(id) {
    if (confirm('Mettre cet abonnement en pause ?')) {
        router.post(route('subscriptions.pause', id))
    }
}

function resume(id) {
    router.post(route('subscriptions.resume', id))
}

function cancel(id) {
    const reason = prompt('Raison de l\'annulation (optionnel) :')
    if (reason !== null) {
        router.post(route('subscriptions.cancel', id), { cancel_reason: reason })
    }
}

function generateNow(id) {
    if (confirm('Générer une facture maintenant pour cet abonnement ?')) {
        router.post(route('subscriptions.generate-now', id))
    }
}

function formatAmount(amount, currency) {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: currency || 'XOF', maximumFractionDigits: 0 }).format(amount)
}

function formatDate(d) {
    if (!d) return '—'
    return new Date(d).toLocaleDateString('fr-FR')
}
</script>

<template>
    <Head title="Abonnements clients" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Abonnements clients
                </h2>
                <a :href="route('subscriptions.create')"
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
                    + Nouvel abonnement
                </a>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

                <!-- Filtres -->
                <div class="flex items-center gap-3">
                    <select v-model="currentStatus" @change="filterByStatus"
                        class="border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 focus:ring-indigo-500 focus:border-indigo-500">
                        <option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                    </select>
                </div>

                <!-- Tableau -->
                <div class="bg-white dark:bg-gray-800 shadow rounded-xl overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Client</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Abonnement</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Fréquence</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Montant</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Prochain débit</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Statut</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-if="subscriptions.data.length === 0">
                                    <td colspan="7" class="px-6 py-10 text-center text-gray-400 dark:text-gray-500 text-sm">
                                        Aucun abonnement trouvé.
                                    </td>
                                </tr>
                                <tr v-for="sub in subscriptions.data" :key="sub.id"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-750 transition">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ sub.customer?.name }}</div>
                                        <div class="text-xs text-gray-400">{{ sub.customer?.email }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ sub.name }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                        {{ frequencyLabel(sub.frequency) }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm font-semibold text-gray-800 dark:text-gray-200">
                                        {{ formatAmount(sub.amount, sub.currency) }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                        {{ formatDate(sub.next_billing_date) }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span :class="['inline-flex px-2 py-1 rounded-full text-xs font-medium', statusBadgeClass(sub.status)]">
                                            {{ statusLabel(sub.status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a :href="route('subscriptions.show', sub.id)"
                                               class="text-xs px-3 py-1 rounded bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                                                Voir
                                            </a>
                                            <button v-if="sub.status === 'active'" @click="pause(sub.id)"
                                                class="text-xs px-3 py-1 rounded bg-yellow-100 text-yellow-800 hover:bg-yellow-200 dark:bg-yellow-900 dark:text-yellow-200 dark:hover:bg-yellow-800 transition">
                                                Pause
                                            </button>
                                            <button v-if="sub.status === 'paused'" @click="resume(sub.id)"
                                                class="text-xs px-3 py-1 rounded bg-green-100 text-green-800 hover:bg-green-200 dark:bg-green-900 dark:text-green-200 dark:hover:bg-green-800 transition">
                                                Reprendre
                                            </button>
                                            <button v-if="['active','paused'].includes(sub.status)" @click="cancel(sub.id)"
                                                class="text-xs px-3 py-1 rounded bg-red-100 text-red-800 hover:bg-red-200 dark:bg-red-900 dark:text-red-200 dark:hover:bg-red-800 transition">
                                                Annuler
                                            </button>
                                            <button v-if="sub.status === 'active'" @click="generateNow(sub.id)"
                                                class="text-xs px-3 py-1 rounded bg-indigo-100 text-indigo-800 hover:bg-indigo-200 dark:bg-indigo-900 dark:text-indigo-200 dark:hover:bg-indigo-800 transition">
                                                Générer
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="subscriptions.last_page > 1"
                         class="px-6 py-4 flex items-center justify-between border-t border-gray-200 dark:border-gray-700">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Page {{ subscriptions.current_page }} / {{ subscriptions.last_page }}
                        </p>
                        <div class="flex gap-2">
                            <a v-if="subscriptions.prev_page_url" :href="subscriptions.prev_page_url"
                               class="px-3 py-1 text-sm rounded border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300">
                                Précédent
                            </a>
                            <a v-if="subscriptions.next_page_url" :href="subscriptions.next_page_url"
                               class="px-3 py-1 text-sm rounded border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300">
                                Suivant
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

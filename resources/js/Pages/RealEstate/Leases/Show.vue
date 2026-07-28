<script setup>
import { ref } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    lease: Object,
})

const statusColors = {
    active:     'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
    expired:    'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
    terminated: 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
}

const paymentStatusColors = {
    pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
    paid:    'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
    late:    'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
    partial: 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
}

const paymentStatusLabels = { pending: 'En attente', paid: 'Payé', late: 'En retard', partial: 'Partiel' }

const showTerminateModal = ref(false)
const terminateForm = useForm({ terminate_reason: '' })

const showRentModal = ref(false)
const rentForm = useForm({ period_month: '' })

function terminate() {
    terminateForm.post(route('real-estate.leases.terminate', props.lease.id), {
        onSuccess: () => { showTerminateModal.value = false }
    })
}

function generateRent() {
    rentForm.post(route('real-estate.leases.generate-rent', props.lease.id), {
        onSuccess: () => { showRentModal.value = false }
    })
}

function formatDate(d) {
    if (!d) return '—'
    return new Date(d).toLocaleDateString('fr-FR')
}

function formatCurrency(amount, currency = 'XOF') {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency }).format(amount)
}

function formatMonth(d) {
    if (!d) return '—'
    return new Date(d).toLocaleDateString('fr-FR', { month: 'long', year: 'numeric' })
}
</script>

<template>
    <Head :title="`Bail — ${lease.property?.name}`" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">
                        Bail — {{ lease.property?.name }}
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Locataire : {{ lease.tenant?.name }}</p>
                </div>
                <div class="flex gap-2">
                    <span :class="['px-3 py-1 rounded-full text-sm font-semibold self-center', statusColors[lease.status]]">
                        {{ lease.status_label }}
                    </span>
                    <button v-if="lease.status === 'active'" @click="showRentModal = true"
                        class="px-3 py-2 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 transition">
                        Générer quittance
                    </button>
                    <button v-if="lease.status === 'active'" @click="showTerminateModal = true"
                        class="px-3 py-2 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700 transition">
                        Résilier
                    </button>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <!-- Détails -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
                        <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Informations du bail</h3>
                        <dl class="space-y-3 text-sm">
                            <div class="flex justify-between"><dt class="text-gray-500">Bien</dt><dd class="text-gray-900 dark:text-white font-medium">{{ lease.property?.name }}</dd></div>
                            <div class="flex justify-between"><dt class="text-gray-500">Locataire</dt><dd class="text-gray-900 dark:text-white">{{ lease.tenant?.name }}</dd></div>
                            <div class="flex justify-between"><dt class="text-gray-500">Début</dt><dd class="text-gray-900 dark:text-white">{{ formatDate(lease.start_date) }}</dd></div>
                            <div class="flex justify-between"><dt class="text-gray-500">Fin</dt><dd class="text-gray-900 dark:text-white">{{ lease.is_open_ended ? 'Indéterminée' : formatDate(lease.end_date) }}</dd></div>
                            <div class="flex justify-between"><dt class="text-gray-500">Loyer mensuel</dt><dd class="font-semibold text-indigo-600 dark:text-indigo-400">{{ formatCurrency(lease.monthly_rent, lease.property?.currency) }}</dd></div>
                            <div class="flex justify-between"><dt class="text-gray-500">Dépôt de garantie</dt><dd class="text-gray-900 dark:text-white">{{ formatCurrency(lease.deposit_amount, lease.property?.currency) }}</dd></div>
                            <div class="flex justify-between"><dt class="text-gray-500">Jour de paiement</dt><dd class="text-gray-900 dark:text-white">Le {{ lease.payment_day }} du mois</dd></div>
                        </dl>
                    </div>

                    <div v-if="lease.terminated_at" class="bg-red-50 dark:bg-red-900/20 rounded-xl p-6">
                        <h3 class="font-semibold text-red-800 dark:text-red-300 mb-2">Bail résilié</h3>
                        <p class="text-sm text-red-700 dark:text-red-400">Date : {{ formatDate(lease.terminated_at) }}</p>
                        <p class="text-sm text-red-700 dark:text-red-400 mt-1">Motif : {{ lease.terminate_reason }}</p>
                    </div>
                    <div v-else-if="lease.notes" class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
                        <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Notes</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-300">{{ lease.notes }}</p>
                    </div>
                </div>

                <!-- Paiements -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                        <h3 class="font-semibold text-gray-900 dark:text-white">Historique des paiements</h3>
                    </div>
                    <table v-if="lease.rent_payments?.length" class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase">Période</th>
                                <th class="text-right px-6 py-3 text-xs font-medium text-gray-500 uppercase">Montant</th>
                                <th class="text-center px-6 py-3 text-xs font-medium text-gray-500 uppercase">Statut</th>
                                <th class="text-center px-6 py-3 text-xs font-medium text-gray-500 uppercase">Facture</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <tr v-for="p in lease.rent_payments" :key="p.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-3 capitalize text-gray-900 dark:text-white">{{ formatMonth(p.period_month) }}</td>
                                <td class="px-6 py-3 text-right text-gray-900 dark:text-white">{{ formatCurrency(p.amount, lease.property?.currency) }}</td>
                                <td class="px-6 py-3 text-center">
                                    <span :class="['px-2 py-1 rounded-full text-xs', paymentStatusColors[p.status]]">{{ paymentStatusLabels[p.status] }}</span>
                                </td>
                                <td class="px-6 py-3 text-center">
                                    <Link v-if="p.document_id" :href="route('documents.show', p.document_id)"
                                        class="text-indigo-600 dark:text-indigo-400 hover:underline text-xs">Voir</Link>
                                    <span v-else class="text-gray-400 text-xs">—</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div v-else class="text-center py-10 text-gray-400">Aucun paiement.</div>
                </div>
            </div>
        </div>

        <!-- Modal résiliation -->
        <div v-if="showTerminateModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl p-6 w-full max-w-sm">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Résilier le bail</h3>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Motif de résiliation *</label>
                    <textarea v-model="terminateForm.terminate_reason" rows="3"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
                    <p v-if="terminateForm.errors.terminate_reason" class="mt-1 text-xs text-red-600">{{ terminateForm.errors.terminate_reason }}</p>
                </div>
                <div class="flex gap-3 justify-end">
                    <button @click="showTerminateModal = false" class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300">Annuler</button>
                    <button @click="terminate" :disabled="terminateForm.processing"
                        class="px-4 py-2 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700 disabled:opacity-50">
                        Confirmer la résiliation
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal quittance -->
        <div v-if="showRentModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl p-6 w-full max-w-sm">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Générer une quittance</h3>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mois concerné</label>
                    <input type="month" v-model="rentForm.period_month"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                </div>
                <div class="flex gap-3 justify-end">
                    <button @click="showRentModal = false" class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300">Annuler</button>
                    <button @click="generateRent" :disabled="rentForm.processing"
                        class="px-4 py-2 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 disabled:opacity-50">
                        Générer
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

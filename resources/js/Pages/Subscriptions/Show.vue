<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    subscription: Object,
})

const sub = props.subscription

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

const invoiceStatusLabel = (status) => {
    const map = { generated: 'Générée', failed: 'Échec', skipped: 'Ignorée' }
    return map[status] ?? status
}

const invoiceStatusClass = (status) => {
    const map = {
        generated: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        failed:    'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
        skipped:   'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
    }
    return map[status] ?? 'bg-gray-100 text-gray-800'
}

const frequencyLabel = (f) => {
    const map = { weekly: 'Hebdomadaire', monthly: 'Mensuel', quarterly: 'Trimestriel', biannual: 'Semestriel', annual: 'Annuel' }
    return map[f] ?? f
}

function formatAmount(amount, currency) {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: currency || 'XOF', maximumFractionDigits: 0 }).format(amount)
}

function formatDate(d) {
    if (!d) return '—'
    return new Date(d).toLocaleDateString('fr-FR')
}

function pause() {
    if (confirm('Mettre cet abonnement en pause ?')) {
        router.post(route('subscriptions.pause', sub.id))
    }
}

function resume() {
    router.post(route('subscriptions.resume', sub.id))
}

function cancel() {
    const reason = prompt('Raison de l\'annulation (optionnel) :')
    if (reason !== null) {
        router.post(route('subscriptions.cancel', sub.id), { cancel_reason: reason })
    }
}

function generateNow() {
    if (confirm('Générer une facture maintenant ?')) {
        router.post(route('subscriptions.generate-now', sub.id))
    }
}
</script>

<template>
    <Head :title="'Abonnement — ' + sub.name" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Abonnement : {{ sub.name }}
                </h2>
                <a :href="route('subscriptions.index')"
                   class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
                    &larr; Retour à la liste
                </a>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <!-- Hero : prochain débit -->
                <div class="bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-200 dark:border-indigo-700 rounded-xl p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-wider text-indigo-500 dark:text-indigo-400 font-medium mb-1">Prochain débit</p>
                        <p class="text-4xl font-bold text-indigo-700 dark:text-indigo-200">
                            {{ formatDate(sub.next_billing_date) }}
                        </p>
                        <p class="text-sm text-indigo-500 dark:text-indigo-400 mt-1">
                            {{ formatAmount(sub.amount, sub.currency) }} — {{ frequencyLabel(sub.frequency) }}
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <span :class="['inline-flex px-3 py-1 rounded-full text-sm font-medium', statusBadgeClass(sub.status)]">
                            {{ statusLabel(sub.status) }}
                        </span>
                        <button v-if="sub.status === 'active'" @click="pause"
                            class="px-4 py-1.5 text-sm rounded-lg bg-yellow-100 text-yellow-800 hover:bg-yellow-200 dark:bg-yellow-900 dark:text-yellow-200 dark:hover:bg-yellow-800 transition font-medium">
                            Mettre en pause
                        </button>
                        <button v-if="sub.status === 'paused'" @click="resume"
                            class="px-4 py-1.5 text-sm rounded-lg bg-green-100 text-green-800 hover:bg-green-200 dark:bg-green-900 dark:text-green-200 dark:hover:bg-green-800 transition font-medium">
                            Reprendre
                        </button>
                        <button v-if="['active','paused'].includes(sub.status)" @click="cancel"
                            class="px-4 py-1.5 text-sm rounded-lg bg-red-100 text-red-800 hover:bg-red-200 dark:bg-red-900 dark:text-red-200 dark:hover:bg-red-800 transition font-medium">
                            Annuler
                        </button>
                        <button v-if="sub.status === 'active'" @click="generateNow"
                            class="px-4 py-1.5 text-sm rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition font-medium">
                            Générer maintenant
                        </button>
                    </div>
                </div>

                <!-- Détails -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <!-- Infos abonnement -->
                    <div class="bg-white dark:bg-gray-800 shadow rounded-xl p-6">
                        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">Abonnement</h3>
                        <dl class="space-y-3">
                            <div class="flex justify-between text-sm">
                                <dt class="text-gray-500 dark:text-gray-400">Nom</dt>
                                <dd class="font-medium text-gray-800 dark:text-gray-200">{{ sub.name }}</dd>
                            </div>
                            <div class="flex justify-between text-sm">
                                <dt class="text-gray-500 dark:text-gray-400">Fréquence</dt>
                                <dd class="font-medium text-gray-800 dark:text-gray-200">{{ frequencyLabel(sub.frequency) }}</dd>
                            </div>
                            <div class="flex justify-between text-sm">
                                <dt class="text-gray-500 dark:text-gray-400">Montant HT</dt>
                                <dd class="font-medium text-gray-800 dark:text-gray-200">{{ formatAmount(sub.amount, sub.currency) }}</dd>
                            </div>
                            <div class="flex justify-between text-sm">
                                <dt class="text-gray-500 dark:text-gray-400">TVA</dt>
                                <dd class="font-medium text-gray-800 dark:text-gray-200">{{ sub.tax_rate }} %</dd>
                            </div>
                            <div class="flex justify-between text-sm">
                                <dt class="text-gray-500 dark:text-gray-400">Délai paiement</dt>
                                <dd class="font-medium text-gray-800 dark:text-gray-200">{{ sub.payment_terms }} jours</dd>
                            </div>
                            <div class="flex justify-between text-sm">
                                <dt class="text-gray-500 dark:text-gray-400">Début</dt>
                                <dd class="font-medium text-gray-800 dark:text-gray-200">{{ formatDate(sub.start_date) }}</dd>
                            </div>
                            <div class="flex justify-between text-sm">
                                <dt class="text-gray-500 dark:text-gray-400">Fin</dt>
                                <dd class="font-medium text-gray-800 dark:text-gray-200">{{ formatDate(sub.end_date) }}</dd>
                            </div>
                            <div v-if="sub.last_billed_at" class="flex justify-between text-sm">
                                <dt class="text-gray-500 dark:text-gray-400">Dernière facturation</dt>
                                <dd class="font-medium text-gray-800 dark:text-gray-200">{{ formatDate(sub.last_billed_at) }}</dd>
                            </div>
                            <div v-if="sub.cancel_reason" class="flex justify-between text-sm">
                                <dt class="text-gray-500 dark:text-gray-400">Raison annulation</dt>
                                <dd class="font-medium text-red-600 dark:text-red-400">{{ sub.cancel_reason }}</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Infos client -->
                    <div class="bg-white dark:bg-gray-800 shadow rounded-xl p-6">
                        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">Client</h3>
                        <dl class="space-y-3">
                            <div class="flex justify-between text-sm">
                                <dt class="text-gray-500 dark:text-gray-400">Nom</dt>
                                <dd class="font-medium text-gray-800 dark:text-gray-200">{{ sub.customer?.name }}</dd>
                            </div>
                            <div v-if="sub.customer?.email" class="flex justify-between text-sm">
                                <dt class="text-gray-500 dark:text-gray-400">Email</dt>
                                <dd class="font-medium text-gray-800 dark:text-gray-200">{{ sub.customer.email }}</dd>
                            </div>
                            <div v-if="sub.customer?.phone" class="flex justify-between text-sm">
                                <dt class="text-gray-500 dark:text-gray-400">Téléphone</dt>
                                <dd class="font-medium text-gray-800 dark:text-gray-200">{{ sub.customer.phone }}</dd>
                            </div>
                            <div v-if="sub.customer?.city" class="flex justify-between text-sm">
                                <dt class="text-gray-500 dark:text-gray-400">Ville</dt>
                                <dd class="font-medium text-gray-800 dark:text-gray-200">{{ sub.customer.city }}</dd>
                            </div>
                        </dl>
                        <div v-if="sub.notes" class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Notes</p>
                            <p class="text-sm text-gray-600 dark:text-gray-300">{{ sub.notes }}</p>
                        </div>
                    </div>
                </div>

                <!-- Historique factures -->
                <div class="bg-white dark:bg-gray-800 shadow rounded-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Historique des factures générées</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Date</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Montant</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Statut</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Document</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Notes</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-if="!sub.invoices || sub.invoices.length === 0">
                                    <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-400 dark:text-gray-500">
                                        Aucune facture générée pour cet abonnement.
                                    </td>
                                </tr>
                                <tr v-for="inv in sub.invoices" :key="inv.id"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-750">
                                    <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                        {{ formatDate(inv.billing_date) }}
                                    </td>
                                    <td class="px-6 py-4 text-sm font-semibold text-right text-gray-800 dark:text-gray-200">
                                        {{ formatAmount(inv.amount, sub.currency) }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span :class="['inline-flex px-2 py-1 rounded-full text-xs font-medium', invoiceStatusClass(inv.status)]">
                                            {{ invoiceStatusLabel(inv.status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <a v-if="inv.document"
                                           :href="route('documents.show', inv.document.id)"
                                           class="text-indigo-600 dark:text-indigo-400 hover:underline font-medium">
                                            {{ inv.document.number }}
                                        </a>
                                        <span v-else class="text-gray-400">—</span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                        {{ inv.notes || '—' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

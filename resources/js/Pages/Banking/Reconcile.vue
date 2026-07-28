<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    account: Object,
    bankTransactions: Object,   // paginated
    unmatchedPayments: Array,
})

// Sélection courante
const selectedTransaction = ref(null)
const selectedPayment = ref(null)
const matching = ref(false)
const matchError = ref(null)

// Transactions affichées localement (pour retirer les rapprochées sans reload)
const localTransactions = ref([...props.bankTransactions.data])
const localPayments = ref([...props.unmatchedPayments])

const totalTransactions = ref(props.bankTransactions.total)
const reconciledCount = computed(() => {
    // On affiche ce qui a déjà été rapproché (total - non rapprochées restantes)
    return totalTransactions.value - localTransactions.value.length
})

function selectTransaction(tx) {
    if (selectedTransaction.value?.id === tx.id) {
        selectedTransaction.value = null
    } else {
        selectedTransaction.value = tx
    }
    matchError.value = null
}

function selectPayment(pay) {
    if (selectedPayment.value?.id === pay.id) {
        selectedPayment.value = null
    } else {
        selectedPayment.value = pay
    }
    matchError.value = null
}

async function matchSelected() {
    if (!selectedTransaction.value || !selectedPayment.value) return
    matching.value = true
    matchError.value = null

    try {
        const resp = await fetch(route('banking.match'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                transaction_id: selectedTransaction.value.id,
                payment_id: selectedPayment.value.id,
            }),
        })
        const data = await resp.json()
        if (data.success) {
            localTransactions.value = localTransactions.value.filter(t => t.id !== selectedTransaction.value.id)
            localPayments.value = localPayments.value.filter(p => p.id !== selectedPayment.value.id)
            selectedTransaction.value = null
            selectedPayment.value = null
        } else {
            matchError.value = data.message || 'Erreur lors du rapprochement.'
        }
    } catch {
        matchError.value = 'Erreur réseau.'
    } finally {
        matching.value = false
    }
}

function formatAmount(amount, type) {
    const n = parseFloat(amount)
    return new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(n)
}

function formatDate(d) {
    if (!d) return '—'
    return new Date(d).toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

function progressPct() {
    if (totalTransactions.value === 0) return 0
    return Math.round((reconciledCount.value / totalTransactions.value) * 100)
}

const methodLabels = {
    cash: 'Espèces',
    mobile_money: 'Mobile Money',
    card: 'Carte',
    bank_transfer: 'Virement',
    cheque: 'Chèque',
    credit: 'Crédit',
}
</script>

<template>
    <Head :title="`Rapprochement — ${account.name}`" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <a :href="route('banking.index')" class="text-indigo-600 dark:text-indigo-400 hover:underline text-sm">← Comptes</a>
                <span class="text-gray-400">/</span>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ account.name }}
                </h2>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

                <!-- Barre de progression -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            Progression du rapprochement
                        </span>
                        <span class="text-sm font-bold text-gray-900 dark:text-white">
                            {{ reconciledCount }} / {{ totalTransactions }} transactions
                        </span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                        <div
                            class="bg-green-500 h-3 rounded-full transition-all duration-500"
                            :style="`width: ${progressPct()}%`"
                        ></div>
                    </div>
                    <div class="flex justify-between mt-1 text-xs text-gray-500 dark:text-gray-400">
                        <span>{{ progressPct() }}% rapproché</span>
                        <span>{{ localTransactions.length }} en attente</span>
                    </div>
                </div>

                <!-- Bouton Associer -->
                <div v-if="selectedTransaction && selectedPayment" class="bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-700 rounded-xl p-4 flex items-center justify-between">
                    <div class="text-sm text-indigo-800 dark:text-indigo-300">
                        <span class="font-medium">{{ selectedTransaction.description }}</span>
                        <span class="mx-2">↔</span>
                        <span class="font-medium">{{ selectedPayment.document?.customer?.name || 'Client' }}</span>
                        <span class="mx-2">|</span>
                        <span>{{ formatAmount(selectedTransaction.amount) }} FCFA</span>
                    </div>
                    <button
                        @click="matchSelected"
                        :disabled="matching"
                        class="px-5 py-2 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 disabled:opacity-60 transition"
                    >
                        {{ matching ? 'Rapprochement...' : '✓ Associer' }}
                    </button>
                </div>
                <div v-if="matchError" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-xl px-4 py-3 text-sm text-red-700 dark:text-red-400">
                    {{ matchError }}
                </div>

                <!-- Layout 2 colonnes -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

                    <!-- Colonne gauche: transactions bancaires -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 flex flex-col" style="max-height: 70vh;">
                        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between sticky top-0 bg-white dark:bg-gray-800 rounded-t-xl z-10">
                            <h3 class="font-semibold text-gray-900 dark:text-white text-sm">
                                Transactions bancaires non rapprochées
                            </h3>
                            <span class="text-xs text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded-full">{{ localTransactions.length }}</span>
                        </div>

                        <div class="overflow-y-auto flex-1 divide-y divide-gray-100 dark:divide-gray-700">
                            <div v-if="localTransactions.length === 0" class="p-8 text-center text-gray-500 dark:text-gray-400 text-sm">
                                Toutes les transactions sont rapprochées !
                            </div>
                            <button
                                v-for="tx in localTransactions"
                                :key="tx.id"
                                @click="selectTransaction(tx)"
                                class="w-full text-left px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition flex items-start gap-3"
                                :class="selectedTransaction?.id === tx.id ? 'bg-indigo-50 dark:bg-indigo-900/20 border-l-4 border-indigo-500' : ''"
                            >
                                <!-- Indicateur type -->
                                <div
                                    class="mt-0.5 w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0"
                                    :class="tx.type === 'credit' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'"
                                >
                                    {{ tx.type === 'credit' ? '+' : '−' }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex justify-between items-start">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate pr-2">{{ tx.description }}</p>
                                        <span
                                            class="text-sm font-bold whitespace-nowrap flex-shrink-0"
                                            :class="tx.type === 'credit' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'"
                                        >
                                            {{ tx.type === 'credit' ? '+' : '−' }}{{ formatAmount(tx.amount) }}
                                        </span>
                                    </div>
                                    <div class="flex gap-2 mt-0.5">
                                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ formatDate(tx.date) }}</span>
                                        <span v-if="tx.reference" class="text-xs text-gray-400 dark:text-gray-500 truncate">Réf: {{ tx.reference }}</span>
                                    </div>
                                </div>
                            </button>
                        </div>

                        <!-- Pagination -->
                        <div v-if="bankTransactions.last_page > 1" class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 flex justify-between items-center">
                            <span class="text-xs text-gray-500 dark:text-gray-400">Page {{ bankTransactions.current_page }} / {{ bankTransactions.last_page }}</span>
                            <div class="flex gap-2">
                                <a
                                    v-if="bankTransactions.prev_page_url"
                                    :href="bankTransactions.prev_page_url"
                                    class="px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400"
                                >Précédent</a>
                                <a
                                    v-if="bankTransactions.next_page_url"
                                    :href="bankTransactions.next_page_url"
                                    class="px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400"
                                >Suivant</a>
                            </div>
                        </div>
                    </div>

                    <!-- Colonne droite: paiements FactPro -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 flex flex-col" style="max-height: 70vh;">
                        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between sticky top-0 bg-white dark:bg-gray-800 rounded-t-xl z-10">
                            <h3 class="font-semibold text-gray-900 dark:text-white text-sm">
                                Paiements FactPro non rapprochés
                            </h3>
                            <span class="text-xs text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded-full">{{ localPayments.length }}</span>
                        </div>

                        <div class="overflow-y-auto flex-1 divide-y divide-gray-100 dark:divide-gray-700">
                            <div v-if="localPayments.length === 0" class="p-8 text-center text-gray-500 dark:text-gray-400 text-sm">
                                Tous les paiements sont rapprochés.
                            </div>
                            <button
                                v-for="pay in localPayments"
                                :key="pay.id"
                                @click="selectPayment(pay)"
                                class="w-full text-left px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition flex items-start gap-3"
                                :class="selectedPayment?.id === pay.id ? 'bg-indigo-50 dark:bg-indigo-900/20 border-l-4 border-indigo-500' : ''"
                            >
                                <!-- Méthode paiement badge -->
                                <div class="mt-0.5 px-1.5 py-0.5 bg-blue-100 dark:bg-blue-900/30 rounded text-xs font-medium text-blue-700 dark:text-blue-400 flex-shrink-0 whitespace-nowrap">
                                    {{ methodLabels[pay.method] || pay.method }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex justify-between items-start">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate pr-2">
                                            {{ pay.document?.customer?.name || 'Client inconnu' }}
                                        </p>
                                        <span class="text-sm font-bold text-gray-900 dark:text-white whitespace-nowrap flex-shrink-0">
                                            {{ formatAmount(pay.amount) }}
                                        </span>
                                    </div>
                                    <div class="flex gap-2 mt-0.5">
                                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ formatDate(pay.paid_at) }}</span>
                                        <span v-if="pay.document?.number" class="text-xs text-indigo-600 dark:text-indigo-400 truncate">{{ pay.document.number }}</span>
                                        <span v-if="pay.reference" class="text-xs text-gray-400 dark:text-gray-500 truncate">Réf: {{ pay.reference }}</span>
                                    </div>
                                </div>
                            </button>
                        </div>
                    </div>

                </div>

                <!-- Légende -->
                <div class="text-xs text-gray-500 dark:text-gray-400 text-center">
                    Cliquez sur une transaction et un paiement pour les sélectionner, puis appuyez sur "Associer".
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

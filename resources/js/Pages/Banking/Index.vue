<script setup>
import { ref } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    accounts: Array,
})

// Modal état
const showCreateModal = ref(false)
const showImportModal = ref(false)
const importAccountId = ref(null)
const importResult = ref(null)
const importLoading = ref(false)

const form = useForm({
    name: '',
    bank_name: '',
    account_number: '',
    iban: '',
    swift_bic: '',
    currency: 'XOF',
    current_balance: 0,
})

function submitCreate() {
    form.post(route('banking.account.create'), {
        onSuccess: () => {
            showCreateModal.value = false
            form.reset()
        },
    })
}

function openImport(accountId) {
    importAccountId.value = accountId
    importResult.value = null
    showImportModal.value = true
}

async function submitImport(event) {
    const file = event.target.files[0]
    if (!file) return
    importLoading.value = true
    importResult.value = null

    const formData = new FormData()
    formData.append('file', file)

    try {
        const resp = await fetch(route('banking.import', { account: importAccountId.value }), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: formData,
        })
        const data = await resp.json()
        importResult.value = data
        if (data.success) {
            router.reload({ only: ['accounts'] })
        }
    } catch {
        importResult.value = { success: false, message: 'Erreur lors de l\'import.' }
    } finally {
        importLoading.value = false
        event.target.value = ''
    }
}

function maskIban(iban) {
    if (!iban) return '—'
    if (iban.length <= 8) return iban
    return iban.slice(0, 4) + ' ···· ' + iban.slice(-4)
}

function formatAmount(amount, currency) {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: currency || 'XOF', minimumFractionDigits: 0 }).format(amount)
}
</script>

<template>
    <Head title="Rapprochement bancaire" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Rapprochement bancaire
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <!-- Header actions -->
                <div class="flex justify-between items-center mb-6">
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        {{ accounts.length }} compte(s) bancaire(s) configuré(s)
                    </p>
                    <button
                        @click="showCreateModal = true"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium transition"
                    >
                        + Ajouter un compte
                    </button>
                </div>

                <!-- Comptes vides -->
                <div v-if="accounts.length === 0" class="bg-white dark:bg-gray-800 rounded-xl shadow p-12 text-center">
                    <div class="text-5xl mb-4">🏦</div>
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-2">Aucun compte bancaire</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6 text-sm">Ajoutez votre premier compte pour commencer le rapprochement.</p>
                    <button @click="showCreateModal = true" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium">
                        Ajouter un compte
                    </button>
                </div>

                <!-- Grille de comptes -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="account in accounts"
                        :key="account.id"
                        class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 flex flex-col gap-4"
                    >
                        <!-- Entête -->
                        <div class="flex items-start justify-between">
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white text-base">{{ account.name }}</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ account.bank_name || 'Banque non précisée' }}</p>
                            </div>
                            <span
                                class="px-2 py-0.5 rounded-full text-xs font-medium"
                                :class="account.is_active ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400'"
                            >
                                {{ account.is_active ? 'Actif' : 'Inactif' }}
                            </span>
                        </div>

                        <!-- IBAN & Solde -->
                        <div class="space-y-1">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500 dark:text-gray-400">IBAN</span>
                                <span class="font-mono text-gray-700 dark:text-gray-300">{{ maskIban(account.iban) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500 dark:text-gray-400">Solde</span>
                                <span class="font-semibold text-gray-900 dark:text-white">{{ formatAmount(account.current_balance, account.currency) }}</span>
                            </div>
                        </div>

                        <!-- Statistiques rapprochement -->
                        <div class="flex gap-2 text-xs">
                            <div class="flex-1 bg-green-50 dark:bg-green-900/20 rounded-lg p-2 text-center">
                                <div class="font-bold text-green-700 dark:text-green-400 text-base">{{ account.reconciled_transactions_count ?? 0 }}</div>
                                <div class="text-green-600 dark:text-green-500">Rapprochées</div>
                            </div>
                            <div class="flex-1 bg-orange-50 dark:bg-orange-900/20 rounded-lg p-2 text-center">
                                <div class="font-bold text-orange-700 dark:text-orange-400 text-base">{{ (account.transactions_count ?? 0) - (account.reconciled_transactions_count ?? 0) }}</div>
                                <div class="text-orange-600 dark:text-orange-500">En attente</div>
                            </div>
                            <div class="flex-1 bg-gray-50 dark:bg-gray-700/40 rounded-lg p-2 text-center">
                                <div class="font-bold text-gray-700 dark:text-gray-300 text-base">{{ account.transactions_count ?? 0 }}</div>
                                <div class="text-gray-500 dark:text-gray-400">Total</div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex gap-2 pt-2 border-t border-gray-100 dark:border-gray-700">
                            <a
                                :href="route('banking.show', { account: account.id })"
                                class="flex-1 text-center px-3 py-1.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium transition"
                            >
                                Rapprocher
                            </a>
                            <button
                                @click="openImport(account.id)"
                                class="flex-1 px-3 py-1.5 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 text-sm font-medium transition"
                            >
                                Importer CSV
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Modal Ajouter compte -->
        <div v-if="showCreateModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-lg">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Ajouter un compte bancaire</h3>
                    <button @click="showCreateModal = false; form.reset()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">✕</button>
                </div>
                <form @submit.prevent="submitCreate" class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nom du compte *</label>
                            <input v-model="form.name" type="text" required class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="Compte courant principal" />
                            <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Banque</label>
                            <input v-model="form.bank_name" type="text" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="BNI, Ecobank..." />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Devise *</label>
                            <select v-model="form.currency" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                                <option value="XOF">XOF (FCFA)</option>
                                <option value="EUR">EUR</option>
                                <option value="USD">USD</option>
                                <option value="GHS">GHS</option>
                                <option value="NGN">NGN</option>
                            </select>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">IBAN</label>
                            <input v-model="form.iban" type="text" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm font-mono dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="CI00 XXXX XXXX XXXX XXXX" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">N° de compte</label>
                            <input v-model="form.account_number" type="text" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">SWIFT/BIC</label>
                            <input v-model="form.swift_bic" type="text" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm font-mono dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none" />
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Solde initial</label>
                            <input v-model.number="form.current_balance" type="number" step="0.01" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none" />
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="showCreateModal = false; form.reset()" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">Annuler</button>
                        <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 disabled:opacity-60">
                            {{ form.processing ? 'Création...' : 'Créer le compte' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Import CSV -->
        <div v-if="showImportModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-md">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Importer un relevé CSV</h3>
                    <button @click="showImportModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">✕</button>
                </div>
                <div class="p-6 space-y-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Format accepté : <span class="font-mono bg-gray-100 dark:bg-gray-700 px-1 rounded">date;description;montant</span><br/>
                        ou avec virgule. Les montants négatifs sont des débits.
                    </p>
                    <div v-if="importResult" :class="importResult.success ? 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-700 text-green-700 dark:text-green-400' : 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-700 text-red-700 dark:text-red-400'" class="border rounded-lg p-3 text-sm">
                        {{ importResult.message }}
                    </div>
                    <label class="block w-full">
                        <input
                            type="file"
                            accept=".csv,.txt"
                            @change="submitImport"
                            :disabled="importLoading"
                            class="block w-full text-sm text-gray-500 dark:text-gray-400
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-lg file:border-0
                                file:text-sm file:font-medium
                                file:bg-indigo-50 file:text-indigo-700
                                dark:file:bg-indigo-900/30 dark:file:text-indigo-400
                                hover:file:bg-indigo-100 cursor-pointer disabled:opacity-60"
                        />
                    </label>
                    <div v-if="importLoading" class="text-center text-sm text-gray-500 dark:text-gray-400">Importation en cours...</div>
                    <div class="flex justify-end">
                        <button @click="showImportModal = false" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">Fermer</button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

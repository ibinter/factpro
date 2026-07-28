<script setup>
import { ref, computed } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    entries:  Object,
    accounts: Array,
    filters:  Object,
    currency: String,
})

const TYPE_LABELS = {
    manual:       'Manuel',
    auto_invoice: 'Facture auto',
    auto_payment: 'Paiement auto',
    auto_purchase: 'Achat auto',
}

// Filtres
const filterForm = useForm({
    date_from: props.filters?.date_from ?? '',
    date_to:   props.filters?.date_to   ?? '',
    type:      props.filters?.type      ?? '',
})

function applyFilters() {
    router.get(route('accounting.journal'), filterForm.data(), { preserveState: true, replace: true })
}

// Expand/collapse des lignes
const expanded = ref(new Set())
function toggle(id) {
    if (expanded.value.has(id)) expanded.value.delete(id)
    else expanded.value.add(id)
}

// Modal nouvelle écriture
const showModal = ref(false)
const entryForm = useForm({
    date:        new Date().toISOString().slice(0, 10),
    description: '',
    reference:   '',
    lines: [
        { account_id: null, label: '', debit: 0, credit: 0 },
        { account_id: null, label: '', debit: 0, credit: 0 },
    ],
})

const totalDebit  = computed(() => entryForm.lines.reduce((s, l) => s + parseFloat(l.debit  || 0), 0))
const totalCredit = computed(() => entryForm.lines.reduce((s, l) => s + parseFloat(l.credit || 0), 0))
const isBalanced  = computed(() => Math.abs(totalDebit.value - totalCredit.value) < 0.01)

function addLine() {
    entryForm.lines.push({ account_id: null, label: '', debit: 0, credit: 0 })
}
function removeLine(i) {
    if (entryForm.lines.length > 2) entryForm.lines.splice(i, 1)
}

function openModal() {
    entryForm.reset()
    entryForm.date = new Date().toISOString().slice(0, 10)
    entryForm.lines = [
        { account_id: null, label: '', debit: 0, credit: 0 },
        { account_id: null, label: '', debit: 0, credit: 0 },
    ]
    showModal.value = true
}

function submitEntry() {
    if (!isBalanced.value) return
    entryForm.post(route('accounting.entries.store'), {
        onSuccess: () => { showModal.value = false },
    })
}

function fmt(val) {
    return new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(val ?? 0)
}

function accountLabel(account) {
    return account ? `${account.code} - ${account.name}` : ''
}
</script>

<template>
    <Head title="Journal comptable" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Journal comptable
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <!-- Filtres -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4">
                    <div class="flex flex-wrap gap-3 items-end">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Du</label>
                            <input v-model="filterForm.date_from" type="date"
                                class="border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 dark:text-white" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Au</label>
                            <input v-model="filterForm.date_to" type="date"
                                class="border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 dark:text-white" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Type</label>
                            <select v-model="filterForm.type"
                                class="border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 dark:text-white">
                                <option value="">Tous</option>
                                <option v-for="(label, val) in TYPE_LABELS" :key="val" :value="val">{{ label }}</option>
                            </select>
                        </div>
                        <button @click="applyFilters"
                            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                            Filtrer
                        </button>
                        <div class="ml-auto">
                            <button @click="openModal"
                                class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition">
                                + Nouvelle écriture
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Liste des écritures -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow overflow-hidden">
                    <div v-if="entries.data.length === 0" class="p-12 text-center text-gray-400 dark:text-gray-500">
                        Aucune écriture pour cette période.
                    </div>

                    <div v-else>
                        <div
                            v-for="entry in entries.data"
                            :key="entry.id"
                            class="border-b border-gray-100 dark:border-gray-700 last:border-0"
                        >
                            <!-- En-tête écriture -->
                            <div
                                @click="toggle(entry.id)"
                                class="flex items-center gap-4 px-4 py-3 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition select-none"
                            >
                                <span class="text-gray-400 dark:text-gray-500 text-xs w-4 flex-shrink-0">
                                    {{ expanded.has(entry.id) ? '▼' : '▶' }}
                                </span>
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400 w-24 flex-shrink-0">
                                    {{ entry.date }}
                                </span>
                                <span v-if="entry.reference" class="font-mono text-xs text-indigo-600 dark:text-indigo-400 w-24 flex-shrink-0">
                                    {{ entry.reference }}
                                </span>
                                <span v-else class="w-24 flex-shrink-0"></span>
                                <span class="text-sm text-gray-800 dark:text-gray-200 flex-1 truncate">
                                    {{ entry.description }}
                                </span>
                                <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 flex-shrink-0">
                                    {{ TYPE_LABELS[entry.type] ?? entry.type }}
                                </span>
                                <span class="font-mono text-sm font-semibold text-gray-800 dark:text-gray-200 w-32 text-right flex-shrink-0">
                                    {{ fmt(entry.total_debit) }}
                                </span>
                            </div>

                            <!-- Lignes -->
                            <div v-if="expanded.has(entry.id)" class="bg-gray-50 dark:bg-gray-900/50 border-t border-gray-100 dark:border-gray-700">
                                <table class="min-w-full">
                                    <thead>
                                        <tr class="text-xs text-gray-400 dark:text-gray-500">
                                            <th class="px-8 py-2 text-left w-32">Compte</th>
                                            <th class="px-4 py-2 text-left">Libellé</th>
                                            <th class="px-4 py-2 text-right w-28">Débit</th>
                                            <th class="px-4 py-2 text-right w-28">Crédit</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                        <tr v-for="line in entry.lines" :key="line.id" class="text-sm">
                                            <td class="px-8 py-2 font-mono text-indigo-700 dark:text-indigo-300">
                                                {{ line.account?.code }}
                                                <span class="font-sans text-gray-500 dark:text-gray-400 ml-1">{{ line.account?.name }}</span>
                                            </td>
                                            <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ line.label }}</td>
                                            <td class="px-4 py-2 text-right font-mono">{{ line.debit > 0 ? fmt(line.debit) : '' }}</td>
                                            <td class="px-4 py-2 text-right font-mono">{{ line.credit > 0 ? fmt(line.credit) : '' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="entries.last_page > 1" class="px-4 py-3 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            {{ entries.from }}–{{ entries.to }} sur {{ entries.total }}
                        </span>
                        <div class="flex gap-1">
                            <a v-if="entries.prev_page_url" :href="entries.prev_page_url"
                                class="px-3 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                                Précédent
                            </a>
                            <a v-if="entries.next_page_url" :href="entries.next_page_url"
                                class="px-3 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                                Suivant
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Modal nouvelle écriture -->
        <Teleport to="body">
            <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 overflow-y-auto">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-3xl my-4">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Nouvelle écriture manuelle</h3>
                        <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-2xl leading-none">&times;</button>
                    </div>
                    <form @submit.prevent="submitEntry" class="p-6 space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date *</label>
                                <input v-model="entryForm.date" type="date" required
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Référence</label>
                                <input v-model="entryForm.reference" type="text"
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description *</label>
                            <input v-model="entryForm.description" type="text" required
                                class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none" />
                            <p v-if="entryForm.errors.description" class="mt-1 text-xs text-red-500">{{ entryForm.errors.description }}</p>
                        </div>

                        <!-- Lignes -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Lignes d'écriture</label>
                                <button type="button" @click="addLine"
                                    class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">
                                    + Ajouter une ligne
                                </button>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="min-w-full border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                                    <thead class="bg-gray-50 dark:bg-gray-900">
                                        <tr class="text-xs text-gray-500 dark:text-gray-400">
                                            <th class="px-3 py-2 text-left">Compte</th>
                                            <th class="px-3 py-2 text-left">Libellé</th>
                                            <th class="px-3 py-2 text-right w-28">Débit</th>
                                            <th class="px-3 py-2 text-right w-28">Crédit</th>
                                            <th class="px-3 py-2 w-8"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                        <tr v-for="(line, i) in entryForm.lines" :key="i">
                                            <td class="px-3 py-2">
                                                <select v-model="line.account_id"
                                                    class="w-full border border-gray-300 dark:border-gray-600 rounded px-2 py-1 text-sm bg-white dark:bg-gray-700 dark:text-white">
                                                    <option :value="null">— Choisir —</option>
                                                    <option v-for="acc in accounts" :key="acc.id" :value="acc.id">
                                                        {{ acc.code }} - {{ acc.name }}
                                                    </option>
                                                </select>
                                            </td>
                                            <td class="px-3 py-2">
                                                <input v-model="line.label" type="text" placeholder="Libellé"
                                                    class="w-full border border-gray-300 dark:border-gray-600 rounded px-2 py-1 text-sm bg-white dark:bg-gray-700 dark:text-white" />
                                            </td>
                                            <td class="px-3 py-2">
                                                <input v-model="line.debit" type="number" min="0" step="0.01"
                                                    class="w-full border border-gray-300 dark:border-gray-600 rounded px-2 py-1 text-sm bg-white dark:bg-gray-700 dark:text-white text-right" />
                                            </td>
                                            <td class="px-3 py-2">
                                                <input v-model="line.credit" type="number" min="0" step="0.01"
                                                    class="w-full border border-gray-300 dark:border-gray-600 rounded px-2 py-1 text-sm bg-white dark:bg-gray-700 dark:text-white text-right" />
                                            </td>
                                            <td class="px-3 py-2 text-center">
                                                <button type="button" @click="removeLine(i)"
                                                    class="text-red-400 hover:text-red-600 dark:hover:text-red-300 text-lg leading-none"
                                                    :disabled="entryForm.lines.length <= 2">
                                                    &times;
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot class="bg-gray-50 dark:bg-gray-900">
                                        <tr class="font-semibold text-sm">
                                            <td colspan="2" class="px-3 py-2 text-right text-gray-600 dark:text-gray-400">Totaux</td>
                                            <td class="px-3 py-2 text-right font-mono" :class="isBalanced ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400'">
                                                {{ fmt(totalDebit) }}
                                            </td>
                                            <td class="px-3 py-2 text-right font-mono" :class="isBalanced ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400'">
                                                {{ fmt(totalCredit) }}
                                            </td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <p v-if="!isBalanced && (totalDebit > 0 || totalCredit > 0)" class="mt-2 text-xs text-red-500">
                                L'écriture n'est pas équilibrée. Différence : {{ fmt(Math.abs(totalDebit - totalCredit)) }}
                            </p>
                            <p v-if="isBalanced && totalDebit > 0" class="mt-2 text-xs text-emerald-600 dark:text-emerald-400">
                                Écriture équilibrée.
                            </p>
                            <p v-if="entryForm.errors.lines" class="mt-1 text-xs text-red-500">{{ entryForm.errors.lines }}</p>
                        </div>

                        <div class="flex gap-3 pt-2">
                            <button type="button" @click="showModal = false"
                                class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                Annuler
                            </button>
                            <button type="submit" :disabled="entryForm.processing || !isBalanced"
                                class="flex-1 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition disabled:opacity-50">
                                Enregistrer l'écriture
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>

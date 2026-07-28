<script setup>
import { computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    rows:     Array,
    currency: String,
})

const grandDebit  = computed(() => props.rows.reduce((s, r) => s + r.debit,  0))
const grandCredit = computed(() => props.rows.reduce((s, r) => s + r.credit, 0))
const isBalanced  = computed(() => Math.abs(grandDebit.value - grandCredit.value) < 0.01)

function fmt(val) {
    return new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(val ?? 0)
}

function exportCsv() {
    const BOM = '﻿'
    const sep = ';'
    const headers = ['Code', 'Compte', 'Type', 'Total Débits', 'Total Crédits', 'Solde'].join(sep)
    const lines = props.rows.map(r =>
        [r.code, `"${r.name}"`, r.type_label, r.debit.toFixed(2), r.credit.toFixed(2), r.balance.toFixed(2)].join(sep)
    )
    const total = ['TOTAL', '', '', grandDebit.value.toFixed(2), grandCredit.value.toFixed(2), (grandDebit.value - grandCredit.value).toFixed(2)].join(sep)
    const csv = BOM + [headers, ...lines, total].join('\r\n')

    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' })
    const url  = URL.createObjectURL(blob)
    const a    = document.createElement('a')
    a.href = url
    a.download = `balance-verification_${new Date().toISOString().slice(0, 10)}.csv`
    a.click()
    URL.revokeObjectURL(url)
}

const TYPE_COLORS = {
    asset:     'text-blue-600 dark:text-blue-400',
    liability: 'text-red-600 dark:text-red-400',
    equity:    'text-purple-600 dark:text-purple-400',
    revenue:   'text-green-600 dark:text-green-400',
    expense:   'text-orange-600 dark:text-orange-400',
}
</script>

<template>
    <Head title="Balance de vérification" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Balance de vérification
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <!-- Indicateur équilibre + export -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-medium"
                            :class="isBalanced
                                ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900 dark:text-emerald-300'
                                : 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300'"
                        >
                            <span>{{ isBalanced ? '✓' : '!' }}</span>
                            {{ isBalanced ? 'Balance équilibrée' : 'Balance déséquilibrée' }}
                        </span>
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            {{ rows.length }} compte{{ rows.length !== 1 ? 's' : '' }} mouvementés
                        </span>
                    </div>
                    <button
                        @click="exportCsv"
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition shadow-sm"
                    >
                        Exporter CSV
                    </button>
                </div>

                <!-- Tableau -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow overflow-hidden">
                    <div v-if="rows.length === 0" class="p-12 text-center text-gray-400 dark:text-gray-500">
                        Aucun mouvement comptable enregistré.
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-24">Code</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Compte</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-28">Type</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-36">Total Débits</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-36">Total Crédits</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-36">Solde</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <tr
                                    v-for="row in rows"
                                    :key="row.id"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition"
                                >
                                    <td class="px-4 py-3 font-mono text-sm font-semibold text-indigo-700 dark:text-indigo-300">
                                        {{ row.code }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-800 dark:text-gray-200">
                                        {{ row.name }}
                                    </td>
                                    <td class="px-4 py-3 text-xs font-medium" :class="TYPE_COLORS[row.type]">
                                        {{ row.type_label }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-mono text-sm text-gray-800 dark:text-gray-200">
                                        {{ fmt(row.debit) }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-mono text-sm text-gray-800 dark:text-gray-200">
                                        {{ fmt(row.credit) }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-mono text-sm font-semibold"
                                        :class="row.balance >= 0 ? 'text-gray-800 dark:text-gray-200' : 'text-red-600 dark:text-red-400'">
                                        {{ fmt(row.balance) }}
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot class="bg-gray-100 dark:bg-gray-900 border-t-2 border-gray-300 dark:border-gray-600">
                                <tr class="font-bold text-sm">
                                    <td colspan="3" class="px-4 py-3 text-gray-700 dark:text-gray-300 uppercase tracking-wide text-xs">
                                        TOTAL GÉNÉRAL
                                    </td>
                                    <td class="px-4 py-3 text-right font-mono" :class="isBalanced ? 'text-emerald-700 dark:text-emerald-400' : 'text-red-600 dark:text-red-400'">
                                        {{ fmt(grandDebit) }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-mono" :class="isBalanced ? 'text-emerald-700 dark:text-emerald-400' : 'text-red-600 dark:text-red-400'">
                                        {{ fmt(grandCredit) }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-mono" :class="isBalanced ? 'text-emerald-700 dark:text-emerald-400' : 'text-red-600 dark:text-red-400'">
                                        {{ fmt(grandDebit - grandCredit) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

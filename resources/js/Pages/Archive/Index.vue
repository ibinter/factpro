<script setup>
import { ref, computed } from 'vue'
import { router, useForm } from '@inertiajs/vue3'

const props = defineProps({
    archives: Object,
    filters: Object,
    totalCount: Number,
    lastVerified: String,
    years: Array,
})

const verifyResults = ref({})
const verifyLoading = ref({})

const filters = useForm({
    year: props.filters?.year ?? '',
    type: props.filters?.type ?? '',
    verified: props.filters?.verified ?? '',
})

function applyFilters() {
    filters.get(route('archive.index'), { preserveState: true, replace: true })
}

function verifyArchive(archive) {
    verifyLoading.value[archive.id] = true
    router.post(route('archive.verify', archive.id), {}, {
        preserveState: true,
        onSuccess: (page) => {
            // result comes back via flash or we re-fetch
        },
        onFinish: () => {
            verifyLoading.value[archive.id] = false
        },
    })

    // Direct axios call for JSON result
    fetch(route('archive.verify', archive.id), {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content,
            'Accept': 'application/json',
        },
    })
        .then(r => r.json())
        .then(data => {
            verifyResults.value[archive.id] = data
            verifyLoading.value[archive.id] = false
        })
        .catch(() => {
            verifyLoading.value[archive.id] = false
        })
}

function exportZip(year) {
    const form = document.createElement('form')
    form.method = 'POST'
    form.action = route('archive.export-zip')
    const csrfInput = document.createElement('input')
    csrfInput.type = 'hidden'
    csrfInput.name = '_token'
    csrfInput.value = document.querySelector('meta[name=csrf-token]')?.content ?? ''
    const yearInput = document.createElement('input')
    yearInput.type = 'hidden'
    yearInput.name = 'year'
    yearInput.value = year ?? new Date().getFullYear()
    form.appendChild(csrfInput)
    form.appendChild(yearInput)
    document.body.appendChild(form)
    form.submit()
    document.body.removeChild(form)
}

function statusBadge(archive) {
    const result = verifyResults.value[archive.id]
    if (result !== undefined) {
        if (result.valid) return { label: 'Vérifié ✅', cls: 'bg-green-100 text-green-800' }
        if (result.hash_match === false) return { label: 'Corrompu ❌', cls: 'bg-red-100 text-red-800' }
        return { label: 'Signature invalide ⚠️', cls: 'bg-yellow-100 text-yellow-800' }
    }
    if (archive.is_verified) return { label: 'Vérifié ✅', cls: 'bg-green-100 text-green-800' }
    return { label: 'Non vérifié ⚠️', cls: 'bg-yellow-100 text-yellow-800' }
}

function formatBytes(bytes) {
    if (!bytes) return '—'
    if (bytes < 1024) return bytes + ' o'
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' Ko'
    return (bytes / (1024 * 1024)).toFixed(1) + ' Mo'
}

function shortHash(hash) {
    return hash ? hash.substring(0, 8) + '...' : '—'
}
</script>

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-6">
        <!-- En-tête -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    🔐 Archives Légales
                    <span class="ml-2 text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 px-2 py-1 rounded-full">
                        Conservation 10 ans
                    </span>
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    <span class="font-medium">{{ totalCount }}</span> document(s) archivé(s)
                    <template v-if="lastVerified">
                        · Dernière vérification le
                        <span class="font-medium">{{ new Date(lastVerified).toLocaleDateString('fr-FR') }}</span>
                    </template>
                </p>
            </div>
            <button
                @click="exportZip(filters.year || new Date().getFullYear())"
                class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg shadow transition"
            >
                📦 Exporter ZIP {{ filters.year || new Date().getFullYear() }}
            </button>
        </div>

        <!-- Filtres -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-6 flex flex-wrap gap-3">
            <select
                v-model="filters.year"
                @change="applyFilters"
                class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm px-3 py-2 text-gray-700 dark:text-gray-200"
            >
                <option value="">Toutes les années</option>
                <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
            </select>

            <select
                v-model="filters.type"
                @change="applyFilters"
                class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm px-3 py-2 text-gray-700 dark:text-gray-200"
            >
                <option value="">Tous les types</option>
                <option value="invoice">Facture</option>
                <option value="credit_note">Avoir</option>
                <option value="quote">Devis</option>
                <option value="receipt">Reçu</option>
            </select>

            <select
                v-model="filters.verified"
                @change="applyFilters"
                class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm px-3 py-2 text-gray-700 dark:text-gray-200"
            >
                <option value="">Tous les statuts</option>
                <option value="verified">Vérifié ✅</option>
                <option value="unverified">Non vérifié ⚠️</option>
            </select>
        </div>

        <!-- Tableau -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                <thead class="bg-gray-50 dark:bg-gray-750">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Document</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Numéro</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Date archivage</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Hash SHA-256</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Taille</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Statut</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600 dark:text-gray-300">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    <tr v-for="archive in archives.data" :key="archive.id" class="hover:bg-gray-50 dark:hover:bg-gray-750 transition">
                        <td class="px-4 py-3 text-gray-900 dark:text-gray-100 font-medium">
                            {{ archive.document?.type ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300 font-mono">
                            {{ archive.document?.number ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                            {{ new Date(archive.archived_at).toLocaleDateString('fr-FR') }}
                        </td>
                        <td class="px-4 py-3 font-mono text-xs text-gray-500 dark:text-gray-400">
                            {{ shortHash(archive.document_hash) }}
                        </td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                            {{ formatBytes(archive.pdf_size) }}
                        </td>
                        <td class="px-4 py-3">
                            <span
                                class="inline-block px-2 py-0.5 rounded-full text-xs font-medium"
                                :class="statusBadge(archive).cls"
                            >
                                {{ statusBadge(archive).label }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right space-x-2">
                            <button
                                @click="verifyArchive(archive)"
                                :disabled="verifyLoading[archive.id]"
                                class="text-xs bg-blue-50 hover:bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-200 px-3 py-1 rounded-lg transition disabled:opacity-50"
                            >
                                {{ verifyLoading[archive.id] ? '...' : 'Vérifier' }}
                            </button>
                            <a
                                :href="route('archive.download', archive.id)"
                                class="text-xs bg-gray-50 hover:bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200 px-3 py-1 rounded-lg transition"
                            >
                                PDF
                            </a>
                        </td>
                    </tr>
                    <tr v-if="!archives.data?.length">
                        <td colspan="7" class="px-4 py-8 text-center text-gray-400 dark:text-gray-500">
                            Aucune archive pour ces critères.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="archives.last_page > 1" class="mt-4 flex justify-center gap-2">
            <a
                v-for="link in archives.links"
                :key="link.label"
                :href="link.url"
                v-html="link.label"
                class="px-3 py-1 text-sm rounded border"
                :class="link.active ? 'bg-indigo-600 text-white border-indigo-600' : 'border-gray-300 text-gray-600 hover:bg-gray-50'"
            />
        </div>
    </div>
</template>

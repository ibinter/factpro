<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3'

const props = defineProps({
    documents: Object,
    stats: Object,
    filters: Object,
})

const typeFilter = ref(props.filters?.type ?? '')
const yearFilter = ref(props.filters?.year ?? '')

const verifyingAll = ref(false)
const verifyResults = ref({})
const verifyingId = ref(null)

const documentTypes = [
    { value: '', label: 'Tous les types' },
    { value: 'invoice', label: 'Facture' },
    { value: 'contract', label: 'Contrat' },
    { value: 'payslip', label: 'Bulletin de paie' },
    { value: 'ged', label: 'GED' },
]

const currentYear = new Date().getFullYear()
const years = Array.from({ length: 6 }, (_, i) => currentYear - i)

function applyFilters() {
    router.get(route('vault.index'), {
        type: typeFilter.value || undefined,
        year: yearFilter.value || undefined,
    }, { preserveState: true, replace: true })
}

function resetFilters() {
    typeFilter.value = ''
    yearFilter.value = ''
    applyFilters()
}

function verifyDocument(doc) {
    verifyingId.value = doc.id
    fetch(route('vault.verify', doc.id), {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
            'Accept': 'application/json',
        },
    })
        .then(r => r.json())
        .then(data => {
            verifyResults.value[doc.id] = data
        })
        .finally(() => {
            verifyingId.value = null
        })
}

function verifyAll() {
    verifyingAll.value = true
    fetch(route('vault.integrity-report'), {
        headers: { 'Accept': 'application/json' },
    })
        .then(r => r.json())
        .then(data => {
            alert(`Rapport d'intégrité : ${data.valid}/${data.total} valides, ${data.tampered} altérés, ${data.missing} manquants.`)
        })
        .finally(() => {
            verifyingAll.value = false
        })
}

function formatBytes(bytes) {
    if (!bytes) return '-'
    if (bytes < 1024) return bytes + ' o'
    if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' Ko'
    return (bytes / 1048576).toFixed(1) + ' Mo'
}

function integrityBadge(doc) {
    const override = verifyResults.value[doc.id]
    const status = override ? (override.valid ? 'valid' : 'tampered') : doc.integrity_status
    if (status === 'valid') return { text: 'Valide', cls: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' }
    if (status === 'missing') return { text: 'Manquant', cls: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' }
    return { text: 'Altéré', cls: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }
}
</script>

<template>
    <Head title="Coffre-fort numérique" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 leading-tight">
                    Coffre-fort numérique
                </h2>
                <button
                    @click="verifyAll"
                    :disabled="verifyingAll"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition disabled:opacity-50"
                >
                    <span v-if="verifyingAll">Vérification…</span>
                    <span v-else>Vérifier intégrité</span>
                </button>
            </div>
        </template>

        <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Stats header -->
            <div class="grid grid-cols-3 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-5 text-center">
                    <div class="text-3xl font-bold text-gray-800 dark:text-gray-100">{{ stats.total }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Documents archivés</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-5 text-center">
                    <div class="text-3xl font-bold text-green-600">{{ stats.valid }} ✅</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Intégrité valide</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-5 text-center">
                    <div class="text-3xl font-bold" :class="stats.alerts > 0 ? 'text-red-600' : 'text-gray-400'">
                        {{ stats.alerts }} {{ stats.alerts > 0 ? '🔴' : '' }}
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Alertes intégrité</div>
                </div>
            </div>

            <!-- Filtres -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 flex flex-wrap gap-3 items-end">
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Type</label>
                    <select v-model="typeFilter" @change="applyFilters"
                        class="text-sm border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-indigo-500">
                        <option v-for="t in documentTypes" :key="t.value" :value="t.value">{{ t.label }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Année</label>
                    <select v-model="yearFilter" @change="applyFilters"
                        class="text-sm border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-indigo-500">
                        <option value="">Toutes</option>
                        <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
                    </select>
                </div>
                <button @click="resetFilters"
                    class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 underline">
                    Réinitialiser
                </button>
            </div>

            <!-- Tableau -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700 text-xs uppercase text-gray-500 dark:text-gray-400">
                        <tr>
                            <th class="px-4 py-3 text-left">Titre</th>
                            <th class="px-4 py-3 text-left">Type</th>
                            <th class="px-4 py-3 text-left">Archivé le</th>
                            <th class="px-4 py-3 text-left">Conservation</th>
                            <th class="px-4 py-3 text-center">Intégrité</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        <tr v-for="doc in documents.data" :key="doc.id"
                            class="hover:bg-gray-50 dark:hover:bg-gray-750 transition">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100 max-w-xs truncate">
                                {{ doc.title }}
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-300 capitalize">
                                {{ doc.document_type }}
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">
                                {{ doc.archived_at }}
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">
                                <span :class="doc.days_until_expiry < 30 ? 'text-orange-600 font-semibold' : ''">
                                    {{ doc.retention_until }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span :class="['inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium', integrityBadge(doc).cls]">
                                    {{ integrityBadge(doc).text }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a :href="route('vault.download', doc.id)"
                                        class="text-indigo-600 dark:text-indigo-400 hover:underline text-xs">
                                        Télécharger
                                    </a>
                                    <button @click="verifyDocument(doc)"
                                        :disabled="verifyingId === doc.id"
                                        class="text-gray-500 dark:text-gray-400 hover:text-indigo-600 text-xs disabled:opacity-50">
                                        {{ verifyingId === doc.id ? '…' : 'Vérifier' }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!documents.data?.length">
                            <td colspan="6" class="px-4 py-8 text-center text-gray-400 dark:text-gray-500">
                                Aucun document archivé.
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div v-if="documents.last_page > 1" class="px-4 py-3 border-t border-gray-100 dark:border-gray-700 flex gap-2 flex-wrap">
                    <a v-for="link in documents.links" :key="link.label"
                        :href="link.url ?? '#'"
                        v-html="link.label"
                        :class="['px-3 py-1 rounded text-xs', link.active ? 'bg-indigo-600 text-white' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700', !link.url ? 'opacity-40 pointer-events-none' : '']"
                    />
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>

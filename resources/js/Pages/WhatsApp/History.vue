<script setup>
import { ref, computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    messages: Object,
})

const statusConfig = {
    sent:      { label: 'Envoye',   classes: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300' },
    delivered: { label: 'Livre',    classes: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' },
    read:      { label: 'Lu',       classes: 'bg-emerald-100 text-emerald-900 dark:bg-emerald-900/30 dark:text-emerald-200' },
    failed:    { label: 'Echec',    classes: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' },
    queued:    { label: 'En file',  classes: 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' },
}

function getStatus(status) {
    return statusConfig[status] ?? { label: status, classes: 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' }
}

function truncate(text, len = 80) {
    if (!text) return ''
    return text.length > len ? text.substring(0, len) + '...' : text
}

function formatDate(dateStr) {
    if (!dateStr) return '-'
    return new Date(dateStr).toLocaleString('fr-FR', {
        day: '2-digit', month: '2-digit', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
    })
}

function goToPage(url) {
    if (url) router.visit(url)
}
</script>

<template>
    <Head title="Historique WhatsApp" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Historique des messages WhatsApp
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">A (telephone)</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Client</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Type</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Extrait message</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Statut</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-if="messages.data.length === 0">
                                    <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                        Aucun message envoye pour l'instant.
                                    </td>
                                </tr>
                                <tr
                                    v-for="msg in messages.data"
                                    :key="msg.id"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                                >
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100 font-mono">
                                        {{ msg.to_phone }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                        {{ msg.customer ? msg.customer.name : '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300">
                                            {{ msg.message_type === 'template' ? 'Template' : 'Texte' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400 max-w-xs">
                                        <span :title="msg.body">{{ truncate(msg.body) }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <span
                                            :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', getStatus(msg.status).classes]"
                                        >
                                            {{ getStatus(msg.status).label }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400 whitespace-nowrap">
                                        {{ formatDate(msg.sent_at ?? msg.created_at) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="messages.last_page > 1" class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Page {{ messages.current_page }} sur {{ messages.last_page }}
                            ({{ messages.total }} messages)
                        </p>
                        <div class="flex gap-2">
                            <button
                                @click="goToPage(messages.prev_page_url)"
                                :disabled="!messages.prev_page_url"
                                class="px-3 py-1 text-sm rounded border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 disabled:opacity-40 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                            >
                                Precedent
                            </button>
                            <button
                                @click="goToPage(messages.next_page_url)"
                                :disabled="!messages.next_page_url"
                                class="px-3 py-1 text-sm rounded border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 disabled:opacity-40 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                            >
                                Suivant
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    properties: Object,
    filters: Object,
})

const statusFilter = ref(props.filters?.status || '')
const cityFilter = ref(props.filters?.city || '')

function applyFilters() {
    router.get(route('real-estate.properties.index'), {
        status: statusFilter.value,
        city: cityFilter.value,
    }, { preserveState: true })
}

const typeIcons = {
    apartment:  '🏠',
    house:      '🏡',
    villa:      '🏖️',
    commercial: '🏪',
    office:     '🏢',
    warehouse:  '🏗️',
    land:       '🌱',
    parking:    '🅿️',
}

const statusColors = {
    available:   'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
    rented:      'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
    maintenance: 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
    for_sale:    'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
}

const statusLabels = {
    available:   'Disponible',
    rented:      'Loué',
    maintenance: 'Maintenance',
    for_sale:    'À vendre',
}

function formatCurrency(amount, currency = 'XOF') {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency }).format(amount)
}
</script>

<template>
    <Head title="Biens immobiliers" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Biens immobiliers
                </h2>
                <Link
                    :href="route('real-estate.properties.create')"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition"
                >
                    + Nouveau bien
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <!-- Filtres -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mb-6 flex flex-wrap gap-4 items-end">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Statut</label>
                        <select v-model="statusFilter" @change="applyFilters"
                            class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm">
                            <option value="">Tous</option>
                            <option value="available">Disponible</option>
                            <option value="rented">Loué</option>
                            <option value="maintenance">Maintenance</option>
                            <option value="for_sale">À vendre</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Ville</label>
                        <input v-model="cityFilter" @keyup.enter="applyFilters" type="text" placeholder="Rechercher..."
                            class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm" />
                    </div>
                    <button @click="applyFilters"
                        class="px-3 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded text-sm hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                        Filtrer
                    </button>
                </div>

                <!-- Grille de cartes -->
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                    <Link
                        v-for="property in properties.data"
                        :key="property.id"
                        :href="route('real-estate.properties.show', property.id)"
                        class="block bg-white dark:bg-gray-800 rounded-xl shadow hover:shadow-lg transition overflow-hidden"
                    >
                        <!-- En-tête colorée -->
                        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 p-4 flex items-center justify-between">
                            <span class="text-3xl">{{ typeIcons[property.type] || '🏠' }}</span>
                            <span :class="['px-2 py-1 rounded-full text-xs font-semibold', statusColors[property.status]]">
                                {{ statusLabels[property.status] }}
                            </span>
                        </div>

                        <!-- Corps -->
                        <div class="p-4">
                            <p v-if="property.reference" class="text-xs text-gray-400 dark:text-gray-500 mb-1">
                                Réf. {{ property.reference }}
                            </p>
                            <h3 class="font-bold text-gray-900 dark:text-white text-lg mb-1 truncate">
                                {{ property.name }}
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 truncate mb-3">
                                {{ property.address }}<span v-if="property.city">, {{ property.city }}</span>
                            </p>

                            <div class="flex items-center justify-between text-sm">
                                <span v-if="property.area_sqm" class="text-gray-600 dark:text-gray-300">
                                    {{ property.area_sqm }} m²
                                </span>
                                <span v-else class="text-gray-400">—</span>
                                <span class="font-semibold text-indigo-600 dark:text-indigo-400">
                                    {{ formatCurrency(property.monthly_rent, property.currency) }}/mois
                                </span>
                            </div>

                            <!-- Locataire actuel -->
                            <div v-if="property.active_lease?.tenant" class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-700 flex items-center gap-2">
                                <div class="w-6 h-6 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center text-xs text-blue-700 dark:text-blue-300 font-bold">
                                    {{ property.active_lease.tenant.name.charAt(0) }}
                                </div>
                                <span class="text-sm text-gray-700 dark:text-gray-300 truncate">
                                    {{ property.active_lease.tenant.name }}
                                </span>
                            </div>
                        </div>
                    </Link>
                </div>

                <!-- Aucun résultat -->
                <div v-if="properties.data.length === 0" class="text-center py-16 text-gray-500 dark:text-gray-400">
                    <p class="text-5xl mb-4">🏠</p>
                    <p class="text-lg font-medium">Aucun bien immobilier</p>
                    <p class="text-sm">Commencez par ajouter un bien.</p>
                </div>

                <!-- Pagination -->
                <div v-if="properties.last_page > 1" class="mt-6 flex justify-center gap-2">
                    <Link
                        v-for="link in properties.links"
                        :key="link.label"
                        :href="link.url || '#'"
                        :class="['px-3 py-1 rounded text-sm', link.active ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700', !link.url && 'opacity-50 cursor-not-allowed']"
                        v-html="link.label"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Head, Link, useForm, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    property: Object,
    rentPayments: Array,
})

const statusColors = {
    available:   'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
    rented:      'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
    maintenance: 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
    for_sale:    'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
}

const paymentStatusColors = {
    pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
    paid:    'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
    late:    'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
    partial: 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
}

const paymentStatusLabels = {
    pending: 'En attente',
    paid:    'Payé',
    late:    'En retard',
    partial: 'Partiel',
}

const showRentModal = ref(false)
const rentForm = useForm({ period_month: '' })

function generateRent() {
    rentForm.post(route('real-estate.leases.generate-rent', props.property.active_lease.id), {
        onSuccess: () => { showRentModal.value = false }
    })
}

function formatCurrency(amount, currency = 'XOF') {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency }).format(amount)
}

function formatDate(date) {
    if (!date) return '—'
    return new Date(date).toLocaleDateString('fr-FR')
}

function formatMonth(date) {
    if (!date) return '—'
    return new Date(date).toLocaleDateString('fr-FR', { month: 'long', year: 'numeric' })
}

const amenityLabels = {
    wifi: 'Wi-Fi', parking: 'Parking', piscine: 'Piscine',
    gardien: 'Gardien', climatisation: 'Climatisation',
    ascenseur: 'Ascenseur', balcon: 'Balcon', jardin: 'Jardin',
}

const typeIcons = {
    apartment: '🏠', house: '🏡', villa: '🏖️', commercial: '🏪',
    office: '🏢', warehouse: '🏗️', land: '🌱', parking: '🅿️',
}
</script>

<template>
    <Head :title="property.name" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">{{ typeIcons[property.type] || '🏠' }}</span>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">{{ property.name }}</h2>
                            <span v-if="property.reference" class="text-sm text-gray-500 dark:text-gray-400">
                                (Réf. {{ property.reference }})
                            </span>
                            <span :class="['px-2 py-0.5 rounded-full text-xs font-semibold', statusColors[property.status]]">
                                {{ property.status_label }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ property.type_label }}</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <Link :href="route('real-estate.properties.edit', property.id)"
                        class="px-3 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-lg text-sm hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                        Modifier
                    </Link>
                    <Link :href="route('real-estate.leases.create')"
                        class="px-3 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 transition">
                        + Nouveau bail
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <!-- Infos + Commodités -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Infos principales -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
                        <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Informations</h3>
                        <dl class="space-y-3">
                            <div class="flex justify-between text-sm">
                                <dt class="text-gray-500 dark:text-gray-400">Adresse</dt>
                                <dd class="text-gray-900 dark:text-white text-right">{{ property.address }}<span v-if="property.city">, {{ property.city }}</span><span v-if="property.country">, {{ property.country }}</span></dd>
                            </div>
                            <div v-if="property.area_sqm" class="flex justify-between text-sm">
                                <dt class="text-gray-500 dark:text-gray-400">Superficie</dt>
                                <dd class="text-gray-900 dark:text-white">{{ property.area_sqm }} m²</dd>
                            </div>
                            <div v-if="property.bedrooms !== null" class="flex justify-between text-sm">
                                <dt class="text-gray-500 dark:text-gray-400">Chambres / SDB</dt>
                                <dd class="text-gray-900 dark:text-white">{{ property.bedrooms }} ch. / {{ property.bathrooms ?? 0 }} SDB</dd>
                            </div>
                            <div v-if="property.floor !== null" class="flex justify-between text-sm">
                                <dt class="text-gray-500 dark:text-gray-400">Étage</dt>
                                <dd class="text-gray-900 dark:text-white">{{ property.floor }} / {{ property.total_floors ?? '?' }}</dd>
                            </div>
                            <div class="flex justify-between text-sm">
                                <dt class="text-gray-500 dark:text-gray-400">Loyer mensuel</dt>
                                <dd class="font-semibold text-indigo-600 dark:text-indigo-400">{{ formatCurrency(property.monthly_rent, property.currency) }}</dd>
                            </div>
                            <div v-if="property.tax_rate > 0" class="flex justify-between text-sm">
                                <dt class="text-gray-500 dark:text-gray-400">Taux de taxe</dt>
                                <dd class="text-gray-900 dark:text-white">{{ property.tax_rate }}%</dd>
                            </div>
                            <div v-if="property.purchase_date" class="flex justify-between text-sm">
                                <dt class="text-gray-500 dark:text-gray-400">Date d'achat</dt>
                                <dd class="text-gray-900 dark:text-white">{{ formatDate(property.purchase_date) }}</dd>
                            </div>
                            <div v-if="property.purchase_price" class="flex justify-between text-sm">
                                <dt class="text-gray-500 dark:text-gray-400">Prix d'achat</dt>
                                <dd class="text-gray-900 dark:text-white">{{ formatCurrency(property.purchase_price, property.currency) }}</dd>
                            </div>
                        </dl>
                        <div v-if="property.description" class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                            <p class="text-sm text-gray-600 dark:text-gray-300">{{ property.description }}</p>
                        </div>
                    </div>

                    <!-- Commodités -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
                        <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Commodités</h3>
                        <div v-if="property.amenities && property.amenities.length" class="flex flex-wrap gap-2">
                            <span
                                v-for="a in property.amenities" :key="a"
                                class="px-3 py-1 bg-indigo-50 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300 rounded-full text-sm font-medium"
                            >
                                {{ amenityLabels[a] || a }}
                            </span>
                        </div>
                        <p v-else class="text-sm text-gray-400 dark:text-gray-500">Aucune commodité renseignée.</p>
                    </div>
                </div>

                <!-- Bail actif -->
                <div v-if="property.active_lease" class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-gray-900 dark:text-white">Bail actif</h3>
                        <button @click="showRentModal = true"
                            class="px-4 py-2 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 transition">
                            Générer quittance
                        </button>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Locataire</p>
                            <p class="font-medium text-gray-900 dark:text-white">{{ property.active_lease.tenant?.name }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Début</p>
                            <p class="font-medium text-gray-900 dark:text-white">{{ formatDate(property.active_lease.start_date) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Fin</p>
                            <p class="font-medium text-gray-900 dark:text-white">{{ property.active_lease.is_open_ended ? 'Indéterminé' : formatDate(property.active_lease.end_date) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Loyer / Jour de paiement</p>
                            <p class="font-medium text-gray-900 dark:text-white">
                                {{ formatCurrency(property.active_lease.monthly_rent, property.currency) }} — le {{ property.active_lease.payment_day }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Tableau des paiements -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                        <h3 class="font-semibold text-gray-900 dark:text-white">Historique des paiements (12 derniers mois)</h3>
                    </div>
                    <div v-if="rentPayments.length" class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Période</th>
                                    <th class="text-right px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Montant</th>
                                    <th class="text-center px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Statut</th>
                                    <th class="text-center px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Quittance</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <tr v-for="payment in rentPayments" :key="payment.id"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                    <td class="px-6 py-3 text-gray-900 dark:text-white capitalize">{{ formatMonth(payment.period_month) }}</td>
                                    <td class="px-6 py-3 text-right font-medium text-gray-900 dark:text-white">{{ formatCurrency(payment.amount, property.currency) }}</td>
                                    <td class="px-6 py-3 text-center">
                                        <span :class="['px-2 py-1 rounded-full text-xs font-medium', paymentStatusColors[payment.status]]">
                                            {{ paymentStatusLabels[payment.status] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 text-center">
                                        <Link v-if="payment.document_id"
                                            :href="route('documents.show', payment.document_id)"
                                            class="text-indigo-600 dark:text-indigo-400 hover:underline text-xs">
                                            Voir facture
                                        </Link>
                                        <span v-else class="text-gray-400 text-xs">—</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="text-center py-10 text-gray-400 dark:text-gray-500">
                        Aucun paiement enregistré.
                    </div>
                </div>

            </div>
        </div>

        <!-- Modal génération quittance -->
        <div v-if="showRentModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl p-6 w-full max-w-sm">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Générer une quittance</h3>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mois concerné</label>
                    <input type="month" v-model="rentForm.period_month"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                    <p v-if="rentForm.errors.period_month" class="mt-1 text-xs text-red-600">{{ rentForm.errors.period_month }}</p>
                </div>
                <div class="flex gap-3 justify-end">
                    <button @click="showRentModal = false"
                        class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                        Annuler
                    </button>
                    <button @click="generateRent" :disabled="rentForm.processing"
                        class="px-4 py-2 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 disabled:opacity-50 transition">
                        Générer
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

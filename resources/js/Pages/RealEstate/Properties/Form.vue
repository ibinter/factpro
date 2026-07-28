<script setup>
import { Head, router } from '@inertiajs/vue3'
import { useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    property: Object,
})

const isEditing = !!props.property

const form = useForm({
    type:           props.property?.type || 'apartment',
    name:           props.property?.name || '',
    reference:      props.property?.reference || '',
    address:        props.property?.address || '',
    city:           props.property?.city || '',
    country:        props.property?.country || '',
    area_sqm:       props.property?.area_sqm || '',
    bedrooms:       props.property?.bedrooms ?? '',
    bathrooms:      props.property?.bathrooms ?? '',
    floor:          props.property?.floor ?? '',
    total_floors:   props.property?.total_floors ?? '',
    monthly_rent:   props.property?.monthly_rent || '',
    currency:       props.property?.currency || 'XOF',
    tax_rate:       props.property?.tax_rate || 0,
    purchase_price: props.property?.purchase_price || '',
    purchase_date:  props.property?.purchase_date || '',
    status:         props.property?.status || 'available',
    description:    props.property?.description || '',
    amenities:      props.property?.amenities || [],
})

const allAmenities = [
    { value: 'wifi', label: 'Wi-Fi' },
    { value: 'parking', label: 'Parking' },
    { value: 'piscine', label: 'Piscine' },
    { value: 'gardien', label: 'Gardien' },
    { value: 'climatisation', label: 'Climatisation' },
    { value: 'ascenseur', label: 'Ascenseur' },
    { value: 'balcon', label: 'Balcon' },
    { value: 'jardin', label: 'Jardin' },
]

function submit() {
    if (isEditing) {
        form.put(route('real-estate.properties.update', props.property.id))
    } else {
        form.post(route('real-estate.properties.store'))
    }
}
</script>

<template>
    <Head :title="isEditing ? 'Modifier le bien' : 'Nouveau bien immobilier'" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ isEditing ? 'Modifier le bien' : 'Nouveau bien immobilier' }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="space-y-6">

                    <!-- Type et statut -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
                        <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Identification</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type *</label>
                                <select v-model="form.type" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                    <option value="apartment">Appartement</option>
                                    <option value="house">Maison</option>
                                    <option value="villa">Villa</option>
                                    <option value="commercial">Local commercial</option>
                                    <option value="office">Bureau</option>
                                    <option value="warehouse">Entrepôt</option>
                                    <option value="land">Terrain</option>
                                    <option value="parking">Parking</option>
                                </select>
                                <p v-if="form.errors.type" class="mt-1 text-xs text-red-600">{{ form.errors.type }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nom *</label>
                                <input v-model="form.name" type="text" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                                <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Référence</label>
                                <input v-model="form.reference" type="text" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                            </div>
                        </div>
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Statut *</label>
                            <select v-model="form.status" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                <option value="available">Disponible</option>
                                <option value="rented">Loué</option>
                                <option value="maintenance">En maintenance</option>
                                <option value="for_sale">À vendre</option>
                            </select>
                        </div>
                    </div>

                    <!-- Localisation -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
                        <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Localisation</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Adresse *</label>
                                <input v-model="form.address" type="text" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                                <p v-if="form.errors.address" class="mt-1 text-xs text-red-600">{{ form.errors.address }}</p>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ville</label>
                                    <input v-model="form.city" type="text" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pays</label>
                                    <input v-model="form.country" type="text" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Caractéristiques -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
                        <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Caractéristiques</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Superficie (m²)</label>
                                <input v-model="form.area_sqm" type="number" step="0.01" min="0"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Chambres</label>
                                <input v-model="form.bedrooms" type="number" min="0"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Salles de bain</label>
                                <input v-model="form.bathrooms" type="number" min="0"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Étage</label>
                                <input v-model="form.floor" type="number"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                            </div>
                        </div>
                    </div>

                    <!-- Financier -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
                        <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Informations financières</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Loyer mensuel *</label>
                                <input v-model="form.monthly_rent" type="number" step="0.01" min="0"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                                <p v-if="form.errors.monthly_rent" class="mt-1 text-xs text-red-600">{{ form.errors.monthly_rent }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Devise *</label>
                                <input v-model="form.currency" type="text" maxlength="3"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Taux de taxe (%)</label>
                                <input v-model="form.tax_rate" type="number" step="0.01" min="0" max="100"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Prix d'achat</label>
                                <input v-model="form.purchase_price" type="number" step="0.01" min="0"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date d'achat</label>
                                <input v-model="form.purchase_date" type="date"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                            </div>
                        </div>
                    </div>

                    <!-- Description + Commodités -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
                        <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Description & Commodités</h3>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                            <textarea v-model="form.description" rows="3"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Commodités</label>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                <label v-for="a in allAmenities" :key="a.value"
                                    class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" :value="a.value" v-model="form.amenities"
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ a.label }}</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-4">
                        <a :href="route('real-estate.properties.index')"
                            class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                            Annuler
                        </a>
                        <button type="submit" :disabled="form.processing"
                            class="px-6 py-2 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 disabled:opacity-50 transition">
                            {{ isEditing ? 'Enregistrer' : 'Créer le bien' }}
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, watch } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    availableProperties: Array,
    customers: Array,
})

const form = useForm({
    property_id:         '',
    customer_id:         '',
    start_date:          '',
    end_date:            '',
    is_open_ended:       false,
    monthly_rent:        '',
    deposit_amount:      '',
    payment_day:         1,
    renewal_notice_days: 90,
    notes:               '',
})

// Pré-remplir le loyer quand on sélectionne un bien
watch(() => form.property_id, (id) => {
    const property = props.availableProperties.find(p => p.id == id)
    if (property) {
        form.monthly_rent = property.monthly_rent
    }
})

function submit() {
    form.post(route('real-estate.leases.store'))
}
</script>

<template>
    <Head title="Nouveau bail" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Nouveau bail locatif</h2>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="space-y-6">

                    <!-- Bien et locataire -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
                        <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Parties</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Bien immobilier * <span class="text-xs text-gray-400">(disponibles uniquement)</span></label>
                                <select v-model="form.property_id" required
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                    <option value="">-- Sélectionner un bien --</option>
                                    <option v-for="p in availableProperties" :key="p.id" :value="p.id">
                                        {{ p.name }}<span v-if="p.reference"> ({{ p.reference }})</span>
                                    </option>
                                </select>
                                <p v-if="form.errors.property_id" class="mt-1 text-xs text-red-600">{{ form.errors.property_id }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Locataire *</label>
                                <select v-model="form.customer_id" required
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                    <option value="">-- Sélectionner un locataire --</option>
                                    <option v-for="c in customers" :key="c.id" :value="c.id">
                                        {{ c.name }}<span v-if="c.email"> — {{ c.email }}</span>
                                    </option>
                                </select>
                                <p v-if="form.errors.customer_id" class="mt-1 text-xs text-red-600">{{ form.errors.customer_id }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Durée -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
                        <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Durée</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date de début *</label>
                                <input v-model="form.start_date" type="date" required
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                                <p v-if="form.errors.start_date" class="mt-1 text-xs text-red-600">{{ form.errors.start_date }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date de fin</label>
                                <input v-model="form.end_date" type="date" :disabled="form.is_open_ended"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white disabled:opacity-50" />
                            </div>
                        </div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" v-model="form.is_open_ended"
                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                            <span class="text-sm text-gray-700 dark:text-gray-300">Bail à durée indéterminée</span>
                        </label>
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Préavis de renouvellement (jours)</label>
                            <input v-model="form.renewal_notice_days" type="number" min="0"
                                class="w-32 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                        </div>
                    </div>

                    <!-- Conditions financières -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
                        <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Conditions financières</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Loyer mensuel *</label>
                                <input v-model="form.monthly_rent" type="number" step="0.01" min="0" required
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                                <p v-if="form.errors.monthly_rent" class="mt-1 text-xs text-red-600">{{ form.errors.monthly_rent }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Dépôt de garantie</label>
                                <input v-model="form.deposit_amount" type="number" step="0.01" min="0"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Jour de paiement *</label>
                                <input v-model="form.payment_day" type="number" min="1" max="28" required
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                                <p class="text-xs text-gray-400 mt-1">Entre 1 et 28</p>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
                        <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Notes</h3>
                        <textarea v-model="form.notes" rows="3" placeholder="Conditions particulières, remarques..."
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-4">
                        <a :href="route('real-estate.leases.index')"
                            class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                            Annuler
                        </a>
                        <button type="submit" :disabled="form.processing"
                            class="px-6 py-2 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 disabled:opacity-50 transition">
                            Créer le bail
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

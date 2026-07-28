<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    lease: Object,
    customers: Array,
})

const form = useForm({
    customer_id:         props.lease.customer_id,
    start_date:          props.lease.start_date?.split('T')[0] || props.lease.start_date || '',
    end_date:            props.lease.end_date?.split('T')[0] || props.lease.end_date || '',
    is_open_ended:       props.lease.is_open_ended,
    monthly_rent:        props.lease.monthly_rent,
    deposit_amount:      props.lease.deposit_amount,
    payment_day:         props.lease.payment_day,
    renewal_notice_days: props.lease.renewal_notice_days,
    notes:               props.lease.notes || '',
})

function submit() {
    form.put(route('real-estate.leases.update', props.lease.id))
}
</script>

<template>
    <Head title="Modifier le bail" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Modifier le bail — {{ lease.property?.name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="space-y-6">

                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
                        <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Locataire</h3>
                        <select v-model="form.customer_id"
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
                        <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Durée</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date de début *</label>
                                <input v-model="form.start_date" type="date" required
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
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
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
                        <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Conditions financières</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Loyer mensuel *</label>
                                <input v-model="form.monthly_rent" type="number" step="0.01" min="0" required
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
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
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
                        <textarea v-model="form.notes" rows="3"
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-4">
                        <a :href="route('real-estate.leases.show', lease.id)"
                            class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900">Annuler</a>
                        <button type="submit" :disabled="form.processing"
                            class="px-6 py-2 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 disabled:opacity-50 transition">
                            Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    customers: Array,
    currency:  String,
})

const form = useForm({
    customer_id:           '',
    name:                  '',
    description:           '',
    frequency:             'monthly',
    amount:                '',
    currency:              props.currency || 'XOF',
    tax_rate:              '0',
    start_date:            new Date().toISOString().slice(0, 10),
    end_date:              '',
    payment_terms:         30,
    notes:                 '',
    auto_generate_invoice: true,
})

const frequencies = [
    { value: 'weekly',    label: 'Hebdomadaire' },
    { value: 'monthly',   label: 'Mensuel' },
    { value: 'quarterly', label: 'Trimestriel' },
    { value: 'biannual',  label: 'Semestriel' },
    { value: 'annual',    label: 'Annuel' },
]

const currencies = ['XOF', 'EUR', 'USD', 'GHS', 'NGN', 'MAD', 'TND', 'DZD', 'EGP', 'KES']

function submit() {
    form.post(route('subscriptions.store'))
}
</script>

<template>
    <Head title="Nouvel abonnement" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Nouvel abonnement client
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 shadow rounded-xl p-8">
                    <form @submit.prevent="submit" class="space-y-6">

                        <!-- Client -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Client <span class="text-red-500">*</span>
                            </label>
                            <select v-model="form.customer_id"
                                class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">-- Choisir un client --</option>
                                <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }}</option>
                            </select>
                            <p v-if="form.errors.customer_id" class="mt-1 text-sm text-red-600">{{ form.errors.customer_id }}</p>
                        </div>

                        <!-- Nom -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Nom de l'abonnement <span class="text-red-500">*</span>
                            </label>
                            <input v-model="form.name" type="text" placeholder="Pack mensuel Pro"
                                class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-indigo-500 focus:border-indigo-500" />
                            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                            <textarea v-model="form.description" rows="3"
                                class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                        </div>

                        <!-- Fréquence + Montant + Devise -->
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Fréquence <span class="text-red-500">*</span>
                                </label>
                                <select v-model="form.frequency"
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option v-for="f in frequencies" :key="f.value" :value="f.value">{{ f.label }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Montant <span class="text-red-500">*</span>
                                </label>
                                <input v-model="form.amount" type="number" min="0" step="0.01" placeholder="0"
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-indigo-500 focus:border-indigo-500" />
                                <p v-if="form.errors.amount" class="mt-1 text-sm text-red-600">{{ form.errors.amount }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Devise</label>
                                <select v-model="form.currency"
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option v-for="c in currencies" :key="c" :value="c">{{ c }}</option>
                                </select>
                            </div>
                        </div>

                        <!-- TVA -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Taux de TVA (%)</label>
                                <input v-model="form.tax_rate" type="number" min="0" max="100" step="0.01"
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-indigo-500 focus:border-indigo-500" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Délai de paiement (jours)</label>
                                <input v-model="form.payment_terms" type="number" min="0"
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-indigo-500 focus:border-indigo-500" />
                            </div>
                        </div>

                        <!-- Dates -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Date de début <span class="text-red-500">*</span>
                                </label>
                                <input v-model="form.start_date" type="date"
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-indigo-500 focus:border-indigo-500" />
                                <p v-if="form.errors.start_date" class="mt-1 text-sm text-red-600">{{ form.errors.start_date }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date de fin (optionnel)</label>
                                <input v-model="form.end_date" type="date"
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-indigo-500 focus:border-indigo-500" />
                            </div>
                        </div>

                        <!-- Notes -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes internes</label>
                            <textarea v-model="form.notes" rows="2"
                                class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                        </div>

                        <!-- Auto-génération -->
                        <div class="flex items-center gap-3">
                            <button type="button" @click="form.auto_generate_invoice = !form.auto_generate_invoice"
                                :class="[
                                    'relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none',
                                    form.auto_generate_invoice ? 'bg-indigo-600' : 'bg-gray-300 dark:bg-gray-600'
                                ]">
                                <span :class="[
                                    'inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform',
                                    form.auto_generate_invoice ? 'translate-x-6' : 'translate-x-1'
                                ]"></span>
                            </button>
                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                Générer automatiquement les factures
                            </span>
                        </div>

                        <!-- Actions -->
                        <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <a :href="route('subscriptions.index')"
                               class="px-5 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                Annuler
                            </a>
                            <button type="submit" :disabled="form.processing"
                                class="px-5 py-2 text-sm rounded-lg bg-indigo-600 text-white font-medium hover:bg-indigo-700 disabled:opacity-50 transition">
                                {{ form.processing ? 'Enregistrement...' : 'Créer l\'abonnement' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

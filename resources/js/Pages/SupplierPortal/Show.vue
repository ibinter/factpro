<script setup>
import { useForm } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    record: Object,
    document: Object,
    company: Object,
    token: String,
});

const form = useForm({
    quoted_price: '',
    delivery_days: '',
    supplier_notes: '',
});

const submit = () => {
    form.post(route('supplier.portal.respond', props.token));
};

const fmt = (n, cur = 'XOF') => new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 0 }).format(n) + ' ' + cur;
</script>

<template>
    <Head title="Demande de prix" />
    <div class="min-h-screen bg-gray-50 py-10 px-4">
        <div class="max-w-3xl mx-auto space-y-6">

            <!-- Header -->
            <div class="bg-white rounded-xl shadow p-6">
                <h1 class="text-2xl font-bold text-gray-900">Demande de prix</h1>
                <p class="text-gray-500 mt-1">De la part de <strong>{{ company.name }}</strong></p>
                <p class="text-sm text-gray-400 mt-1">Bonjour <strong>{{ record.supplier_name }}</strong>, veuillez consulter les articles ci-dessous et soumettre votre offre.</p>
            </div>

            <!-- Document lines -->
            <div class="bg-white rounded-xl shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="font-semibold text-gray-800">Articles demandés</h2>
                    <p class="text-xs text-gray-400">Réf. {{ document.number }}</p>
                </div>
                <table class="min-w-full text-sm divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-gray-500 font-medium">Désignation</th>
                            <th class="px-4 py-3 text-right text-gray-500 font-medium">Qté</th>
                            <th class="px-4 py-3 text-right text-gray-500 font-medium">Unité</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-for="line in document.lines" :key="line.id">
                            <td class="px-4 py-3 text-gray-900">{{ line.description }}</td>
                            <td class="px-4 py-3 text-right text-gray-700">{{ line.quantity }}</td>
                            <td class="px-4 py-3 text-right text-gray-500">{{ line.unit ?? '—' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Form -->
            <div class="bg-white rounded-xl shadow p-6">
                <h2 class="font-semibold text-gray-800 mb-4">Votre offre</h2>
                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Prix total proposé (HT) *</label>
                        <input v-model="form.quoted_price" type="number" min="0" step="1" required
                               class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        <p v-if="form.errors.quoted_price" class="text-red-500 text-xs mt-1">{{ form.errors.quoted_price }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Délai de livraison (jours) *</label>
                        <input v-model="form.delivery_days" type="number" min="1" required
                               class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        <p v-if="form.errors.delivery_days" class="text-red-500 text-xs mt-1">{{ form.errors.delivery_days }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Remarques / conditions</label>
                        <textarea v-model="form.supplier_notes" rows="4"
                                  class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                    <div class="pt-2">
                        <button type="submit" :disabled="form.processing"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition disabled:opacity-50">
                            {{ form.processing ? 'Envoi...' : 'Soumettre mon offre' }}
                        </button>
                    </div>
                </form>
            </div>

            <p class="text-center text-xs text-gray-400">Ce lien expire le {{ record.expires_at ? new Date(record.expires_at).toLocaleDateString('fr-FR') : '—' }}.</p>
        </div>
    </div>
</template>

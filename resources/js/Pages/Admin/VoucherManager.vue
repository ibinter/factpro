<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    batches: Array,
    plans: Array,
});

const fmt = (n) => new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(n ?? 0);

// Modal génération de lot
const showModal = ref(false);
const generateForm = useForm({
    quantity: 10,
    plan_id: '',
    duration_months: 1,
    currency: 'XOF',
    face_value: 0,
    reseller_price: 0,
    reseller_name: '',
    expires_at: '',
});

const submitGenerate = () => {
    generateForm.post(route('admin.vouchers.generate'), {
        onSuccess: () => { showModal.value = false; generateForm.reset(); },
    });
};

const statusBadge = (status) => {
    const map = {
        available: 'bg-green-100 text-green-700',
        used: 'bg-blue-100 text-blue-700',
        expired: 'bg-red-100 text-red-700',
        cancelled: 'bg-gray-100 text-gray-600',
        reserved: 'bg-yellow-100 text-yellow-700',
    };
    return map[status] ?? 'bg-gray-100 text-gray-600';
};

const statusLabel = (status) => {
    const map = { available: '🟢 Disponible', used: '✅ Utilisé', expired: '🔴 Expiré', cancelled: '⚫ Annulé', reserved: '🟡 Réservé' };
    return map[status] ?? status;
};
</script>

<template>
    <Head title="Vouchers — Admin" />

    <div class="min-h-screen bg-gray-100">
        <!-- En-tête -->
        <div class="bg-white shadow">
            <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8 flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">🎫 Codes prépayés revendeurs</h1>
                    <p class="text-sm text-gray-500">Générez et gérez les lots de vouchers</p>
                </div>
                <button
                    @click="showModal = true"
                    class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700"
                >
                    + Générer un lot
                </button>
            </div>
        </div>

        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash success -->
            <div v-if="$page.props.flash?.success" class="rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
                {{ $page.props.flash.success }}
            </div>

            <!-- Liste des lots -->
            <div class="overflow-hidden rounded-xl bg-white shadow">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700">Référence lot</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700">Date</th>
                            <th class="px-6 py-3 text-center font-semibold text-gray-700">Total</th>
                            <th class="px-6 py-3 text-center font-semibold text-gray-700">Disponibles</th>
                            <th class="px-6 py-3 text-center font-semibold text-gray-700">Utilisés</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700">Valeur</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700">Revendeur</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700">Expiration</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-if="!batches?.length">
                            <td colspan="9" class="px-6 py-8 text-center text-gray-400">Aucun lot généré.</td>
                        </tr>
                        <tr v-for="batch in batches" :key="batch.batch_ref" class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-mono font-semibold text-brand-700">{{ batch.batch_ref }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ new Date(batch.created_at).toLocaleDateString('fr-FR') }}</td>
                            <td class="px-6 py-4 text-center font-semibold">{{ batch.total }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="rounded-full bg-green-100 px-2 py-0.5 text-green-700 text-xs font-semibold">{{ batch.available_count }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="rounded-full bg-blue-100 px-2 py-0.5 text-blue-700 text-xs font-semibold">{{ batch.used_count }}</span>
                            </td>
                            <td class="px-6 py-4">{{ fmt(batch.face_value) }} {{ batch.currency }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ batch.reseller_name ?? '—' }}</td>
                            <td class="px-6 py-4 text-gray-600">
                                {{ batch.expires_at ? new Date(batch.expires_at).toLocaleDateString('fr-FR') : 'Sans expiration' }}
                            </td>
                            <td class="px-6 py-4 space-x-2 text-right whitespace-nowrap">
                                <a :href="route('admin.vouchers.batch', batch.batch_ref)" class="text-brand-600 hover:underline text-xs font-semibold">Voir</a>
                                <a :href="route('admin.vouchers.export', batch.batch_ref)" class="text-gray-600 hover:underline text-xs font-semibold">CSV</a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal génération -->
    <Teleport to="body">
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-lg rounded-2xl bg-white shadow-xl">
                <div class="flex items-center justify-between border-b px-6 py-4">
                    <h2 class="font-bold text-gray-900">Générer un lot de vouchers</h2>
                    <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
                </div>
                <form @submit.prevent="submitGenerate" class="p-6 space-y-4">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Quantité *</label>
                            <input v-model="generateForm.quantity" type="number" min="1" max="500" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required />
                            <p v-if="generateForm.errors.quantity" class="mt-1 text-xs text-red-600">{{ generateForm.errors.quantity }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Forfait (optionnel)</label>
                            <select v-model="generateForm.plan_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="">Tous les forfaits</option>
                                <option v-for="p in plans" :key="p.id" :value="p.id">{{ p.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Durée (mois) *</label>
                            <input v-model="generateForm.duration_months" type="number" min="1" max="24" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Devise *</label>
                            <select v-model="generateForm.currency" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="XOF">XOF</option>
                                <option value="EUR">EUR</option>
                                <option value="USD">USD</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Valeur nominale *</label>
                            <input v-model="generateForm.face_value" type="number" min="0" step="100" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Prix revendeur *</label>
                            <input v-model="generateForm.reseller_price" type="number" min="0" step="100" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required />
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nom du revendeur</label>
                            <input v-model="generateForm.reseller_name" type="text" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" placeholder="Ex : Distributeur Abidjan" />
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date d'expiration des codes (optionnel)</label>
                            <input v-model="generateForm.expires_at" type="date" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="showModal = false" class="rounded-lg border px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">Annuler</button>
                        <button type="submit" :disabled="generateForm.processing" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 disabled:opacity-50">
                            {{ generateForm.processing ? 'Génération...' : 'Générer' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </Teleport>
</template>

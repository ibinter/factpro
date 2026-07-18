<script setup>
import { ref, reactive } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    wallets: Array,
});

const showModal = ref(false);
const editTarget = ref(null);

const form = reactive({
    currency: '',
    network: '',
    wallet_address: '',
    label: '',
    qr_code_url: '',
    instructions: '',
    confirmations_required: 1,
    is_active: false,
    display_order: 0,
});

const openCreate = () => {
    editTarget.value = null;
    Object.assign(form, {
        currency: '', network: '', wallet_address: '', label: '',
        qr_code_url: '', instructions: '', confirmations_required: 1,
        is_active: false, display_order: 0,
    });
    showModal.value = true;
};

const openEdit = (wallet) => {
    editTarget.value = wallet;
    Object.assign(form, {
        currency: wallet.currency,
        network: wallet.network,
        wallet_address: wallet.wallet_address,
        label: wallet.label ?? '',
        qr_code_url: wallet.qr_code_url ?? '',
        instructions: wallet.instructions ?? '',
        confirmations_required: wallet.confirmations_required,
        is_active: wallet.is_active,
        display_order: wallet.display_order,
    });
    showModal.value = true;
};

const save = () => {
    if (editTarget.value) {
        router.put(route('admin.crypto-wallets.update', editTarget.value.id), form, {
            preserveScroll: true,
            onSuccess: () => { showModal.value = false; },
        });
    } else {
        router.post(route('admin.crypto-wallets.store'), form, {
            preserveScroll: true,
            onSuccess: () => { showModal.value = false; },
        });
    }
};

const toggleActive = (wallet) => {
    router.put(route('admin.crypto-wallets.update', wallet.id), {
        ...wallet,
        is_active: !wallet.is_active,
    }, { preserveScroll: true });
};

const destroy = (wallet) => {
    if (!confirm(`Supprimer le wallet ${wallet.label ?? wallet.currency} ?`)) return;
    router.delete(route('admin.crypto-wallets.destroy', wallet.id), { preserveScroll: true });
};

const mask = (address) => {
    if (!address || address.length < 12) return address;
    return address.slice(0, 6) + '...' + address.slice(-6);
};
</script>

<template>
    <div class="p-6 max-w-5xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-xl font-bold text-gray-900">Wallets Cryptomonnaie</h1>
            <button @click="openCreate"
                class="px-4 py-2 bg-brand-600 text-white rounded-lg text-sm font-semibold hover:bg-brand-700">
                + Ajouter un wallet
            </button>
        </div>

        <div class="overflow-x-auto rounded-xl border border-gray-200">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left">Devise / Réseau</th>
                        <th class="px-4 py-3 text-left">Adresse</th>
                        <th class="px-4 py-3 text-center">Confirmations</th>
                        <th class="px-4 py-3 text-center">Statut</th>
                        <th class="px-4 py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="wallet in wallets" :key="wallet.id"
                        :class="wallet.deleted_at ? 'opacity-40' : ''">
                        <td class="px-4 py-3">
                            <div class="font-semibold text-gray-900">{{ wallet.currency }}</div>
                            <div class="text-xs text-gray-500">{{ wallet.network }}</div>
                            <div v-if="wallet.label" class="text-xs text-gray-400">{{ wallet.label }}</div>
                        </td>
                        <td class="px-4 py-3 font-mono text-xs text-gray-700">{{ mask(wallet.wallet_address) }}</td>
                        <td class="px-4 py-3 text-center text-gray-700">{{ wallet.confirmations_required }}</td>
                        <td class="px-4 py-3 text-center">
                            <button @click="toggleActive(wallet)"
                                :class="['px-2 py-1 rounded-full text-xs font-semibold', wallet.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500']">
                                {{ wallet.is_active ? 'Actif' : 'Inactif' }}
                            </button>
                        </td>
                        <td class="px-4 py-3 text-center space-x-2">
                            <button @click="openEdit(wallet)"
                                class="text-brand-600 text-xs font-medium hover:underline">Modifier</button>
                            <button @click="destroy(wallet)"
                                class="text-red-500 text-xs font-medium hover:underline">Supprimer</button>
                        </td>
                    </tr>
                    <tr v-if="!wallets.length">
                        <td colspan="5" class="px-4 py-8 text-center text-gray-400">Aucun wallet configuré.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Modal ajout / édition -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6 space-y-4">
                <h2 class="font-bold text-gray-900">{{ editTarget ? 'Modifier le wallet' : 'Nouveau wallet crypto' }}</h2>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-medium text-gray-700">Devise *</label>
                        <input v-model="form.currency" placeholder="USDT" required
                            class="w-full mt-1 px-3 py-2 border rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-700">Réseau *</label>
                        <input v-model="form.network" placeholder="TRC20" required
                            class="w-full mt-1 px-3 py-2 border rounded-lg text-sm">
                    </div>
                </div>

                <div>
                    <label class="text-xs font-medium text-gray-700">Adresse wallet *</label>
                    <input v-model="form.wallet_address" required
                        class="w-full mt-1 px-3 py-2 border rounded-lg text-sm font-mono">
                </div>

                <div>
                    <label class="text-xs font-medium text-gray-700">Label (affiché au client)</label>
                    <input v-model="form.label" placeholder="USDT TRC20 (Tron)"
                        class="w-full mt-1 px-3 py-2 border rounded-lg text-sm">
                </div>

                <div>
                    <label class="text-xs font-medium text-gray-700">URL QR Code (optionnel)</label>
                    <input v-model="form.qr_code_url" type="url"
                        class="w-full mt-1 px-3 py-2 border rounded-lg text-sm">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-medium text-gray-700">Confirmations requises</label>
                        <input v-model.number="form.confirmations_required" type="number" min="1"
                            class="w-full mt-1 px-3 py-2 border rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-700">Ordre d'affichage</label>
                        <input v-model.number="form.display_order" type="number"
                            class="w-full mt-1 px-3 py-2 border rounded-lg text-sm">
                    </div>
                </div>

                <div>
                    <label class="text-xs font-medium text-gray-700">Instructions (optionnel)</label>
                    <textarea v-model="form.instructions" rows="2"
                        class="w-full mt-1 px-3 py-2 border rounded-lg text-sm resize-none"></textarea>
                </div>

                <div class="flex items-center gap-2">
                    <input id="is_active" type="checkbox" v-model="form.is_active" class="rounded">
                    <label for="is_active" class="text-sm text-gray-700">Activer ce wallet (visible sur le checkout)</label>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showModal = false"
                        class="px-4 py-2 text-sm text-gray-600 border rounded-lg hover:bg-gray-50">
                        Annuler
                    </button>
                    <button type="button" @click="save"
                        class="px-4 py-2 text-sm bg-brand-600 text-white rounded-lg font-semibold hover:bg-brand-700">
                        {{ editTarget ? 'Enregistrer' : 'Créer' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

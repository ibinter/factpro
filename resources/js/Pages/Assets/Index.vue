<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    assets: Array,
    totals: Object,
});

const showModal = ref(false);
const confirmingDelete = ref(null);

const form = useForm({
    name: '',
    category: 'materiel',
    reference: '',
    description: '',
    purchase_price: '',
    residual_value: 0,
    purchase_date: '',
    start_date: '',
    duration_years: 5,
    depreciation_method: 'linear',
    supplier: '',
    location: '',
    serial_number: '',
    currency: 'XOF',
});

const categories = [
    { value: 'materiel', label: 'Matériel' },
    { value: 'vehicule', label: 'Véhicule' },
    { value: 'immeuble', label: 'Immeuble' },
    { value: 'logiciel', label: 'Logiciel' },
    { value: 'autre', label: 'Autre' },
];

const openCreate = () => {
    form.reset();
    form.clearErrors();
    showModal.value = true;
};

const submit = () => {
    form.post(route('assets.store'), {
        onSuccess: () => { showModal.value = false; form.reset(); },
    });
};

const deleteAsset = (id) => {
    router.delete(route('assets.destroy', id), {
        onSuccess: () => { confirmingDelete.value = null; },
    });
};

const fmt = (n, currency = 'XOF') =>
    new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 0 }).format(n) + ' ' + currency;

const statusLabel = (s) => ({ active: 'Actif', disposed: 'Cédé', written_off: 'Mis au rebut' }[s] ?? s);
const statusClass = (s) => ({ active: 'bg-green-100 text-green-800', disposed: 'bg-blue-100 text-blue-800', written_off: 'bg-red-100 text-red-800' }[s] ?? 'bg-gray-100 text-gray-800');
const catLabel = (c) => ({ materiel: 'Matériel', vehicule: 'Véhicule', immeuble: 'Immeuble', logiciel: 'Logiciel', autre: 'Autre' }[c] ?? c);
</script>

<template>
    <Head title="Immobilisations" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Immobilisations</h2>
                <PrimaryButton @click="openCreate">+ Nouvelle immobilisation</PrimaryButton>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

                <!-- KPIs -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 text-center">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ totals.count }}</p>
                        <p class="text-xs text-gray-500 mt-1">Total immobilisations</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 text-center">
                        <p class="text-2xl font-bold text-green-600">{{ totals.active }}</p>
                        <p class="text-xs text-gray-500 mt-1">Actives</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 text-center">
                        <p class="text-sm font-bold text-gray-900 dark:text-white">{{ fmt(totals.total_purchase) }}</p>
                        <p class="text-xs text-gray-500 mt-1">Valeur d'achat totale</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 text-center">
                        <p class="text-sm font-bold text-blue-600">{{ fmt(totals.total_nbv) }}</p>
                        <p class="text-xs text-gray-500 mt-1">VNC totale</p>
                    </div>
                </div>

                <!-- Table -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-300">Nom</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-300">Catégorie</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-500 dark:text-gray-300">Prix achat</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-500 dark:text-gray-300">VNC actuelle</th>
                                <th class="px-4 py-3 text-center font-medium text-gray-500 dark:text-gray-300">Durée</th>
                                <th class="px-4 py-3 text-center font-medium text-gray-500 dark:text-gray-300">Méthode</th>
                                <th class="px-4 py-3 text-center font-medium text-gray-500 dark:text-gray-300">Statut</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <tr v-if="assets.length === 0">
                                <td colspan="8" class="px-4 py-8 text-center text-gray-400">Aucune immobilisation enregistrée.</td>
                            </tr>
                            <tr v-for="a in assets" :key="a.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                                    <Link :href="route('assets.show', a.id)" class="hover:underline text-blue-600">{{ a.name }}</Link>
                                    <p class="text-xs text-gray-400">{{ a.reference }}</p>
                                </td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ catLabel(a.category) }}</td>
                                <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-300">{{ fmt(a.purchase_price, a.currency) }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-blue-600">{{ fmt(a.net_book_value, a.currency) }}</td>
                                <td class="px-4 py-3 text-center text-gray-600 dark:text-gray-300">{{ a.duration_years }} ans</td>
                                <td class="px-4 py-3 text-center text-gray-600 dark:text-gray-300">{{ a.method === 'linear' ? 'Linéaire' : 'Dégressif' }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span :class="['px-2 py-0.5 rounded-full text-xs font-medium', statusClass(a.status)]">
                                        {{ statusLabel(a.status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right space-x-2">
                                    <Link :href="route('assets.show', a.id)" class="text-blue-600 hover:underline text-xs">Voir</Link>
                                    <button @click="confirmingDelete = a.id" class="text-red-500 hover:underline text-xs">Suppr.</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal création -->
        <Modal :show="showModal" @close="showModal = false" max-width="2xl">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Nouvelle immobilisation</h3>
                <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <InputLabel value="Nom *" />
                        <TextInput v-model="form.name" class="mt-1 w-full" required />
                        <InputError :message="form.errors.name" />
                    </div>
                    <div>
                        <InputLabel value="Catégorie *" />
                        <select v-model="form.category" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
                            <option v-for="c in categories" :key="c.value" :value="c.value">{{ c.label }}</option>
                        </select>
                    </div>
                    <div>
                        <InputLabel value="Référence" />
                        <TextInput v-model="form.reference" class="mt-1 w-full" />
                    </div>
                    <div>
                        <InputLabel value="Prix d'achat *" />
                        <TextInput v-model="form.purchase_price" type="number" min="0" step="1" class="mt-1 w-full" required />
                        <InputError :message="form.errors.purchase_price" />
                    </div>
                    <div>
                        <InputLabel value="Valeur résiduelle" />
                        <TextInput v-model="form.residual_value" type="number" min="0" step="1" class="mt-1 w-full" />
                    </div>
                    <div>
                        <InputLabel value="Date d'achat *" />
                        <TextInput v-model="form.purchase_date" type="date" class="mt-1 w-full" required />
                        <InputError :message="form.errors.purchase_date" />
                    </div>
                    <div>
                        <InputLabel value="Date début amortissement *" />
                        <TextInput v-model="form.start_date" type="date" class="mt-1 w-full" required />
                        <InputError :message="form.errors.start_date" />
                    </div>
                    <div>
                        <InputLabel value="Durée (années) *" />
                        <TextInput v-model="form.duration_years" type="number" min="1" max="50" class="mt-1 w-full" required />
                        <InputError :message="form.errors.duration_years" />
                    </div>
                    <div>
                        <InputLabel value="Méthode d'amortissement *" />
                        <select v-model="form.depreciation_method" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="linear">Linéaire</option>
                            <option value="declining">Dégressif</option>
                        </select>
                    </div>
                    <div>
                        <InputLabel value="Devise" />
                        <TextInput v-model="form.currency" class="mt-1 w-full" placeholder="XOF" />
                    </div>
                    <div>
                        <InputLabel value="Fournisseur" />
                        <TextInput v-model="form.supplier" class="mt-1 w-full" />
                    </div>
                    <div>
                        <InputLabel value="Localisation" />
                        <TextInput v-model="form.location" class="mt-1 w-full" />
                    </div>
                    <div>
                        <InputLabel value="N° série" />
                        <TextInput v-model="form.serial_number" class="mt-1 w-full" />
                    </div>
                    <div class="md:col-span-2 flex justify-end gap-3 pt-2">
                        <SecondaryButton type="button" @click="showModal = false">Annuler</SecondaryButton>
                        <PrimaryButton type="submit" :disabled="form.processing">
                            {{ form.processing ? 'Enregistrement...' : 'Enregistrer' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Confirm delete -->
        <Modal :show="confirmingDelete !== null" @close="confirmingDelete = null">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Supprimer l'immobilisation ?</h3>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Cette action est irréversible.</p>
                <div class="mt-4 flex justify-end gap-3">
                    <SecondaryButton @click="confirmingDelete = null">Annuler</SecondaryButton>
                    <DangerButton @click="deleteAsset(confirmingDelete)">Supprimer</DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

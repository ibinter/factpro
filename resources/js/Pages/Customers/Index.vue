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
import { ref, watch } from 'vue';

const props = defineProps({
    customers: Object,
    filters: Object,
});

const search = ref(props.filters.search ?? '');
let searchTimeout = null;
watch(search, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('customers.index'), { search: value }, { preserveState: true, replace: true });
    }, 350);
});

const showModal = ref(false);
const editing = ref(null);
const confirmingDelete = ref(null);

const form = useForm({
    type: 'company',
    name: '',
    contact_name: '',
    email: '',
    phone: '',
    address: '',
    city: '',
    country: 'CI',
    tax_id: '',
    currency: 'XOF',
    notes: '',
});

const openCreate = () => {
    editing.value = null;
    form.reset();
    form.clearErrors();
    showModal.value = true;
};

const openEdit = (customer) => {
    editing.value = customer;
    Object.keys(form.data()).forEach((key) => {
        form[key] = customer[key] ?? form[key];
    });
    form.clearErrors();
    showModal.value = true;
};

const submit = () => {
    const options = {
        preserveScroll: true,
        onSuccess: () => { showModal.value = false; form.reset(); },
    };
    if (editing.value) {
        form.put(route('customers.update', editing.value.id), options);
    } else {
        form.post(route('customers.store'), options);
    }
};

const destroy = () => {
    router.delete(route('customers.destroy', confirmingDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => (confirmingDelete.value = null),
    });
};

// Portail client self-service (cahier §11)
const copiedId = ref(null);

const copyPortalLink = async (customer) => {
    const url = window.location.origin + '/portal/' + customer.portal_token;
    try {
        await navigator.clipboard.writeText(url);
    } catch {
        // Fallback (contexte non sécurisé) : sélection manuelle via prompt
        window.prompt('Copiez le lien du portail :', url);
    }
    copiedId.value = customer.id;
    setTimeout(() => {
        if (copiedId.value === customer.id) copiedId.value = null;
    }, 2000);
};

const generatePortalToken = (customer) => {
    router.post(route('portal.generate', customer.id), {}, { preserveScroll: true });
};
</script>

<template>
    <Head title="Clients" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Clients</h2>
                <div class="flex items-center gap-2">
                    <a :href="route('import.index')" class="inline-flex items-center gap-1 rounded-md border border-indigo-300 bg-indigo-50 px-3 py-1.5 text-sm font-medium text-indigo-700 hover:bg-indigo-100 transition">
                        📥 Importer CSV
                    </a>
                    <PrimaryButton @click="openCreate">+ Nouveau client</PrimaryButton>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-4 px-4 sm:px-6 lg:px-8">
                <input
                    v-model="search"
                    type="search"
                    placeholder="Rechercher un client (nom, email, téléphone)…"
                    class="w-full max-w-md rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                />

                <div class="overflow-hidden rounded-lg bg-white shadow">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-6 py-3">Nom</th>
                                <th class="px-6 py-3">Contact</th>
                                <th class="px-6 py-3">Téléphone</th>
                                <th class="px-6 py-3">Ville</th>
                                <th class="px-6 py-3 text-center">Documents</th>
                                <th class="px-6 py-3 text-center">Portail</th>
                                <th class="px-6 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="customer in customers.data" :key="customer.id" class="hover:bg-gray-50">
                                <td class="px-6 py-3">
                                    <div class="font-semibold text-gray-800">{{ customer.name }}</div>
                                    <div class="text-xs text-gray-400">{{ customer.email }}</div>
                                </td>
                                <td class="px-6 py-3">{{ customer.contact_name ?? '—' }}</td>
                                <td class="px-6 py-3">{{ customer.phone ?? '—' }}</td>
                                <td class="px-6 py-3">{{ customer.city ?? '—' }} <span class="text-gray-400">{{ customer.country }}</span></td>
                                <td class="px-6 py-3 text-center">
                                    <span class="rounded-full bg-brand-50 px-2 py-0.5 text-xs font-semibold text-brand-700">
                                        {{ customer.documents_count }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-center">
                                    <template v-if="customer.portal_token">
                                        <button
                                            class="rounded-md bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-700 hover:bg-brand-100"
                                            @click="copyPortalLink(customer)"
                                        >
                                            {{ copiedId === customer.id ? 'Copié !' : '🔗 Copier le lien' }}
                                        </button>
                                        <button
                                            class="ml-1 text-xs text-gray-400 hover:text-brand-600"
                                            title="Régénérer le lien (l'ancien sera invalidé)"
                                            @click="generatePortalToken(customer)"
                                        >
                                            🔄
                                        </button>
                                    </template>
                                    <button
                                        v-else
                                        class="rounded-md border border-brand-200 px-2.5 py-1 text-xs font-semibold text-brand-600 hover:bg-brand-50"
                                        @click="generatePortalToken(customer)"
                                    >
                                        Activer le portail
                                    </button>
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <Link :href="route('crm.pipeline') + '?customer_id=' + customer.id" class="text-sm font-semibold text-purple-600 hover:underline mr-3">→ CRM</Link>
                                    <button class="text-sm font-semibold text-brand-600 hover:underline" @click="openEdit(customer)">Modifier</button>
                                    <button class="ml-3 text-sm font-semibold text-red-500 hover:underline" @click="confirmingDelete = customer">Supprimer</button>
                                </td>
                            </tr>
                            <tr v-if="!customers.data.length">
                                <td colspan="7" class="px-6 py-10 text-center text-gray-400">Aucun client trouvé.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="customers.links.length > 3" class="flex flex-wrap gap-1">
                    <template v-for="link in customers.links" :key="link.label">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            v-html="link.label"
                            class="rounded px-3 py-1.5 text-sm"
                            :class="link.active ? 'bg-brand-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'"
                        />
                        <span v-else v-html="link.label" class="px-3 py-1.5 text-sm text-gray-400" />
                    </template>
                </div>
            </div>
        </div>

        <!-- Modale création/édition -->
        <Modal :show="showModal" @close="showModal = false" max-width="2xl">
            <div class="p-6">
                <h3 class="mb-4 text-lg font-semibold text-gray-800">
                    {{ editing ? 'Modifier le client' : 'Nouveau client' }}
                </h3>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <InputLabel value="Type" />
                        <select v-model="form.type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
                            <option value="company">Entreprise</option>
                            <option value="individual">Particulier</option>
                        </select>
                    </div>
                    <div>
                        <InputLabel value="Nom *" />
                        <TextInput v-model="form.name" class="mt-1 block w-full" required />
                        <InputError :message="form.errors.name" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel value="Personne de contact" />
                        <TextInput v-model="form.contact_name" class="mt-1 block w-full" />
                    </div>
                    <div>
                        <InputLabel value="Email" />
                        <TextInput v-model="form.email" type="email" class="mt-1 block w-full" />
                        <InputError :message="form.errors.email" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel value="Téléphone" />
                        <TextInput v-model="form.phone" class="mt-1 block w-full" />
                    </div>
                    <div>
                        <InputLabel value="N° fiscal / contribuable" />
                        <TextInput v-model="form.tax_id" class="mt-1 block w-full" />
                    </div>
                    <div class="sm:col-span-2">
                        <InputLabel value="Adresse" />
                        <TextInput v-model="form.address" class="mt-1 block w-full" />
                    </div>
                    <div>
                        <InputLabel value="Ville" />
                        <TextInput v-model="form.city" class="mt-1 block w-full" />
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <InputLabel value="Pays" />
                            <TextInput v-model="form.country" maxlength="2" class="mt-1 block w-full uppercase" />
                        </div>
                        <div>
                            <InputLabel value="Devise" />
                            <TextInput v-model="form.currency" maxlength="3" class="mt-1 block w-full uppercase" />
                        </div>
                    </div>
                    <div class="sm:col-span-2">
                        <InputLabel value="Notes" />
                        <textarea v-model="form.notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500"></textarea>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="showModal = false">Annuler</SecondaryButton>
                    <PrimaryButton :disabled="form.processing" @click="submit">
                        {{ editing ? 'Enregistrer' : 'Créer le client' }}
                    </PrimaryButton>
                </div>
            </div>
        </Modal>

        <!-- Confirmation suppression -->
        <Modal :show="!!confirmingDelete" @close="confirmingDelete = null">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-800">Supprimer ce client ?</h3>
                <p class="mt-2 text-sm text-gray-500">
                    « {{ confirmingDelete?.name }} » sera supprimé. Ses documents existants seront conservés.
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="confirmingDelete = null">Annuler</SecondaryButton>
                    <DangerButton @click="destroy">Supprimer</DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

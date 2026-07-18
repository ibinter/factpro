<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdminTabs from '@/Components/AdminTabs.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    methods: Array,
    types: Array,
});

const typeLabels = {
    mobile_money: 'Mobile Money',
    bank_national: 'Banque nationale',
    bank_international: 'Banque internationale',
    transfer_service: 'Service de transfert',
};
const typeColors = {
    mobile_money: 'bg-orange-100 text-orange-700',
    bank_national: 'bg-blue-100 text-blue-700',
    bank_international: 'bg-indigo-100 text-indigo-700',
    transfer_service: 'bg-teal-100 text-teal-700',
};

const fmt = (n) => new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(n ?? 0);

const showForm = ref(false);
const editing = ref(null);
const deleting = ref(null);

const form = useForm({
    type: 'mobile_money',
    label: '',
    country: '',
    operator: '',
    account_number: '',
    account_holder: '',
    iban: '',
    swift_bic: '',
    bank_name: '',
    currency: 'XOF',
    instructions: '',
    min_amount: null,
    max_amount: null,
    is_active: true,
    sort_order: 0,
});

const openCreate = () => {
    editing.value = null;
    form.reset();
    form.clearErrors();
    showForm.value = true;
};

const openEdit = (m) => {
    editing.value = m;
    form.clearErrors();
    form.type = m.type;
    form.label = m.label;
    form.country = m.country ?? '';
    form.operator = m.operator ?? '';
    form.account_number = m.account_number ?? '';
    form.account_holder = m.account_holder ?? '';
    form.iban = m.iban ?? '';
    form.swift_bic = m.swift_bic ?? '';
    form.bank_name = m.bank_name ?? '';
    form.currency = m.currency;
    form.instructions = m.instructions ?? '';
    form.min_amount = m.min_amount;
    form.max_amount = m.max_amount;
    form.is_active = m.is_active;
    form.sort_order = m.sort_order;
    showForm.value = true;
};

const submit = () => {
    if (editing.value) {
        form.put(route('admin.methods.update', editing.value.id), {
            preserveScroll: true,
            onSuccess: () => (showForm.value = false),
        });
    } else {
        form.post(route('admin.methods.store'), {
            preserveScroll: true,
            onSuccess: () => (showForm.value = false),
        });
    }
};

const toggle = (m) => {
    useForm({}).post(route('admin.methods.toggle', m.id), { preserveScroll: true });
};

const confirmDelete = () => {
    useForm({}).delete(route('admin.methods.destroy', deleting.value.id), {
        preserveScroll: true,
        onSuccess: () => (deleting.value = null),
    });
};
</script>

<template>
    <Head title="Admin — Moyens de paiement" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Console Superadmin — <span class="text-gold-600">Moyens de paiement</span>
            </h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <AdminTabs />

                <div class="flex justify-end">
                    <PrimaryButton @click="openCreate">+ Ajouter un moyen de paiement</PrimaryButton>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div
                        v-for="m in methods"
                        :key="m.id"
                        class="rounded-lg bg-white p-5 shadow"
                        :class="{ 'opacity-60': !m.is_active }"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-semibold text-gray-800">{{ m.label }}</span>
                                    <span class="rounded-full px-2 py-0.5 text-xs font-semibold" :class="typeColors[m.type]">
                                        {{ typeLabels[m.type] ?? m.type }}
                                    </span>
                                    <span v-if="m.country" class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-600">{{ m.country }}</span>
                                    <span
                                        class="rounded-full px-2 py-0.5 text-xs font-semibold"
                                        :class="m.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'"
                                    >{{ m.is_active ? 'Actif' : 'Inactif' }}</span>
                                </div>
                                <div v-if="m.operator" class="mt-1 text-xs text-gray-500">Opérateur : {{ m.operator }}</div>
                            </div>
                        </div>

                        <dl class="mt-3 space-y-1 text-sm text-gray-600">
                            <div v-if="m.account_number"><dt class="inline text-gray-400">Compte : </dt><dd class="inline font-mono">{{ m.account_number }}</dd></div>
                            <div v-if="m.account_holder"><dt class="inline text-gray-400">Titulaire : </dt><dd class="inline">{{ m.account_holder }}</dd></div>
                            <div v-if="m.bank_name"><dt class="inline text-gray-400">Banque : </dt><dd class="inline">{{ m.bank_name }}</dd></div>
                            <div v-if="m.iban"><dt class="inline text-gray-400">IBAN : </dt><dd class="inline font-mono">{{ m.iban }}</dd></div>
                            <div v-if="m.swift_bic"><dt class="inline text-gray-400">SWIFT/BIC : </dt><dd class="inline font-mono">{{ m.swift_bic }}</dd></div>
                            <div><dt class="inline text-gray-400">Devise : </dt><dd class="inline">{{ m.currency }}</dd></div>
                            <div v-if="m.min_amount !== null || m.max_amount !== null">
                                <dt class="inline text-gray-400">Montants : </dt>
                                <dd class="inline">{{ m.min_amount !== null ? fmt(m.min_amount) : '—' }} → {{ m.max_amount !== null ? fmt(m.max_amount) : '—' }}</dd>
                            </div>
                        </dl>

                        <div class="mt-4 flex flex-wrap gap-2">
                            <SecondaryButton @click="openEdit(m)">Modifier</SecondaryButton>
                            <button
                                @click="toggle(m)"
                                class="rounded-md px-4 py-2 text-sm font-semibold"
                                :class="m.is_active ? 'bg-amber-100 text-amber-700 hover:bg-amber-200' : 'bg-green-100 text-green-700 hover:bg-green-200'"
                            >{{ m.is_active ? 'Désactiver' : 'Activer' }}</button>
                            <DangerButton @click="deleting = m">Supprimer</DangerButton>
                        </div>
                    </div>

                    <div v-if="!methods.length" class="rounded-lg bg-white p-12 text-center text-gray-400 shadow md:col-span-2">
                        Aucun moyen de paiement configuré.
                    </div>
                </div>
            </div>
        </div>

        <!-- Modale création/édition -->
        <Modal :show="showForm" max-width="2xl" @close="showForm = false">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-800">
                    {{ editing ? 'Modifier le moyen de paiement' : 'Nouveau moyen de paiement' }}
                </h3>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <InputLabel value="Type *" />
                        <select v-model="form.type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
                            <option v-for="t in types" :key="t" :value="t">{{ typeLabels[t] ?? t }}</option>
                        </select>
                        <InputError :message="form.errors.type" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel value="Libellé *" />
                        <TextInput v-model="form.label" type="text" class="mt-1 block w-full" placeholder="Orange Money CI…" />
                        <InputError :message="form.errors.label" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel value="Pays (ISO 2)" />
                        <TextInput v-model="form.country" type="text" maxlength="2" class="mt-1 block w-full" placeholder="CI" />
                        <InputError :message="form.errors.country" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel value="Opérateur" />
                        <TextInput v-model="form.operator" type="text" class="mt-1 block w-full" />
                        <InputError :message="form.errors.operator" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel value="Numéro de compte" />
                        <TextInput v-model="form.account_number" type="text" class="mt-1 block w-full" />
                        <InputError :message="form.errors.account_number" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel value="Titulaire du compte" />
                        <TextInput v-model="form.account_holder" type="text" class="mt-1 block w-full" />
                        <InputError :message="form.errors.account_holder" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel value="Nom de la banque" />
                        <TextInput v-model="form.bank_name" type="text" class="mt-1 block w-full" />
                        <InputError :message="form.errors.bank_name" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel value="IBAN" />
                        <TextInput v-model="form.iban" type="text" class="mt-1 block w-full" />
                        <InputError :message="form.errors.iban" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel value="SWIFT / BIC" />
                        <TextInput v-model="form.swift_bic" type="text" class="mt-1 block w-full" />
                        <InputError :message="form.errors.swift_bic" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel value="Devise * (ISO 3)" />
                        <TextInput v-model="form.currency" type="text" maxlength="3" class="mt-1 block w-full" placeholder="XOF" />
                        <InputError :message="form.errors.currency" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel value="Montant min." />
                        <TextInput v-model="form.min_amount" type="number" step="1" min="0" class="mt-1 block w-full" />
                        <InputError :message="form.errors.min_amount" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel value="Montant max." />
                        <TextInput v-model="form.max_amount" type="number" step="1" min="0" class="mt-1 block w-full" />
                        <InputError :message="form.errors.max_amount" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel value="Ordre d'affichage" />
                        <TextInput v-model="form.sort_order" type="number" step="1" min="0" class="mt-1 block w-full" />
                        <InputError :message="form.errors.sort_order" class="mt-1" />
                    </div>
                    <div class="flex items-end">
                        <label class="flex items-center gap-2 text-sm text-gray-600">
                            <input v-model="form.is_active" type="checkbox" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500" />
                            Actif
                        </label>
                    </div>
                    <div class="sm:col-span-2">
                        <InputLabel value="Instructions (max 1000)" />
                        <textarea v-model="form.instructions" rows="3" maxlength="1000" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500"></textarea>
                        <InputError :message="form.errors.instructions" class="mt-1" />
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="showForm = false">Annuler</SecondaryButton>
                    <PrimaryButton :disabled="form.processing" @click="submit">{{ editing ? 'Enregistrer' : 'Ajouter' }}</PrimaryButton>
                </div>
            </div>
        </Modal>

        <!-- Modale suppression -->
        <Modal :show="!!deleting" @close="deleting = null">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-red-700">Supprimer ce moyen de paiement</h3>
                <p class="mt-1 text-sm text-gray-500">
                    « {{ deleting?.label }} » sera définitivement supprimé.
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="deleting = null">Annuler</SecondaryButton>
                    <DangerButton @click="confirmDelete">Supprimer</DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

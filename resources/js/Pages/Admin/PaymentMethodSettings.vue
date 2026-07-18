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
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    methods: Array,
    grouped: Object,
    types: Array,
});

const sectionMeta = {
    mobile_money: { label: 'Mobile Money', icon: '📱', desc: 'Wave, Orange Money, MTN MoMo, Moov, etc.' },
    bank_national: { label: 'Virement bancaire national', icon: '🏦', desc: 'Comptes bancaires locaux.' },
    bank_international: { label: 'Virement international', icon: '🌍', desc: 'SWIFT, Western Union, MoneyGram…' },
    transfer_service: { label: 'Services de transfert', icon: '💸', desc: 'Western Union, MoneyGram, etc.' },
    cash: { label: 'Espèces', icon: '💵', desc: 'Paiement en espèces sur place.' },
};

const activeTab = ref('mobile_money');

// Modale ajout / modification
const editing = ref(null);
const creating = ref(false);

const form = useForm({
    type: 'mobile_money',
    label: '',
    country: '',
    currency: 'XOF',
    is_active: true,
    config: {
        number: '',
        holder_name: '',
        operator: '',
        instructions: '',
        bank_name: '',
        account_number: '',
        rib: '',
        branch: '',
        address: '',
        hours: '',
    },
    sort_order: 0,
});

const openCreate = (type) => {
    form.reset();
    form.type = type;
    creating.value = true;
};

const openEdit = (method) => {
    editing.value = method;
    form.type = method.type;
    form.label = method.label;
    form.country = method.country;
    form.currency = method.currency;
    form.is_active = method.is_active;
    form.sort_order = method.sort_order ?? 0;
    form.config = { ...method.config };
};

const submitCreate = () => {
    form.post(route('admin.methods.store'), {
        preserveScroll: true,
        onSuccess: () => { creating.value = false; form.reset(); },
    });
};

const submitEdit = () => {
    form.put(route('admin.methods.update', editing.value.id), {
        preserveScroll: true,
        onSuccess: () => { editing.value = null; },
    });
};

const toggleMethod = (method) => {
    router.post(route('admin.methods.toggle', method.id), {}, { preserveScroll: true });
};

const deleteMethod = (method) => {
    if (confirm(`Supprimer "${method.label}" ?`)) {
        router.delete(route('admin.methods.destroy', method.id), { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="Admin — Configuration des paiements" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Console Superadmin — <span class="text-teal-600">Configuration des méthodes de paiement</span>
            </h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <AdminTabs />

                <!-- Onglets par type -->
                <div class="flex flex-wrap gap-2">
                    <button
                        v-for="(meta, type) in sectionMeta"
                        :key="type"
                        @click="activeTab = type"
                        class="rounded-full px-4 py-2 text-sm font-semibold transition"
                        :class="activeTab === type
                            ? 'bg-teal-600 text-white shadow'
                            : 'bg-white text-gray-600 hover:bg-teal-50 hover:text-teal-700'"
                    >
                        {{ meta.icon }} {{ meta.label }}
                        <span v-if="grouped[type]?.length" class="ml-1 rounded-full bg-white/20 px-1.5 text-xs">
                            {{ grouped[type].length }}
                        </span>
                    </button>
                </div>

                <!-- Section active -->
                <div v-for="(meta, type) in sectionMeta" :key="type" v-show="activeTab === type">
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">{{ meta.icon }} {{ meta.label }}</h3>
                            <p class="text-sm text-gray-500">{{ meta.desc }}</p>
                        </div>
                        <button @click="openCreate(type)"
                            class="rounded-md bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-700">
                            + Ajouter
                        </button>
                    </div>

                    <div class="space-y-3">
                        <div
                            v-for="method in (grouped[type] ?? [])"
                            :key="method.id"
                            class="flex items-center justify-between rounded-lg bg-white p-4 shadow"
                            :class="!method.is_active ? 'opacity-60' : ''"
                        >
                            <div class="flex-1 space-y-1">
                                <div class="flex items-center gap-3">
                                    <span class="font-semibold text-gray-900">{{ method.label }}</span>
                                    <span v-if="method.country" class="rounded bg-gray-100 px-2 py-0.5 text-xs text-gray-600">{{ method.country }}</span>
                                    <span class="rounded bg-gray-100 px-2 py-0.5 text-xs text-gray-600">{{ method.currency }}</span>
                                    <span
                                        class="rounded-full px-2 py-0.5 text-xs font-bold"
                                        :class="method.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'"
                                    >
                                        {{ method.is_active ? 'Actif' : 'Inactif' }}
                                    </span>
                                </div>
                                <div class="text-xs text-gray-500 space-x-3">
                                    <span v-if="method.config?.number">📞 {{ method.config.number }}</span>
                                    <span v-if="method.config?.holder_name">👤 {{ method.config.holder_name }}</span>
                                    <span v-if="method.config?.bank_name">🏦 {{ method.config.bank_name }}</span>
                                    <span v-if="method.config?.account_number">No {{ method.config.account_number }}</span>
                                </div>
                                <div v-if="method.config?.instructions" class="text-xs text-gray-400 max-w-lg truncate">
                                    ℹ️ {{ method.config.instructions }}
                                </div>
                            </div>
                            <div class="flex items-center gap-2 ml-4 shrink-0">
                                <button @click="toggleMethod(method)"
                                    class="rounded px-3 py-1 text-xs font-semibold"
                                    :class="method.is_active ? 'bg-orange-100 text-orange-700 hover:bg-orange-200' : 'bg-green-100 text-green-700 hover:bg-green-200'">
                                    {{ method.is_active ? 'Désactiver' : 'Activer' }}
                                </button>
                                <button @click="openEdit(method)"
                                    class="rounded bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700 hover:bg-blue-200">
                                    Modifier
                                </button>
                                <button @click="deleteMethod(method)"
                                    class="rounded bg-red-100 px-3 py-1 text-xs font-semibold text-red-700 hover:bg-red-200">
                                    Supprimer
                                </button>
                            </div>
                        </div>

                        <div v-if="!grouped[type]?.length" class="rounded-lg bg-white p-8 text-center text-gray-400 shadow">
                            Aucune méthode configurée pour ce type. Cliquez sur "+ Ajouter".
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modale création / édition -->
        <Modal :show="creating || !!editing" @close="creating = false; editing = null">
            <div class="p-6 space-y-4">
                <h3 class="text-lg font-semibold text-gray-800">
                    {{ editing ? 'Modifier' : 'Ajouter' }} — {{ sectionMeta[form.type]?.label }}
                </h3>

                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <InputLabel value="Libellé *" />
                        <TextInput v-model="form.label" type="text" class="mt-1 block w-full" placeholder="Ex : Orange Money CI" />
                        <InputError :message="form.errors.label" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel value="Pays" />
                        <TextInput v-model="form.country" type="text" class="mt-1 block w-full" placeholder="CI, SN, CM…" />
                    </div>
                    <div>
                        <InputLabel value="Devise" />
                        <TextInput v-model="form.currency" type="text" class="mt-1 block w-full" placeholder="XOF, EUR…" />
                    </div>
                    <div>
                        <InputLabel value="Numéro / Compte" />
                        <TextInput v-model="form.config.number" type="text" class="mt-1 block w-full" placeholder="+225 07 00 00 00" />
                    </div>
                    <div>
                        <InputLabel value="Titulaire" />
                        <TextInput v-model="form.config.holder_name" type="text" class="mt-1 block w-full" placeholder="Nom complet" />
                    </div>
                    <div v-if="form.type === 'bank_national' || form.type === 'bank_international'">
                        <InputLabel value="Banque" />
                        <TextInput v-model="form.config.bank_name" type="text" class="mt-1 block w-full" />
                    </div>
                    <div v-if="form.type === 'bank_national' || form.type === 'bank_international'">
                        <InputLabel value="No de compte / RIB" />
                        <TextInput v-model="form.config.rib" type="text" class="mt-1 block w-full" />
                    </div>
                    <div class="col-span-2">
                        <InputLabel value="Instructions pour le client" />
                        <textarea v-model="form.config.instructions" rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                            placeholder="Indiquez la procédure à suivre…"></textarea>
                    </div>
                    <div v-if="form.type === 'cash'" class="col-span-2">
                        <InputLabel value="Adresse(s) / horaires" />
                        <textarea v-model="form.config.address" rows="2"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                            placeholder="Adresse et horaires d'accueil…"></textarea>
                    </div>
                    <div>
                        <InputLabel value="Ordre d'affichage" />
                        <TextInput v-model="form.sort_order" type="number" min="0" class="mt-1 block w-full" />
                    </div>
                    <div class="flex items-center gap-2 pt-5">
                        <input type="checkbox" v-model="form.is_active" id="is-active" class="rounded border-gray-300" />
                        <label for="is-active" class="text-sm text-gray-700">Actif (visible par les clients)</label>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <SecondaryButton @click="creating = false; editing = null">Annuler</SecondaryButton>
                    <PrimaryButton :disabled="form.processing" @click="editing ? submitEdit() : submitCreate()">
                        {{ editing ? 'Enregistrer' : 'Ajouter' }}
                    </PrimaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

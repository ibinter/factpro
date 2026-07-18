<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    companies: Array,
    canCreate: Boolean,
    limit: Number,
});

const nf = new Intl.NumberFormat('fr-FR');

const roleLabels = {
    owner: 'Propriétaire',
    admin: 'Administrateur',
    member: 'Membre',
};

const initials = (name) =>
    (name ?? '')
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map((w) => w[0].toUpperCase())
        .join('');

const switchTo = (company) => {
    router.post(route('companies.switch', company.id));
};

const showModal = ref(false);

const form = useForm({
    name: '',
    country: 'CI',
    currency: 'XOF',
    email: '',
    phone: '',
    tax_id: '',
});

const openCreate = () => {
    form.reset();
    form.clearErrors();
    showModal.value = true;
};

const submit = () => {
    form.post(route('companies.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showModal.value = false;
            form.reset();
        },
    });
};
</script>

<template>
    <Head title="Mes sociétés" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">Mes sociétés</h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Gérez plusieurs entreprises depuis un seul compte —
                        <span v-if="limit !== null">{{ nf.format(companies.length) }} / {{ nf.format(limit) }} société(s) sur votre forfait.</span>
                        <span v-else>sociétés illimitées sur votre forfait.</span>
                    </p>
                </div>
                <PrimaryButton v-if="canCreate" @click="openCreate">+ Créer une société</PrimaryButton>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    <!-- Cartes société -->
                    <div
                        v-for="company in companies"
                        :key="company.id"
                        class="relative flex flex-col rounded-lg bg-white p-5 shadow transition hover:shadow-md"
                        :class="{ 'ring-2 ring-gold-400': company.is_current }"
                    >
                        <div class="flex items-start gap-4">
                            <img
                                v-if="company.logo_path"
                                :src="`/storage/${company.logo_path}`"
                                alt=""
                                class="h-12 w-12 shrink-0 rounded-full border border-gray-100 object-cover"
                            />
                            <div
                                v-else
                                class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-brand-600 text-lg font-bold text-white"
                            >
                                {{ initials(company.name) }}
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="truncate font-semibold text-gray-800">{{ company.name }}</div>
                                <div class="text-sm text-gray-500">
                                    {{ company.city ?? '—' }}
                                    <span class="text-gray-400">{{ company.country }}</span>
                                    · {{ company.currency }}
                                </div>
                                <div class="mt-2 flex flex-wrap gap-1.5">
                                    <span class="rounded-full bg-brand-50 px-2 py-0.5 text-xs font-semibold text-brand-700">
                                        {{ roleLabels[company.role] ?? company.role }}
                                    </span>
                                    <span
                                        v-if="company.is_current"
                                        class="rounded-full bg-gold-400 px-2 py-0.5 text-xs font-bold text-brand-900"
                                    >
                                        ACTIVE
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 flex items-center gap-4 border-t border-gray-100 pt-3 text-sm text-gray-500">
                            <span>
                                <span class="font-semibold text-gray-700">{{ nf.format(company.customers_count) }}</span>
                                client(s)
                            </span>
                            <span>
                                <span class="font-semibold text-gray-700">{{ nf.format(company.documents_count) }}</span>
                                document(s)
                            </span>
                        </div>

                        <div class="mt-4 flex gap-2">
                            <Link
                                v-if="company.is_current"
                                :href="route('companies.settings')"
                                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold uppercase tracking-widest text-gray-700 transition hover:bg-gray-50"
                            >
                                ⚙ Paramètres
                            </Link>
                            <button
                                v-else
                                type="button"
                                class="inline-flex items-center rounded-md bg-brand-600 px-3 py-1.5 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-brand-500"
                                @click="switchTo(company)"
                            >
                                Basculer
                            </button>
                        </div>
                    </div>

                    <!-- Carte création -->
                    <button
                        v-if="canCreate"
                        type="button"
                        class="flex min-h-[180px] flex-col items-center justify-center gap-2 rounded-lg border-2 border-dashed border-gray-300 bg-white/50 p-5 text-gray-400 transition hover:border-brand-400 hover:text-brand-600"
                        @click="openCreate"
                    >
                        <span class="text-3xl">+</span>
                        <span class="text-sm font-semibold">Créer une société</span>
                    </button>
                    <div
                        v-else
                        class="flex min-h-[180px] flex-col items-center justify-center gap-2 rounded-lg border-2 border-dashed border-gray-200 bg-gray-50 p-5 text-center text-gray-400"
                    >
                        <span class="text-3xl">🔒</span>
                        <span class="text-sm font-semibold">
                            Limite de sociétés atteinte pour votre forfait<span v-if="limit !== null"> ({{ nf.format(limit) }})</span>.
                        </span>
                        <Link :href="route('billing.plans')" class="text-sm font-semibold text-brand-600 hover:underline">
                            Passer au forfait supérieur
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modale création -->
        <Modal :show="showModal" @close="showModal = false" max-width="xl">
            <div class="p-6">
                <h3 class="mb-4 text-lg font-semibold text-gray-800">Nouvelle société</h3>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <InputLabel value="Nom de la société *" />
                        <TextInput v-model="form.name" class="mt-1 block w-full" required autofocus />
                        <InputError :message="form.errors.name" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel value="Pays (code 2 lettres) *" />
                        <TextInput v-model="form.country" maxlength="2" class="mt-1 block w-full uppercase" required />
                        <InputError :message="form.errors.country" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel value="Devise (code 3 lettres) *" />
                        <TextInput v-model="form.currency" maxlength="3" class="mt-1 block w-full uppercase" required />
                        <InputError :message="form.errors.currency" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel value="Email" />
                        <TextInput v-model="form.email" type="email" class="mt-1 block w-full" />
                        <InputError :message="form.errors.email" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel value="Téléphone" />
                        <TextInput v-model="form.phone" class="mt-1 block w-full" />
                        <InputError :message="form.errors.phone" class="mt-1" />
                    </div>
                    <div class="sm:col-span-2">
                        <InputLabel value="N° fiscal / contribuable" />
                        <TextInput v-model="form.tax_id" class="mt-1 block w-full" />
                        <InputError :message="form.errors.tax_id" class="mt-1" />
                    </div>
                </div>

                <p class="mt-4 text-xs text-gray-400">
                    La nouvelle société deviendra automatiquement votre société active.
                </p>

                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="showModal = false">Annuler</SecondaryButton>
                    <PrimaryButton :disabled="form.processing" @click="submit">Créer la société</PrimaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdminTabs from '@/Components/AdminTabs.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    plans: Array,
});

const fmt = (n) => new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(n ?? 0);

const editing = ref(null);
const form = useForm({
    price_monthly: 0,
    promo_price: null,
    short_description: '',
    trial_days: 7,
    is_active: true,
});

const openEdit = (p) => {
    editing.value = p;
    form.clearErrors();
    form.price_monthly = p.price_monthly;
    form.promo_price = p.promo_price;
    form.short_description = p.short_description ?? '';
    form.trial_days = p.trial_days;
    form.is_active = p.is_active;
};

const submit = () => {
    form.put(route('admin.plans.update', editing.value.id), {
        preserveScroll: true,
        onSuccess: () => (editing.value = null),
    });
};
</script>

<template>
    <Head title="Admin — Forfaits" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Console Superadmin — <span class="text-gold-600">Forfaits</span>
            </h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <AdminTabs />

                <div class="grid gap-4 md:grid-cols-2">
                    <div
                        v-for="p in plans"
                        :key="p.id"
                        class="rounded-lg bg-white p-6 shadow"
                        :class="{ 'opacity-70': !p.is_active }"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-lg font-bold text-gray-800">{{ p.name }}</h3>
                                    <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-mono text-gray-500">{{ p.code }}</span>
                                    <span
                                        class="rounded-full px-2 py-0.5 text-xs font-semibold"
                                        :class="p.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'"
                                    >{{ p.is_active ? 'Actif' : 'Inactif' }}</span>
                                </div>
                                <p v-if="p.short_description" class="mt-1 text-sm text-gray-500">{{ p.short_description }}</p>
                            </div>
                            <div class="text-right">
                                <div class="text-2xl font-bold text-brand-700">{{ fmt(p.price_monthly) }}</div>
                                <div class="text-xs text-gray-400">{{ p.currency }} / mois</div>
                                <div v-if="p.promo_price !== null" class="mt-1 text-xs font-semibold text-gold-600">Promo : {{ fmt(p.promo_price) }}</div>
                            </div>
                        </div>

                        <div class="mt-4 flex flex-wrap gap-4 text-sm">
                            <div><span class="text-gray-400">Essai : </span><b>{{ p.trial_days }} j</b></div>
                            <div><span class="text-gray-400">Licences actives : </span><b class="text-green-600">{{ p.active_licenses }}</b></div>
                        </div>

                        <!-- Limits & features en lecture seule -->
                        <div class="mt-4 grid gap-3 sm:grid-cols-2">
                            <div class="rounded-md bg-gray-50 p-3">
                                <div class="mb-1 text-xs font-semibold uppercase tracking-wide text-gray-400">Limites (lecture seule)</div>
                                <ul class="space-y-0.5 text-xs text-gray-600">
                                    <li v-for="(val, key) in p.limits" :key="key">
                                        <span class="text-gray-400">{{ key }} :</span> {{ val === null ? '∞' : val }}
                                    </li>
                                </ul>
                            </div>
                            <div class="rounded-md bg-gray-50 p-3">
                                <div class="mb-1 text-xs font-semibold uppercase tracking-wide text-gray-400">Fonctionnalités (lecture seule)</div>
                                <ul class="space-y-0.5 text-xs text-gray-600">
                                    <li v-for="(feat, i) in p.features" :key="i">• {{ feat }}</li>
                                </ul>
                            </div>
                        </div>

                        <div class="mt-4 flex justify-end">
                            <SecondaryButton @click="openEdit(p)">Modifier le tarif</SecondaryButton>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modale édition tarif -->
        <Modal :show="!!editing" @close="editing = null">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-800">Modifier « {{ editing?.name }} »</h3>
                <p class="mt-1 text-xs text-gray-400">Limites & fonctionnalités non modifiables ici.</p>
                <div class="mt-4 space-y-4">
                    <div>
                        <InputLabel value="Prix mensuel *" />
                        <TextInput v-model="form.price_monthly" type="number" step="1" min="0" class="mt-1 block w-full" />
                        <InputError :message="form.errors.price_monthly" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel value="Prix promotionnel (optionnel)" />
                        <TextInput v-model="form.promo_price" type="number" step="1" min="0" class="mt-1 block w-full" />
                        <InputError :message="form.errors.promo_price" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel value="Description courte" />
                        <TextInput v-model="form.short_description" type="text" maxlength="255" class="mt-1 block w-full" />
                        <InputError :message="form.errors.short_description" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel value="Jours d'essai *" />
                        <TextInput v-model="form.trial_days" type="number" step="1" min="0" max="255" class="mt-1 block w-full" />
                        <InputError :message="form.errors.trial_days" class="mt-1" />
                    </div>
                    <label class="flex items-center gap-2 text-sm text-gray-600">
                        <input v-model="form.is_active" type="checkbox" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500" />
                        Forfait actif (proposé à la souscription)
                    </label>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="editing = null">Annuler</SecondaryButton>
                    <PrimaryButton :disabled="form.processing" @click="submit">Enregistrer</PrimaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

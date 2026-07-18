<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdminTabs from '@/Components/AdminTabs.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    coupons: Object,
    stats: Object,
    planCodes: Array,
});

const fmt = (n) => new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(n ?? 0);

const editing = ref(null);
const showModal = ref(false);

const form = useForm({
    code: '',
    description: '',
    type: 'percent',
    value: 0,
    plan_code: null,
    max_redemptions: null,
    per_user_limit: 1,
    min_amount: null,
    starts_at: null,
    expires_at: null,
    is_active: true,
});

const openCreate = () => {
    editing.value = null;
    form.reset();
    form.clearErrors();
    showModal.value = true;
};

const openEdit = (c) => {
    editing.value = c;
    form.clearErrors();
    form.code = c.code;
    form.description = c.description ?? '';
    form.type = c.type;
    form.value = Number(c.value);
    form.plan_code = c.plan_code;
    form.max_redemptions = c.max_redemptions;
    form.per_user_limit = c.per_user_limit;
    form.min_amount = c.min_amount !== null ? Number(c.min_amount) : null;
    form.starts_at = c.starts_at;
    form.expires_at = c.expires_at;
    form.is_active = c.is_active;
    showModal.value = true;
};

const submit = () => {
    const opts = { preserveScroll: true, onSuccess: () => (showModal.value = false) };
    if (editing.value) {
        form.put(route('admin.coupons.update', editing.value.id), opts);
    } else {
        form.post(route('admin.coupons.store'), opts);
    }
};

const toggle = (c) => {
    router.post(route('admin.coupons.toggle', c.id), {}, { preserveScroll: true });
};

const destroy = (c) => {
    if (confirm(`Supprimer le coupon « ${c.code} » ?`)) {
        router.delete(route('admin.coupons.destroy', c.id), { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="Admin — Coupons" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Console Superadmin — <span class="text-gold-600">Coupons & réductions</span>
            </h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <AdminTabs />

                <!-- Stats -->
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-lg bg-white p-4 shadow">
                        <div class="text-xs uppercase tracking-wide text-gray-400">Coupons actifs</div>
                        <div class="mt-1 text-2xl font-bold text-green-600">{{ stats.active }}</div>
                    </div>
                    <div class="rounded-lg bg-white p-4 shadow">
                        <div class="text-xs uppercase tracking-wide text-gray-400">Total coupons</div>
                        <div class="mt-1 text-2xl font-bold text-brand-700">{{ stats.total }}</div>
                    </div>
                    <div class="rounded-lg bg-white p-4 shadow">
                        <div class="text-xs uppercase tracking-wide text-gray-400">Utilisations</div>
                        <div class="mt-1 text-2xl font-bold text-brand-700">{{ stats.redemptions }}</div>
                    </div>
                    <div class="rounded-lg bg-white p-4 shadow">
                        <div class="text-xs uppercase tracking-wide text-gray-400">Remises cumulées</div>
                        <div class="mt-1 text-2xl font-bold text-gold-600">{{ fmt(stats.discounted) }} <span class="text-sm">XOF</span></div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <PrimaryButton @click="openCreate">+ Nouveau coupon</PrimaryButton>
                </div>

                <!-- Tableau -->
                <div class="overflow-x-auto rounded-lg bg-white shadow">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-400">
                            <tr>
                                <th class="px-4 py-3">Code</th>
                                <th class="px-4 py-3">Réduction</th>
                                <th class="px-4 py-3">Forfait</th>
                                <th class="px-4 py-3">Utilisations</th>
                                <th class="px-4 py-3">Validité</th>
                                <th class="px-4 py-3">Statut</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="c in coupons.data" :key="c.id" :class="{ 'opacity-60': !c.is_active }">
                                <td class="px-4 py-3">
                                    <span class="font-mono font-semibold text-gray-800">{{ c.code }}</span>
                                    <div v-if="c.description" class="text-xs text-gray-400">{{ c.description }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="font-semibold text-brand-700">
                                        {{ c.type === 'percent' ? `−${Number(c.value)} %` : `−${fmt(c.value)} XOF` }}
                                    </span>
                                    <div v-if="c.min_amount" class="text-xs text-gray-400">min {{ fmt(c.min_amount) }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <span v-if="c.plan_code" class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-mono text-gray-600">{{ c.plan_code }}</span>
                                    <span v-else class="text-xs text-gray-400">Tous</span>
                                </td>
                                <td class="px-4 py-3">
                                    {{ c.redemptions_count }}<span class="text-gray-400"> / {{ c.max_redemptions ?? '∞' }}</span>
                                    <div class="text-xs text-gray-400">{{ c.per_user_limit }} / utilisateur</div>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-500">
                                    <div>{{ c.starts_at ? `dès ${c.starts_at}` : 'immédiat' }}</div>
                                    <div>{{ c.expires_at ? `→ ${c.expires_at}` : 'sans fin' }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <button
                                        @click="toggle(c)"
                                        class="rounded-full px-2 py-0.5 text-xs font-semibold"
                                        :class="c.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'"
                                    >{{ c.is_active ? 'Actif' : 'Inactif' }}</button>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <button @click="openEdit(c)" class="text-brand-600 hover:underline">Éditer</button>
                                    <button @click="destroy(c)" class="ml-3 text-red-600 hover:underline">Suppr.</button>
                                </td>
                            </tr>
                            <tr v-if="!coupons.data.length">
                                <td colspan="7" class="px-4 py-8 text-center text-gray-400">Aucun coupon pour le moment.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modale création / édition -->
        <Modal :show="showModal" @close="showModal = false">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-800">
                    {{ editing ? `Modifier « ${editing.code} »` : 'Nouveau coupon' }}
                </h3>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <InputLabel value="Code *" />
                        <TextInput v-model="form.code" type="text" maxlength="50" class="mt-1 block w-full font-mono uppercase" />
                        <InputError :message="form.errors.code" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel value="Type *" />
                        <select v-model="form.type" class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500">
                            <option value="percent">Pourcentage (%)</option>
                            <option value="fixed">Montant fixe (XOF)</option>
                        </select>
                        <InputError :message="form.errors.type" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel :value="form.type === 'percent' ? 'Valeur (%) *' : 'Valeur (XOF) *'" />
                        <TextInput v-model="form.value" type="number" step="1" min="0" class="mt-1 block w-full" />
                        <InputError :message="form.errors.value" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel value="Forfait ciblé" />
                        <select v-model="form.plan_code" class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500">
                            <option :value="null">Tous les forfaits</option>
                            <option v-for="code in planCodes" :key="code" :value="code">{{ code }}</option>
                        </select>
                        <InputError :message="form.errors.plan_code" class="mt-1" />
                    </div>
                    <div class="sm:col-span-2">
                        <InputLabel value="Description" />
                        <TextInput v-model="form.description" type="text" maxlength="255" class="mt-1 block w-full" />
                        <InputError :message="form.errors.description" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel value="Montant minimum" />
                        <TextInput v-model="form.min_amount" type="number" step="1" min="0" class="mt-1 block w-full" />
                        <InputError :message="form.errors.min_amount" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel value="Max. utilisations (global)" />
                        <TextInput v-model="form.max_redemptions" type="number" step="1" min="1" class="mt-1 block w-full" />
                        <InputError :message="form.errors.max_redemptions" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel value="Limite par utilisateur" />
                        <TextInput v-model="form.per_user_limit" type="number" step="1" min="1" max="255" class="mt-1 block w-full" />
                        <InputError :message="form.errors.per_user_limit" class="mt-1" />
                    </div>
                    <div class="flex items-end">
                        <label class="flex items-center gap-2 text-sm text-gray-600">
                            <input v-model="form.is_active" type="checkbox" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500" />
                            Coupon actif
                        </label>
                    </div>
                    <div>
                        <InputLabel value="Début de validité" />
                        <TextInput v-model="form.starts_at" type="date" class="mt-1 block w-full" />
                        <InputError :message="form.errors.starts_at" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel value="Fin de validité" />
                        <TextInput v-model="form.expires_at" type="date" class="mt-1 block w-full" />
                        <InputError :message="form.errors.expires_at" class="mt-1" />
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="showModal = false">Annuler</SecondaryButton>
                    <PrimaryButton :disabled="form.processing" @click="submit">Enregistrer</PrimaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

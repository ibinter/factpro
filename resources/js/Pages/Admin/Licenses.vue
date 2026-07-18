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
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    licenses: Object,
    stats: Object,
    plans: Array,
    statuses: Array,
    filters: Object,
});

const statusLabels = {
    trial: 'Essai', pending: 'En attente', provisional: 'Provisoire', active: 'Active',
    grace_period: 'Période de grâce', suspended: 'Suspendue', expired: 'Expirée',
    terminated: 'Résiliée', revoked: 'Révoquée',
};
const statusColors = {
    trial: 'bg-blue-100 text-blue-700', pending: 'bg-amber-100 text-amber-700',
    provisional: 'bg-indigo-100 text-indigo-700', active: 'bg-green-100 text-green-700',
    grace_period: 'bg-teal-100 text-teal-700', suspended: 'bg-orange-100 text-orange-700',
    expired: 'bg-gray-100 text-gray-500', terminated: 'bg-gray-200 text-gray-600',
    revoked: 'bg-red-100 text-red-700',
};

// Filtres
const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');
const planCode = ref(props.filters.plan_code ?? '');

let timer = null;
watch([search, status, planCode], () => {
    clearTimeout(timer);
    timer = setTimeout(() => {
        router.get(route('admin.licenses'), {
            search: search.value || undefined,
            status: status.value || undefined,
            plan_code: planCode.value || undefined,
        }, { preserveState: true, replace: true, preserveScroll: true });
    }, 300);
});

// Modales d'action
const extending = ref(null);
const suspending = ref(null);
const reactivating = ref(null);
const revoking = ref(null);

const extendForm = useForm({ months: 1, reason: '' });
const suspendForm = useForm({ reason: '' });
const reactivateForm = useForm({ reason: '' });
const revokeForm = useForm({ reason: '', confirmation: false });

const openExtend = (l) => { extending.value = l; extendForm.reset(); extendForm.clearErrors(); };
const openSuspend = (l) => { suspending.value = l; suspendForm.reset(); suspendForm.clearErrors(); };
const openReactivate = (l) => { reactivating.value = l; reactivateForm.reset(); reactivateForm.clearErrors(); };
const openRevoke = (l) => { revoking.value = l; revokeForm.reset(); revokeForm.clearErrors(); };

const submitExtend = () => extendForm.post(route('admin.licenses.extend', extending.value.id), {
    preserveScroll: true, onSuccess: () => (extending.value = null),
});
const submitSuspend = () => suspendForm.post(route('admin.licenses.suspend', suspending.value.id), {
    preserveScroll: true, onSuccess: () => (suspending.value = null),
});
const submitReactivate = () => reactivateForm.post(route('admin.licenses.reactivate', reactivating.value.id), {
    preserveScroll: true, onSuccess: () => (reactivating.value = null),
});
const submitRevoke = () => revokeForm.post(route('admin.licenses.revoke', revoking.value.id), {
    preserveScroll: true, onSuccess: () => (revoking.value = null),
});
</script>

<template>
    <Head title="Admin — Licences" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Console Superadmin — <span class="text-gold-600">Licences</span>
            </h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <AdminTabs />

                <!-- Stats -->
                <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                    <div class="rounded-lg bg-white p-5 shadow">
                        <div class="text-xs uppercase tracking-wide text-gray-500">Actives</div>
                        <div class="mt-1 text-2xl font-bold text-green-600">{{ stats.active }}</div>
                    </div>
                    <div class="rounded-lg bg-white p-5 shadow">
                        <div class="text-xs uppercase tracking-wide text-gray-500">Essais</div>
                        <div class="mt-1 text-2xl font-bold text-blue-600">{{ stats.trials }}</div>
                    </div>
                    <div class="rounded-lg bg-white p-5 shadow">
                        <div class="text-xs uppercase tracking-wide text-gray-500">Suspendues</div>
                        <div class="mt-1 text-2xl font-bold text-orange-600">{{ stats.suspended }}</div>
                    </div>
                    <div class="rounded-lg bg-white p-5 shadow">
                        <div class="text-xs uppercase tracking-wide text-gray-500">Expirent sous 7j</div>
                        <div class="mt-1 text-2xl font-bold text-amber-600">{{ stats.expiring_7d }}</div>
                    </div>
                </div>

                <!-- Filtres -->
                <div class="flex flex-wrap gap-3 rounded-lg bg-white p-4 shadow">
                    <TextInput v-model="search" type="text" placeholder="Rechercher (clé, nom, email)…" class="min-w-64 flex-1" />
                    <select v-model="status" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                        <option value="">Tous les statuts</option>
                        <option v-for="s in statuses" :key="s" :value="s">{{ statusLabels[s] ?? s }}</option>
                    </select>
                    <select v-model="planCode" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                        <option value="">Tous les forfaits</option>
                        <option v-for="p in plans" :key="p.id" :value="p.code">{{ p.name }}</option>
                    </select>
                </div>

                <!-- Table -->
                <div class="overflow-hidden rounded-lg bg-white shadow">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-6 py-3">Licence</th>
                                <th class="px-6 py-3">Client</th>
                                <th class="px-6 py-3">Forfait</th>
                                <th class="px-6 py-3">Statut</th>
                                <th class="px-6 py-3 text-right">Échéance</th>
                                <th class="px-6 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="l in licenses.data" :key="l.id" class="hover:bg-gray-50">
                                <td class="px-6 py-3">
                                    <div class="font-mono text-xs font-bold text-brand-700">{{ l.license_key }}</div>
                                    <div class="text-xs text-gray-400">{{ l.type }}</div>
                                </td>
                                <td class="px-6 py-3">
                                    <div class="font-semibold text-gray-800">{{ l.user?.name ?? '—' }}</div>
                                    <div class="text-xs text-gray-400">{{ l.user?.email }}</div>
                                </td>
                                <td class="px-6 py-3 text-gray-500">{{ l.plan?.name ?? '—' }}</td>
                                <td class="px-6 py-3">
                                    <span class="rounded-full px-2 py-0.5 text-xs font-semibold" :class="statusColors[l.status]">
                                        {{ statusLabels[l.status] ?? l.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <div class="font-semibold text-gray-800">{{ l.ends_at ?? '—' }}</div>
                                    <div v-if="l.ends_at" class="text-xs text-gray-400">J-{{ l.days_remaining }}</div>
                                </td>
                                <td class="px-6 py-3">
                                    <div class="flex flex-wrap justify-end gap-1">
                                        <button
                                            v-if="l.status !== 'revoked'"
                                            @click="openExtend(l)"
                                            class="rounded-md bg-brand-600 px-2.5 py-1 text-xs font-semibold text-white hover:bg-brand-700"
                                        >Prolonger</button>
                                        <button
                                            v-if="!['revoked', 'suspended'].includes(l.status)"
                                            @click="openSuspend(l)"
                                            class="rounded-md bg-orange-500 px-2.5 py-1 text-xs font-semibold text-white hover:bg-orange-600"
                                        >Suspendre</button>
                                        <button
                                            v-if="l.status === 'suspended'"
                                            @click="openReactivate(l)"
                                            class="rounded-md bg-green-600 px-2.5 py-1 text-xs font-semibold text-white hover:bg-green-700"
                                        >Réactiver</button>
                                        <button
                                            v-if="l.status !== 'revoked'"
                                            @click="openRevoke(l)"
                                            class="rounded-md bg-red-600 px-2.5 py-1 text-xs font-semibold text-white hover:bg-red-700"
                                        >Révoquer</button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!licenses.data.length">
                                <td colspan="6" class="px-6 py-10 text-center text-gray-400">Aucune licence trouvée.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="licenses.links?.length > 3" class="flex flex-wrap gap-1">
                    <template v-for="link in licenses.links" :key="link.label">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            preserve-scroll
                            class="rounded-md px-3 py-1.5 text-sm"
                            :class="link.active ? 'bg-brand-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-100'"
                            v-html="link.label"
                        />
                        <span
                            v-else
                            class="rounded-md px-3 py-1.5 text-sm text-gray-300"
                            v-html="link.label"
                        />
                    </template>
                </div>
            </div>
        </div>

        <!-- Modale prolonger -->
        <Modal :show="!!extending" @close="extending = null">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-800">Prolonger la licence</h3>
                <p class="mt-1 text-sm text-gray-500">{{ extending?.user?.name }} — {{ extending?.license_key }}</p>
                <div class="mt-4 space-y-4">
                    <div>
                        <InputLabel value="Nombre de mois * (1 à 24)" />
                        <TextInput v-model="extendForm.months" type="number" min="1" max="24" class="mt-1 block w-full" />
                        <InputError :message="extendForm.errors.months" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel value="Motif * (obligatoire)" />
                        <textarea v-model="extendForm.reason" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500" placeholder="Ex : renouvellement annuel réglé par virement…"></textarea>
                        <InputError :message="extendForm.errors.reason" class="mt-1" />
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="extending = null">Annuler</SecondaryButton>
                    <PrimaryButton :disabled="extendForm.processing" @click="submitExtend">Prolonger</PrimaryButton>
                </div>
            </div>
        </Modal>

        <!-- Modale suspendre -->
        <Modal :show="!!suspending" @close="suspending = null">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-800">Suspendre la licence</h3>
                <p class="mt-1 text-sm text-gray-500">{{ suspending?.user?.name }} — {{ suspending?.license_key }}</p>
                <div class="mt-4">
                    <InputLabel value="Motif * (obligatoire)" />
                    <textarea v-model="suspendForm.reason" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500" placeholder="Ex : impayé, usage frauduleux…"></textarea>
                    <InputError :message="suspendForm.errors.reason" class="mt-1" />
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="suspending = null">Annuler</SecondaryButton>
                    <DangerButton :disabled="suspendForm.processing" @click="submitSuspend">Suspendre</DangerButton>
                </div>
            </div>
        </Modal>

        <!-- Modale réactiver -->
        <Modal :show="!!reactivating" @close="reactivating = null">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-800">Réactiver la licence</h3>
                <p class="mt-1 text-sm text-gray-500">{{ reactivating?.user?.name }} — {{ reactivating?.license_key }}</p>
                <div class="mt-4">
                    <InputLabel value="Motif * (obligatoire)" />
                    <textarea v-model="reactivateForm.reason" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500" placeholder="Ex : régularisation du paiement…"></textarea>
                    <InputError :message="reactivateForm.errors.reason" class="mt-1" />
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="reactivating = null">Annuler</SecondaryButton>
                    <PrimaryButton :disabled="reactivateForm.processing" @click="submitReactivate">Réactiver</PrimaryButton>
                </div>
            </div>
        </Modal>

        <!-- Modale révoquer -->
        <Modal :show="!!revoking" @close="revoking = null">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-red-700">Révoquer définitivement</h3>
                <p class="mt-1 text-sm text-gray-500">
                    {{ revoking?.user?.name }} — {{ revoking?.license_key }}.
                    Action <b>irréversible</b>.
                </p>
                <div class="mt-4 space-y-4">
                    <div>
                        <InputLabel value="Motif * (obligatoire)" />
                        <textarea v-model="revokeForm.reason" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500" placeholder="Ex : fraude avérée, résiliation contractuelle…"></textarea>
                        <InputError :message="revokeForm.errors.reason" class="mt-1" />
                    </div>
                    <label class="flex items-start gap-2 text-sm text-gray-600">
                        <input v-model="revokeForm.confirmation" type="checkbox" class="mt-0.5 rounded border-gray-300 text-red-600 focus:ring-red-500" />
                        Je confirme la révocation définitive de cette licence.
                    </label>
                    <InputError :message="revokeForm.errors.confirmation" class="mt-1" />
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="revoking = null">Annuler</SecondaryButton>
                    <DangerButton :disabled="revokeForm.processing" @click="submitRevoke">Révoquer</DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

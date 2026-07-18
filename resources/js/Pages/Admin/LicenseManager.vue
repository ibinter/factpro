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
import { ref, watch, computed } from 'vue';

const props = defineProps({
    licenses: Object,
    provisional: Array,
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

const activeTab = ref('all');

// Filtres
const search = ref(props.filters.search ?? '');
const statusFilter = ref(props.filters.status ?? '');
const planCode = ref(props.filters.plan_code ?? '');
const expiringFilter = ref(props.filters.expiring ?? '');

let timer = null;
watch([search, statusFilter, planCode, expiringFilter], () => {
    clearTimeout(timer);
    timer = setTimeout(() => {
        router.get(route('admin.license-manager'), {
            search: search.value || undefined,
            status: statusFilter.value || undefined,
            plan_code: planCode.value || undefined,
            expiring: expiringFilter.value || undefined,
        }, { preserveState: true, replace: true, preserveScroll: true });
    }, 300);
});

// Countdown pour licences provisoires
const countdownLabel = (isoDate) => {
    if (!isoDate) return '—';
    const diff = new Date(isoDate) - new Date();
    if (diff <= 0) return 'Expirée';
    const hours = Math.floor(diff / 3600000);
    if (hours < 24) return `${hours}h restantes`;
    const days = Math.floor(hours / 24);
    return `${days}j restants`;
};

const countdownColor = (isoDate) => {
    if (!isoDate) return 'text-gray-400';
    const diff = new Date(isoDate) - new Date();
    if (diff <= 0) return 'text-red-600';
    if (diff < 86400000 * 2) return 'text-red-500';
    if (diff < 86400000 * 7) return 'text-amber-600';
    return 'text-green-600';
};

// Modales
const extending = ref(null);
const suspending = ref(null);
const reactivating = ref(null);
const revoking = ref(null);
const confirming = ref(null);

const extendForm = useForm({ months: 1, reason: '' });
const suspendForm = useForm({ reason: '' });
const reactivateForm = useForm({ reason: '' });
const revokeForm = useForm({ reason: '', confirmation: false });
const confirmForm = useForm({ reason: '' });

const submitExtend = () => {
    extendForm.post(route('admin.licenses.extend', extending.value.id), {
        preserveScroll: true,
        onSuccess: () => { extending.value = null; extendForm.reset(); },
    });
};
const submitSuspend = () => {
    suspendForm.post(route('admin.licenses.suspend', suspending.value.id), {
        preserveScroll: true,
        onSuccess: () => { suspending.value = null; suspendForm.reset(); },
    });
};
const submitReactivate = () => {
    reactivateForm.post(route('admin.licenses.reactivate', reactivating.value.id), {
        preserveScroll: true,
        onSuccess: () => { reactivating.value = null; reactivateForm.reset(); },
    });
};
const submitRevoke = () => {
    revokeForm.post(route('admin.licenses.revoke', revoking.value.id), {
        preserveScroll: true,
        onSuccess: () => { revoking.value = null; revokeForm.reset(); },
    });
};
const submitConfirm = () => {
    confirmForm.post(route('admin.licenses.confirm-provisional', confirming.value.id), {
        preserveScroll: true,
        onSuccess: () => { confirming.value = null; confirmForm.reset(); },
    });
};
</script>

<template>
    <Head title="Admin — Gestionnaire de licences" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Console Superadmin — <span class="text-indigo-600">Gestionnaire de licences</span>
            </h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <AdminTabs />

                <!-- Stats -->
                <div class="grid grid-cols-2 gap-4 lg:grid-cols-5">
                    <div class="rounded-lg bg-white p-4 shadow text-center">
                        <div class="text-xs uppercase text-gray-500">Actives</div>
                        <div class="text-2xl font-bold text-green-600">{{ stats.active }}</div>
                    </div>
                    <div class="rounded-lg bg-white p-4 shadow text-center">
                        <div class="text-xs uppercase text-gray-500">Essais</div>
                        <div class="text-2xl font-bold text-blue-600">{{ stats.trials }}</div>
                    </div>
                    <div class="rounded-lg bg-white p-4 shadow text-center">
                        <div class="text-xs uppercase text-gray-500">Provisoires</div>
                        <div class="text-2xl font-bold text-indigo-600">{{ stats.provisional }}</div>
                    </div>
                    <div class="rounded-lg bg-white p-4 shadow text-center">
                        <div class="text-xs uppercase text-gray-500">Suspendues</div>
                        <div class="text-2xl font-bold text-orange-600">{{ stats.suspended }}</div>
                    </div>
                    <div class="rounded-lg bg-white p-4 shadow text-center">
                        <div class="text-xs uppercase text-gray-500">Exp. 30j</div>
                        <div class="text-2xl font-bold text-red-600">{{ stats.expiring_30d }}</div>
                    </div>
                </div>

                <!-- Onglets -->
                <div class="flex gap-2">
                    <button @click="activeTab = 'all'"
                        :class="activeTab === 'all' ? 'bg-brand-600 text-white shadow' : 'bg-white text-gray-600 hover:bg-brand-50'"
                        class="rounded-full px-4 py-2 text-sm font-semibold transition">
                        Toutes les licences
                    </button>
                    <button @click="activeTab = 'provisional'"
                        :class="activeTab === 'provisional' ? 'bg-indigo-600 text-white shadow' : 'bg-white text-gray-600 hover:bg-indigo-50'"
                        class="rounded-full px-4 py-2 text-sm font-semibold transition">
                        Licences provisoires ({{ provisional.length }})
                    </button>
                </div>

                <!-- Onglet : Toutes -->
                <div v-if="activeTab === 'all'" class="space-y-4">
                    <!-- Filtres -->
                    <div class="rounded-lg bg-white p-4 shadow">
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                            <input v-model="search" type="text" placeholder="Clé / email / nom…"
                                class="rounded-md border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500" />
                            <select v-model="statusFilter" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                                <option value="">Tous statuts</option>
                                <option v-for="s in statuses" :key="s" :value="s">{{ statusLabels[s] ?? s }}</option>
                            </select>
                            <select v-model="planCode" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                                <option value="">Tous forfaits</option>
                                <option v-for="p in plans" :key="p.code" :value="p.code">{{ p.name }}</option>
                            </select>
                            <select v-model="expiringFilter" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                                <option value="">Toutes expirations</option>
                                <option value="7">Expire sous 7j</option>
                                <option value="30">Expire sous 30j</option>
                                <option value="90">Expire sous 90j</option>
                            </select>
                        </div>
                    </div>

                    <!-- Tableau licences -->
                    <div class="overflow-x-auto rounded-lg bg-white shadow">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 text-xs uppercase text-gray-400">
                                <tr>
                                    <th class="px-4 py-3 text-left">Clé</th>
                                    <th class="px-4 py-3 text-left">Client</th>
                                    <th class="px-4 py-3 text-left">Forfait</th>
                                    <th class="px-4 py-3 text-left">Statut</th>
                                    <th class="px-4 py-3 text-left">Début</th>
                                    <th class="px-4 py-3 text-left">Fin</th>
                                    <th class="px-4 py-3 text-left">Restant</th>
                                    <th class="px-4 py-3 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="lic in licenses.data" :key="lic.id" class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-mono text-xs text-brand-700">{{ lic.license_key }}</td>
                                    <td class="px-4 py-3">
                                        <div class="font-medium">{{ lic.user?.name }}</div>
                                        <div class="text-xs text-gray-400">{{ lic.user?.email }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="rounded bg-gray-100 px-2 py-0.5 text-xs font-semibold">{{ lic.plan?.name }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="rounded-full px-2 py-0.5 text-xs font-bold" :class="statusColors[lic.status]">
                                            {{ statusLabels[lic.status] ?? lic.status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-xs text-gray-500">{{ lic.starts_at ?? '—' }}</td>
                                    <td class="px-4 py-3 text-xs text-gray-500">{{ lic.ends_at ?? '—' }}</td>
                                    <td class="px-4 py-3 text-xs" :class="lic.days_remaining !== null && lic.days_remaining < 7 ? 'text-red-600 font-bold' : 'text-gray-500'">
                                        {{ lic.days_remaining !== null ? `${lic.days_remaining}j` : '—' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-center gap-1 flex-wrap">
                                            <button @click="extending = lic" title="Prolonger"
                                                class="rounded bg-blue-50 px-2 py-1 text-xs font-semibold text-blue-700 hover:bg-blue-100">
                                                +Mois
                                            </button>
                                            <button v-if="lic.status !== 'suspended' && lic.status !== 'revoked'"
                                                @click="suspending = lic" title="Suspendre"
                                                class="rounded bg-orange-50 px-2 py-1 text-xs font-semibold text-orange-700 hover:bg-orange-100">
                                                Suspendre
                                            </button>
                                            <button v-if="lic.status === 'suspended'"
                                                @click="reactivating = lic" title="Réactiver"
                                                class="rounded bg-green-50 px-2 py-1 text-xs font-semibold text-green-700 hover:bg-green-100">
                                                Réactiver
                                            </button>
                                            <button v-if="lic.status === 'provisional'"
                                                @click="confirming = lic" title="Confirmer provisoire"
                                                class="rounded bg-indigo-50 px-2 py-1 text-xs font-semibold text-indigo-700 hover:bg-indigo-100">
                                                Confirmer
                                            </button>
                                            <button v-if="lic.status !== 'revoked'"
                                                @click="revoking = lic" title="Révoquer"
                                                class="rounded bg-red-50 px-2 py-1 text-xs font-semibold text-red-700 hover:bg-red-100">
                                                Révoquer
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!licenses.data.length">
                                    <td colspan="8" class="px-4 py-10 text-center text-gray-400">Aucune licence trouvée.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="licenses.last_page > 1" class="flex justify-center gap-2">
                        <Link
                            v-for="link in licenses.links"
                            :key="link.label"
                            :href="link.url ?? '#'"
                            v-html="link.label"
                            class="rounded px-3 py-1 text-sm"
                            :class="link.active ? 'bg-brand-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-100'"
                        />
                    </div>
                </div>

                <!-- Onglet : Licences provisoires -->
                <div v-if="activeTab === 'provisional'">
                    <div class="overflow-x-auto rounded-lg bg-white shadow">
                        <table class="w-full text-sm">
                            <thead class="bg-indigo-50 text-xs uppercase text-indigo-400">
                                <tr>
                                    <th class="px-4 py-3 text-left">Clé</th>
                                    <th class="px-4 py-3 text-left">Client</th>
                                    <th class="px-4 py-3 text-left">Forfait</th>
                                    <th class="px-4 py-3 text-left">Expiration</th>
                                    <th class="px-4 py-3 text-left">Countdown</th>
                                    <th class="px-4 py-3 text-left">Motif</th>
                                    <th class="px-4 py-3 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="lic in provisional" :key="lic.id" class="hover:bg-indigo-50/30">
                                    <td class="px-4 py-3 font-mono text-xs text-indigo-700">{{ lic.license_key }}</td>
                                    <td class="px-4 py-3">
                                        <div class="font-medium">{{ lic.user?.name }}</div>
                                        <div class="text-xs text-gray-400">{{ lic.user?.email }}</div>
                                    </td>
                                    <td class="px-4 py-3">{{ lic.plan?.name }}</td>
                                    <td class="px-4 py-3 text-xs">{{ lic.ends_at }}</td>
                                    <td class="px-4 py-3 text-xs font-bold" :class="countdownColor(lic.ends_at_iso)">
                                        {{ countdownLabel(lic.ends_at_iso) }}
                                    </td>
                                    <td class="px-4 py-3 text-xs text-gray-500 max-w-xs truncate">{{ lic.motif ?? '—' }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <button @click="confirming = lic"
                                            class="rounded bg-indigo-600 px-3 py-1 text-xs font-semibold text-white hover:bg-indigo-700">
                                            ✓ Confirmer définitive
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="!provisional.length">
                                    <td colspan="7" class="px-4 py-10 text-center text-gray-400">Aucune licence provisoire active.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modale prolonger -->
        <Modal :show="!!extending" @close="extending = null">
            <div class="p-6 space-y-4">
                <h3 class="text-lg font-semibold">Prolonger la licence</h3>
                <p class="text-sm text-gray-500">{{ extending?.license_key }} — {{ extending?.user?.name }}</p>
                <div>
                    <InputLabel value="Durée (mois) *" />
                    <TextInput v-model="extendForm.months" type="number" min="1" max="24" class="mt-1 block w-full" />
                    <InputError :message="extendForm.errors.months" class="mt-1" />
                </div>
                <div>
                    <InputLabel value="Motif *" />
                    <textarea v-model="extendForm.reason" rows="2" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500"></textarea>
                    <InputError :message="extendForm.errors.reason" class="mt-1" />
                </div>
                <div class="flex justify-end gap-3">
                    <SecondaryButton @click="extending = null">Annuler</SecondaryButton>
                    <PrimaryButton :disabled="extendForm.processing" @click="submitExtend">Prolonger</PrimaryButton>
                </div>
            </div>
        </Modal>

        <!-- Modale suspendre -->
        <Modal :show="!!suspending" @close="suspending = null">
            <div class="p-6 space-y-4">
                <h3 class="text-lg font-semibold">Suspendre la licence</h3>
                <div>
                    <InputLabel value="Motif *" />
                    <textarea v-model="suspendForm.reason" rows="2" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500"></textarea>
                    <InputError :message="suspendForm.errors.reason" class="mt-1" />
                </div>
                <div class="flex justify-end gap-3">
                    <SecondaryButton @click="suspending = null">Annuler</SecondaryButton>
                    <DangerButton :disabled="suspendForm.processing" @click="submitSuspend">Suspendre</DangerButton>
                </div>
            </div>
        </Modal>

        <!-- Modale réactiver -->
        <Modal :show="!!reactivating" @close="reactivating = null">
            <div class="p-6 space-y-4">
                <h3 class="text-lg font-semibold">Réactiver la licence</h3>
                <div>
                    <InputLabel value="Motif *" />
                    <textarea v-model="reactivateForm.reason" rows="2" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500"></textarea>
                    <InputError :message="reactivateForm.errors.reason" class="mt-1" />
                </div>
                <div class="flex justify-end gap-3">
                    <SecondaryButton @click="reactivating = null">Annuler</SecondaryButton>
                    <PrimaryButton :disabled="reactivateForm.processing" @click="submitReactivate">Réactiver</PrimaryButton>
                </div>
            </div>
        </Modal>

        <!-- Modale révoquer -->
        <Modal :show="!!revoking" @close="revoking = null">
            <div class="p-6 space-y-4">
                <h3 class="text-lg font-semibold text-red-700">Révoquer définitivement</h3>
                <p class="text-sm text-red-600">Action irréversible. La licence sera désactivée définitivement.</p>
                <div>
                    <InputLabel value="Motif *" />
                    <textarea v-model="revokeForm.reason" rows="2" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500"></textarea>
                    <InputError :message="revokeForm.errors.reason" class="mt-1" />
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" v-model="revokeForm.confirmation" id="revoke-confirm" class="rounded border-gray-300" />
                    <label for="revoke-confirm" class="text-sm text-red-600">Je confirme cette révocation définitive</label>
                </div>
                <div class="flex justify-end gap-3">
                    <SecondaryButton @click="revoking = null">Annuler</SecondaryButton>
                    <DangerButton :disabled="revokeForm.processing || !revokeForm.confirmation" @click="submitRevoke">Révoquer</DangerButton>
                </div>
            </div>
        </Modal>

        <!-- Modale confirmer provisoire -->
        <Modal :show="!!confirming" @close="confirming = null">
            <div class="p-6 space-y-4">
                <h3 class="text-lg font-semibold text-indigo-700">Convertir en licence définitive</h3>
                <p class="text-sm text-gray-500">La licence provisoire de <b>{{ confirming?.user?.name }}</b> sera convertie en licence active.</p>
                <div>
                    <InputLabel value="Motif de confirmation *" />
                    <textarea v-model="confirmForm.reason" rows="2" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                        placeholder="Ex : virement reçu et vérifié, ref…"></textarea>
                    <InputError :message="confirmForm.errors.reason" class="mt-1" />
                </div>
                <div class="flex justify-end gap-3">
                    <SecondaryButton @click="confirming = null">Annuler</SecondaryButton>
                    <PrimaryButton :disabled="confirmForm.processing" @click="submitConfirm">Confirmer</PrimaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

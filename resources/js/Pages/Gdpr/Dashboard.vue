<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    stats: Object,
    requests: Array,
});

const showNewRequest = ref(false);
const newForm = ref({
    type: 'access',
    subject_name: '',
    subject_email: '',
    subject_type: '',
    description: '',
});
const submitting = ref(false);

const requestTypes = [
    { value: 'access', label: 'Accès aux données (Art. 15)' },
    { value: 'rectification', label: 'Rectification (Art. 16)' },
    { value: 'deletion', label: 'Effacement (Art. 17)' },
    { value: 'portability', label: 'Portabilité (Art. 20)' },
    { value: 'opposition', label: 'Opposition (Art. 21)' },
];

const statusLabels = {
    pending: { label: 'En attente', cls: 'bg-yellow-100 text-yellow-700' },
    in_progress: { label: 'En cours', cls: 'bg-blue-100 text-blue-700' },
    completed: { label: 'Traité', cls: 'bg-green-100 text-green-700' },
    rejected: { label: 'Refusé', cls: 'bg-red-100 text-red-700' },
};

const scoreColor = computed(() => {
    const s = props.stats.compliance_score;
    if (s >= 80) return 'text-green-600';
    if (s >= 50) return 'text-yellow-600';
    return 'text-red-600';
});

const scoreBarColor = computed(() => {
    const s = props.stats.compliance_score;
    if (s >= 80) return 'bg-green-500';
    if (s >= 50) return 'bg-yellow-500';
    return 'bg-red-500';
});

function submitRequest() {
    submitting.value = true;
    router.post(route('gdpr.requests.store'), newForm.value, {
        onSuccess: () => {
            showNewRequest.value = false;
            newForm.value = { type: 'access', subject_name: '', subject_email: '', subject_type: '', description: '' };
        },
        onFinish: () => { submitting.value = false; },
    });
}

function downloadReport() {
    window.open(route('gdpr.report'), '_blank');
}

function updateStatus(request, status) {
    router.put(route('gdpr.requests.update', request.id), { status }, {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Conformité RGPD" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Conformité RGPD</h2>
                </div>
                <div class="flex gap-3">
                    <button @click="showNewRequest = true"
                        class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-sm font-medium transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Nouvelle demande
                    </button>
                    <button @click="downloadReport"
                        class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-xl text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition flex items-center gap-2">
                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                        </svg>
                        Rapport PDF
                    </button>
                </div>
            </div>
        </template>

        <div class="py-8 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            <!-- Score de conformité -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-gray-900 dark:text-white">Score de conformité RGPD</h3>
                    <span :class="['text-3xl font-bold', scoreColor]">{{ stats.compliance_score }}/100</span>
                </div>
                <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-3">
                    <div :class="['h-3 rounded-full transition-all duration-700', scoreBarColor]"
                        :style="{ width: stats.compliance_score + '%' }"></div>
                </div>
                <p class="text-xs text-gray-400 mt-2">
                    Basé sur les consentements actifs et le respect des délais de traitement des demandes.
                </p>
            </div>

            <!-- KPI cards -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 text-center">
                    <div class="text-2xl font-bold text-purple-600">{{ stats.active_consents }}</div>
                    <div class="text-xs text-gray-500 mt-1">Consentements actifs</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 text-center">
                    <div class="text-2xl font-bold text-gray-500">{{ stats.revoked_consents }}</div>
                    <div class="text-xs text-gray-500 mt-1">Révoqués</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 text-center">
                    <div class="text-2xl font-bold text-blue-600">{{ stats.pending_requests }}</div>
                    <div class="text-xs text-gray-500 mt-1">Demandes en cours</div>
                </div>
                <div :class="['rounded-xl shadow-sm border p-4 text-center', stats.overdue_requests > 0
                    ? 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800'
                    : 'bg-white dark:bg-gray-800 border-gray-100 dark:border-gray-700']">
                    <div :class="['text-2xl font-bold', stats.overdue_requests > 0 ? 'text-red-600' : 'text-green-600']">
                        {{ stats.overdue_requests }}
                    </div>
                    <div class="text-xs text-gray-500 mt-1">Délais dépassés</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 text-center">
                    <div class="text-2xl font-bold text-gray-700 dark:text-gray-200">{{ stats.total_consents }}</div>
                    <div class="text-xs text-gray-500 mt-1">Total consentements</div>
                </div>
            </div>

            <!-- Alerte délais dépassés -->
            <div v-if="stats.overdue_requests > 0"
                class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4 flex items-center gap-3">
                <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <p class="font-semibold text-red-800 dark:text-red-200 text-sm">
                        {{ stats.overdue_requests }} demande(s) dépassent le délai légal de 30 jours !
                    </p>
                    <p class="text-xs text-red-600 dark:text-red-400 mt-0.5">
                        Conformément au RGPD Art. 12.3, les demandes doivent être traitées dans un délai d'un mois.
                    </p>
                </div>
            </div>

            <!-- Tableau des demandes -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-900 dark:text-white">Demandes d'exercice des droits</h3>
                    <a :href="route('gdpr.requests')" class="text-sm text-purple-600 hover:text-purple-800 font-medium">
                        Voir tout →
                    </a>
                </div>

                <div v-if="!requests?.length" class="py-12 text-center text-gray-400">
                    <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="text-sm">Aucune demande enregistrée</p>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs text-gray-500 border-b dark:border-gray-700">
                                <th class="px-6 py-3 font-medium">Type</th>
                                <th class="px-6 py-3 font-medium">Sujet</th>
                                <th class="px-6 py-3 font-medium">Reçue le</th>
                                <th class="px-6 py-3 font-medium">Délai</th>
                                <th class="px-6 py-3 font-medium">Statut</th>
                                <th class="px-6 py-3 font-medium">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="req in requests" :key="req.id"
                                :class="['border-b dark:border-gray-700 last:border-0', req.is_overdue ? 'bg-red-50 dark:bg-red-900/10' : '']">
                                <td class="px-6 py-3">
                                    <span class="bg-purple-50 dark:bg-purple-900/20 text-purple-700 dark:text-purple-300 px-2 py-0.5 rounded text-xs font-mono capitalize">
                                        {{ req.type }}
                                    </span>
                                </td>
                                <td class="px-6 py-3">
                                    <div class="font-medium text-gray-900 dark:text-gray-100">{{ req.subject_name }}</div>
                                    <div class="text-xs text-gray-500">{{ req.subject_email }}</div>
                                </td>
                                <td class="px-6 py-3 text-gray-500 text-xs">
                                    {{ req.received_at ? new Date(req.received_at).toLocaleDateString('fr-FR') : '—' }}
                                </td>
                                <td class="px-6 py-3">
                                    <span v-if="req.is_overdue" class="text-red-600 font-semibold text-xs">
                                        Dépassé !
                                    </span>
                                    <span v-else-if="req.days_remaining <= 5" class="text-orange-500 text-xs font-medium">
                                        {{ req.days_remaining }}j restants
                                    </span>
                                    <span v-else class="text-gray-500 text-xs">
                                        {{ req.days_remaining }}j restants
                                    </span>
                                </td>
                                <td class="px-6 py-3">
                                    <span :class="['px-2 py-0.5 rounded-full text-xs font-medium', statusLabels[req.status]?.cls]">
                                        {{ statusLabels[req.status]?.label }}
                                    </span>
                                </td>
                                <td class="px-6 py-3">
                                    <select v-if="req.status !== 'completed' && req.status !== 'rejected'"
                                        @change="e => updateStatus(req, e.target.value)"
                                        :value="req.status"
                                        class="text-xs border border-gray-200 dark:border-gray-600 rounded px-2 py-1 bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-300">
                                        <option value="pending">En attente</option>
                                        <option value="in_progress">En cours</option>
                                        <option value="completed">Traité</option>
                                        <option value="rejected">Refusé</option>
                                    </select>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Modal nouvelle demande -->
        <Teleport to="body">
            <div v-if="showNewRequest" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showNewRequest = false"></div>
                <div class="relative bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-md p-6">
                    <button @click="showNewRequest = false" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Nouvelle demande RGPD</h3>
                    <form @submit.prevent="submitRequest" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type de demande *</label>
                            <select v-model="newForm.type" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2 text-sm">
                                <option v-for="t in requestTypes" :key="t.value" :value="t.value">{{ t.label }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nom du demandeur *</label>
                            <input v-model="newForm.subject_name" required type="text"
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2 text-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email *</label>
                            <input v-model="newForm.subject_email" required type="email"
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2 text-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type de sujet</label>
                            <select v-model="newForm.subject_type" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2 text-sm">
                                <option value="">Non précisé</option>
                                <option value="customer">Client</option>
                                <option value="employee">Employé</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                            <textarea v-model="newForm.description" rows="3"
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2 text-sm resize-none"></textarea>
                        </div>
                        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3 text-xs text-blue-700 dark:text-blue-300">
                            Le délai légal de traitement est de <strong>30 jours</strong> (RGPD Art. 12.3).
                            La date limite sera automatiquement fixée.
                        </div>
                        <div class="flex gap-3 pt-2">
                            <button type="button" @click="showNewRequest = false"
                                class="flex-1 px-4 py-2.5 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-xl text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                                Annuler
                            </button>
                            <button type="submit" :disabled="submitting"
                                class="flex-1 px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-sm font-medium transition disabled:opacity-60">
                                {{ submitting ? 'Enregistrement…' : 'Créer la demande' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>

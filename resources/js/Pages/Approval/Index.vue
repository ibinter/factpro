<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    hasAccess: Boolean,
    pendingSteps: Array,
    workflows: Array,
});

const activeTab = ref('pending');

// Approval modal
const showApproveModal = ref(false);
const selectedStep = ref(null);
const approveForm = useForm({ comment: '' });

const openApproveModal = (step) => {
    selectedStep.value = step;
    approveForm.comment = '';
    showApproveModal.value = true;
};
const submitApprove = () => {
    approveForm.post(route('approval.approve', selectedStep.value.id), {
        preserveScroll: true,
        onSuccess: () => { showApproveModal.value = false; },
    });
};

// Reject modal
const showRejectModal = ref(false);
const rejectForm = useForm({ comment: '' });

const openRejectModal = (step) => {
    selectedStep.value = step;
    rejectForm.comment = '';
    showRejectModal.value = true;
};
const submitReject = () => {
    rejectForm.post(route('approval.reject', selectedStep.value.id), {
        preserveScroll: true,
        onSuccess: () => { showRejectModal.value = false; },
    });
};

// Delegate modal
const showDelegateModal = ref(false);
const delegateForm = useForm({ delegate_to_id: '' });

const openDelegateModal = (step) => {
    selectedStep.value = step;
    delegateForm.delegate_to_id = '';
    showDelegateModal.value = true;
};
const submitDelegate = () => {
    delegateForm.post(route('approval.delegate', selectedStep.value.id), {
        preserveScroll: true,
        onSuccess: () => { showDelegateModal.value = false; },
    });
};

const fmt = (n) => new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(n ?? 0);

const typeLabels = {
    invoice: 'Facture', quote: 'Devis', proforma: 'Proforma',
    sales_order: 'Commande', delivery_note: 'BL',
};
</script>

<template>
    <Head title="Circuit de validation" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Circuit de validation</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">

                <div v-if="!hasAccess" class="rounded-lg border border-amber-200 bg-amber-50 p-6">
                    <p class="font-semibold text-amber-800">Cette fonctionnalité est réservée aux plans BUSINESS et ENTERPRISE.</p>
                </div>

                <template v-else>
                    <!-- Tabs -->
                    <div class="border-b border-gray-200">
                        <nav class="flex gap-6">
                            <button
                                v-for="tab in [
                                    { key: 'pending', label: '⏳ En attente de moi' },
                                    { key: 'workflows', label: '⚙️ Workflows' },
                                ]"
                                :key="tab.key"
                                @click="activeTab = tab.key"
                                class="border-b-2 px-1 pb-3 text-sm font-medium transition-colors"
                                :class="activeTab === tab.key
                                    ? 'border-brand-600 text-brand-600'
                                    : 'border-transparent text-gray-500 hover:text-gray-700'"
                            >
                                {{ tab.label }}
                            </button>
                        </nav>
                    </div>

                    <!-- Tab: En attente -->
                    <div v-if="activeTab === 'pending'">
                        <div v-if="!pendingSteps?.length" class="py-12 text-center text-gray-400">
                            Aucune étape en attente de votre validation.
                        </div>
                        <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            <div
                                v-for="step in pendingSteps"
                                :key="step.id"
                                class="rounded-lg bg-white p-5 shadow"
                            >
                                <div class="mb-3 flex items-start justify-between">
                                    <div>
                                        <span class="text-xs font-semibold uppercase tracking-wide text-gray-400">
                                            {{ typeLabels[step.document?.type] ?? step.document?.type }}
                                        </span>
                                        <div class="mt-0.5 font-semibold text-gray-800">
                                            {{ step.document?.number }}
                                        </div>
                                    </div>
                                    <span class="rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-700">
                                        Étape {{ step.step_number }}
                                    </span>
                                </div>

                                <div class="space-y-1 text-sm text-gray-600">
                                    <div v-if="step.document?.customer">
                                        Client : <b>{{ step.document.customer.name }}</b>
                                    </div>
                                    <div v-if="step.document?.total">
                                        Montant : <b>{{ fmt(step.document.total) }} {{ step.document.currency }}</b>
                                    </div>
                                    <div class="text-xs text-gray-400">
                                        Depuis {{ new Date(step.created_at).toLocaleDateString('fr-FR') }}
                                    </div>
                                </div>

                                <div class="mt-4 flex flex-wrap gap-2">
                                    <button
                                        @click="openApproveModal(step)"
                                        class="rounded-md bg-green-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-green-700"
                                    >
                                        ✅ Approuver
                                    </button>
                                    <button
                                        @click="openRejectModal(step)"
                                        class="rounded-md bg-red-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-700"
                                    >
                                        ❌ Rejeter
                                    </button>
                                    <button
                                        @click="openDelegateModal(step)"
                                        class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50"
                                    >
                                        → Déléguer
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab: Workflows -->
                    <div v-if="activeTab === 'workflows'">
                        <div class="mb-4 flex justify-end">
                            <Link
                                :href="route('approval.index')"
                                class="rounded-md bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700"
                            >
                                + Créer un workflow
                            </Link>
                        </div>
                        <div v-if="!workflows?.length" class="py-12 text-center text-gray-400">
                            Aucun workflow configuré.
                        </div>
                        <div v-else class="grid gap-4 sm:grid-cols-2">
                            <div
                                v-for="wf in workflows"
                                :key="wf.id"
                                class="rounded-lg bg-white p-5 shadow"
                            >
                                <div class="flex items-start justify-between">
                                    <div>
                                        <div class="font-semibold text-gray-800">{{ wf.name }}</div>
                                        <div v-if="wf.description" class="mt-0.5 text-sm text-gray-500">{{ wf.description }}</div>
                                    </div>
                                    <span
                                        class="rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                        :class="wf.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'"
                                    >
                                        {{ wf.is_active ? 'Actif' : 'Inactif' }}
                                    </span>
                                </div>
                                <div class="mt-3 text-sm text-gray-500">
                                    {{ wf.steps_count }} étape(s) — Types : {{ (wf.document_types ?? []).join(', ') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Modal Approuver -->
        <Modal :show="showApproveModal" @close="showApproveModal = false">
            <div class="p-6">
                <h3 class="mb-4 text-lg font-semibold text-gray-800">Approuver l'étape</h3>
                <InputLabel value="Commentaire (optionnel)" />
                <textarea
                    v-model="approveForm.comment"
                    rows="3"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                    placeholder="Un commentaire d'approbation…"
                ></textarea>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="showApproveModal = false">Annuler</SecondaryButton>
                    <PrimaryButton :disabled="approveForm.processing" @click="submitApprove">
                        ✅ Confirmer l'approbation
                    </PrimaryButton>
                </div>
            </div>
        </Modal>

        <!-- Modal Rejeter -->
        <Modal :show="showRejectModal" @close="showRejectModal = false">
            <div class="p-6">
                <h3 class="mb-4 text-lg font-semibold text-gray-800">Rejeter l'étape</h3>
                <InputLabel value="Raison du rejet *" />
                <textarea
                    v-model="rejectForm.comment"
                    rows="3"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                    placeholder="Expliquez la raison du rejet…"
                ></textarea>
                <InputError :message="rejectForm.errors.comment" class="mt-1" />
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="showRejectModal = false">Annuler</SecondaryButton>
                    <PrimaryButton
                        :disabled="rejectForm.processing || !rejectForm.comment.trim()"
                        @click="submitReject"
                        class="bg-red-600 hover:bg-red-700"
                    >
                        ❌ Confirmer le rejet
                    </PrimaryButton>
                </div>
            </div>
        </Modal>

        <!-- Modal Déléguer -->
        <Modal :show="showDelegateModal" @close="showDelegateModal = false">
            <div class="p-6">
                <h3 class="mb-4 text-lg font-semibold text-gray-800">Déléguer l'étape</h3>
                <InputLabel value="ID du membre *" />
                <input
                    v-model="delegateForm.delegate_to_id"
                    type="number"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                    placeholder="ID de l'utilisateur"
                />
                <InputError :message="delegateForm.errors.delegate_to_id" class="mt-1" />
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="showDelegateModal = false">Annuler</SecondaryButton>
                    <PrimaryButton :disabled="delegateForm.processing" @click="submitDelegate">
                        → Déléguer
                    </PrimaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

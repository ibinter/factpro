<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import AdminTabs from '@/Components/AdminTabs.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    transactions: Object,
    stats: Object,
    filters: Object,
});

const fmt = (n) => new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(n ?? 0);

const riskColors = {
    LOW: 'bg-green-100 text-green-700',
    MEDIUM: 'bg-amber-100 text-amber-700',
    HIGH: 'bg-orange-100 text-orange-700',
    CRITICAL: 'bg-red-100 text-red-700',
};

const providerLabels = {
    orange_money: 'Orange Money', mtn_momo: 'MTN MoMo', wave: 'Wave', moov: 'Moov Money',
    bank_transfer_national: 'Virement national', bank_transfer_international: 'Virement international',
    moneroo: 'Moneroo', cash: 'Espèces',
};

const validating = ref(null);
const rejecting = ref(null);

const validateForm = useForm({ amount_received: 0, note: '' });
const rejectForm = useForm({ reason: '' });

const openValidate = (transaction) => {
    validating.value = transaction;
    validateForm.amount_received = transaction.amount_declared ?? transaction.amount_expected;
    validateForm.clearErrors();
};

const submitValidate = () => {
    validateForm.post(route('admin.payments.validate', validating.value.id), {
        preserveScroll: true,
        onSuccess: () => (validating.value = null),
    });
};

const submitReject = () => {
    rejectForm.post(route('admin.payments.reject', rejecting.value.id), {
        preserveScroll: true,
        onSuccess: () => { rejecting.value = null; rejectForm.reset(); },
    });
};
</script>

<template>
    <Head title="Admin — Validation des paiements" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Console Superadmin — <span class="text-gold-600">Validation des paiements</span>
            </h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <AdminTabs />

                <!-- Stats -->
                <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                    <div class="rounded-lg bg-white p-5 shadow">
                        <div class="text-xs uppercase tracking-wide text-gray-500">Preuves à vérifier</div>
                        <div class="mt-1 text-2xl font-bold text-amber-600">{{ stats.to_review }}</div>
                    </div>
                    <div class="rounded-lg bg-white p-5 shadow">
                        <div class="text-xs uppercase tracking-wide text-gray-500">Validés aujourd'hui</div>
                        <div class="mt-1 text-2xl font-bold text-green-600">{{ stats.validated_today }}</div>
                    </div>
                    <div class="rounded-lg bg-white p-5 shadow">
                        <div class="text-xs uppercase tracking-wide text-gray-500">Licences actives</div>
                        <div class="mt-1 text-2xl font-bold text-gray-800">{{ stats.active_licenses }}</div>
                    </div>
                    <div class="rounded-lg bg-gradient-to-br from-brand-900 to-brand-600 p-5 text-white shadow">
                        <div class="text-xs uppercase tracking-wide opacity-75">CA du mois</div>
                        <div class="mt-1 text-2xl font-bold">{{ fmt(stats.revenue_month) }} <span class="text-sm font-normal">FCFA</span></div>
                    </div>
                </div>

                <!-- File de validation -->
                <div class="space-y-4">
                    <div
                        v-for="transaction in transactions.data"
                        :key="transaction.id"
                        class="rounded-lg bg-white p-6 shadow"
                    >
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-mono text-sm font-bold text-brand-700">{{ transaction.internal_reference }}</span>
                                    <span class="rounded-full px-2 py-0.5 text-xs font-bold" :class="riskColors[transaction.risk_level]">
                                        Risque {{ transaction.risk_level }}
                                    </span>
                                    <span class="rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-semibold text-indigo-700">
                                        {{ providerLabels[transaction.provider] ?? transaction.provider }}
                                    </span>
                                </div>
                                <div class="mt-2 text-sm text-gray-600">
                                    <b>{{ transaction.user?.name }}</b> ({{ transaction.user?.email }}, {{ transaction.user?.country }})
                                    — {{ transaction.order?.plan }} · {{ transaction.order?.duration_months }} mois
                                    · Commande {{ transaction.order?.order_number }}
                                </div>
                                <div class="mt-1 text-sm">
                                    Attendu : <b>{{ fmt(transaction.amount_expected) }} {{ transaction.currency }}</b>
                                    · Déclaré :
                                    <b :class="Number(transaction.amount_declared) !== Number(transaction.amount_expected) ? 'text-red-600' : 'text-green-600'">
                                        {{ transaction.amount_declared !== null ? fmt(transaction.amount_declared) : '—' }} {{ transaction.currency }}
                                    </b>
                                </div>
                                <div class="mt-1 text-xs text-gray-400">
                                    Réf. fournie : {{ transaction.provider_reference ?? '—' }}
                                    · Expéditeur : {{ transaction.sender_name ?? '—' }} ({{ transaction.sender_number ?? '—' }})
                                    · Soumis le {{ transaction.created_at }}
                                </div>
                                <div v-if="transaction.proofs?.length" class="mt-2 flex flex-wrap gap-2">
                                    <a
                                        v-for="proof in transaction.proofs"
                                        :key="proof.id"
                                        :href="route('admin.proofs.show', proof.id)"
                                        target="_blank"
                                        class="rounded-md bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700 hover:bg-gray-200"
                                    >
                                        📎 {{ proof.original_filename }} ({{ Math.round(proof.file_size / 1024) }} Ko)
                                    </a>
                                </div>
                            </div>
                            <div class="flex shrink-0 flex-col gap-2">
                                <button
                                    @click="openValidate(transaction)"
                                    class="rounded-md bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700"
                                >
                                    ✓ Valider
                                </button>
                                <button
                                    @click="rejecting = transaction"
                                    class="rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700"
                                >
                                    ✗ Rejeter
                                </button>
                            </div>
                        </div>
                    </div>

                    <div v-if="!transactions.data.length" class="rounded-lg bg-white p-12 text-center text-gray-400 shadow">
                        🎉 Aucun paiement en attente de vérification.
                    </div>
                </div>
            </div>
        </div>

        <!-- Modale validation -->
        <Modal :show="!!validating" @close="validating = null">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-800">Valider ce paiement</h3>
                <p class="mt-1 text-sm text-gray-500">
                    {{ validating?.internal_reference }} — la licence sera activée immédiatement pour
                    <b>{{ validating?.user?.name }}</b>.
                </p>
                <div class="mt-4 space-y-4">
                    <div>
                        <InputLabel value="Montant réellement reçu *" />
                        <TextInput v-model="validateForm.amount_received" type="number" step="1" min="1" class="mt-1 block w-full" />
                        <InputError :message="validateForm.errors.amount_received" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel value="Note interne" />
                        <textarea v-model="validateForm.note" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500"></textarea>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="validating = null">Annuler</SecondaryButton>
                    <PrimaryButton :disabled="validateForm.processing" @click="submitValidate">
                        Valider & activer la licence
                    </PrimaryButton>
                </div>
            </div>
        </Modal>

        <!-- Modale rejet -->
        <Modal :show="!!rejecting" @close="rejecting = null">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-800">Rejeter ce paiement</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Le client sera notifié. Le motif est <b>obligatoire</b>.
                </p>
                <div class="mt-4">
                    <InputLabel value="Motif du rejet *" />
                    <textarea
                        v-model="rejectForm.reason"
                        rows="3"
                        required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                        placeholder="Ex : référence introuvable, montant insuffisant, preuve illisible…"
                    ></textarea>
                    <InputError :message="rejectForm.errors.reason" class="mt-1" />
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="rejecting = null">Annuler</SecondaryButton>
                    <DangerButton :disabled="rejectForm.processing" @click="submitReject">Rejeter le paiement</DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

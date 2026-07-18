<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import { Head } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    teamMembers: { type: Array, default: () => [] },
});

const documentTypeOptions = [
    { value: 'invoice', label: 'Facture' },
    { value: 'quote', label: 'Devis' },
    { value: 'proforma', label: 'Proforma' },
    { value: 'sales_order', label: 'Bon de commande' },
    { value: 'delivery_note', label: 'Bon de livraison' },
    { value: 'credit_note', label: 'Avoir' },
];

const form = useForm({
    name: '',
    description: '',
    document_types: [],
    approvers: [],
    is_active: true,
});

const addApprover = () => {
    if (form.approvers.length < 5) {
        form.approvers.push('');
    }
};

const removeApprover = (index) => {
    form.approvers.splice(index, 1);
};

const moveUp = (index) => {
    if (index > 0) {
        const temp = form.approvers[index];
        form.approvers[index] = form.approvers[index - 1];
        form.approvers[index - 1] = temp;
    }
};

const moveDown = (index) => {
    if (index < form.approvers.length - 1) {
        const temp = form.approvers[index];
        form.approvers[index] = form.approvers[index + 1];
        form.approvers[index + 1] = temp;
    }
};

const canSubmit = computed(() =>
    form.name.trim() &&
    form.document_types.length > 0 &&
    form.approvers.length >= 1 &&
    form.approvers.every((a) => !!a),
);

const submit = () => {
    form.post(route('approval.workflows.store'));
};
</script>

<template>
    <Head title="Créer un workflow" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Créer un workflow d'approbation</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-3xl space-y-6 px-4 sm:px-6 lg:px-8">
                <div class="rounded-lg bg-white p-6 shadow">
                    <!-- Nom -->
                    <div class="mb-4">
                        <InputLabel value="Nom du workflow *" />
                        <input
                            v-model="form.name"
                            type="text"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                            placeholder="ex. Validation factures clients"
                        />
                        <InputError :message="form.errors.name" class="mt-1" />
                    </div>

                    <!-- Description -->
                    <div class="mb-4">
                        <InputLabel value="Description (optionnel)" />
                        <textarea
                            v-model="form.description"
                            rows="2"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                        ></textarea>
                    </div>

                    <!-- Types de documents -->
                    <div class="mb-6">
                        <InputLabel value="Types de documents concernés *" />
                        <div class="mt-2 flex flex-wrap gap-3">
                            <label
                                v-for="option in documentTypeOptions"
                                :key="option.value"
                                class="flex cursor-pointer items-center gap-2 text-sm"
                            >
                                <input
                                    type="checkbox"
                                    :value="option.value"
                                    v-model="form.document_types"
                                    class="rounded border-gray-300 text-brand-600 shadow-sm focus:ring-brand-500"
                                />
                                {{ option.label }}
                            </label>
                        </div>
                        <InputError :message="form.errors.document_types" class="mt-1" />
                    </div>

                    <!-- Approbateurs -->
                    <div class="mb-6">
                        <div class="mb-2 flex items-center justify-between">
                            <InputLabel value="Ordre d'approbation (1 à 5 étapes) *" />
                            <button
                                v-if="form.approvers.length < 5"
                                type="button"
                                @click="addApprover"
                                class="rounded-md border border-brand-600 px-3 py-1 text-xs font-semibold text-brand-600 hover:bg-brand-50"
                            >
                                + Ajouter un approbateur
                            </button>
                        </div>

                        <div v-if="!form.approvers.length" class="rounded-md border border-dashed border-gray-300 py-8 text-center text-sm text-gray-400">
                            Cliquez sur « + Ajouter un approbateur » pour commencer.
                        </div>

                        <div class="space-y-2">
                            <div
                                v-for="(_, index) in form.approvers"
                                :key="index"
                                class="flex items-center gap-2"
                            >
                                <span class="w-6 text-center text-sm font-bold text-gray-400">{{ index + 1 }}</span>

                                <input
                                    v-model="form.approvers[index]"
                                    type="number"
                                    placeholder="ID utilisateur"
                                    class="flex-1 rounded-md border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500"
                                />

                                <button
                                    type="button"
                                    @click="moveUp(index)"
                                    :disabled="index === 0"
                                    class="rounded p-1 text-gray-400 hover:text-gray-700 disabled:opacity-30"
                                >↑</button>
                                <button
                                    type="button"
                                    @click="moveDown(index)"
                                    :disabled="index === form.approvers.length - 1"
                                    class="rounded p-1 text-gray-400 hover:text-gray-700 disabled:opacity-30"
                                >↓</button>
                                <button
                                    type="button"
                                    @click="removeApprover(index)"
                                    class="rounded p-1 text-red-400 hover:text-red-700"
                                >✕</button>
                            </div>
                        </div>
                        <InputError :message="form.errors.approvers" class="mt-1" />
                    </div>

                    <!-- Actif -->
                    <div class="mb-6 flex items-center gap-2">
                        <input
                            id="is_active"
                            v-model="form.is_active"
                            type="checkbox"
                            class="rounded border-gray-300 text-brand-600 shadow-sm focus:ring-brand-500"
                        />
                        <label for="is_active" class="text-sm text-gray-700">Activer ce workflow immédiatement</label>
                    </div>

                    <div class="flex justify-end gap-3">
                        <SecondaryButton type="button" @click="$inertia.visit(route('approval.index'))">
                            Annuler
                        </SecondaryButton>
                        <PrimaryButton :disabled="form.processing || !canSubmit" @click="submit">
                            Créer le workflow
                        </PrimaryButton>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

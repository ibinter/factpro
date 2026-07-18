<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    user: Object,
});

const showDeleteConfirm = ref(false);

const deleteForm = useForm({
    password: '',
});

const submitDelete = () => {
    deleteForm.delete(route('gdpr.destroy'), {
        onSuccess: () => {
            showDeleteConfirm.value = false;
        },
    });
};
</script>

<template>
    <Head title="Mes données & RGPD" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Mes données &amp; RGPD
            </h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-3xl space-y-6 px-4 sm:px-6 lg:px-8">

                <!-- Informations personnelles -->
                <div class="rounded-lg bg-white p-6 shadow">
                    <h3 class="mb-4 text-lg font-semibold text-gray-800">Mes informations personnelles</h3>
                    <dl class="space-y-2 text-sm text-gray-700">
                        <div class="flex gap-2">
                            <dt class="w-32 font-medium text-gray-500">Nom</dt>
                            <dd>{{ user.name }}</dd>
                        </div>
                        <div class="flex gap-2">
                            <dt class="w-32 font-medium text-gray-500">Email</dt>
                            <dd>{{ user.email }}</dd>
                        </div>
                        <div class="flex gap-2">
                            <dt class="w-32 font-medium text-gray-500">Compte créé</dt>
                            <dd>{{ user.created_at }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Export des données -->
                <div class="rounded-lg bg-white p-6 shadow">
                    <h3 class="mb-2 text-lg font-semibold text-gray-800">Portabilité des données (Art. 20 RGPD)</h3>
                    <p class="mb-4 text-sm text-gray-600">
                        Téléchargez une copie complète de vos données personnelles au format JSON.
                    </p>
                    <a
                        :href="route('gdpr.export')"
                        class="inline-flex items-center rounded-md bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500"
                    >
                        Télécharger mes données (JSON)
                    </a>
                </div>

                <!-- Journal d'audit -->
                <div class="rounded-lg bg-white p-6 shadow">
                    <h3 class="mb-2 text-lg font-semibold text-gray-800">Journal d'audit de la société</h3>
                    <p class="mb-4 text-sm text-gray-600">
                        Consultez le journal des actions effectuées sur les documents de votre société courante.
                    </p>
                    <a
                        :href="route('gdpr.audit-log')"
                        class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-brand-500"
                    >
                        Voir le journal d'audit
                    </a>
                </div>

                <!-- Suppression du compte -->
                <div class="rounded-lg border border-red-200 bg-red-50 p-6 shadow">
                    <h3 class="mb-2 text-lg font-semibold text-red-800">Droit à l'oubli (Art. 17 RGPD)</h3>
                    <p class="mb-4 text-sm text-red-700">
                        La suppression de votre compte est <strong>irréversible</strong>. Vos documents d'entreprise
                        sont conservés pour vos sociétés, mais votre compte personnel sera définitivement supprimé.
                    </p>

                    <button
                        v-if="!showDeleteConfirm"
                        type="button"
                        @click="showDeleteConfirm = true"
                        class="inline-flex items-center rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500"
                    >
                        Supprimer mon compte
                    </button>

                    <form v-else @submit.prevent="submitDelete" class="mt-2 space-y-4">
                        <p class="text-sm font-medium text-red-800">Confirmez votre mot de passe pour continuer :</p>
                        <div>
                            <input
                                v-model="deleteForm.password"
                                type="password"
                                placeholder="Mot de passe"
                                class="block w-full max-w-sm rounded-md border-gray-300 text-sm shadow-sm focus:border-red-500 focus:ring-red-500"
                                required
                            />
                            <p v-if="deleteForm.errors.password" class="mt-1 text-xs text-red-600">
                                {{ deleteForm.errors.password }}
                            </p>
                        </div>
                        <div class="flex gap-3">
                            <button
                                type="submit"
                                :disabled="deleteForm.processing"
                                class="inline-flex items-center rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-60"
                            >
                                Confirmer la suppression
                            </button>
                            <button
                                type="button"
                                @click="showDeleteConfirm = false"
                                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                            >
                                Annuler
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

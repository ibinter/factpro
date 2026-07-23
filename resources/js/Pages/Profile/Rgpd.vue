<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
    deletion_requested: {
        type: Boolean,
        default: false,
    },
});

const form = useForm({
    password: '',
});

const showDeleteForm = ref(false);

function submitDeletion() {
    form.post(route('profile.delete-request'), {
        onSuccess: () => {
            form.reset('password');
            showDeleteForm.value = false;
        },
    });
}
</script>

<template>
    <Head title="Mes données & RGPD" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Mes données &amp; RGPD
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl space-y-6 sm:px-6 lg:px-8">

                <!-- Message succès suppression -->
                <div
                    v-if="deletion_requested"
                    class="rounded-lg border border-green-200 bg-green-50 p-4 text-green-800"
                >
                    <p class="font-medium">
                        ✓ Demande envoyée. Nous vous contacterons sous 30 jours.
                    </p>
                </div>

                <!-- Section 1 : Export -->
                <div class="bg-white p-6 shadow sm:rounded-lg">
                    <div class="flex items-start gap-4">
                        <span class="text-3xl">📦</span>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900">
                                Télécharger mes données
                            </h3>
                            <p class="mt-2 text-sm text-gray-600">
                                Conformément au RGPD (article 20), vous avez le droit à la portabilité de vos données.
                                Téléchargez une copie complète de toutes vos données FactPro au format ZIP (JSON + CSV).
                            </p>

                            <ul class="mt-4 space-y-1 text-sm text-gray-600">
                                <li class="flex items-center gap-2">
                                    <span class="text-indigo-500">✓</span>
                                    Informations de compte
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="text-indigo-500">✓</span>
                                    Données de société
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="text-indigo-500">✓</span>
                                    Tous vos documents (factures, devis, bons de commande…)
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="text-indigo-500">✓</span>
                                    Liste de clients
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="text-indigo-500">✓</span>
                                    Liste de produits
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="text-indigo-500">✓</span>
                                    Historique des licences
                                </li>
                            </ul>

                            <div class="mt-6">
                                <a
                                    :href="route('profile.export')"
                                    target="_blank"
                                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    Télécharger mes données (ZIP)
                                </a>
                            </div>

                            <p class="mt-3 text-xs text-gray-400">
                                Votre demande sera traitée immédiatement. Max 2 exports par heure.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Section 2 : Suppression -->
                <div class="rounded-lg border border-red-200 bg-red-50 p-6 shadow-sm">
                    <div class="flex items-start gap-4">
                        <span class="text-3xl">🗑️</span>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-red-900">
                                Demander la suppression de mon compte
                            </h3>
                            <p class="mt-2 text-sm text-red-700">
                                Conformément au RGPD (article 17 — droit à l'effacement), vous pouvez demander la suppression
                                de votre compte et de toutes vos données. Cette action est irréversible.
                                Délai de traitement : 30 jours.
                            </p>

                            <div class="mt-4 rounded-md border border-red-300 bg-red-100 p-3">
                                <p class="text-sm font-medium text-red-800">
                                    ⚠️ Attention : toutes vos données (factures, clients, produits, documents)
                                    seront définitivement supprimées.
                                </p>
                            </div>

                            <div class="mt-5">
                                <button
                                    v-if="!showDeleteForm"
                                    type="button"
                                    class="rounded-lg border border-red-300 bg-white px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                                    @click="showDeleteForm = true"
                                >
                                    Demander la suppression
                                </button>

                                <form v-else @submit.prevent="submitDeletion" class="mt-4 space-y-4">
                                    <div>
                                        <label for="password" class="block text-sm font-medium text-red-800">
                                            Confirmez votre mot de passe pour continuer
                                        </label>
                                        <input
                                            id="password"
                                            v-model="form.password"
                                            type="password"
                                            required
                                            autocomplete="current-password"
                                            class="mt-1 block w-full max-w-xs rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                                            placeholder="Votre mot de passe"
                                        />
                                        <p v-if="form.errors.password" class="mt-1 text-xs text-red-600">
                                            {{ form.errors.password }}
                                        </p>
                                    </div>

                                    <div class="flex gap-3">
                                        <button
                                            type="submit"
                                            :disabled="form.processing"
                                            class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:opacity-50"
                                        >
                                            <span v-if="form.processing">Envoi en cours…</span>
                                            <span v-else>Demander la suppression</span>
                                        </button>
                                        <button
                                            type="button"
                                            class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                                            @click="showDeleteForm = false; form.reset('password')"
                                        >
                                            Annuler
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Retour profil -->
                <div class="text-center">
                    <a
                        :href="route('profile.edit')"
                        class="text-sm text-gray-500 hover:text-gray-700"
                    >
                        ← Retour au profil
                    </a>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

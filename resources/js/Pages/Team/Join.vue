<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    valid: Boolean,
    authenticated: Boolean,
    token: String,
    company: String,
    role: String,
    roleLabel: String,
    email: String,
});

const form = useForm({});

const accept = () => {
    form.post(route('team.join.accept', props.token));
};
</script>

<template>
    <Head title="Rejoindre une équipe" />

    <GuestLayout>
        <!-- Invitation invalide / expirée -->
        <div v-if="!valid" class="text-center">
            <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-red-100 text-2xl">⚠️</div>
            <h1 class="text-lg font-semibold text-gray-800">Invitation invalide ou expirée</h1>
            <p class="mt-2 text-sm text-gray-500">
                Ce lien d'invitation n'est plus valable. Demandez à l'administrateur de la société de vous renvoyer une invitation.
            </p>
            <Link :href="route('home')" class="mt-4 inline-block text-sm font-semibold text-brand-600 hover:underline">
                Retour à l'accueil
            </Link>
        </div>

        <!-- Invitation valide -->
        <div v-else class="text-center">
            <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-brand-50 text-2xl">✉️</div>
            <h1 class="text-lg font-semibold text-gray-800">Invitation à rejoindre une équipe</h1>
            <p class="mt-2 text-sm text-gray-600">
                Vous avez été invité(e) à rejoindre
                <strong class="text-brand-900">{{ company }}</strong>
                en tant que <strong class="text-brand-700">{{ roleLabel }}</strong>.
            </p>

            <div v-if="authenticated" class="mt-6">
                <PrimaryButton class="w-full justify-center" :disabled="form.processing" @click="accept">
                    Accepter et rejoindre l'équipe
                </PrimaryButton>
            </div>

            <div v-else class="mt-6 space-y-3">
                <p class="text-sm text-gray-500">
                    Connectez-vous ou créez un compte avec l'adresse
                    <strong>{{ email }}</strong> pour rejoindre l'équipe.
                </p>
                <div class="flex flex-col gap-2">
                    <Link
                        :href="route('login')"
                        class="inline-flex w-full items-center justify-center rounded-md bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-500"
                    >
                        Se connecter
                    </Link>
                    <Link
                        :href="route('register')"
                        class="inline-flex w-full items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
                    >
                        Créer un compte
                    </Link>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>

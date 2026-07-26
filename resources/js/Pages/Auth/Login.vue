<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { onMounted } from 'vue';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

onMounted(() => {
    // Pre-fill email from query string (used by /demo-login page)
    const params = new URLSearchParams(window.location.search);
    const email = params.get('email');
    if (email) {
        form.email = email;
    }
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Connexion — FactPro" />

    <div class="min-h-screen flex">

        <!-- Colonne gauche : navy avec logo + tagline (masquée sur mobile) -->
        <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-brand-950 via-brand-900 to-brand-800 flex-col items-center justify-center px-12 py-16 text-white">
            <a href="/">
                <img src="/logo.svg" alt="IBIG FactPro" class="h-20 w-auto rounded-xl bg-white/95 p-2 shadow-lg mb-10" />
            </a>
            <h2 class="text-3xl font-bold mb-4 text-center leading-snug">
                La facturation simple<br />pour les PME africaines
            </h2>
            <p class="text-brand-200 text-center text-base max-w-sm leading-relaxed mb-8">
                Créez vos factures, suivez vos paiements et gérez votre trésorerie en quelques clics.
            </p>
            <ul class="space-y-3 text-sm text-brand-100">
                <li class="flex items-center gap-3">
                    <span class="text-amber-400 text-lg">✓</span>
                    Factures PDF professionnelles en 30 secondes
                </li>
                <li class="flex items-center gap-3">
                    <span class="text-amber-400 text-lg">✓</span>
                    Relances automatiques des impayés
                </li>
                <li class="flex items-center gap-3">
                    <span class="text-amber-400 text-lg">✓</span>
                    Tableau de bord financier en temps réel
                </li>
                <li class="flex items-center gap-3">
                    <span class="text-amber-400 text-lg">✓</span>
                    Données hébergées en Afrique & Europe
                </li>
            </ul>
            <div class="mt-12 border-t border-white/10 pt-6 text-center text-xs text-white/40">
                © {{ new Date().getFullYear() }} IBIG SARL — factpro.ibigsoft.com
            </div>
        </div>

        <!-- Colonne droite : formulaire blanc -->
        <div class="flex w-full lg:w-1/2 flex-col items-center justify-center bg-white px-6 py-12 sm:px-12">

            <!-- Logo visible uniquement sur mobile -->
            <div class="mb-8 lg:hidden">
                <a href="/">
                    <img src="/logo.svg" alt="IBIG FactPro" class="h-14 w-auto rounded-xl bg-brand-900 p-2 shadow" />
                </a>
            </div>

            <div class="w-full max-w-sm">
                <h1 class="text-2xl font-bold text-gray-900 mb-1">Connexion</h1>
                <p class="text-sm text-gray-500 mb-8">Bienvenue ! Entrez vos identifiants pour accéder à votre espace.</p>

                <div v-if="status" class="mb-4 rounded-md bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <InputLabel for="email" value="Adresse email" />
                        <TextInput
                            id="email"
                            type="email"
                            class="mt-1 block w-full"
                            v-model="form.email"
                            required
                            autofocus
                            autocomplete="username"
                        />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <InputLabel for="password" value="Mot de passe" />
                            <Link
                                v-if="canResetPassword"
                                :href="route('password.request')"
                                class="text-xs text-brand-600 hover:text-brand-800 underline"
                            >
                                Mot de passe oublié ?
                            </Link>
                        </div>
                        <TextInput
                            id="password"
                            type="password"
                            class="mt-1 block w-full"
                            v-model="form.password"
                            required
                            autocomplete="current-password"
                        />
                        <InputError class="mt-2" :message="form.errors.password" />
                    </div>

                    <div>
                        <label class="flex items-center">
                            <Checkbox name="remember" v-model:checked="form.remember" />
                            <span class="ms-2 text-sm text-gray-600">Se souvenir de moi</span>
                        </label>
                    </div>

                    <PrimaryButton
                        class="w-full justify-center"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        Se connecter
                    </PrimaryButton>
                </form>

                <!-- Lien démo -->
                <div class="mt-4 text-center">
                    <Link
                        href="/demo-login"
                        class="text-sm text-gray-400 hover:text-brand-600 transition-colors"
                    >
                        Essayer la démo sans inscription →
                    </Link>
                </div>

                <!-- Séparateur -->
                <div class="my-6 flex items-center gap-3">
                    <div class="flex-1 h-px bg-gray-200"></div>
                    <span class="text-xs text-gray-400">ou</span>
                    <div class="flex-1 h-px bg-gray-200"></div>
                </div>

                <!-- Lien register -->
                <div class="text-center">
                    <p class="text-sm text-gray-600">
                        Pas encore de compte ?
                    </p>
                    <Link
                        :href="route('register')"
                        class="mt-1 inline-block text-sm font-semibold text-brand-700 hover:text-brand-900 underline underline-offset-2"
                    >
                        Démarrer l'essai gratuit 7 jours →
                    </Link>
                </div>
            </div>

            <p class="mt-12 text-xs text-gray-300 lg:hidden">
                © {{ new Date().getFullYear() }} IBIG SARL
            </p>
        </div>
    </div>
</template>

<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    company_name: '',
    phone: '',
    country: 'CI',
    email: '',
    password: '',
    password_confirmation: '',
});

const countries = [
    { code: 'CI', name: "Côte d'Ivoire" },
    { code: 'SN', name: 'Sénégal' },
    { code: 'BF', name: 'Burkina Faso' },
    { code: 'ML', name: 'Mali' },
    { code: 'TG', name: 'Togo' },
    { code: 'BJ', name: 'Bénin' },
    { code: 'CM', name: 'Cameroun' },
    { code: 'GN', name: 'Guinée' },
    { code: 'MA', name: 'Maroc' },
    { code: 'FR', name: 'France' },
];

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Créer un compte" />

        <div class="mb-4 rounded-md bg-brand-50 px-4 py-3 text-center text-sm text-brand-800">
            <b>7 jours d'essai gratuit</b> — sans carte bancaire
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="name" value="Votre nom complet" />
                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div class="mt-4">
                <InputLabel for="company_name" value="Nom de votre entreprise" />
                <TextInput
                    id="company_name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.company_name"
                    required
                />
                <InputError class="mt-2" :message="form.errors.company_name" />
            </div>

            <div class="mt-4 grid grid-cols-2 gap-3">
                <div>
                    <InputLabel for="phone" value="Téléphone" />
                    <TextInput
                        id="phone"
                        type="tel"
                        class="mt-1 block w-full"
                        v-model="form.phone"
                        placeholder="+225…"
                    />
                    <InputError class="mt-2" :message="form.errors.phone" />
                </div>
                <div>
                    <InputLabel for="country" value="Pays" />
                    <select
                        id="country"
                        v-model="form.country"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                    >
                        <option v-for="c in countries" :key="c.code" :value="c.code">{{ c.name }}</option>
                    </select>
                </div>
            </div>

            <div class="mt-4">
                <InputLabel for="email" value="Adresse email" />
                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Mot de passe" />
                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4">
                <InputLabel for="password_confirmation" value="Confirmer le mot de passe" />
                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                />
                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>

            <div class="mt-4 flex items-center justify-end">
                <Link
                    :href="route('login')"
                    class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2"
                >
                    Déjà inscrit ?
                </Link>

                <PrimaryButton
                    class="ms-4"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Démarrer l'essai gratuit
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>

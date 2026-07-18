<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

const useRecovery = ref(false);
const codeInput = ref(null);
const recoveryInput = ref(null);

const form = useForm({
    code: '',
    recovery_code: '',
});

const toggleRecovery = async () => {
    useRecovery.value = !useRecovery.value;
    form.clearErrors();

    await nextTick();

    if (useRecovery.value) {
        form.code = '';
        recoveryInput.value?.focus();
    } else {
        form.recovery_code = '';
        codeInput.value?.focus();
    }
};

const submit = () => {
    form.post(route('two-factor.challenge.store'), {
        onError: () => {
            form.reset('code', 'recovery_code');
            if (useRecovery.value) {
                recoveryInput.value?.focus();
            } else {
                codeInput.value?.focus();
            }
        },
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Double authentification" />

        <div class="mb-4 text-sm text-gray-600">
            <template v-if="!useRecovery">
                Confirmez l'accès à votre compte en saisissant le code généré
                par votre application d'authentification.
            </template>
            <template v-else>
                Confirmez l'accès à votre compte en saisissant l'un de vos codes
                de récupération d'urgence.
            </template>
        </div>

        <form @submit.prevent="submit">
            <div v-if="!useRecovery">
                <InputLabel for="code" value="Code" />

                <TextInput
                    id="code"
                    ref="codeInput"
                    v-model="form.code"
                    type="text"
                    inputmode="numeric"
                    class="mt-1 block w-full"
                    autofocus
                    autocomplete="one-time-code"
                />

                <InputError class="mt-2" :message="form.errors.code" />
            </div>

            <div v-else>
                <InputLabel
                    for="recovery_code"
                    value="Code de récupération"
                />

                <TextInput
                    id="recovery_code"
                    ref="recoveryInput"
                    v-model="form.recovery_code"
                    type="text"
                    class="mt-1 block w-full"
                    autocomplete="one-time-code"
                />

                <InputError
                    class="mt-2"
                    :message="form.errors.recovery_code"
                />
            </div>

            <div class="mt-4 flex items-center justify-between">
                <button
                    type="button"
                    class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-600 focus:ring-offset-2"
                    @click="toggleRecovery"
                >
                    <template v-if="!useRecovery">
                        Utiliser un code de récupération
                    </template>
                    <template v-else>
                        Utiliser un code d'authentification
                    </template>
                </button>

                <PrimaryButton
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Se connecter
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>

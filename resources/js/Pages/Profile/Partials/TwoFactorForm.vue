<script setup>
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { usePage } from '@inertiajs/vue3';
import { computed, nextTick, ref } from 'vue';

const page = usePage();

const enabled = computed(
    () => !!page.props.auth.user.two_factor_confirmed_at,
);

// État local de l'assistant d'activation.
const setupInProgress = ref(false);
const qr = ref(null);
const secret = ref(null);
const confirmCode = ref('');
const recoveryCodes = ref([]);

// Chargement / erreurs.
const processing = ref(false);
const errors = ref({});

// Modales sécurisées par mot de passe.
const showDisableModal = ref(false);
const showRegenerateModal = ref(false);
const currentPassword = ref('');
const passwordInput = ref(null);

const readErrors = (error, fallback) => {
    const data = error?.response?.data;
    if (data?.errors) {
        return data.errors;
    }
    return { general: [data?.message ?? fallback] };
};

const startSetup = async () => {
    processing.value = true;
    errors.value = {};
    recoveryCodes.value = [];

    try {
        const { data } = await window.axios.post(route('two-factor.store'));
        qr.value = data.qr;
        secret.value = data.secret;
        setupInProgress.value = true;
        confirmCode.value = '';
    } catch (error) {
        errors.value = readErrors(error, "Impossible de démarrer l'activation.");
    } finally {
        processing.value = false;
    }
};

const confirmSetup = async () => {
    processing.value = true;
    errors.value = {};

    try {
        const { data } = await window.axios.post(route('two-factor.confirm'), {
            code: confirmCode.value,
        });
        recoveryCodes.value = data.recovery_codes;
        setupInProgress.value = false;
        qr.value = null;
        secret.value = null;
        confirmCode.value = '';
        // Reflète l'activation sans recharger la page.
        page.props.auth.user.two_factor_confirmed_at = new Date().toISOString();
    } catch (error) {
        errors.value = readErrors(error, 'Le code est invalide.');
    } finally {
        processing.value = false;
    }
};

const cancelSetup = () => {
    setupInProgress.value = false;
    qr.value = null;
    secret.value = null;
    confirmCode.value = '';
    errors.value = {};
};

const openDisableModal = () => {
    currentPassword.value = '';
    errors.value = {};
    showDisableModal.value = true;
    nextTick(() => passwordInput.value?.focus());
};

const openRegenerateModal = () => {
    currentPassword.value = '';
    errors.value = {};
    showRegenerateModal.value = true;
    nextTick(() => passwordInput.value?.focus());
};

const closeModals = () => {
    showDisableModal.value = false;
    showRegenerateModal.value = false;
    currentPassword.value = '';
    errors.value = {};
};

const disableTwoFactor = async () => {
    processing.value = true;
    errors.value = {};

    try {
        await window.axios.delete(route('two-factor.destroy'), {
            data: { current_password: currentPassword.value },
        });
        recoveryCodes.value = [];
        page.props.auth.user.two_factor_confirmed_at = null;
        closeModals();
    } catch (error) {
        errors.value = readErrors(error, 'Le mot de passe est incorrect.');
    } finally {
        processing.value = false;
    }
};

const regenerateCodes = async () => {
    processing.value = true;
    errors.value = {};

    try {
        const { data } = await window.axios.post(
            route('two-factor.recovery-codes'),
            { current_password: currentPassword.value },
        );
        recoveryCodes.value = data.recovery_codes;
        closeModals();
    } catch (error) {
        errors.value = readErrors(error, 'Le mot de passe est incorrect.');
    } finally {
        processing.value = false;
    }
};
</script>

<template>
    <section class="space-y-6">
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                Double authentification
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Ajoutez une couche de sécurité supplémentaire à votre compte en
                exigeant un code temporaire à la connexion.
            </p>
        </header>

        <InputError
            v-if="errors.general"
            :message="errors.general[0]"
            class="mt-2"
        />

        <!-- Désactivée : bouton d'activation -->
        <div v-if="!enabled && !setupInProgress">
            <p class="mb-4 text-sm text-gray-600">
                La double authentification n'est pas activée.
            </p>
            <PrimaryButton :disabled="processing" @click="startSetup">
                Activer
            </PrimaryButton>
        </div>

        <!-- Assistant d'activation : QR + confirmation -->
        <div v-if="setupInProgress" class="space-y-4">
            <p class="text-sm text-gray-600">
                Scannez ce QR code avec votre application d'authentification
                (Google Authenticator, Authy…), puis saisissez le code généré
                pour confirmer.
            </p>

            <img
                v-if="qr"
                :src="qr"
                alt="QR code de double authentification"
                class="h-48 w-48 border border-gray-200"
            />

            <p class="text-sm text-gray-600">
                Ou saisissez la clé manuellement :
                <span class="font-mono font-semibold">{{ secret }}</span>
            </p>

            <div>
                <InputLabel for="confirm_code" value="Code de vérification" />
                <TextInput
                    id="confirm_code"
                    v-model="confirmCode"
                    type="text"
                    inputmode="numeric"
                    autocomplete="one-time-code"
                    class="mt-1 block w-full max-w-xs"
                    @keyup.enter="confirmSetup"
                />
                <InputError
                    v-if="errors.code"
                    :message="errors.code[0]"
                    class="mt-2"
                />
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="processing" @click="confirmSetup">
                    Confirmer
                </PrimaryButton>
                <SecondaryButton :disabled="processing" @click="cancelSetup">
                    Annuler
                </SecondaryButton>
            </div>
        </div>

        <!-- Activée -->
        <div v-if="enabled" class="space-y-4">
            <p class="text-sm font-medium text-green-600">
                La double authentification est activée.
            </p>

            <div class="flex items-center gap-4">
                <SecondaryButton
                    :disabled="processing"
                    @click="openRegenerateModal"
                >
                    Régénérer les codes de récupération
                </SecondaryButton>
                <DangerButton :disabled="processing" @click="openDisableModal">
                    Désactiver
                </DangerButton>
            </div>
        </div>

        <!-- Codes de récupération fraîchement générés -->
        <div v-if="recoveryCodes.length" class="space-y-2">
            <p class="text-sm font-semibold text-red-600">
                Conservez ces codes de récupération dans un endroit sûr. Ils
                permettent de récupérer l'accès à votre compte si vous perdez
                votre appareil. Ils ne seront plus affichés.
            </p>
            <pre
                class="rounded-md bg-gray-100 p-4 font-mono text-sm text-gray-800"
            >{{ recoveryCodes.join('\n') }}</pre>
        </div>

        <!-- Modale de désactivation -->
        <Modal :show="showDisableModal" @close="closeModals">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Désactiver la double authentification ?
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    Saisissez votre mot de passe pour confirmer la
                    désactivation.
                </p>

                <div class="mt-6">
                    <InputLabel
                        for="disable_password"
                        value="Mot de passe"
                        class="sr-only"
                    />
                    <TextInput
                        id="disable_password"
                        ref="passwordInput"
                        v-model="currentPassword"
                        type="password"
                        class="mt-1 block w-3/4"
                        placeholder="Mot de passe"
                        @keyup.enter="disableTwoFactor"
                    />
                    <InputError
                        v-if="errors.current_password"
                        :message="errors.current_password[0]"
                        class="mt-2"
                    />
                </div>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="closeModals">
                        Annuler
                    </SecondaryButton>
                    <DangerButton
                        class="ms-3"
                        :class="{ 'opacity-25': processing }"
                        :disabled="processing"
                        @click="disableTwoFactor"
                    >
                        Désactiver
                    </DangerButton>
                </div>
            </div>
        </Modal>

        <!-- Modale de régénération -->
        <Modal :show="showRegenerateModal" @close="closeModals">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Régénérer les codes de récupération ?
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    Les anciens codes seront invalidés. Saisissez votre mot de
                    passe pour confirmer.
                </p>

                <div class="mt-6">
                    <InputLabel
                        for="regenerate_password"
                        value="Mot de passe"
                        class="sr-only"
                    />
                    <TextInput
                        id="regenerate_password"
                        ref="passwordInput"
                        v-model="currentPassword"
                        type="password"
                        class="mt-1 block w-3/4"
                        placeholder="Mot de passe"
                        @keyup.enter="regenerateCodes"
                    />
                    <InputError
                        v-if="errors.current_password"
                        :message="errors.current_password[0]"
                        class="mt-2"
                    />
                </div>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="closeModals">
                        Annuler
                    </SecondaryButton>
                    <PrimaryButton
                        class="ms-3"
                        :class="{ 'opacity-25': processing }"
                        :disabled="processing"
                        @click="regenerateCodes"
                    >
                        Régénérer
                    </PrimaryButton>
                </div>
            </div>
        </Modal>
    </section>
</template>

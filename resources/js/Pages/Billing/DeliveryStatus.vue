<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    order:    Object,
    delivery: Object,
});

const fmt = (n) => new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(n ?? 0);

const steps = [
    { key: 'pending',          icon: '⏳', label: 'Commande enregistrée' },
    { key: 'assigned',         icon: '👤', label: 'Agent assigné' },
    { key: 'out_for_delivery', icon: '🚚', label: 'En cours de livraison' },
    { key: 'payment_received', icon: '💰', label: 'Paiement reçu' },
    { key: 'done',             icon: '✅', label: 'Licence activée' },
];

const currentStepIndex = computed(() => {
    if (!props.delivery) return 0;
    const status = props.delivery.status;
    if (status === 'payment_received') return 4;
    const idx = steps.findIndex((s) => s.key === status);
    return idx >= 0 ? idx : 0;
});

// Saisie du code de confirmation
const codeInput = ref('');
const codeSubmitting = ref(false);
const codeError = ref('');

const submitCode = () => {
    if (!codeInput.value) return;
    codeSubmitting.value = true;
    codeError.value = '';
    router.post(route('billing.delivery.confirm-code'), {
        confirmation_code: codeInput.value.toUpperCase(),
        order_id: props.order.id,
    }, {
        onError: (errors) => {
            codeError.value = errors.confirmation_code ?? 'Code invalide ou expiré.';
        },
        onFinish: () => { codeSubmitting.value = false; },
    });
};
</script>

<template>
    <Head title="Suivi de livraison" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Suivi de livraison — <span class="text-brand-600">{{ order.order_number }}</span>
            </h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-2xl space-y-5 px-4 sm:px-6 lg:px-8">

                <!-- Récapitulatif commande -->
                <div class="rounded-xl bg-gradient-to-r from-brand-900 to-brand-700 p-6 text-white shadow">
                    <div class="text-sm opacity-75">Forfait {{ order.plan }} — paiement à la livraison</div>
                    <div class="mt-1 text-3xl font-extrabold">
                        {{ fmt(order.total_amount) }}
                        <span class="text-lg font-normal">{{ order.currency }}</span>
                    </div>
                    <div class="mt-2 text-sm opacity-75">Préparez ce montant en espèces pour le livreur.</div>
                </div>

                <!-- Timeline de statut -->
                <div class="rounded-xl bg-white p-6 shadow">
                    <h3 class="mb-5 font-semibold text-gray-800">Statut de votre livraison</h3>
                    <ol class="relative border-l border-gray-200 space-y-5 ml-3">
                        <li
                            v-for="(step, idx) in steps"
                            :key="step.key"
                            class="ml-6"
                        >
                            <span
                                class="absolute -left-3 flex h-6 w-6 items-center justify-center rounded-full text-sm ring-4 ring-white"
                                :class="idx <= currentStepIndex ? 'bg-brand-600 text-white' : 'bg-gray-100 text-gray-400'"
                            >
                                {{ step.icon }}
                            </span>
                            <p
                                class="text-sm font-medium"
                                :class="idx <= currentStepIndex ? 'text-brand-700' : 'text-gray-400'"
                            >
                                {{ step.label }}
                            </p>
                        </li>
                    </ol>
                </div>

                <!-- Infos agent si assigné -->
                <div v-if="delivery?.agent" class="rounded-xl border border-brand-200 bg-brand-50 p-5">
                    <h3 class="mb-3 font-semibold text-brand-800">Votre agent de livraison</h3>
                    <div class="text-sm text-brand-700 space-y-1">
                        <div>Nom : <strong>{{ delivery.agent.name }}</strong></div>
                        <div v-if="delivery.agent.phone">
                            Téléphone : <a :href="`tel:${delivery.agent.phone}`" class="font-semibold underline">{{ delivery.agent.phone }}</a>
                        </div>
                    </div>
                </div>

                <!-- Infos livraison -->
                <div v-if="delivery" class="rounded-xl bg-white p-5 shadow text-sm text-gray-700 space-y-2">
                    <h3 class="font-semibold text-gray-800 mb-3">Détails de livraison</h3>
                    <div>Destinataire : <strong>{{ delivery.contact_name }}</strong></div>
                    <div>Téléphone : <strong>{{ delivery.contact_phone }}</strong></div>
                    <div>Adresse : <strong>{{ delivery.delivery_address }}, {{ delivery.delivery_city }}</strong></div>
                    <div v-if="delivery.delivery_notes">Instructions : {{ delivery.delivery_notes }}</div>
                    <div v-if="delivery.payment_confirmed_at" class="mt-2 rounded-md bg-green-50 p-3 text-green-800">
                        Paiement confirmé le {{ delivery.payment_confirmed_at }}
                    </div>
                </div>

                <!-- Saisie du code de confirmation (statut out_for_delivery) -->
                <div v-if="delivery?.status === 'out_for_delivery'" class="rounded-xl bg-white p-5 shadow">
                    <h3 class="mb-3 font-semibold text-gray-800">Confirmer la réception</h3>
                    <p class="mb-3 text-sm text-gray-600">
                        Si le livreur vous a communiqué un code de confirmation, saisissez-le ici pour activer votre licence immédiatement.
                    </p>
                    <form @submit.prevent="submitCode" class="flex gap-3">
                        <input
                            v-model="codeInput"
                            type="text"
                            maxlength="6"
                            placeholder="XXXXXX"
                            class="flex-1 rounded-lg border border-gray-300 px-3 py-2 font-mono text-center uppercase tracking-widest text-gray-900 focus:border-brand-500 focus:ring-brand-500"
                        />
                        <button
                            type="submit"
                            :disabled="codeSubmitting || !codeInput"
                            class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 disabled:opacity-50"
                        >
                            {{ codeSubmitting ? '...' : 'Valider' }}
                        </button>
                    </form>
                    <p v-if="codeError" class="mt-2 text-xs text-red-600">{{ codeError }}</p>
                </div>

                <!-- Bouton support -->
                <div class="text-center">
                    <a
                        href="mailto:support@ibigsoft.com"
                        class="text-sm text-brand-600 hover:underline"
                    >
                        Besoin d'aide ? Contacter le support →
                    </a>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

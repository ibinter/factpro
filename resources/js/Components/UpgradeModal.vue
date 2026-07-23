<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    show: { type: Boolean, default: false },
    feature: { type: String, default: '' },
    currentPlan: { type: String, default: 'starter' },
    requiredPlan: { type: String, default: 'pro' },
});

const emit = defineEmits(['close']);

const planFeatures = {
    pro: [
        '100 templates PDF',
        'API REST complète',
        'Webhooks sortants',
        'Caisse POS',
        'Stocks avancés',
        'Mobile Money automatique',
    ],
    business: [
        'Utilisateurs illimités',
        'Multi-sociétés',
        'White-label',
        'Analytics BI avancé',
        'Module RH & Paie',
        'Support prioritaire',
    ],
    enterprise: [
        'Accès illimité à tout',
        'SLA garanti 99.9%',
        'Gestionnaire de compte dédié',
        'Formation sur site',
        'API sans limite de débit',
    ],
};

const planPrices = {
    pro: 9900,
    business: 19900,
    enterprise: 49900,
};

const features = computed(() => planFeatures[props.requiredPlan] ?? planFeatures.pro);
const price = computed(() => planPrices[props.requiredPlan] ?? planPrices.pro);

const planLabel = computed(() => {
    const labels = { starter: 'Starter', pro: 'Pro', business: 'Business', enterprise: 'Enterprise' };
    return labels[props.requiredPlan] ?? props.requiredPlan;
});

const currentPlanLabel = computed(() => {
    const labels = { starter: 'Starter', pro: 'Pro', business: 'Business', enterprise: 'Enterprise' };
    return labels[props.currentPlan] ?? props.currentPlan;
});

const goToBilling = () => {
    emit('close');
    router.visit('/billing');
};
</script>

<template>
    <Teleport to="body">
        <Transition name="upgrade-modal">
            <div
                v-if="show"
                class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
                @click.self="emit('close')"
            >
                <!-- Backdrop -->
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="emit('close')" />

                <!-- Card -->
                <div class="upgrade-card relative w-full max-w-[480px] overflow-hidden rounded-2xl bg-white shadow-2xl">

                    <!-- Header gradient -->
                    <div class="relative px-6 py-5" style="background: linear-gradient(135deg, #002D5B 0%, #0062CC 100%)">
                        <button
                            type="button"
                            class="absolute right-4 top-4 flex h-7 w-7 items-center justify-center rounded-full text-white/70 transition hover:bg-white/20 hover:text-white"
                            @click="emit('close')"
                            aria-label="Fermer"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>

                        <div class="flex items-center gap-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-white/20 text-xl">🔒</span>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-widest text-blue-200">Fonctionnalité Premium</p>
                                <h2 class="text-lg font-bold text-white">Passez au niveau supérieur</h2>
                            </div>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="px-6 py-5">
                        <!-- Feature name -->
                        <p class="text-sm text-gray-700" v-if="feature">
                            <span class="font-medium text-gray-900">{{ feature }}</span> requiert le plan
                            <span class="font-bold text-[#0062CC]">{{ planLabel }}</span> ou supérieur.
                        </p>
                        <p class="text-sm text-gray-700" v-else>
                            Cette fonctionnalité requiert le plan
                            <span class="font-bold text-[#0062CC]">{{ planLabel }}</span> ou supérieur.
                        </p>

                        <!-- Current plan badge -->
                        <div class="mt-3 inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600">
                            <span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span>
                            Votre plan actuel : {{ currentPlanLabel }}
                        </div>

                        <!-- Feature list -->
                        <div class="mt-4">
                            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-400">Inclus dans le plan {{ planLabel }} :</p>
                            <ul class="space-y-1.5">
                                <li
                                    v-for="f in features"
                                    :key="f"
                                    class="flex items-center gap-2 text-sm text-gray-700"
                                >
                                    <svg class="h-4 w-4 shrink-0 text-[#0062CC]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                    {{ f }}
                                </li>
                            </ul>
                        </div>

                        <!-- Price teaser -->
                        <div class="mt-4 rounded-lg bg-blue-50 px-4 py-2.5 text-sm text-blue-700">
                            À partir de <span class="font-bold">{{ price.toLocaleString('fr-FR') }} FCFA</span> / mois
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="flex items-center gap-3 border-t border-gray-100 px-6 py-4">
                        <button
                            type="button"
                            class="flex-1 rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-50"
                            @click="emit('close')"
                        >
                            Rester sur {{ currentPlanLabel }}
                        </button>
                        <button
                            type="button"
                            class="flex-1 rounded-lg px-4 py-2.5 text-sm font-bold text-[#002D5B] shadow-sm transition hover:brightness-95"
                            style="background-color: #F0C040;"
                            @click="goToBilling"
                        >
                            Passer au plan {{ planLabel }} →
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.upgrade-modal-enter-active,
.upgrade-modal-leave-active {
    transition: opacity 0.2s ease;
}
.upgrade-modal-enter-active .upgrade-card,
.upgrade-modal-leave-active .upgrade-card {
    transition: transform 0.2s ease, opacity 0.2s ease;
}
.upgrade-modal-enter-from {
    opacity: 0;
}
.upgrade-modal-enter-from .upgrade-card {
    transform: scale(0.9);
    opacity: 0;
}
.upgrade-modal-leave-to {
    opacity: 0;
}
.upgrade-modal-leave-to .upgrade-card {
    transform: scale(0.9);
    opacity: 0;
}
</style>

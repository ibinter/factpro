<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['complete', 'skip']);

const currentStep = ref(0);
const animating = ref(false);

const steps = [
    {
        icon: '🎉',
        title: 'Bienvenue !',
        text: "Bienvenue sur IBIG FactPro ! Je suis SARA, votre assistante. Laissez-moi vous faire découvrir les fonctionnalités clés en 2 minutes.",
        cta: 'Commencer la visite →',
    },
    {
        icon: '📊',
        title: 'Votre tableau de bord',
        text: "Retrouvez ici vos KPIs, votre CA du mois, vos factures récentes et les alertes importantes.",
        cta: 'Suivant →',
    },
    {
        icon: '📄',
        title: 'Créer votre première facture',
        text: "Cliquez sur Nouvelle Facture pour créer votre premier document en moins de 30 secondes.",
        cta: 'Suivant →',
    },
    {
        icon: '👥',
        title: 'Vos clients',
        text: "Importez vos clients existants ou créez-les un par un. Retrouvez leur historique complet ici.",
        cta: 'Suivant →',
    },
    {
        icon: '✅',
        title: 'Vous êtes prêt !',
        text: "Vous connaissez l'essentiel. Explorez les autres modules à votre rythme. Le guide utilisateur et SARA sont là pour vous aider.",
        cta: 'Terminer',
    },
];

const totalSteps = steps.length;
const currentStepData = computed(() => steps[currentStep.value]);
const isFirst = computed(() => currentStep.value === 0);
const isLast = computed(() => currentStep.value === totalSteps - 1);

function goTo(index) {
    if (animating.value) return;
    animating.value = true;
    setTimeout(() => {
        currentStep.value = index;
        animating.value = false;
    }, 200);
}

function next() {
    if (isLast.value) {
        complete();
    } else {
        goTo(currentStep.value + 1);
    }
}

function prev() {
    if (!isFirst.value) {
        goTo(currentStep.value - 1);
    }
}

function complete() {
    emit('complete');
    fetch('/onboarding/complete', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '', 'Content-Type': 'application/json' },
    }).catch(() => {});
}

function skip() {
    emit('skip');
    fetch('/onboarding/skip', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '', 'Content-Type': 'application/json' },
    }).catch(() => {});
}

// Reset step when tour re-opens
watch(() => props.show, (val) => {
    if (val) currentStep.value = 0;
});
</script>

<template>
    <Teleport to="body">
        <Transition name="onboarding-fade">
            <div
                v-if="show"
                class="onboarding-overlay"
                role="dialog"
                aria-modal="true"
                aria-labelledby="onboarding-title"
            >
                <!-- Backdrop -->
                <div class="onboarding-backdrop" @click.self="skip" />

                <!-- Modal card -->
                <Transition :name="animating ? 'onboarding-slide' : ''" mode="out-in">
                    <div :key="currentStep" class="onboarding-card">
                        <!-- Header -->
                        <div class="onboarding-header">
                            <div class="onboarding-header-brand">
                                <img src="/logo.svg" alt="IBIG FactPro" class="onboarding-logo" />
                            </div>
                            <button
                                type="button"
                                class="onboarding-close"
                                @click="skip"
                                aria-label="Ignorer la visite"
                            >
                                × Ignorer
                            </button>
                        </div>

                        <!-- Body -->
                        <div class="onboarding-body">
                            <div class="onboarding-icon">{{ currentStepData.icon }}</div>
                            <h2 id="onboarding-title" class="onboarding-title">
                                {{ currentStepData.title }}
                            </h2>
                            <p class="onboarding-text">{{ currentStepData.text }}</p>

                            <!-- Progress bar -->
                            <div class="onboarding-progress-wrap" aria-label="Progression de la visite">
                                <div class="onboarding-progress-track">
                                    <div
                                        class="onboarding-progress-fill"
                                        :style="{ width: `${((currentStep + 1) / totalSteps) * 100}%` }"
                                    />
                                </div>
                                <span class="onboarding-progress-label">
                                    Étape {{ currentStep + 1 }}/{{ totalSteps }}
                                </span>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="onboarding-footer">
                            <button
                                v-if="!isFirst"
                                type="button"
                                class="onboarding-btn-prev"
                                @click="prev"
                            >
                                ← Précédent
                            </button>
                            <span v-else />

                            <button
                                type="button"
                                class="onboarding-btn-next"
                                @click="next"
                            >
                                {{ currentStepData.cta }}
                            </button>
                        </div>

                        <!-- "Retry later" link on last step -->
                        <div v-if="isLast" class="onboarding-retry">
                            <button type="button" class="onboarding-retry-link" @click="skip">
                                Recommencer la visite plus tard
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
/* Overlay */
.onboarding-overlay {
    position: fixed;
    inset: 0;
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
}

.onboarding-backdrop {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.55);
    backdrop-filter: blur(2px);
}

/* Card */
.onboarding-card {
    position: relative;
    z-index: 1;
    background: #ffffff;
    border-radius: 16px;
    width: 100%;
    max-width: 26rem;
    margin: 1rem;
    box-shadow: 0 25px 60px rgba(0, 0, 0, 0.3);
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

/* Header */
.onboarding-header {
    background: #001d3d;
    padding: 1rem 1.25rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.onboarding-logo {
    height: 2rem;
    width: auto;
    filter: brightness(0) invert(1);
}

.onboarding-close {
    color: rgba(255, 255, 255, 0.7);
    background: none;
    border: none;
    cursor: pointer;
    font-size: 0.85rem;
    transition: color 0.15s;
    padding: 0;
}
.onboarding-close:hover { color: #ffffff; }

/* Body */
.onboarding-body {
    padding: 2rem 2rem 1.5rem;
    text-align: center;
}

.onboarding-icon {
    font-size: 3rem;
    line-height: 1;
    margin-bottom: 1rem;
}

.onboarding-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #001d3d;
    margin: 0 0 0.75rem;
}

.onboarding-text {
    font-size: 0.95rem;
    color: #4b5563;
    line-height: 1.6;
    margin: 0 0 1.5rem;
}

/* Progress */
.onboarding-progress-wrap {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.onboarding-progress-track {
    flex: 1;
    height: 6px;
    background: #e5e7eb;
    border-radius: 999px;
    overflow: hidden;
}

.onboarding-progress-fill {
    height: 100%;
    background: #001d3d;
    border-radius: 999px;
    transition: width 0.4s ease;
}

.onboarding-progress-label {
    font-size: 0.78rem;
    color: #9ca3af;
    white-space: nowrap;
}

/* Footer */
.onboarding-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 2rem 1.25rem;
    border-top: 1px solid #f3f4f6;
    gap: 0.75rem;
}

.onboarding-btn-prev {
    background: none;
    border: 1px solid #d1d5db;
    color: #6b7280;
    border-radius: 8px;
    padding: 0.55rem 1.1rem;
    font-size: 0.9rem;
    cursor: pointer;
    transition: background 0.15s, color 0.15s;
}
.onboarding-btn-prev:hover {
    background: #f9fafb;
    color: #374151;
}

.onboarding-btn-next {
    background: #001d3d;
    color: #ffffff;
    border: none;
    border-radius: 8px;
    padding: 0.55rem 1.4rem;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s;
}
.onboarding-btn-next:hover { background: #003063; }

/* Retry link */
.onboarding-retry {
    text-align: center;
    padding: 0 2rem 1.25rem;
}
.onboarding-retry-link {
    background: none;
    border: none;
    color: #9ca3af;
    font-size: 0.8rem;
    cursor: pointer;
    text-decoration: underline;
    transition: color 0.15s;
}
.onboarding-retry-link:hover { color: #4b5563; }

/* Overlay fade transition */
.onboarding-fade-enter-active,
.onboarding-fade-leave-active {
    transition: opacity 0.3s ease;
}
.onboarding-fade-enter-from,
.onboarding-fade-leave-to {
    opacity: 0;
}

/* Step slide transition */
.onboarding-slide-enter-active,
.onboarding-slide-leave-active {
    transition: opacity 0.2s ease, transform 0.2s ease;
}
.onboarding-slide-enter-from {
    opacity: 0;
    transform: translateX(20px);
}
.onboarding-slide-leave-to {
    opacity: 0;
    transform: translateX(-20px);
}
</style>

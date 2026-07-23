<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick, watch } from 'vue';

const props = defineProps({
    steps: { type: Array, required: true },
});
const emit = defineEmits(['complete', 'skip']);

// ── State ────────────────────────────────────────────────────────────────────
const currentStep = ref(0);
const visible     = ref(false);
const tooltipStyle = ref({});
const spotlightStyle = ref({});
const spotlightRect = ref(null);

const step = computed(() => props.steps[currentStep.value]);
const isLast = computed(() => currentStep.value === props.steps.length - 1);

// ── Spotlight / tooltip positioning ──────────────────────────────────────────
function positionForStep(s) {
    if (!s) return;
    const el = document.querySelector(s.target);
    if (!el) {
        // No target found — show centered tooltip, no spotlight
        spotlightRect.value = null;
        spotlightStyle.value = { display: 'none' };
        tooltipStyle.value = {
            position: 'fixed',
            top: '50%',
            left: '50%',
            transform: 'translate(-50%, -50%)',
        };
        return;
    }

    el.scrollIntoView({ behavior: 'smooth', block: 'center' });

    nextTick(() => {
        const rect = el.getBoundingClientRect();
        const pad = 8;
        spotlightRect.value = rect;
        spotlightStyle.value = {
            position: 'fixed',
            top:    `${rect.top  - pad}px`,
            left:   `${rect.left - pad}px`,
            width:  `${rect.width  + pad * 2}px`,
            height: `${rect.height + pad * 2}px`,
            borderRadius: '8px',
            // The "hole" is created via the overlay's clip approach in the template
        };

        // Tooltip position
        const TW = 300; // tooltip width
        const TH = 240; // approx tooltip height
        const margin = 16;
        const pos = s.position ?? 'bottom';
        const vw = window.innerWidth;
        const vh = window.innerHeight;

        let top, left;

        if (pos === 'bottom') {
            top  = rect.bottom + pad + margin;
            left = rect.left + rect.width / 2 - TW / 2;
        } else if (pos === 'top') {
            top  = rect.top - pad - margin - TH;
            left = rect.left + rect.width / 2 - TW / 2;
        } else if (pos === 'right') {
            top  = rect.top + rect.height / 2 - TH / 2;
            left = rect.right + pad + margin;
        } else { // left
            top  = rect.top + rect.height / 2 - TH / 2;
            left = rect.left - pad - margin - TW;
        }

        // Clamp within viewport
        left = Math.max(margin, Math.min(left, vw - TW - margin));
        top  = Math.max(margin, Math.min(top, vh - TH - margin));

        tooltipStyle.value = {
            position: 'fixed',
            top:  `${top}px`,
            left: `${left}px`,
            width: `${TW}px`,
        };
    });
}

// ── Navigation ────────────────────────────────────────────────────────────────
function next() {
    if (isLast.value) {
        complete();
    } else {
        currentStep.value++;
    }
}

function prev() {
    if (currentStep.value > 0) currentStep.value--;
}

function complete() {
    localStorage.setItem('factpro_tour_completed', '1');
    visible.value = false;
    emit('complete');
}

function skip() {
    localStorage.setItem('factpro_tour_completed', '1');
    visible.value = false;
    emit('skip');
}

// Re-position when step changes
watch(currentStep, async () => {
    await nextTick();
    positionForStep(step.value);
}, { immediate: false });

onMounted(async () => {
    await nextTick();
    visible.value = true;
    positionForStep(step.value);

    window.addEventListener('resize', onResize);
});

onUnmounted(() => {
    window.removeEventListener('resize', onResize);
});

function onResize() {
    positionForStep(step.value);
}

// Spotlight hole coords for the SVG overlay approach
const overlayClipId = 'tour-clip-' + Math.random().toString(36).slice(2);
</script>

<template>
    <Teleport to="body">
        <Transition name="tour-fade">
            <div v-if="visible" class="tour-root">

                <!-- Dark overlay with spotlight hole -->
                <div class="tour-overlay" @click.self="skip">
                    <!-- Spotlight highlight box -->
                    <div
                        v-if="spotlightRect"
                        class="tour-spotlight"
                        :style="spotlightStyle"
                    ></div>
                </div>

                <!-- Tooltip card -->
                <Transition name="step-fade" mode="out-in">
                    <div
                        :key="currentStep"
                        class="tour-tooltip"
                        :style="tooltipStyle"
                    >
                        <!-- Header -->
                        <div class="tour-header">
                            <div class="tour-header-left">
                                <span class="tour-step-badge">
                                    Étape {{ currentStep + 1 }} / {{ steps.length }}
                                </span>
                                <h3 class="tour-title">{{ step.title }}</h3>
                            </div>
                            <button class="tour-skip-btn" @click="skip" title="Ignorer">
                                ✕
                            </button>
                        </div>

                        <!-- Body -->
                        <div class="tour-body">
                            <p class="tour-content">{{ step.content }}</p>
                        </div>

                        <!-- Progress dots -->
                        <div class="tour-dots">
                            <span
                                v-for="(_, i) in steps"
                                :key="i"
                                class="tour-dot"
                                :class="{ 'tour-dot-active': i === currentStep }"
                                @click="currentStep = i; positionForStep(steps[i])"
                            ></span>
                        </div>

                        <!-- Footer actions -->
                        <div class="tour-footer">
                            <button
                                v-if="currentStep > 0"
                                class="btn-prev"
                                @click="prev"
                            >
                                ← Précédent
                            </button>
                            <span v-else></span>

                            <div class="tour-footer-right">
                                <button class="btn-skip-text" @click="skip">Ignorer la visite</button>
                                <button
                                    v-if="!isLast"
                                    class="btn-next"
                                    @click="next"
                                >
                                    Suivant →
                                </button>
                                <button
                                    v-else
                                    class="btn-finish"
                                    @click="complete"
                                >
                                    Terminer ✓
                                </button>
                            </div>
                        </div>
                    </div>
                </Transition>

            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
/* ── Root ─────────────────────────────────────────────────────── */
.tour-root {
    position: fixed;
    inset: 0;
    z-index: 9999;
    pointer-events: none;
}

/* ── Overlay ──────────────────────────────────────────────────── */
.tour-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.62);
    pointer-events: all;
}

/* Spotlight: bright cutout via box-shadow */
.tour-spotlight {
    position: fixed;
    pointer-events: none;
    box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.62);
    z-index: 10001;
    background: transparent;
    transition: all 0.3s cubic-bezier(.4,0,.2,1);
    outline: 2px solid rgba(0, 98, 204, 0.6);
    outline-offset: 2px;
}

/* ── Tooltip card ─────────────────────────────────────────────── */
.tour-tooltip {
    position: fixed;
    z-index: 10002;
    pointer-events: all;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 8px 40px rgba(0,0,0,0.22), 0 2px 8px rgba(0,0,0,0.1);
    overflow: hidden;
    min-width: 280px;
}

/* Header */
.tour-header {
    background: #0062CC;
    padding: 0.9rem 1rem 0.75rem;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.5rem;
}
.tour-header-left { flex: 1; }

.tour-step-badge {
    font-size: 0.7rem;
    font-weight: 600;
    letter-spacing: 0.05em;
    color: rgba(255,255,255,0.65);
    text-transform: uppercase;
    display: block;
    margin-bottom: 0.25rem;
}

.tour-title {
    font-size: 0.95rem;
    font-weight: 700;
    color: #fff;
    line-height: 1.3;
}

.tour-skip-btn {
    background: rgba(255,255,255,0.15);
    border: none;
    color: rgba(255,255,255,0.8);
    width: 24px;
    height: 24px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 0.7rem;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: background 0.15s;
    margin-top: 2px;
}
.tour-skip-btn:hover { background: rgba(255,255,255,0.3); }

/* Body */
.tour-body {
    padding: 0.9rem 1rem 0.5rem;
    background: #f9fafb;
}
.tour-content {
    font-size: 0.875rem;
    color: #374151;
    line-height: 1.55;
}

/* Dots */
.tour-dots {
    display: flex;
    justify-content: center;
    gap: 6px;
    padding: 0.6rem 1rem 0.3rem;
    background: #f9fafb;
}
.tour-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #d1d5db;
    cursor: pointer;
    transition: background 0.2s, transform 0.2s;
}
.tour-dot:hover { background: #9ca3af; }
.tour-dot-active {
    background: #0062CC;
    transform: scale(1.25);
}

/* Footer */
.tour-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.65rem 1rem 0.75rem;
    background: #fff;
    border-top: 1px solid #f3f4f6;
    gap: 0.5rem;
}
.tour-footer-right {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-prev {
    font-size: 0.8rem;
    font-weight: 600;
    color: #0062CC;
    background: transparent;
    border: 1.5px solid #0062CC;
    border-radius: 6px;
    padding: 0.4rem 0.85rem;
    cursor: pointer;
    transition: background 0.15s;
}
.btn-prev:hover { background: #eff6ff; }

.btn-skip-text {
    font-size: 0.75rem;
    color: #9ca3af;
    background: none;
    border: none;
    cursor: pointer;
    padding: 0.3rem 0;
    transition: color 0.15s;
}
.btn-skip-text:hover { color: #6b7280; }

.btn-next {
    font-size: 0.8rem;
    font-weight: 700;
    color: #fff;
    background: #0062CC;
    border: none;
    border-radius: 6px;
    padding: 0.4rem 0.9rem;
    cursor: pointer;
    transition: background 0.15s, transform 0.1s;
}
.btn-next:hover { background: #004fa3; transform: translateY(-1px); }

.btn-finish {
    font-size: 0.8rem;
    font-weight: 700;
    color: #001d3d;
    background: #F0C040;
    border: none;
    border-radius: 6px;
    padding: 0.4rem 0.9rem;
    cursor: pointer;
    transition: background 0.15s, transform 0.1s;
    box-shadow: 0 2px 8px rgba(240,192,64,0.35);
}
.btn-finish:hover { background: #e0b030; transform: translateY(-1px); }

/* ── Transitions ──────────────────────────────────────────────── */
.tour-fade-enter-active,
.tour-fade-leave-active { transition: opacity 0.3s ease; }
.tour-fade-enter-from,
.tour-fade-leave-to    { opacity: 0; }

.step-fade-enter-active,
.step-fade-leave-active { transition: opacity 0.2s ease, transform 0.2s ease; }
.step-fade-enter-from   { opacity: 0; transform: translateY(6px); }
.step-fade-leave-to     { opacity: 0; transform: translateY(-6px); }
</style>

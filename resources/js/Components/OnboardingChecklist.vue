<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { usePage } from '@inertiajs/vue3';

const page = usePage();

const loading   = ref(true);
const collapsed = ref(false);
const data      = ref(null);

const STORAGE_KEY = 'factpro_onboarding_dismissed';

// On vérifie d'abord le flag serveur (partagé via Inertia), puis localStorage
const dismissed = ref(
    (page.props.checklist_dismissed === true) ||
    (localStorage.getItem(STORAGE_KEY) === 'true')
);

onMounted(async () => {
    if (dismissed.value) { loading.value = false; return; }
    try {
        const { data: d } = await axios.get('/onboarding/status');
        data.value = d;
        // Auto-dismiss si 100% complété
        if (d.complete || d.checklist_dismissed) {
            localStorage.setItem(STORAGE_KEY, 'true');
            dismissed.value = true;
        }
        // Auto-collapse si déjà bien avancé
        if (d.done >= 5) collapsed.value = true;
    } catch (_) {
        // Silencieux : le widget ne bloque pas le dashboard
    }
    loading.value = false;
});

async function dismiss() {
    localStorage.setItem(STORAGE_KEY, 'true');
    dismissed.value = true;
    try {
        await axios.post('/onboarding/checklist/dismiss');
    } catch (_) {
        // Non bloquant
    }
}

const progressColor = computed(() => {
    const p = data.value?.percent || 0;
    if (p >= 80) return '#057a55';
    if (p >= 50) return '#F0C040';
    return '#1a56db';
});
</script>

<template>
    <div v-if="!loading && !dismissed && data && !data.complete"
         class="mb-6 rounded-2xl border overflow-hidden"
         style="border-left: 4px solid #0062CC; border-color: #e5e7eb; border-left-color: #0062CC">

        <!-- Header -->
        <div class="flex items-center gap-3 px-5 py-4 cursor-pointer select-none"
             style="background: linear-gradient(135deg, #002D5B, #0062CC)"
             @click="collapsed = !collapsed">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-3">
                    <span class="text-base font-bold text-white">🚀 Démarrez avec FactPro</span>
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full"
                          style="background: rgba(255,255,255,.15); color: #fff">
                        {{ data.done }}/{{ data.total }} étapes
                    </span>
                </div>
                <!-- Barre de progression -->
                <div class="mt-2 h-1.5 rounded-full overflow-hidden" style="max-width: 260px; background: rgba(255,255,255,.2)">
                    <div class="h-full rounded-full transition-all duration-500"
                         :style="{ width: data.percent + '%', background: progressColor }"></div>
                </div>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                <span class="text-sm font-bold text-white/90">{{ data.percent }}%</span>
                <svg class="w-5 h-5 transition-transform duration-200"
                     :class="{ 'rotate-180': !collapsed }"
                     style="color: rgba(255,255,255,.7)"
                     fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
            <button @click.stop="dismiss"
                    class="transition ml-1 flex-shrink-0"
                    style="color: rgba(255,255,255,.5)"
                    title="Masquer définitivement">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Steps list -->
        <Transition name="slide-down">
            <div v-if="!collapsed" class="bg-white">
                <!-- Message de félicitations (affiché si 100%) -->
                <div v-if="data.complete"
                     class="flex items-center gap-3 px-5 py-4 bg-green-50 border-t border-green-100">
                    <span class="text-xl">🎉</span>
                    <p class="text-sm font-semibold text-green-700">
                        Vous avez complété la configuration initiale !
                    </p>
                </div>

                <div v-for="step in data.steps" :key="step.id"
                     class="flex items-center gap-4 px-5 py-3.5 transition-colors hover:bg-gray-50 border-t border-gray-100 first:border-t-0">
                    <!-- Checkmark / icône -->
                    <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-base"
                         :style="step.done ? 'background: #d1fae5' : 'background: #f3f4f6'">
                        <span v-if="step.done">✅</span>
                        <span v-else>{{ step.icon }}</span>
                    </div>

                    <!-- Texte -->
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold"
                           :class="step.done ? 'text-gray-400 line-through' : 'text-gray-800'">
                            {{ step.title }}
                        </p>
                        <p v-if="!step.done" class="text-xs text-gray-500 mt-0.5">{{ step.description }}</p>
                    </div>

                    <!-- CTA -->
                    <a v-if="!step.done" :href="step.route"
                       class="flex-shrink-0 rounded-lg px-3 py-1.5 text-xs font-semibold transition hover:scale-105 active:scale-95"
                       style="background: #0062CC; color: #fff">
                        {{ step.cta }}
                    </a>
                    <span v-else class="flex-shrink-0 text-xs font-semibold text-green-600">Fait ✓</span>
                </div>

                <!-- Pied : bouton Masquer -->
                <div class="px-5 py-3 border-t border-gray-100 flex justify-end">
                    <button @click="dismiss"
                            class="text-xs text-gray-400 hover:text-gray-600 transition-colors font-medium">
                        Masquer cette checklist
                    </button>
                </div>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.slide-down-enter-active,
.slide-down-leave-active {
    transition: all 0.25s ease;
}
.slide-down-enter-from,
.slide-down-leave-to {
    opacity: 0;
    transform: translateY(-8px);
}
</style>

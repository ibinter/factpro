<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({ user: Object });

const visible   = ref(false);
const selected  = ref(null);
const comment   = ref('');
const submitted = ref(false);
const sending   = ref(false);

const LS_KEY = 'factpro_nps_dismissed';

onMounted(() => {
    const dismissed = localStorage.getItem(LS_KEY);
    const now       = Date.now();

    // Si déjà rejeté/soumis il y a moins de 30 jours → ne pas afficher
    if (dismissed && now - parseInt(dismissed, 10) < 30 * 24 * 60 * 60 * 1000) {
        return;
    }

    // Vérifier que l'utilisateur a > 7 jours
    if (!props.user?.created_at) return;
    const createdAt = new Date(props.user.created_at).getTime();
    if (now - createdAt < 7 * 24 * 60 * 60 * 1000) return;

    setTimeout(() => { visible.value = true; }, 5000);
});

function dismiss() {
    localStorage.setItem(LS_KEY, Date.now().toString());
    visible.value = false;
}

async function submit() {
    if (selected.value === null) return;
    sending.value = true;
    try {
        await axios.post('/nps', {
            score:   selected.value,
            comment: comment.value,
            context: 'app',
        });
        submitted.value = true;
        localStorage.setItem(LS_KEY, Date.now().toString());
        setTimeout(() => { visible.value = false; }, 2000);
    } finally {
        sending.value = false;
    }
}

function scoreColor(n) {
    if (n <= 6) {
        // rouge → orange dégradé
        const ratio = n / 6;
        const r = 220;
        const g = Math.round(60 + ratio * 100);
        return `rgb(${r},${g},40)`;
    }
    if (n <= 8) return '#D97706'; // ambre
    return '#16A34A'; // vert
}
</script>

<template>
    <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="opacity-0 translate-y-4"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 translate-y-4"
    >
        <div
            v-if="visible"
            class="fixed bottom-24 right-6 z-40 w-80 max-w-sm rounded-2xl border border-gray-100 bg-white shadow-2xl"
        >
            <!-- Header -->
            <div class="rounded-t-2xl px-5 py-4" style="background:#0A2240;">
                <p class="text-sm font-bold text-white">💬 Comment évaluez-vous FactPro ?</p>
                <p class="mt-0.5 text-xs text-blue-200">
                    0 = Pas du tout probable · 10 = Très probable
                </p>
            </div>

            <!-- Corps -->
            <div class="px-5 py-4">
                <template v-if="submitted">
                    <p class="py-4 text-center text-sm font-semibold text-green-600">
                        ✅ Merci pour votre retour !
                    </p>
                </template>
                <template v-else>
                    <!-- Score buttons -->
                    <div class="grid grid-cols-11 gap-1">
                        <button
                            v-for="n in Array.from({length: 11}, (_, i) => i)"
                            :key="n"
                            class="h-7 w-full rounded text-xs font-bold transition"
                            :style="selected === n
                                ? 'background:#0062CC;color:#fff;'
                                : `background:${scoreColor(n)}22;color:${scoreColor(n)};border:1px solid ${scoreColor(n)}44`"
                            @click="selected = n"
                        >
                            {{ n }}
                        </button>
                    </div>

                    <!-- Commentaire -->
                    <textarea
                        v-model="comment"
                        rows="2"
                        placeholder="Un commentaire ? (optionnel)"
                        class="mt-3 w-full resize-none rounded-lg border border-gray-200 px-3 py-2 text-xs focus:border-blue-400 focus:outline-none focus:ring-1 focus:ring-blue-400"
                    />

                    <!-- Actions -->
                    <div class="mt-3 flex gap-2">
                        <button
                            class="flex-1 rounded-lg border border-gray-200 py-2 text-xs font-semibold text-gray-500 transition hover:bg-gray-50"
                            @click="dismiss"
                        >
                            Ignorer
                        </button>
                        <button
                            class="flex-1 rounded-lg py-2 text-xs font-bold text-gray-900 transition hover:opacity-90 disabled:opacity-50"
                            style="background:#F0C040;"
                            :disabled="selected === null || sending"
                            @click="submit"
                        >
                            {{ sending ? '…' : 'Envoyer' }}
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </Transition>
</template>

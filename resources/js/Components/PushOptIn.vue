<script setup>
import { ref, onMounted } from 'vue';
import { PushManager } from '@/Services/PushManager.js';

const supported  = ref(false);
const permission = ref('default');
const hidden     = ref(false);
const loading    = ref(false);

const DISMISS_KEY = 'push_opt_in_dismissed_until';

onMounted(async () => {
    supported.value  = await PushManager.isSupported();
    permission.value = await PushManager.getPermission();

    // Vérifier si l'utilisateur a cliqué "Plus tard" récemment
    const dismissedUntil = localStorage.getItem(DISMISS_KEY);
    if (dismissedUntil && Date.now() < parseInt(dismissedUntil)) {
        hidden.value = true;
    }
});

async function activate() {
    loading.value = true;
    try {
        const result = await Notification.requestPermission();
        permission.value = result;

        if (result === 'granted') {
            await PushManager.subscribe();
        }
    } catch (err) {
        console.error('[PushOptIn] Erreur activation :', err);
    } finally {
        loading.value = false;
    }
}

function dismiss() {
    // Masquer pour 7 jours
    const until = Date.now() + 7 * 24 * 60 * 60 * 1000;
    localStorage.setItem(DISMISS_KEY, String(until));
    hidden.value = true;
}
</script>

<template>
    <!-- Push supporté + pas encore répondu + pas masqué -->
    <div
        v-if="supported && permission === 'default' && !hidden"
        class="mx-auto mt-3 max-w-7xl px-4 sm:px-6 lg:px-8"
    >
        <div
            class="flex items-center justify-between rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800 shadow-sm"
        >
            <span>
                📱 Activer les notifications push pour recevoir vos alertes en temps réel
            </span>
            <div class="ml-4 flex shrink-0 gap-2">
                <button
                    type="button"
                    :disabled="loading"
                    class="rounded bg-blue-600 px-3 py-1 text-xs font-semibold text-white hover:bg-blue-700 disabled:opacity-60"
                    @click="activate"
                >
                    {{ loading ? 'Activation…' : 'Activer' }}
                </button>
                <button
                    type="button"
                    class="rounded border border-blue-300 px-3 py-1 text-xs text-blue-600 hover:bg-blue-100"
                    @click="dismiss"
                >
                    Plus tard
                </button>
            </div>
        </div>
    </div>

    <!-- Push accordé -->
    <div
        v-else-if="supported && permission === 'granted'"
        class="mx-auto mt-3 max-w-7xl px-4 sm:px-6 lg:px-8"
    >
        <p class="text-xs text-green-600">Notifications push activées ✅</p>
    </div>

    <!-- Push refusé -->
    <div
        v-else-if="supported && permission === 'denied'"
        class="mx-auto mt-3 max-w-7xl px-4 sm:px-6 lg:px-8"
    >
        <p class="text-xs text-gray-400">
            Notifications push désactivées dans votre navigateur.
        </p>
    </div>
</template>

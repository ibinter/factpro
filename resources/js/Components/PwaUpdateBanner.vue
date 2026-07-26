<script setup>
import { ref, onMounted } from 'vue';
const show = ref(false);
let registration = null;

onMounted(() => {
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.addEventListener('controllerchange', () => {
            // Nouvelle version active — reload géré manuellement
        });
        navigator.serviceWorker.ready.then(reg => {
            registration = reg;
            reg.addEventListener('updatefound', () => {
                const newWorker = reg.installing;
                newWorker?.addEventListener('statechange', () => {
                    if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                        show.value = true;
                    }
                });
            });
        });
    }
});

function reload() {
    registration?.waiting?.postMessage({ type: 'SKIP_WAITING' });
    window.location.reload();
}
function dismiss() { show.value = false; }
</script>

<template>
    <Transition name="slide-down">
        <div v-if="show" class="fixed top-0 inset-x-0 z-[9999] flex items-center justify-between gap-3 px-4 py-2.5 text-sm font-medium text-white shadow-lg" style="background:#002D5B">
            <span>✨ Nouvelle version d'IBIG FactPro disponible !</span>
            <div class="flex gap-2 flex-shrink-0">
                <button @click="reload" class="rounded-lg px-3 py-1.5 text-xs font-bold transition" style="background:#F0C040;color:#002D5B">Mettre à jour</button>
                <button @click="dismiss" class="rounded-lg px-2 py-1.5 text-xs hover:bg-white/10">✕</button>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.slide-down-enter-active { transition: transform .3s ease, opacity .3s ease; }
.slide-down-leave-active { transition: transform .25s ease, opacity .25s ease; }
.slide-down-enter-from  { transform: translateY(-100%); opacity: 0; }
.slide-down-leave-to    { transform: translateY(-100%); opacity: 0; }
</style>

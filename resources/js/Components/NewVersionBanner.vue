<script setup>
import { ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';

const page = usePage();
const newVersion = page.props.new_version;

const visible = ref(!!newVersion);

function dismiss() {
    visible.value = false;
    router.post('/mark-version-seen', {}, { preserveScroll: true, preserveState: true });
}

function goToChangelog() {
    router.visit('/nouveautes');
    dismiss();
}
</script>

<template>
    <Transition
        enter-active-class="transition-all duration-300 ease-out"
        enter-from-class="opacity-0 -translate-y-2"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition-all duration-200 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 -translate-y-2"
    >
        <div
            v-if="visible && newVersion"
            class="relative z-50 flex items-center justify-between gap-4 px-4 py-3 text-sm text-white"
            style="background-color: #002D5B;"
        >
            <div class="flex items-center gap-2 flex-1 min-w-0">
                <span class="text-base flex-shrink-0">✨</span>
                <span class="truncate">
                    <strong>IBIG FactPro {{ newVersion.version }}</strong> est disponible !
                    <span class="hidden sm:inline opacity-80"> — {{ newVersion.title }}</span>
                </span>
            </div>

            <div class="flex items-center gap-2 flex-shrink-0">
                <button
                    @click="goToChangelog"
                    class="rounded-md px-3 py-1 text-xs font-semibold bg-white text-[#002D5B] hover:bg-blue-50 transition-colors"
                >
                    Voir les nouveautés
                </button>
                <button
                    @click="dismiss"
                    class="rounded-md p-1 text-white/70 hover:text-white hover:bg-white/10 transition-colors"
                    aria-label="Fermer"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </Transition>
</template>

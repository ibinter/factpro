<script setup>
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    canLogin: { type: Boolean, default: true },
    canRegister: { type: Boolean, default: true },
});

const open = ref(false);
</script>

<template>
    <nav class="sticky top-0 z-40 border-b border-gray-100 bg-white/90 backdrop-blur">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
            <Link href="/" class="flex items-center">
                <img src="/logo.svg" alt="IBIG FactPro" class="h-10 w-auto" />
            </Link>

            <!-- Liens desktop -->
            <div class="hidden items-center gap-8 md:flex">
                <a href="/#fonctionnalites" class="text-sm font-semibold text-gray-600 hover:text-brand-600">Fonctionnalités</a>
                <Link :href="route('public.pricing')" class="text-sm font-semibold text-gray-600 hover:text-brand-600">Tarifs</Link>
                <a href="/#faq" class="text-sm font-semibold text-gray-600 hover:text-brand-600">FAQ</a>
            </div>

            <div class="hidden items-center gap-3 md:flex">
                <Link
                    v-if="canLogin"
                    :href="route('login')"
                    class="px-3 py-2 text-sm font-semibold text-brand-900 hover:text-brand-600"
                >
                    Se connecter
                </Link>
                <Link
                    v-if="canRegister"
                    :href="route('register')"
                    class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-bold text-white shadow transition hover:bg-brand-700"
                >
                    Essai gratuit
                </Link>
            </div>

            <!-- Burger mobile -->
            <button
                class="inline-flex items-center justify-center rounded-md p-2 text-brand-900 md:hidden"
                aria-label="Menu"
                @click="open = !open"
            >
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path v-if="!open" stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    <path v-else stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Menu mobile -->
        <div v-show="open" class="border-t border-gray-100 bg-white px-6 py-4 md:hidden">
            <div class="flex flex-col gap-3">
                <a href="/#fonctionnalites" class="text-sm font-semibold text-gray-700" @click="open = false">Fonctionnalités</a>
                <Link :href="route('public.pricing')" class="text-sm font-semibold text-gray-700">Tarifs</Link>
                <a href="/#faq" class="text-sm font-semibold text-gray-700" @click="open = false">FAQ</a>
                <div class="mt-2 flex flex-col gap-2">
                    <Link
                        v-if="canLogin"
                        :href="route('login')"
                        class="rounded-lg border border-gray-200 px-4 py-2 text-center text-sm font-semibold text-brand-900"
                    >
                        Se connecter
                    </Link>
                    <Link
                        v-if="canRegister"
                        :href="route('register')"
                        class="rounded-lg bg-brand-600 px-4 py-2 text-center text-sm font-bold text-white"
                    >
                        Essai gratuit 7 jours
                    </Link>
                </div>
            </div>
        </div>
    </nav>
</template>

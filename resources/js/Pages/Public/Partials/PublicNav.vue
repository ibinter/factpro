<script setup>
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
            <a href="/" class="flex items-center">
                <img src="/logo.svg" alt="IBIG FactPro" class="h-10 w-auto" />
            </a>

            <!-- Liens desktop -->
            <div class="hidden items-center gap-8 md:flex">
                <a href="/#fonctionnalites" class="text-sm font-semibold text-gray-600 hover:text-brand-600">Fonctionnalités</a>
                <a href="/pricing" class="text-sm font-semibold text-gray-600 hover:text-brand-600">Tarifs</a>
                <a href="/#faq" class="text-sm font-semibold text-gray-600 hover:text-brand-600">FAQ</a>
                <a href="/temoignages" class="text-sm font-semibold text-gray-600 hover:text-brand-600">Témoignages</a>
                <a href="/blog" class="text-sm font-semibold text-gray-600 hover:text-brand-600">Blog</a>
                <a href="/roadmap" class="text-sm font-semibold text-gray-600 hover:text-brand-600">Roadmap</a>
                <a href="/a-propos" class="text-sm font-semibold text-gray-600 hover:text-brand-600">À propos</a>
                <a href="/partenaires" class="text-sm font-semibold text-gray-600 hover:text-brand-600">Partenaires</a>
                <a href="/contact" class="text-sm font-semibold text-gray-600 hover:text-brand-600">Contact</a>
            </div>

            <div class="hidden items-center gap-3 md:flex">
                <a
                    href="/demo"
                    class="rounded-lg px-4 py-2 text-sm font-bold shadow transition hover:opacity-90"
                    style="background:#C9A84C;color:#002D5B;"
                >
                    Demander une démo
                </a>
                <a
                    v-if="canLogin"
                    href="/login"
                    class="px-3 py-2 text-sm font-semibold text-brand-900 hover:text-brand-600"
                >
                    Se connecter
                </a>
                <a
                    v-if="canRegister"
                    href="/register"
                    class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-bold text-white shadow transition hover:bg-brand-700"
                >
                    Essai gratuit
                </a>
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
                <a href="/pricing" class="text-sm font-semibold text-gray-700" @click="open = false">Tarifs</a>
                <a href="/#faq" class="text-sm font-semibold text-gray-700" @click="open = false">FAQ</a>
                <a href="/temoignages" class="text-sm font-semibold text-gray-700" @click="open = false">Témoignages</a>
                <a href="/blog" class="text-sm font-semibold text-gray-700" @click="open = false">Blog</a>
                <a href="/roadmap" class="text-sm font-semibold text-gray-700" @click="open = false">Roadmap</a>
                <a href="/a-propos" class="text-sm font-semibold text-gray-700" @click="open = false">À propos</a>
                <a href="/partenaires" class="text-sm font-semibold text-gray-700" @click="open = false">Partenaires</a>
                <a href="/contact" class="text-sm font-semibold text-gray-700" @click="open = false">Contact</a>
                <div class="mt-2 flex flex-col gap-2">
                    <a
                        href="/demo"
                        class="rounded-lg px-4 py-2 text-center text-sm font-bold"
                        style="background:#C9A84C;color:#002D5B;"
                        @click="open = false"
                    >
                        Demander une démo
                    </a>
                    <a
                        v-if="canLogin"
                        href="/login"
                        class="rounded-lg border border-gray-200 px-4 py-2 text-center text-sm font-semibold text-brand-900"
                    >
                        Se connecter
                    </a>
                    <a
                        v-if="canRegister"
                        href="/register"
                        class="rounded-lg bg-brand-600 px-4 py-2 text-center text-sm font-bold text-white"
                    >
                        Essai gratuit 7 jours
                    </a>
                </div>
            </div>
        </div>
    </nav>
</template>

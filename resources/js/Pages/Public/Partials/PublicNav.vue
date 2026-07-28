<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

defineProps({
    canLogin:    { type: Boolean, default: true },
    canRegister: { type: Boolean, default: true },
});

const mobileOpen = ref(false);
const activeDropdown = ref(null);

const menus = [
    {
        key: 'product',
        label: 'Produit',
        items: [
            { href: '/#fonctionnalites', label: 'Fonctionnalités', desc: 'Tout ce que FactPro peut faire', icon: '⚡' },
            { href: '/changelog',        label: 'Nouveautés',      desc: 'Dernières mises à jour',       icon: '🚀' },
            { href: '/roadmap',          label: 'Roadmap',         desc: 'Ce qui arrive bientôt',        icon: '🗺️' },
        ],
    },
    {
        key: 'resources',
        label: 'Ressources',
        items: [
            { href: '/#faq',        label: 'FAQ',          desc: 'Questions fréquentes',         icon: '❓' },
            { href: '/blog',        label: 'Blog',         desc: 'Conseils & actualités',        icon: '📝' },
            { href: '/temoignages', label: 'Témoignages',  desc: 'Ce qu\'en disent nos clients', icon: '⭐' },
            { href: '/aide',        label: 'Aide',         desc: 'Centre d\'assistance',         icon: '🛟' },
        ],
    },
    {
        key: 'company',
        label: 'Entreprise',
        items: [
            { href: '/a-propos',   label: 'À propos',    desc: 'Notre mission & équipe',   icon: '🏢' },
            { href: '/partenaires', label: 'Partenaires', desc: 'Devenir revendeur',        icon: '🤝' },
            { href: '/contact',    label: 'Contact',     desc: 'Écrivez-nous',             icon: '✉️' },
        ],
    },
];

function toggle(key) {
    activeDropdown.value = activeDropdown.value === key ? null : key;
}

function closeAll() {
    activeDropdown.value = null;
}

function onKey(e) {
    if (e.key === 'Escape') closeAll();
}

onMounted(() => document.addEventListener('keydown', onKey));
onUnmounted(() => document.removeEventListener('keydown', onKey));
</script>

<template>
    <!-- Overlay to close dropdowns on outside click -->
    <div v-if="activeDropdown" class="fixed inset-0 z-30" @click="closeAll" />

    <nav class="sticky top-0 z-40 border-b border-gray-100 bg-white/95 shadow-sm backdrop-blur">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-6 py-3">

            <!-- Logo -->
            <a href="/" class="flex shrink-0 items-center">
                <img src="/logo.svg" alt="IBIG FactPro" class="h-9 w-auto" />
            </a>

            <!-- Desktop nav -->
            <div class="hidden items-center gap-1 lg:flex">

                <!-- Dropdown menus -->
                <div v-for="menu in menus" :key="menu.key" class="relative">
                    <button
                        @click="toggle(menu.key)"
                        class="flex items-center gap-1 rounded-lg px-3 py-2 text-sm font-semibold text-gray-600 transition hover:bg-gray-50 hover:text-brand-600"
                        :class="activeDropdown === menu.key ? 'bg-gray-50 text-brand-600' : ''"
                    >
                        {{ menu.label }}
                        <svg
                            class="h-3.5 w-3.5 transition-transform duration-200"
                            :class="activeDropdown === menu.key ? 'rotate-180' : ''"
                            fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <!-- Dropdown panel -->
                    <transition
                        enter-active-class="transition duration-150 ease-out"
                        enter-from-class="opacity-0 -translate-y-1 scale-95"
                        enter-to-class="opacity-100 translate-y-0 scale-100"
                        leave-active-class="transition duration-100 ease-in"
                        leave-from-class="opacity-100 translate-y-0 scale-100"
                        leave-to-class="opacity-0 -translate-y-1 scale-95"
                    >
                        <div
                            v-if="activeDropdown === menu.key"
                            class="absolute left-0 top-full z-50 mt-1.5 w-60 rounded-xl border border-gray-100 bg-white p-1.5 shadow-xl"
                        >
                            <a
                                v-for="item in menu.items"
                                :key="item.href"
                                :href="item.href"
                                class="flex items-start gap-3 rounded-lg px-3 py-2.5 transition hover:bg-brand-50"
                                @click="closeAll"
                            >
                                <span class="mt-0.5 text-base leading-none">{{ item.icon }}</span>
                                <span>
                                    <span class="block text-sm font-semibold text-gray-800">{{ item.label }}</span>
                                    <span class="block text-xs text-gray-400 mt-0.5">{{ item.desc }}</span>
                                </span>
                            </a>
                        </div>
                    </transition>
                </div>

                <!-- Direct link: Tarifs -->
                <a href="/pricing" class="rounded-lg px-3 py-2 text-sm font-semibold text-gray-600 transition hover:bg-gray-50 hover:text-brand-600">
                    Tarifs
                </a>

            </div>

            <!-- CTAs desktop -->
            <div class="hidden items-center gap-2 lg:flex">
                <a
                    href="/demo"
                    class="rounded-lg px-4 py-2 text-sm font-bold shadow-sm transition hover:opacity-90"
                    style="background:#C9A84C;color:#002D5B;"
                >
                    Demander une démo
                </a>
                <a
                    v-if="canLogin"
                    href="/login"
                    class="rounded-lg px-3 py-2 text-sm font-semibold text-gray-700 transition hover:text-brand-600"
                >
                    Se connecter
                </a>
                <a
                    v-if="canRegister"
                    href="/register"
                    class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-brand-700"
                >
                    Essai gratuit
                </a>
            </div>

            <!-- Burger mobile -->
            <button
                class="inline-flex items-center justify-center rounded-md p-2 text-gray-600 lg:hidden"
                aria-label="Menu"
                @click="mobileOpen = !mobileOpen"
            >
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path v-if="!mobileOpen" stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    <path v-else stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Menu mobile -->
        <transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0 -translate-y-2"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 -translate-y-2"
        >
            <div v-show="mobileOpen" class="border-t border-gray-100 bg-white px-6 py-4 lg:hidden">
                <div class="flex flex-col gap-1">

                    <!-- Grouped sections mobile -->
                    <div v-for="menu in menus" :key="'m-' + menu.key">
                        <p class="mb-1 mt-3 px-2 text-[11px] font-bold uppercase tracking-widest text-gray-400">
                            {{ menu.label }}
                        </p>
                        <a
                            v-for="item in menu.items"
                            :key="item.href"
                            :href="item.href"
                            class="flex items-center gap-2 rounded-lg px-2 py-2 text-sm font-semibold text-gray-700 hover:bg-brand-50 hover:text-brand-600"
                            @click="mobileOpen = false"
                        >
                            <span class="text-base">{{ item.icon }}</span>
                            {{ item.label }}
                        </a>
                    </div>

                    <div>
                        <p class="mb-1 mt-3 px-2 text-[11px] font-bold uppercase tracking-widest text-gray-400">Offres</p>
                        <a href="/pricing" class="flex items-center gap-2 rounded-lg px-2 py-2 text-sm font-semibold text-gray-700 hover:bg-brand-50 hover:text-brand-600" @click="mobileOpen = false">
                            <span class="text-base">💳</span> Tarifs
                        </a>
                    </div>

                    <div class="mt-4 flex flex-col gap-2 border-t border-gray-100 pt-4">
                        <a
                            href="/demo"
                            class="rounded-lg px-4 py-2.5 text-center text-sm font-bold"
                            style="background:#C9A84C;color:#002D5B;"
                            @click="mobileOpen = false"
                        >
                            Demander une démo
                        </a>
                        <a
                            v-if="canLogin"
                            href="/login"
                            class="rounded-lg border border-gray-200 px-4 py-2.5 text-center text-sm font-semibold text-gray-700"
                            @click="mobileOpen = false"
                        >
                            Se connecter
                        </a>
                        <a
                            v-if="canRegister"
                            href="/register"
                            class="rounded-lg bg-brand-600 px-4 py-2.5 text-center text-sm font-bold text-white"
                            @click="mobileOpen = false"
                        >
                            Essai gratuit 7 jours
                        </a>
                    </div>
                </div>
            </div>
        </transition>
    </nav>
</template>

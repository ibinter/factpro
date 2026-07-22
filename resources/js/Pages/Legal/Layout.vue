<script setup>
import { Head } from '@inertiajs/vue3';
import PublicNav from '@/Pages/Public/Partials/PublicNav.vue';
import PublicFooter from '@/Pages/Public/Partials/PublicFooter.vue';

defineProps({
    title: { type: String, required: true },
    lastUpdated: { type: String, default: '22 juillet 2026' },
    canLogin: { type: Boolean, default: true },
    canRegister: { type: Boolean, default: true },
});
</script>

<template>
    <Head :title="`${title} — IBIG FactPro`" />
    <div class="min-h-screen bg-white">
        <PublicNav :can-login="canLogin" :can-register="canRegister" />
        <div class="mx-auto max-w-4xl px-6 py-16">
            <div class="mb-8">
                <a href="/" class="text-sm text-blue-600 hover:underline">← Accueil</a>
            </div>
            <h1 class="text-3xl font-extrabold text-gray-900 mb-2">{{ title }}</h1>
            <p class="text-sm text-gray-400 mb-10">Dernière mise à jour : {{ lastUpdated }}</p>
            <div class="prose prose-gray max-w-none text-sm leading-relaxed">
                <slot />
            </div>
        </div>
        <PublicFooter />
    </div>
</template>

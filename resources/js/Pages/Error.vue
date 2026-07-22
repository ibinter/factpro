<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    status: { type: Number, required: true },
});

const ERRORS = {
    503: { icon: '🔧', title: 'Service indisponible', desc: 'Le service est temporairement indisponible pour maintenance. Veuillez réessayer dans quelques minutes.' },
    500: { icon: '💥', title: 'Erreur serveur', desc: "Une erreur inattendue s'est produite. Notre équipe en a été notifiée. Veuillez réessayer ou contacter le support." },
    404: { icon: '🔍', title: 'Page introuvable', desc: "La page que vous recherchez n'existe pas ou a été déplacée." },
    403: { icon: '🔒', title: 'Accès refusé', desc: "Vous n'êtes pas autorisé à accéder à cette ressource." },
    419: { icon: '⏱️', title: 'Session expirée', desc: 'Votre session a expiré. Veuillez recharger la page et réessayer.' },
    429: { icon: '🚦', title: 'Trop de requêtes', desc: 'Vous avez effectué trop de requêtes en peu de temps. Veuillez patienter quelques instants.' },
};
const info = computed(() => ERRORS[props.status] || { icon: '❓', title: 'Erreur', desc: "Une erreur s'est produite." });
</script>

<template>
    <Head :title="`Erreur ${status} — IBIG FactPro`" />

    <div class="min-h-screen flex items-center justify-center px-6" style="background:linear-gradient(135deg,#001d3d,#0062CC)">
        <div class="text-center max-w-lg">
            <div class="text-8xl mb-6">{{ info.icon }}</div>
            <div class="text-7xl font-extrabold text-white/20 mb-2">{{ status }}</div>
            <h1 class="text-2xl font-extrabold text-white mb-4">{{ info.title }}</h1>
            <p class="text-white/70 mb-8 leading-relaxed">{{ info.desc }}</p>

            <div class="flex flex-wrap justify-center gap-4">
                <a href="/"
                   class="rounded-xl px-6 py-3 text-sm font-bold transition hover:scale-105"
                   style="background:#F0C040;color:#001d3d">
                    ← Retour à l'accueil
                </a>
                <a href="/login"
                   class="rounded-xl border px-6 py-3 text-sm font-semibold text-white transition hover:bg-white/10"
                   style="border-color:rgba(255,255,255,.3)">
                    Se connecter
                </a>
                <a href="mailto:factpro@ibigsoft.com"
                   class="rounded-xl border px-6 py-3 text-sm font-semibold text-white transition hover:bg-white/10"
                   style="border-color:rgba(255,255,255,.3)">
                    Contacter le support
                </a>
            </div>

            <p class="mt-8 text-xs text-white/30">
                IBIG FactPro · <a href="https://ibigsoft.com" class="hover:text-white/60">ibigsoft.com</a>
            </p>
        </div>
    </div>
</template>

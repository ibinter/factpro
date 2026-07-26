<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';

const copied = ref(false);

const profiles = [
    {
        key: 'admin',
        label: 'Administrateur',
        description: 'Accès complet — gestion entreprise, utilisateurs, rapports',
        email: 'admin@demo.factpro.ibigsoft.com',
        icon: '🏢',
        color: 'from-brand-800 to-brand-900',
    },
    {
        key: 'comptable',
        label: 'Comptable',
        description: 'Factures, paiements, rapports comptables, export',
        email: 'comptable@demo.factpro.ibigsoft.com',
        icon: '📊',
        color: 'from-emerald-700 to-emerald-900',
    },
    {
        key: 'caissier',
        label: 'Caissier',
        description: 'Encaissements, reçus, journal de caisse du jour',
        email: 'caissier@demo.factpro.ibigsoft.com',
        icon: '💳',
        color: 'from-amber-600 to-amber-800',
    },
];

function goToLogin(email) {
    window.location.href = '/login?email=' + encodeURIComponent(email);
}
</script>

<template>
    <Head title="Essayer la démo — FactPro" />

    <div class="min-h-screen bg-gradient-to-br from-brand-950 via-brand-900 to-brand-800 flex flex-col items-center justify-center px-4 py-12">

        <!-- Logo -->
        <a href="/">
            <img src="/logo.svg" alt="IBIG FactPro" class="h-16 w-auto rounded-xl bg-white/95 p-2 shadow-lg mb-8" />
        </a>

        <!-- Header -->
        <div class="text-center mb-10 max-w-lg">
            <h1 class="text-3xl font-bold text-white mb-3">
                Essayez FactPro sans inscription
            </h1>
            <p class="text-brand-200 text-base">
                Compte de démonstration — les données sont réinitialisées toutes les 24h
            </p>
            <div class="mt-4 inline-flex items-center gap-2 bg-amber-400/20 border border-amber-400/40 rounded-full px-4 py-1.5 text-amber-300 text-sm">
                <span>⚡</span>
                <span>Accès instantané — aucune carte bancaire requise</span>
            </div>
        </div>

        <!-- Mot de passe commun -->
        <div class="mb-6 flex items-center gap-3 bg-white/10 border border-amber-400/40 rounded-xl px-5 py-3 text-sm text-white">
            <span class="text-amber-300 text-lg">🔑</span>
            <span>Mot de passe pour tous les comptes : <strong class="text-amber-300 font-mono text-base tracking-widest">demo1234</strong></span>
            <button
                @click="navigator.clipboard.writeText('demo1234').then(() => copied = true)"
                class="ml-2 text-xs bg-white/10 hover:bg-white/20 border border-white/20 rounded-lg px-2 py-1 transition"
                title="Copier"
            >{{ copied ? '✅ Copié' : '📋 Copier' }}</button>
        </div>

        <!-- Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 w-full max-w-3xl">
            <div
                v-for="profile in profiles"
                :key="profile.key"
                class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl p-6 flex flex-col items-center text-center hover:bg-white/15 transition-all duration-200 hover:scale-105 hover:shadow-2xl"
            >
                <div class="text-5xl mb-4">{{ profile.icon }}</div>
                <h2 class="text-white font-bold text-lg mb-2">{{ profile.label }}</h2>
                <p class="text-brand-200 text-sm mb-4 leading-relaxed">{{ profile.description }}</p>
                <!-- Identifiants visibles -->
                <div class="w-full bg-black/20 rounded-xl p-3 mb-4 text-left text-xs space-y-1">
                    <div class="flex justify-between text-white/60">
                        <span>Email</span>
                        <span class="text-white font-mono truncate ml-2 max-w-[140px]">{{ profile.email }}</span>
                    </div>
                    <div class="flex justify-between text-white/60">
                        <span>Mot de passe</span>
                        <span class="text-amber-300 font-mono font-bold tracking-widest">demo1234</span>
                    </div>
                </div>
                <button
                    @click="goToLogin(profile.email)"
                    class="w-full bg-amber-400 hover:bg-amber-300 text-amber-900 font-semibold py-2.5 px-4 rounded-xl transition-colors duration-150 text-sm shadow"
                >
                    Se connecter →
                </button>
            </div>
        </div>

        <!-- Footer links -->
        <div class="mt-10 flex flex-col sm:flex-row gap-4 items-center text-sm">
            <Link
                href="/register"
                class="text-amber-300 hover:text-amber-200 underline underline-offset-2"
            >
                Créer un vrai compte — essai 7 jours gratuit
            </Link>
            <span class="hidden sm:inline text-white/30">·</span>
            <Link
                href="/login"
                class="text-white/50 hover:text-white/80 transition-colors"
            >
                J'ai déjà un compte →
            </Link>
        </div>

        <p class="mt-8 text-xs text-white/30">
            © {{ new Date().getFullYear() }} IBIG SARL — factpro.ibigsoft.com
        </p>
    </div>
</template>

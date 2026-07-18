<script setup>
import { Link } from '@inertiajs/vue3';

const tabs = [
    { label: '📊 Tableau de bord', route: 'admin.dashboard' },
    { label: '📈 Finances', route: 'admin.financial-dashboard' },
    { label: '💳 Paiements', route: 'admin.payments' },
    { label: '📋 File de validation', route: 'admin.payment-queue' },
    { label: '🔑 Licences', route: 'admin.licenses' },
    { label: '🗂 Gestionnaire licences', route: 'admin.license-manager' },
    { label: '🏦 Moyens de paiement', route: 'admin.methods' },
    { label: '⚙️ Config. paiement', route: 'admin.payment-method-settings' },
    { label: '📦 Forfaits', route: 'admin.plans' },
    { label: '🎟 Coupons', route: 'admin.coupons' },
    { label: '🌍 Passerelles', route: 'admin.gateways' },
    { label: '🎨 White-label', route: 'admin.white-label.index' },
];

const isActive = (name) => {
    try {
        return route().current(name);
    } catch (e) {
        return false;
    }
};
</script>

<template>
    <nav class="flex flex-wrap gap-2">
        <Link
            v-for="tab in tabs"
            :key="tab.route"
            :href="route(tab.route)"
            class="rounded-full px-4 py-2 text-sm font-semibold transition"
            :class="isActive(tab.route)
                ? 'bg-brand-600 text-white shadow'
                : 'bg-white text-gray-600 hover:bg-brand-50 hover:text-brand-700'"
        >
            {{ tab.label }}
        </Link>
    </nav>
</template>

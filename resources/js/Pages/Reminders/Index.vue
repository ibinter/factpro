<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    overdue: Array,
    history: Array,
    stats: Object,
    settings: Object,
    currency: String,
});

const fmt = (n) => new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(n ?? 0);
const fmtDate = (d) => (d ? new Intl.DateTimeFormat('fr-FR').format(new Date(d)) : '—');
const fmtDateTime = (d) =>
    d ? new Intl.DateTimeFormat('fr-FR', { dateStyle: 'short', timeStyle: 'short' }).format(new Date(d)) : '—';

const levelLabels = { 1: 'Courtois', 2: 'Ferme', 3: 'Mise en demeure' };
const levelColors = {
    1: 'bg-blue-100 text-blue-700',
    2: 'bg-amber-100 text-amber-700',
    3: 'bg-red-100 text-red-700',
};

/* Panneau paramètres repliable */
const showSettings = ref(false);
const settingsForm = useForm({
    enabled: props.settings.enabled,
    levels: {
        1: props.settings.levels.find((l) => l.level === 1)?.days ?? 3,
        2: props.settings.levels.find((l) => l.level === 2)?.days ?? 7,
        3: props.settings.levels.find((l) => l.level === 3)?.days ?? 15,
    },
});
const saveSettings = () => {
    settingsForm.patch(route('reminders.settings'), { preserveScroll: true });
};

/* Relance manuelle */
const sending = ref(null);
const sendReminder = (invoice) => {
    if (
        !confirm(
            `Envoyer la relance niveau ${invoice.next_level} (${levelLabels[invoice.next_level]}) ` +
                `pour la facture ${invoice.number} à ${invoice.customer_name ?? 'ce client'} ?`,
        )
    ) {
        return;
    }
    sending.value = invoice.id;
    router.post(
        route('reminders.send', invoice.id),
        {},
        {
            preserveScroll: true,
            onFinish: () => (sending.value = null),
        },
    );
};
</script>

<template>
    <Head title="Relances" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Relances intelligentes</h2>
                <button
                    type="button"
                    class="rounded-md border border-brand-600 px-4 py-2 text-sm font-semibold text-brand-600 hover:bg-brand-50"
                    @click="showSettings = !showSettings"
                >
                    ⚙ Paramètres
                </button>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <!-- Cartes stats -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div class="rounded-lg bg-white p-5 shadow">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Factures en retard</p>
                        <p class="mt-2 text-3xl font-bold text-red-600">{{ stats.overdue_count }}</p>
                    </div>
                    <div class="rounded-lg bg-white p-5 shadow">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Montant total dû</p>
                        <p class="mt-2 text-3xl font-bold text-brand-600">{{ fmt(stats.overdue_total) }} {{ currency }}</p>
                    </div>
                    <div class="rounded-lg bg-white p-5 shadow">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Relances envoyées ce mois</p>
                        <p class="mt-2 text-3xl font-bold text-gray-800">{{ stats.sent_this_month }}</p>
                    </div>
                </div>

                <!-- Panneau paramètres repliable -->
                <div v-if="showSettings" class="rounded-lg bg-white p-6 shadow">
                    <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">
                        Paramètres des relances automatiques
                    </h3>
                    <form class="space-y-4" @submit.prevent="saveSettings">
                        <label class="flex items-center gap-3">
                            <input
                                v-model="settingsForm.enabled"
                                type="checkbox"
                                class="rounded border-gray-300 text-brand-600 shadow-sm focus:ring-brand-500"
                            />
                            <span class="text-sm font-medium text-gray-700">
                                Relances automatiques activées (escalade quotidienne)
                            </span>
                        </label>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <div v-for="n in [1, 2, 3]" :key="n">
                                <label class="mb-1 block text-sm font-medium text-gray-700">
                                    <span class="mr-1 rounded-full px-2 py-0.5 text-xs font-semibold" :class="levelColors[n]">
                                        Niveau {{ n }}
                                    </span>
                                    {{ levelLabels[n] }} — jours après échéance
                                </label>
                                <input
                                    v-model.number="settingsForm.levels[n]"
                                    type="number"
                                    min="1"
                                    max="90"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                                />
                            </div>
                        </div>

                        <p v-if="settingsForm.errors.levels" class="text-sm text-red-600">{{ settingsForm.errors.levels }}</p>
                        <p v-if="settingsForm.errors.enabled" class="text-sm text-red-600">{{ settingsForm.errors.enabled }}</p>

                        <div class="flex justify-end">
                            <button
                                type="submit"
                                :disabled="settingsForm.processing"
                                class="rounded-md bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 disabled:opacity-50"
                            >
                                Enregistrer
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Factures en retard -->
                <div>
                    <h3 class="mb-2 text-sm font-semibold uppercase tracking-wide text-gray-500">Factures en retard</h3>
                    <div class="overflow-hidden rounded-lg bg-white shadow">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                                <tr>
                                    <th class="px-6 py-3">Numéro</th>
                                    <th class="px-6 py-3">Client</th>
                                    <th class="px-6 py-3">Échéance</th>
                                    <th class="px-6 py-3">Retard</th>
                                    <th class="px-6 py-3 text-right">Restant dû</th>
                                    <th class="px-6 py-3">Dernier rappel</th>
                                    <th class="px-6 py-3 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr v-for="inv in overdue" :key="inv.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-3">
                                        <Link
                                            :href="route('documents.show', inv.id)"
                                            class="font-semibold text-brand-600 hover:underline"
                                        >
                                            {{ inv.number }}
                                        </Link>
                                    </td>
                                    <td class="px-6 py-3">{{ inv.customer_name ?? '—' }}</td>
                                    <td class="px-6 py-3 text-gray-500">{{ fmtDate(inv.due_date) }}</td>
                                    <td class="px-6 py-3">
                                        <span class="rounded-full bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-700">
                                            J+{{ inv.days_late }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 text-right font-semibold">
                                        {{ fmt(inv.balance_due) }} {{ inv.currency }}
                                    </td>
                                    <td class="px-6 py-3">
                                        <template v-if="inv.last_level">
                                            <span
                                                class="rounded-full px-2 py-0.5 text-xs font-semibold"
                                                :class="levelColors[inv.last_level]"
                                            >
                                                Niveau {{ inv.last_level }}
                                            </span>
                                            <span class="ml-1 text-xs text-gray-500">{{ fmtDateTime(inv.last_sent_at) }}</span>
                                        </template>
                                        <span v-else class="text-gray-400">Aucun</span>
                                    </td>
                                    <td class="px-6 py-3 text-right">
                                        <button
                                            v-if="inv.next_level"
                                            type="button"
                                            :disabled="sending === inv.id"
                                            class="rounded-md bg-brand-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-brand-700 disabled:opacity-50"
                                            @click="sendReminder(inv)"
                                        >
                                            Relancer (niveau {{ inv.next_level }})
                                        </button>
                                        <span v-else class="text-xs text-gray-400">Mise en demeure envoyée</span>
                                    </td>
                                </tr>
                                <tr v-if="!overdue.length">
                                    <td colspan="7" class="px-6 py-10 text-center text-gray-400">
                                        Aucune facture en retard. 🎉
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Historique des relances -->
                <div>
                    <h3 class="mb-2 text-sm font-semibold uppercase tracking-wide text-gray-500">
                        Historique des relances (50 dernières)
                    </h3>
                    <div class="overflow-hidden rounded-lg bg-white shadow">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                                <tr>
                                    <th class="px-6 py-3">Facture</th>
                                    <th class="px-6 py-3">Niveau</th>
                                    <th class="px-6 py-3">Destinataire</th>
                                    <th class="px-6 py-3">Date d'envoi</th>
                                    <th class="px-6 py-3">Déclencheur</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr v-for="log in history" :key="log.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-3">
                                        <Link
                                            :href="route('documents.show', log.document_id)"
                                            class="font-semibold text-brand-600 hover:underline"
                                        >
                                            {{ log.number ?? '—' }}
                                        </Link>
                                    </td>
                                    <td class="px-6 py-3">
                                        <span
                                            class="rounded-full px-2 py-0.5 text-xs font-semibold"
                                            :class="levelColors[log.level]"
                                        >
                                            {{ log.level }} — {{ log.level_label }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 text-gray-600">{{ log.sent_to }}</td>
                                    <td class="px-6 py-3 text-gray-500">{{ fmtDateTime(log.sent_at) }}</td>
                                    <td class="px-6 py-3">
                                        <span v-if="log.triggered_by === 'auto'" class="text-gray-500">Automatique</span>
                                        <span v-else class="text-gray-700">
                                            Manuelle<span v-if="log.sender_name"> ({{ log.sender_name }})</span>
                                        </span>
                                    </td>
                                </tr>
                                <tr v-if="!history.length">
                                    <td colspan="5" class="px-6 py-10 text-center text-gray-400">
                                        Aucune relance envoyée pour l'instant.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

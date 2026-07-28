<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    rules: Array,
});

/* ── Formulaire ajout ──────────────────────────────────────────── */
const form = useForm({
    days_after_due: '',
    channel: 'email',
    message_template: '',
    is_active: true,
});

const editingId = ref(null);
const editForm = useForm({
    days_after_due: '',
    channel: 'email',
    message_template: '',
    is_active: true,
});

/* ── Templates pré-définis ──────────────────────────────────────── */
const TEMPLATES = [
    {
        label: 'Rappel courtois (J+3)',
        days: 3,
        channel: 'email',
        message: 'Bonjour {client_name},\n\nNous vous rappelons que la facture {invoice_number} d\'un montant de {amount} est arrivée à échéance il y a {days_overdue} jour(s).\n\nMerci de procéder au règlement dans les meilleurs délais.\n\nCordialement,\n{company_name}',
    },
    {
        label: 'Relance ferme (J+7)',
        days: 7,
        channel: 'email',
        message: 'Bonjour {client_name},\n\nMalgré notre précédent rappel, la facture {invoice_number} ({amount}) demeure impayée depuis {days_overdue} jours.\n\nNous vous demandons de régulariser cette situation sous 48 heures afin d\'éviter des pénalités de retard.\n\nCordialement,\n{company_name}',
    },
    {
        label: 'Mise en demeure (J+15)',
        days: 15,
        channel: 'email',
        message: 'Madame, Monsieur,\n\nEn dépit de nos relances successives, la facture {invoice_number} d\'un montant de {amount} reste impayée depuis {days_overdue} jours.\n\nNous vous mettons en demeure de régler cette somme dans un délai de 72 heures, faute de quoi nous nous verrons dans l\'obligation d\'engager une procédure de recouvrement.\n\n{company_name}',
    },
];

function applyTemplate(tpl) {
    form.days_after_due = tpl.days;
    form.channel = tpl.channel;
    form.message_template = tpl.message;
}

function submitCreate() {
    form.post(route('reminder-rules.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
}

function startEdit(rule) {
    editingId.value = rule.id;
    editForm.days_after_due = rule.days_after_due;
    editForm.channel = rule.channel;
    editForm.message_template = rule.message_template ?? '';
    editForm.is_active = rule.is_active;
}

function submitEdit(rule) {
    editForm.put(route('reminder-rules.update', rule.id), {
        preserveScroll: true,
        onSuccess: () => { editingId.value = null; },
    });
}

function cancelEdit() {
    editingId.value = null;
}

function deleteRule(rule) {
    if (!confirm('Supprimer cette règle de rappel ?')) return;
    router.delete(route('reminder-rules.destroy', rule.id), { preserveScroll: true });
}

const channelLabel = (c) => ({ email: 'Email', whatsapp: 'WhatsApp' }[c] ?? c);
const channelBadge = (c) => c === 'whatsapp'
    ? 'bg-green-100 text-green-700'
    : 'bg-blue-100 text-blue-700';
</script>

<template>
    <Head title="Règles de rappel" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200">
                Rappels de paiement intelligents
            </h2>
        </template>

        <div class="py-8 max-w-4xl mx-auto px-4 space-y-8">

            <!-- Templates pré-définis -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
                <h3 class="font-semibold text-gray-700 dark:text-gray-200 mb-4">
                    Templates pré-définis
                </h3>
                <div class="grid gap-3 sm:grid-cols-3">
                    <button
                        v-for="tpl in TEMPLATES"
                        :key="tpl.label"
                        type="button"
                        @click="applyTemplate(tpl)"
                        class="text-left border border-gray-200 dark:border-gray-600 rounded-lg p-3 hover:border-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 transition"
                    >
                        <p class="font-medium text-sm text-gray-800 dark:text-gray-100">{{ tpl.label }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            J+{{ tpl.days }} · {{ channelLabel(tpl.channel) }}
                        </p>
                    </button>
                </div>
            </div>

            <!-- Formulaire ajout -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
                <h3 class="font-semibold text-gray-700 dark:text-gray-200 mb-4">
                    Ajouter une règle
                </h3>

                <form @submit.prevent="submitCreate" class="space-y-4">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Jours après échéance
                            </label>
                            <input
                                v-model="form.days_after_due"
                                type="number" min="1" max="365"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="ex : 7"
                                required
                            />
                            <p v-if="form.errors.days_after_due" class="mt-1 text-xs text-red-500">{{ form.errors.days_after_due }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Canal
                            </label>
                            <select
                                v-model="form.channel"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="email">Email</option>
                                <option value="whatsapp">WhatsApp</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Message personnalisé
                            <span class="font-normal text-gray-400">(optionnel — variables : {client_name}, {invoice_number}, {amount}, {days_overdue}, {company_name})</span>
                        </label>
                        <textarea
                            v-model="form.message_template"
                            rows="5"
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="Laissez vide pour utiliser le message par défaut"
                        />
                    </div>

                    <div class="flex items-center gap-2">
                        <input id="is_active" v-model="form.is_active" type="checkbox" class="rounded border-gray-300 text-indigo-600" />
                        <label for="is_active" class="text-sm text-gray-700 dark:text-gray-300">Règle active</label>
                    </div>

                    <div class="flex justify-end">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-5 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 transition text-sm font-medium"
                        >
                            Ajouter la règle
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tableau des règles -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-700 dark:text-gray-200">
                        Règles configurées
                        <span class="ml-2 text-sm font-normal text-gray-400">({{ rules.length }})</span>
                    </h3>
                </div>

                <div v-if="!rules.length" class="px-6 py-10 text-center text-gray-400 text-sm">
                    Aucune règle configurée. Ajoutez-en une ci-dessus ou choisissez un template.
                </div>

                <div v-else class="divide-y divide-gray-100 dark:divide-gray-700">
                    <div v-for="rule in rules" :key="rule.id" class="px-6 py-4">

                        <!-- Vue lecture -->
                        <div v-if="editingId !== rule.id" class="flex items-start gap-4">
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-2 mb-1">
                                    <span class="font-semibold text-gray-800 dark:text-gray-100">
                                        J+{{ rule.days_after_due }}
                                    </span>
                                    <span :class="['text-xs px-2 py-0.5 rounded-full font-medium', channelBadge(rule.channel)]">
                                        {{ channelLabel(rule.channel) }}
                                    </span>
                                    <span v-if="!rule.is_active" class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-500">
                                        Inactive
                                    </span>
                                </div>
                                <p v-if="rule.message_template" class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2 whitespace-pre-wrap">
                                    {{ rule.message_template }}
                                </p>
                                <p v-else class="text-xs text-gray-400 italic">Message par défaut</p>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <button @click="startEdit(rule)" class="text-xs text-indigo-600 hover:underline">Modifier</button>
                                <button @click="deleteRule(rule)" class="text-xs text-red-500 hover:underline">Supprimer</button>
                            </div>
                        </div>

                        <!-- Vue édition inline -->
                        <form v-else @submit.prevent="submitEdit(rule)" class="space-y-3">
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Jours après échéance</label>
                                    <input v-model="editForm.days_after_due" type="number" min="1" max="365"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm" required />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Canal</label>
                                    <select v-model="editForm.channel"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                        <option value="email">Email</option>
                                        <option value="whatsapp">WhatsApp</option>
                                    </select>
                                </div>
                            </div>
                            <textarea v-model="editForm.message_template" rows="4"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm"
                                placeholder="Message personnalisé (optionnel)" />
                            <div class="flex items-center gap-3">
                                <label class="flex items-center gap-1.5 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                                    <input v-model="editForm.is_active" type="checkbox" class="rounded border-gray-300 text-indigo-600" />
                                    Active
                                </label>
                                <div class="flex-1" />
                                <button type="button" @click="cancelEdit" class="text-sm text-gray-500 hover:underline">Annuler</button>
                                <button type="submit" :disabled="editForm.processing"
                                    class="px-4 py-1.5 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 disabled:opacity-50">
                                    Enregistrer
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    channels: Array,
});

/* ── Modal ajout ── */
const showModal = ref(false);
const editChannel = ref(null);

const form = useForm({
    type: 'sms',
    provider: 'africas_talking',
    config: {
        api_key: '',
        username: '',
        account_sid: '',
        auth_token: '',
        from_number: '',
        test_number: '',
    },
});

const providerOptions = computed(() => {
    return form.type === 'sms'
        ? [{ value: 'africas_talking', label: "Africa's Talking" }]
        : [{ value: 'twilio', label: 'Twilio WhatsApp' }];
});

function openModal(channel = null) {
    editChannel.value = channel;
    if (channel) {
        form.type = channel.type;
        form.provider = channel.provider;
        form.config.test_number = channel.test_number ?? '';
    } else {
        form.reset();
        form.type = 'sms';
        form.provider = 'africas_talking';
    }
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
    editChannel.value = null;
    form.reset();
    form.clearErrors();
}

function onTypeChange() {
    form.provider = form.type === 'sms' ? 'africas_talking' : 'twilio';
}

function submitChannel() {
    if (editChannel.value) {
        form.put(route('notification-channels.update', editChannel.value.id), {
            onSuccess: closeModal,
        });
    } else {
        form.post(route('notification-channels.store'), {
            onSuccess: closeModal,
        });
    }
}

function deleteChannel(channel) {
    if (!confirm(`Supprimer le canal ${channel.type.toUpperCase()} (${channel.provider}) ?`)) return;
    router.delete(route('notification-channels.destroy', channel.id));
}

function testChannel(channel) {
    router.post(route('notification-channels.test', channel.id));
}

const typeLabel = { sms: 'SMS', whatsapp: 'WhatsApp' };
const providerLabel = { africas_talking: "Africa's Talking", twilio: 'Twilio' };
</script>

<template>
    <Head title="Canaux de notification" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
                Canaux de notification SMS / WhatsApp
            </h2>
        </template>

        <div class="py-8 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Info relances -->
            <div class="rounded-lg border border-brand-600/30 bg-brand-50 dark:bg-brand-900/20 p-4 text-sm text-brand-900 dark:text-brand-200">
                📲 Les relances automatiques <strong>J+3, J+7, J+15</strong> utiliseront ces canaux en
                <strong>complément de l'email</strong> pour notifier vos clients par SMS ou WhatsApp.
            </div>

            <!-- Liste canaux -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="font-medium text-gray-900 dark:text-gray-100">Canaux configurés</h3>
                    <button
                        @click="openModal()"
                        class="inline-flex items-center gap-1.5 rounded-md bg-brand-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-brand-700 transition"
                    >
                        + Ajouter un canal
                    </button>
                </div>

                <div v-if="channels.length === 0" class="py-12 text-center text-gray-400 text-sm">
                    Aucun canal configuré. Ajoutez un canal SMS ou WhatsApp pour enrichir vos relances.
                </div>

                <table v-else class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/40">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fournisseur</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">N° test</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-for="ch in channels" :key="ch.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                            <td class="px-6 py-4 text-sm font-semibold text-gray-900 dark:text-gray-100">
                                {{ typeLabel[ch.type] ?? ch.type }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                {{ providerLabel[ch.provider] ?? ch.provider }}
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    :class="ch.is_active
                                        ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300'
                                        : 'bg-gray-100 text-gray-500'"
                                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                >
                                    {{ ch.is_active ? 'Actif' : 'Inactif' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 font-mono">
                                {{ ch.test_number ?? '—' }}
                            </td>
                            <td class="px-6 py-4 text-right space-x-2 whitespace-nowrap">
                                <button
                                    @click="testChannel(ch)"
                                    class="text-xs font-medium text-brand-600 hover:text-brand-800"
                                    title="Envoyer un message de test"
                                >Test</button>
                                <button
                                    @click="openModal(ch)"
                                    class="text-xs font-medium text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                                >Modifier</button>
                                <button
                                    @click="deleteChannel(ch)"
                                    class="text-xs font-medium text-red-500 hover:text-red-700"
                                >Supprimer</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal ajout / édition -->
        <Teleport to="body">
            <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-lg mx-4 p-6 space-y-5">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            {{ editChannel ? 'Modifier le canal' : 'Ajouter un canal' }}
                        </h3>
                        <button @click="closeModal" class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
                    </div>

                    <form @submit.prevent="submitChannel" class="space-y-4">
                        <!-- Type -->
                        <div v-if="!editChannel">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type</label>
                            <select v-model="form.type" @change="onTypeChange" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm">
                                <option value="sms">SMS</option>
                                <option value="whatsapp">WhatsApp</option>
                            </select>
                        </div>

                        <!-- Provider -->
                        <div v-if="!editChannel">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fournisseur</label>
                            <select v-model="form.provider" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm">
                                <option v-for="opt in providerOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                            </select>
                        </div>

                        <!-- Africa's Talking fields -->
                        <template v-if="form.provider === 'africas_talking'">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Clé API</label>
                                <input v-model="form.config.api_key" type="password" placeholder="Votre clé API Africa's Talking"
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm" />
                                <p v-if="form.errors['config.api_key']" class="mt-1 text-xs text-red-500">{{ form.errors['config.api_key'] }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Username</label>
                                <input v-model="form.config.username" type="text" placeholder="sandbox (test) ou votre username"
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm" />
                                <p v-if="form.errors['config.username']" class="mt-1 text-xs text-red-500">{{ form.errors['config.username'] }}</p>
                            </div>
                        </template>

                        <!-- Twilio fields -->
                        <template v-if="form.provider === 'twilio'">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Account SID</label>
                                <input v-model="form.config.account_sid" type="text"
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm" />
                                <p v-if="form.errors['config.account_sid']" class="mt-1 text-xs text-red-500">{{ form.errors['config.account_sid'] }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Auth Token</label>
                                <input v-model="form.config.auth_token" type="password"
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm" />
                                <p v-if="form.errors['config.auth_token']" class="mt-1 text-xs text-red-500">{{ form.errors['config.auth_token'] }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Numéro expéditeur (sans +)</label>
                                <input v-model="form.config.from_number" type="text" placeholder="14155238886"
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm" />
                                <p v-if="form.errors['config.from_number']" class="mt-1 text-xs text-red-500">{{ form.errors['config.from_number'] }}</p>
                            </div>
                        </template>

                        <!-- Numéro de test -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Numéro de test (sans +)</label>
                            <input v-model="form.config.test_number" type="text" placeholder="22500000000"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm" />
                            <p class="mt-1 text-xs text-gray-400">Utilisé par le bouton « Test »</p>
                        </div>

                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" @click="closeModal"
                                class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                                Annuler
                            </button>
                            <button type="submit" :disabled="form.processing"
                                class="rounded-md bg-brand-600 px-4 py-2 text-sm font-medium text-white hover:bg-brand-700 disabled:opacity-50">
                                {{ form.processing ? 'Enregistrement…' : 'Enregistrer' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>

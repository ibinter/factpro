<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdminTabs from '@/Components/AdminTabs.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    gateways: Array,
});

const gatewayMeta = {
    moneroo: {
        label: 'Moneroo',
        emoji: '💳',
        description: 'Agrégateur multi-opérateurs Afrique de l\'Ouest',
        fields: [
            { key: 'secret_key', label: 'Clé secrète', type: 'password' },
            { key: 'public_key', label: 'Clé publique', type: 'text' },
            { key: 'webhook_secret', label: 'Secret webhook', type: 'password' },
        ],
    },
    cinetpay: {
        label: 'CinetPay',
        emoji: '🌍',
        description: 'Mobile Money CI, SN, BF, ML, CM et plus',
        fields: [
            { key: 'api_key', label: 'API Key', type: 'password' },
            { key: 'site_id', label: 'Site ID', type: 'text' },
        ],
    },
    fedapay: {
        label: 'FedaPay',
        emoji: '🇧🇯',
        description: 'Paiements Bénin, Togo, Sénégal, Côte d\'Ivoire',
        fields: [
            { key: 'secret_key', label: 'Clé secrète', type: 'password' },
        ],
    },
    flutterwave: {
        label: 'Flutterwave',
        emoji: '🦋',
        description: 'Paiements multi-pays : CI, SN, GH, NG, CM et plus',
        fields: [
            { key: 'secret_key', label: 'Secret Key', type: 'password' },
            { key: 'public_key', label: 'Public Key', type: 'text' },
            { key: 'secret_hash', label: 'Secret Hash (webhook)', type: 'password' },
        ],
    },
};

// Un form par gateway
const forms = Object.fromEntries(
    (props.gateways ?? []).map((g) => [
        g.gateway,
        useForm({
            is_active: g.is_active,
            config: { ...(g.config ?? {}) },
        }),
    ])
);

const submit = (g) => {
    forms[g.gateway].put(route('admin.gateways.update', g.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Admin — Passerelles de paiement" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Console Superadmin — <span class="text-gold-600">Passerelles de paiement</span>
            </h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <AdminTabs />

                <div class="grid gap-6 md:grid-cols-2">
                    <div
                        v-for="g in gateways"
                        :key="g.id"
                        class="rounded-xl bg-white p-6 shadow"
                        :class="{ 'opacity-60': !forms[g.gateway]?.data?.is_active && !forms[g.gateway]?.is_active }"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-2xl">{{ gatewayMeta[g.gateway]?.emoji ?? '🔌' }}</span>
                                    <span class="text-lg font-bold text-gray-800">
                                        {{ gatewayMeta[g.gateway]?.label ?? g.gateway }}
                                    </span>
                                    <span
                                        class="rounded-full px-2 py-0.5 text-xs font-semibold"
                                        :class="forms[g.gateway]?.is_active
                                            ? 'bg-green-100 text-green-700'
                                            : 'bg-gray-100 text-gray-500'"
                                    >
                                        {{ forms[g.gateway]?.is_active ? 'Actif' : 'Inactif' }}
                                    </span>
                                </div>
                                <p class="mt-1 text-sm text-gray-500">{{ gatewayMeta[g.gateway]?.description }}</p>
                            </div>

                            <!-- Toggle actif/inactif -->
                            <label class="flex cursor-pointer items-center gap-2 text-sm text-gray-600">
                                <div class="relative">
                                    <input
                                        v-model="forms[g.gateway].is_active"
                                        type="checkbox"
                                        class="sr-only"
                                    />
                                    <div
                                        class="h-6 w-11 rounded-full transition"
                                        :class="forms[g.gateway].is_active ? 'bg-green-500' : 'bg-gray-300'"
                                    ></div>
                                    <div
                                        class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow transition"
                                        :class="forms[g.gateway].is_active ? 'translate-x-5' : 'translate-x-0'"
                                    ></div>
                                </div>
                            </label>
                        </div>

                        <!-- Pays & devises supportés -->
                        <div class="mt-4 flex flex-wrap gap-2">
                            <span
                                v-for="country in g.supported_countries"
                                :key="country"
                                class="rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700"
                            >{{ country }}</span>
                            <span
                                v-for="currency in g.supported_currencies"
                                :key="currency"
                                class="rounded-full bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-700"
                            >{{ currency }}</span>
                        </div>

                        <!-- Champs de configuration -->
                        <div class="mt-5 space-y-3">
                            <div
                                v-for="field in gatewayMeta[g.gateway]?.fields ?? []"
                                :key="field.key"
                            >
                                <InputLabel :value="field.label" />
                                <TextInput
                                    v-model="forms[g.gateway].config[field.key]"
                                    :type="field.type"
                                    class="mt-1 block w-full font-mono text-sm"
                                    :placeholder="field.type === 'password' ? '••••••••••••' : ''"
                                    autocomplete="off"
                                />
                            </div>
                        </div>

                        <div class="mt-5 flex justify-end">
                            <PrimaryButton
                                :disabled="forms[g.gateway]?.processing"
                                @click="submit(g)"
                            >
                                Enregistrer
                            </PrimaryButton>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

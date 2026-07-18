<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    hasAccess: Boolean,
    planCode: String,
    tokens: Array,
    plainToken: String,
    apiBaseUrl: String,
});

const form = useForm({
    name: '',
    abilities: ['read'],
});

const submit = () => {
    form.post(route('api-tokens.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};

const confirmingRevoke = ref(null);

const revoke = () => {
    router.delete(route('api-tokens.destroy', confirmingRevoke.value.id), {
        preserveScroll: true,
        onSuccess: () => (confirmingRevoke.value = null),
    });
};

const copied = ref(false);
const copyToken = async () => {
    try {
        await navigator.clipboard.writeText(props.plainToken);
        copied.value = true;
        setTimeout(() => (copied.value = false), 2500);
    } catch {
        window.prompt('Copiez votre clé API :', props.plainToken);
    }
};

const abilityLabel = (ability) => (ability === 'write' ? 'Écriture' : 'Lecture');

const curlExample = `curl -H "Authorization: Bearer VOTRE_TOKEN" \\
     -H "Accept: application/json" \\
     ${props.apiBaseUrl}/customers`;
</script>

<template>
    <Head title="Clés API" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Clés API</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">
                <!-- Upsell : forfait insuffisant -->
                <div v-if="!hasAccess" class="overflow-hidden rounded-xl bg-white shadow">
                    <div class="bg-brand-900 px-8 py-10 text-center">
                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-gold-400/20">
                            <svg class="h-7 w-7 text-gold-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                            </svg>
                        </div>
                        <h3 class="mt-4 text-2xl font-bold text-white">API REST FactPro</h3>
                        <p class="mx-auto mt-3 max-w-xl text-sm text-brand-100/90">
                            Intégrez FactPro à vos propres outils : clients, produits, factures et PDF,
                            le tout en JSON sécurisé par tokens.
                        </p>
                        <p class="mt-5 inline-block rounded-full bg-white/10 px-4 py-1.5 text-sm font-semibold text-gold-400">
                            L'API REST est disponible à partir du forfait BUSINESS
                        </p>
                    </div>
                    <div class="flex flex-col items-center gap-4 px-8 py-8">
                        <ul class="grid gap-2 text-sm text-gray-600 sm:grid-cols-2">
                            <li class="flex items-center gap-2"><span class="text-brand-600">✓</span> BUSINESS : 1 000 requêtes / heure</li>
                            <li class="flex items-center gap-2"><span class="text-brand-600">✓</span> ENTERPRISE : requêtes illimitées</li>
                            <li class="flex items-center gap-2"><span class="text-brand-600">✓</span> Clients, produits &amp; documents</li>
                            <li class="flex items-center gap-2"><span class="text-brand-600">✓</span> Génération de PDF à la volée</li>
                        </ul>
                        <Link
                            :href="route('billing.plans')"
                            class="rounded-md bg-brand-600 px-6 py-2.5 text-sm font-semibold text-white shadow hover:bg-brand-700"
                        >
                            Passer au forfait BUSINESS
                        </Link>
                        <p class="text-xs text-gray-400">Votre forfait actuel : {{ planCode ? planCode.toUpperCase() : '—' }}</p>
                    </div>
                </div>

                <template v-else>
                    <!-- Token en clair : affiché UNE SEULE fois -->
                    <div v-if="plainToken" class="rounded-xl border-2 border-gold-400 bg-gold-400/10 p-5 shadow">
                        <div class="flex items-start gap-3">
                            <svg class="mt-0.5 h-6 w-6 shrink-0 text-gold-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                            </svg>
                            <div class="min-w-0 flex-1">
                                <h3 class="font-semibold text-gray-800">Votre nouvelle clé API</h3>
                                <p class="mt-1 text-sm font-semibold text-amber-700">
                                    Copiez-la maintenant : elle ne sera plus jamais affichée.
                                </p>
                                <div class="mt-3 flex flex-wrap items-center gap-2">
                                    <code class="max-w-full overflow-x-auto whitespace-nowrap rounded-md bg-brand-900 px-3 py-2 font-mono text-sm text-gold-400">{{ plainToken }}</code>
                                    <PrimaryButton @click="copyToken">
                                        {{ copied ? 'Copié ✓' : 'Copier' }}
                                    </PrimaryButton>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Création d'une clé -->
                    <div class="rounded-xl bg-white p-6 shadow">
                        <h3 class="text-lg font-semibold text-gray-800">Créer une clé API</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Chaque clé est liée à votre compte et agit sur votre société courante.
                        </p>
                        <form class="mt-4 flex flex-wrap items-end gap-4" @submit.prevent="submit">
                            <div class="min-w-64 flex-1">
                                <InputLabel for="token-name" value="Nom de la clé *" />
                                <TextInput
                                    id="token-name"
                                    v-model="form.name"
                                    class="mt-1 block w-full"
                                    placeholder="Ex. : Intégration boutique en ligne"
                                    required
                                />
                                <InputError :message="form.errors.name" class="mt-1" />
                            </div>
                            <div>
                                <InputLabel value="Droits" />
                                <div class="mt-2 flex gap-4">
                                    <label class="flex items-center gap-2 text-sm text-gray-700">
                                        <input
                                            v-model="form.abilities"
                                            type="checkbox"
                                            value="read"
                                            class="rounded border-gray-300 text-brand-600 shadow-sm focus:ring-brand-500"
                                        />
                                        Lecture
                                    </label>
                                    <label class="flex items-center gap-2 text-sm text-gray-700">
                                        <input
                                            v-model="form.abilities"
                                            type="checkbox"
                                            value="write"
                                            class="rounded border-gray-300 text-brand-600 shadow-sm focus:ring-brand-500"
                                        />
                                        Écriture
                                    </label>
                                </div>
                                <InputError :message="form.errors.abilities" class="mt-1" />
                            </div>
                            <PrimaryButton :disabled="form.processing || !form.abilities.length">
                                Générer la clé
                            </PrimaryButton>
                        </form>
                    </div>

                    <!-- Liste des clés -->
                    <div class="overflow-hidden rounded-xl bg-white shadow">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                                <tr>
                                    <th class="px-6 py-3">Nom</th>
                                    <th class="px-6 py-3">Droits</th>
                                    <th class="px-6 py-3">Dernier usage</th>
                                    <th class="px-6 py-3">Créée le</th>
                                    <th class="px-6 py-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr v-for="token in tokens" :key="token.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-3 font-semibold text-gray-800">{{ token.name }}</td>
                                    <td class="px-6 py-3">
                                        <span
                                            v-for="ability in token.abilities"
                                            :key="ability"
                                            class="mr-1 rounded-full px-2 py-0.5 text-xs font-semibold"
                                            :class="ability === 'write' ? 'bg-amber-100 text-amber-700' : 'bg-brand-50 text-brand-700'"
                                        >
                                            {{ abilityLabel(ability) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 text-gray-500">{{ token.last_used_at ?? 'Jamais utilisée' }}</td>
                                    <td class="px-6 py-3 text-gray-500">{{ token.created_at }}</td>
                                    <td class="px-6 py-3 text-right">
                                        <button class="text-sm font-semibold text-red-500 hover:underline" @click="confirmingRevoke = token">
                                            Révoquer
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="!tokens.length">
                                    <td colspan="5" class="px-6 py-10 text-center text-gray-400">
                                        Aucune clé API pour le moment. Créez-en une ci-dessus.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Démarrage rapide -->
                    <div class="rounded-xl bg-white p-6 shadow">
                        <h3 class="text-lg font-semibold text-gray-800">Démarrage rapide</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Appelez l'API avec votre clé dans l'en-tête <code class="rounded bg-gray-100 px-1.5 py-0.5 text-xs">Authorization</code> :
                        </p>
                        <pre class="mt-3 overflow-x-auto rounded-lg bg-brand-900 p-4 font-mono text-sm leading-relaxed text-gold-400">{{ curlExample }}</pre>
                        <p class="mt-3 text-sm text-gray-500">
                            Quota : <strong>BUSINESS</strong> 1 000 requêtes/heure · <strong>ENTERPRISE</strong> illimité.
                            Documentation complète :
                            <a :href="`${apiBaseUrl}/docs`" target="_blank" class="font-semibold text-brand-600 hover:underline">
                                {{ apiBaseUrl }}/docs
                            </a>
                        </p>
                    </div>
                </template>
            </div>
        </div>

        <!-- Confirmation révocation -->
        <Modal :show="!!confirmingRevoke" @close="confirmingRevoke = null">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-800">Révoquer cette clé API ?</h3>
                <p class="mt-2 text-sm text-gray-500">
                    « {{ confirmingRevoke?.name }} » sera immédiatement invalidée. Toute intégration
                    qui l'utilise cessera de fonctionner. Cette action est irréversible.
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="confirmingRevoke = null">Annuler</SecondaryButton>
                    <DangerButton @click="revoke">Révoquer la clé</DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

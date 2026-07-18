<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    agents: Object,
});

const showCreate = ref(false);
const createForm = useForm({
    name:    '',
    phone:   '',
    email:   '',
    zone:    '',
    city:    '',
    country: 'CI',
});

const submitCreate = () => {
    createForm.post(route('admin.delivery-agents.store'), {
        onSuccess: () => {
            showCreate.value = false;
            createForm.reset();
        },
    });
};

const toggleActive = (agent) => {
    router.put(route('admin.delivery-agents.update', agent.id), {
        is_active: !agent.is_active,
    }, { preserveScroll: true });
};

const destroy = (agent) => {
    if (confirm(`Désactiver l'agent ${agent.name} ?`)) {
        router.delete(route('admin.delivery-agents.destroy', agent.id), { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="Agents de livraison" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Agents de livraison</h2>
                <button @click="showCreate = true"
                    class="rounded-md bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                    + Nouvel agent
                </button>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-xl bg-white shadow overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600">Nom</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600">Téléphone</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600">Ville / Zone</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600">Statut</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-if="!agents.data?.length">
                                <td colspan="5" class="px-4 py-8 text-center text-gray-400">Aucun agent enregistré.</td>
                            </tr>
                            <tr v-for="a in agents.data" :key="a.id" class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium">{{ a.name }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ a.phone ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ [a.city, a.zone].filter(Boolean).join(' / ') || '—' }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium"
                                        :class="a.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'">
                                        {{ a.is_active ? 'Actif' : 'Inactif' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex gap-2">
                                        <button @click="toggleActive(a)"
                                            class="rounded bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-700 hover:bg-gray-200">
                                            {{ a.is_active ? 'Désactiver' : 'Activer' }}
                                        </button>
                                        <button v-if="!a.deleted_at" @click="destroy(a)"
                                            class="rounded bg-red-100 px-2 py-1 text-xs font-semibold text-red-700 hover:bg-red-200">
                                            Supprimer
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal créer agent -->
        <div v-if="showCreate" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-4 font-semibold text-gray-800">Nouvel agent de livraison</h3>
                <form @submit.prevent="submitCreate" class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nom *</label>
                        <input v-model="createForm.name" type="text" required
                            class="block w-full rounded-md border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500" />
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
                            <input v-model="createForm.phone" type="tel"
                                class="block w-full rounded-md border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input v-model="createForm.email" type="email"
                                class="block w-full rounded-md border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ville</label>
                            <input v-model="createForm.city" type="text"
                                class="block w-full rounded-md border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Zone</label>
                            <input v-model="createForm.zone" type="text"
                                class="block w-full rounded-md border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500" />
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="showCreate = false" class="text-sm text-gray-500 hover:text-gray-700">Annuler</button>
                        <button type="submit" :disabled="createForm.processing"
                            class="rounded-md bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 disabled:opacity-50">
                            Créer
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </AuthenticatedLayout>
</template>

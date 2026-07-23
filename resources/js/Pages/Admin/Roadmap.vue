<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdminTabs from '@/Components/AdminTabs.vue';

const props = defineProps({
    features: { type: Array, default: () => [] },
});

const flash = ref('');

// Form ajout
const form = useForm({
    title:       '',
    description: '',
    category:    'general',
    status:      'planned',
    sort_order:  0,
});

function store() {
    form.post(route('admin.roadmap.store'), {
        preserveScroll: true,
        onSuccess: () => { form.reset(); flash.value = 'Fonctionnalité ajoutée.'; },
    });
}

// Édition inline
const editing = ref(null);
const editForm = useForm({
    title:       '',
    description: '',
    category:    '',
    status:      '',
    sort_order:  0,
});

function startEdit(f) {
    editing.value = f.id;
    editForm.title       = f.title;
    editForm.description = f.description;
    editForm.category    = f.category;
    editForm.status      = f.status;
    editForm.sort_order  = f.sort_order;
}

function saveEdit(f) {
    editForm.put(route('admin.roadmap.update', f.id), {
        preserveScroll: true,
        onSuccess: () => { editing.value = null; flash.value = 'Fonctionnalité mise à jour.'; },
    });
}

function destroy(f) {
    if (!confirm(`Supprimer "${f.title}" ?`)) return;
    router.delete(route('admin.roadmap.destroy', f.id), { preserveScroll: true });
}

const categories = ['general','pos','facturation','stocks','api','mobile'];
const statuses   = ['planned','in_progress','delivered','cancelled'];

const statusLabel = (s) => ({ planned: 'Planifié', in_progress: 'En cours', delivered: 'Livré', cancelled: 'Annulé' }[s] ?? s);
const statusCls   = (s) => ({
    planned:     'bg-blue-100 text-blue-700',
    in_progress: 'bg-amber-100 text-amber-700',
    delivered:   'bg-green-100 text-green-700',
    cancelled:   'bg-gray-100 text-gray-400',
}[s] ?? '');
</script>

<template>
    <Head title="Roadmap — Admin" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold text-gray-800">🗺️ Roadmap — Administration</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">

                <AdminTabs />

                <!-- Flash -->
                <div v-if="flash" class="rounded-xl bg-green-50 px-4 py-3 text-sm font-semibold text-green-700 flex items-center justify-between">
                    {{ flash }}
                    <button @click="flash = ''" class="ml-4 text-green-500 hover:text-green-700">✕</button>
                </div>

                <!-- Formulaire ajout -->
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
                    <h3 class="mb-4 text-base font-bold text-gray-800">Ajouter une fonctionnalité</h3>
                    <form @submit.prevent="store" class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Titre *</label>
                            <input v-model="form.title" type="text" maxlength="150" required
                                   class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400"
                                   placeholder="Ex : Application mobile native iOS & Android" />
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Description *</label>
                            <textarea v-model="form.description" maxlength="500" required rows="2"
                                      class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400"
                                      placeholder="Description courte de la fonctionnalité…" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Catégorie</label>
                            <select v-model="form.category" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                                <option v-for="c in categories" :key="c" :value="c">{{ c }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Statut</label>
                            <select v-model="form.status" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                                <option v-for="s in statuses" :key="s" :value="s">{{ statusLabel(s) }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Ordre d'affichage</label>
                            <input v-model.number="form.sort_order" type="number" min="0"
                                   class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400" />
                        </div>
                        <div class="flex items-end">
                            <button type="submit" :disabled="form.processing"
                                    class="rounded-xl bg-brand-600 px-6 py-2.5 text-sm font-bold text-white shadow hover:bg-brand-700 disabled:opacity-50 transition">
                                Ajouter
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Tableau -->
                <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-100">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-gray-500">Titre</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-gray-500">Catégorie</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-gray-500">Statut</th>
                                    <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wide text-gray-500">Votes</th>
                                    <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wide text-gray-500">Ordre</th>
                                    <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wide text-gray-500">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <template v-for="f in features" :key="f.id">
                                    <!-- Mode lecture -->
                                    <tr v-if="editing !== f.id" class="hover:bg-gray-50">
                                        <td class="max-w-xs px-4 py-3">
                                            <div class="font-semibold text-gray-800 truncate">{{ f.title }}</div>
                                            <div class="text-xs text-gray-400 truncate">{{ f.description }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-gray-600">{{ f.category }}</td>
                                        <td class="px-4 py-3">
                                            <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold" :class="statusCls(f.status)">
                                                {{ statusLabel(f.status) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-center font-bold text-brand-600">{{ f.votes_count }}</td>
                                        <td class="px-4 py-3 text-center text-gray-500">{{ f.sort_order }}</td>
                                        <td class="px-4 py-3 text-right">
                                            <button @click="startEdit(f)" class="mr-2 rounded-lg bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 hover:bg-amber-100 transition">Modifier</button>
                                            <button @click="destroy(f)" class="rounded-lg bg-red-50 px-3 py-1 text-xs font-semibold text-red-600 hover:bg-red-100 transition">Supprimer</button>
                                        </td>
                                    </tr>

                                    <!-- Mode édition -->
                                    <tr v-else class="bg-amber-50">
                                        <td class="px-4 py-3">
                                            <input v-model="editForm.title" type="text" maxlength="150"
                                                   class="w-full rounded-lg border border-amber-200 px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400" />
                                            <textarea v-model="editForm.description" maxlength="500" rows="2"
                                                      class="mt-1 w-full rounded-lg border border-amber-200 px-2 py-1 text-xs focus:outline-none focus:ring-2 focus:ring-amber-400" />
                                        </td>
                                        <td class="px-4 py-3">
                                            <select v-model="editForm.category" class="rounded-lg border border-amber-200 px-2 py-1 text-sm">
                                                <option v-for="c in categories" :key="c" :value="c">{{ c }}</option>
                                            </select>
                                        </td>
                                        <td class="px-4 py-3">
                                            <select v-model="editForm.status" class="rounded-lg border border-amber-200 px-2 py-1 text-sm">
                                                <option v-for="s in statuses" :key="s" :value="s">{{ statusLabel(s) }}</option>
                                            </select>
                                        </td>
                                        <td class="px-4 py-3 text-center font-bold text-brand-600">{{ f.votes_count }}</td>
                                        <td class="px-4 py-3">
                                            <input v-model.number="editForm.sort_order" type="number" min="0"
                                                   class="w-16 rounded-lg border border-amber-200 px-2 py-1 text-sm text-center" />
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <button @click="saveEdit(f)" :disabled="editForm.processing"
                                                    class="mr-2 rounded-lg bg-green-600 px-3 py-1 text-xs font-bold text-white hover:bg-green-700 transition disabled:opacity-50">
                                                Sauver
                                            </button>
                                            <button @click="editing = null" class="rounded-lg bg-gray-200 px-3 py-1 text-xs font-semibold text-gray-600 hover:bg-gray-300 transition">
                                                Annuler
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                                <tr v-if="!features.length">
                                    <td colspan="6" class="py-10 text-center text-gray-400">Aucune fonctionnalité pour l'instant.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

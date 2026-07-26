<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdminTabs from '@/Components/AdminTabs.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, reactive } from 'vue';

const props = defineProps({
    items: Object,
    filters: Object,
});

// ── Modal state ──────────────────────────────────────────────────────────────
const showModal = ref(false);
const editingId = ref(null);

const PLANS = ['starter', 'pro', 'business', 'enterprise'];
const CATEGORIES = ['module', 'feature', 'integration'];
const STATUSES = ['available', 'coming_soon', 'beta', 'removed'];

const emptyForm = () => ({
    slug: '',
    category: 'module',
    name_fr: '',
    name_en: '',
    description_fr: '',
    description_en: '',
    icon: '',
    available_in_plans: [...PLANS],
    status: 'available',
    sort_order: 0,
    is_active: true,
});

const form = useForm(emptyForm());

function openCreate() {
    editingId.value = null;
    form.reset();
    Object.assign(form, emptyForm());
    showModal.value = true;
}

function openEdit(item) {
    editingId.value = item.id;
    form.slug = item.slug;
    form.category = item.category;
    form.name_fr = item.name_fr;
    form.name_en = item.name_en ?? '';
    form.description_fr = item.description_fr ?? '';
    form.description_en = item.description_en ?? '';
    form.icon = item.icon ?? '';
    form.available_in_plans = item.available_in_plans ?? [...PLANS];
    form.status = item.status;
    form.sort_order = item.sort_order;
    form.is_active = item.is_active;
    form.clearErrors();
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
    editingId.value = null;
}

function submit() {
    if (editingId.value) {
        form.put(route('admin.module-features.update', editingId.value), {
            onSuccess: closeModal,
        });
    } else {
        form.post(route('admin.module-features.store'), {
            onSuccess: closeModal,
        });
    }
}

function destroy(item) {
    if (!confirm(`Supprimer "${item.name_fr}" ?`)) return;
    router.delete(route('admin.module-features.destroy', item.id));
}

function togglePlan(plan) {
    const idx = form.available_in_plans.indexOf(plan);
    if (idx === -1) {
        form.available_in_plans.push(plan);
    } else {
        form.available_in_plans.splice(idx, 1);
    }
}

function setAllPlans(val) {
    form.available_in_plans = val ? [...PLANS] : [];
}

const categoryLabel = (c) => ({ module: 'Module', feature: 'Fonctionnalité', integration: 'Intégration' }[c] ?? c);
const statusLabel   = (s) => ({ available: 'Disponible', coming_soon: 'Bientôt', beta: 'Bêta', removed: 'Retiré' }[s] ?? s);
const statusClass   = (s) => ({
    available:    'bg-green-100 text-green-800',
    coming_soon:  'bg-yellow-100 text-yellow-800',
    beta:         'bg-blue-100 text-blue-800',
    removed:      'bg-red-100 text-red-800',
}[s] ?? 'bg-gray-100 text-gray-700');
</script>

<template>
    <Head title="Modules & Fonctionnalités" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Modules & Fonctionnalités</h2>
        </template>

        <div class="py-6 px-4 sm:px-6 lg:px-8 space-y-6">
            <AdminTabs />

            <!-- Toolbar -->
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-500">Source de vérité unique — §43 cahier des charges IBIG SOFT</p>
                <button
                    @click="openCreate"
                    class="rounded-md bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-brand-700 transition"
                >
                    + Nouveau module
                </button>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Icône</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Nom FR</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Catégorie</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Plans</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Statut</th>
                            <th class="px-4 py-3 text-center font-semibold text-gray-600 dark:text-gray-300">Ordre</th>
                            <th class="px-4 py-3 text-center font-semibold text-gray-600 dark:text-gray-300">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        <tr v-if="items.data.length === 0">
                            <td colspan="7" class="px-4 py-8 text-center text-gray-400">Aucun module. Cliquez sur « + Nouveau module » pour commencer.</td>
                        </tr>
                        <tr
                            v-for="item in items.data"
                            :key="item.id"
                            class="hover:bg-gray-50 dark:hover:bg-gray-750 transition"
                            :class="{ 'opacity-50': !item.is_active }"
                        >
                            <td class="px-4 py-3 text-xl">{{ item.icon ?? '—' }}</td>
                            <td class="px-4 py-3 font-medium text-gray-800 dark:text-gray-100">
                                {{ item.name_fr }}
                                <span v-if="item.name_en" class="ml-1 text-xs text-gray-400">({{ item.name_en }})</span>
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ categoryLabel(item.category) }}</td>
                            <td class="px-4 py-3">
                                <span v-if="!item.available_in_plans" class="text-xs text-gray-400 italic">Tous</span>
                                <span v-else class="flex flex-wrap gap-1">
                                    <span
                                        v-for="p in item.available_in_plans"
                                        :key="p"
                                        class="rounded-full bg-brand-100 px-2 py-0.5 text-xs font-medium text-brand-700 dark:bg-brand-900 dark:text-brand-300"
                                    >{{ p }}</span>
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span :class="['rounded-full px-2 py-0.5 text-xs font-semibold', statusClass(item.status)]">
                                    {{ statusLabel(item.status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center text-gray-500">{{ item.sort_order }}</td>
                            <td class="px-4 py-3 text-center space-x-2">
                                <button @click="openEdit(item)" class="text-brand-600 hover:underline text-xs font-medium">Modifier</button>
                                <button @click="destroy(item)" class="text-red-600 hover:underline text-xs font-medium">Supprimer</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="items.last_page > 1" class="flex justify-center gap-2">
                <a
                    v-for="link in items.links"
                    :key="link.label"
                    :href="link.url ?? '#'"
                    v-html="link.label"
                    class="rounded px-3 py-1 text-sm border"
                    :class="link.active ? 'bg-brand-600 text-white border-brand-600' : 'border-gray-300 text-gray-600 hover:bg-gray-50'"
                />
            </div>
        </div>
    </AuthenticatedLayout>

    <!-- ── Modal ───────────────────────────────────────────────────────────── -->
    <Teleport to="body">
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-2xl rounded-xl bg-white dark:bg-gray-800 shadow-2xl overflow-y-auto max-h-screen">
                <div class="flex items-center justify-between border-b px-6 py-4 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">
                        {{ editingId ? 'Modifier le module' : 'Nouveau module' }}
                    </h3>
                    <button @click="closeModal" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
                </div>

                <form @submit.prevent="submit" class="px-6 py-5 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <!-- Slug -->
                        <div class="col-span-2 sm:col-span-1">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Slug <span class="text-red-500">*</span></label>
                            <input v-model="form.slug" type="text" class="input w-full" placeholder="ex: facturation" />
                            <p v-if="form.errors.slug" class="text-red-500 text-xs mt-1">{{ form.errors.slug }}</p>
                        </div>

                        <!-- Catégorie -->
                        <div class="col-span-2 sm:col-span-1">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Catégorie <span class="text-red-500">*</span></label>
                            <select v-model="form.category" class="input w-full">
                                <option v-for="c in CATEGORIES" :key="c" :value="c">{{ categoryLabel(c) }}</option>
                            </select>
                        </div>

                        <!-- Nom FR -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nom FR <span class="text-red-500">*</span></label>
                            <input v-model="form.name_fr" type="text" class="input w-full" />
                            <p v-if="form.errors.name_fr" class="text-red-500 text-xs mt-1">{{ form.errors.name_fr }}</p>
                        </div>

                        <!-- Nom EN -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nom EN</label>
                            <input v-model="form.name_en" type="text" class="input w-full" />
                        </div>

                        <!-- Description FR -->
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description FR</label>
                            <textarea v-model="form.description_fr" rows="2" class="input w-full" />
                        </div>

                        <!-- Description EN -->
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description EN</label>
                            <textarea v-model="form.description_en" rows="2" class="input w-full" />
                        </div>

                        <!-- Icône -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Icône (emoji)</label>
                            <input v-model="form.icon" type="text" class="input w-full" placeholder="📄" />
                        </div>

                        <!-- Statut -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Statut</label>
                            <select v-model="form.status" class="input w-full">
                                <option v-for="s in STATUSES" :key="s" :value="s">{{ statusLabel(s) }}</option>
                            </select>
                        </div>

                        <!-- Plans -->
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Plans disponibles</label>
                            <div class="flex items-center gap-4 mb-2">
                                <button type="button" @click="setAllPlans(true)" class="text-xs text-brand-600 hover:underline">Tous</button>
                                <button type="button" @click="setAllPlans(false)" class="text-xs text-gray-400 hover:underline">Aucun (= tous)</button>
                            </div>
                            <div class="flex flex-wrap gap-3">
                                <label v-for="p in PLANS" :key="p" class="flex items-center gap-1.5 cursor-pointer">
                                    <input
                                        type="checkbox"
                                        :value="p"
                                        :checked="form.available_in_plans.includes(p)"
                                        @change="togglePlan(p)"
                                        class="rounded border-gray-300"
                                    />
                                    <span class="text-sm text-gray-700 dark:text-gray-300 capitalize">{{ p }}</span>
                                </label>
                            </div>
                        </div>

                        <!-- Ordre -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ordre</label>
                            <input v-model.number="form.sort_order" type="number" min="0" class="input w-full" />
                        </div>

                        <!-- Actif -->
                        <div class="flex items-center gap-2 pt-5">
                            <input v-model="form.is_active" type="checkbox" id="is_active" class="rounded border-gray-300" />
                            <label for="is_active" class="text-sm text-gray-700 dark:text-gray-300">Actif</label>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 border-t pt-4 dark:border-gray-700">
                        <button type="button" @click="closeModal" class="rounded-md border border-gray-300 px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 transition">Annuler</button>
                        <button type="submit" :disabled="form.processing" class="rounded-md bg-brand-600 px-5 py-2 text-sm font-semibold text-white shadow hover:bg-brand-700 disabled:opacity-60 transition">
                            {{ form.processing ? 'Enregistrement…' : 'Enregistrer' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </Teleport>
</template>

<style scoped>
.input {
    @apply rounded-md border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100;
}
</style>

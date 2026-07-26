<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, usePage, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdminTabs from '@/Components/AdminTabs.vue';

const props = defineProps({
    versions: Object, // paginated
    plans: Array,
});

const page = usePage();
const flash = computed(() => page.props.flash ?? {});

// ── Type badges ─────────────────────────────────────────────────────────────
const typeConfig = {
    major: { label: 'Majeure', bg: '#c81e1e',  text: '#fff' },
    minor: { label: 'Mineure', bg: '#1a56db',  text: '#fff' },
    patch: { label: 'Correctif', bg: '#057a55', text: '#fff' },
};

// ── Création ────────────────────────────────────────────────────────────────
const form = useForm({
    version: '',
    title: '',
    description: '',
    type: 'minor',
    target_plans: [],
});

function submit() {
    form.post(route('admin.versions.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
}

// ── Suppression ──────────────────────────────────────────────────────────────
function destroy(id) {
    if (!confirm('Supprimer ce brouillon ?')) return;
    router.delete(route('admin.versions.destroy', id), { preserveScroll: true });
}

// ── Publication ──────────────────────────────────────────────────────────────
const publishTarget = ref(null); // version à publier
const showPublishModal = ref(false);

function openPublish(version) {
    publishTarget.value = version;
    showPublishModal.value = true;
}

function confirmPublish() {
    if (!publishTarget.value) return;
    router.post(route('admin.versions.publish', publishTarget.value.id), {}, {
        preserveScroll: true,
        onFinish: () => {
            showPublishModal.value = false;
            publishTarget.value = null;
        },
    });
}
</script>

<template>
    <Head title="Gestion des versions" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <span class="text-2xl">🏷️</span>
                <div>
                    <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100">Gestion des versions</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Versionnement sémantique et notifications</p>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

                <!-- Admin tabs -->
                <AdminTabs />

                <!-- Flash messages -->
                <div v-if="flash.success" class="rounded-xl px-4 py-3 bg-green-50 border border-green-200 text-green-800 text-sm">
                    {{ flash.success }}
                </div>
                <div v-if="flash.error" class="rounded-xl px-4 py-3 bg-red-50 border border-red-200 text-red-800 text-sm">
                    {{ flash.error }}
                </div>

                <!-- ── Formulaire création ── -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-base font-bold text-gray-800 dark:text-gray-100 mb-4">Nouvelle version</h3>

                    <form @submit.prevent="submit" class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <!-- Numéro de version -->
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">
                                    Version <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.version"
                                    type="text"
                                    placeholder="ex: 14.1.0"
                                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    required
                                />
                                <p v-if="form.errors.version" class="text-xs text-red-600 mt-1">{{ form.errors.version }}</p>
                            </div>

                            <!-- Type -->
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">
                                    Type <span class="text-red-500">*</span>
                                </label>
                                <select
                                    v-model="form.type"
                                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    required
                                >
                                    <option value="major">Majeure</option>
                                    <option value="minor">Mineure</option>
                                    <option value="patch">Correctif</option>
                                </select>
                            </div>

                            <!-- Formules cibles -->
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">
                                    Formules cibles <span class="text-gray-400">(vide = toutes)</span>
                                </label>
                                <select
                                    v-model="form.target_plans"
                                    multiple
                                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 h-20"
                                >
                                    <option v-for="plan in plans" :key="plan.id" :value="plan.id">
                                        {{ plan.name }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- Titre -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">
                                Titre <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.title"
                                type="text"
                                placeholder="ex: Nouveau module de livraison & correctifs"
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required
                            />
                            <p v-if="form.errors.title" class="text-xs text-red-600 mt-1">{{ form.errors.title }}</p>
                        </div>

                        <!-- Description markdown -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">
                                Résumé des nouveautés <span class="text-gray-400">(markdown)</span> <span class="text-red-500">*</span>
                            </label>
                            <textarea
                                v-model="form.description"
                                rows="5"
                                placeholder="- **Nouvelle fonctionnalité** : livraison COD&#10;- Correction d'un bug sur les factures PDF&#10;- Amélioration des performances de recherche"
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono"
                                required
                            />
                            <p v-if="form.errors.description" class="text-xs text-red-600 mt-1">{{ form.errors.description }}</p>
                        </div>

                        <div class="flex justify-end">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-5 py-2 rounded-lg bg-[#002D5B] text-white text-sm font-semibold hover:bg-[#0044a3] transition-colors disabled:opacity-60"
                            >
                                Créer le brouillon
                            </button>
                        </div>
                    </form>
                </div>

                <!-- ── Tableau des versions ── -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                        <h3 class="font-bold text-gray-800 dark:text-gray-100">Historique des versions</h3>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-750 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                <tr>
                                    <th class="px-4 py-3 text-left">Version</th>
                                    <th class="px-4 py-3 text-left">Titre</th>
                                    <th class="px-4 py-3 text-left">Type</th>
                                    <th class="px-4 py-3 text-left">Statut</th>
                                    <th class="px-4 py-3 text-left">Publié le</th>
                                    <th class="px-4 py-3 text-left">Notifs envoyées</th>
                                    <th class="px-4 py-3 text-left">Publié par</th>
                                    <th class="px-4 py-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <tr v-if="!versions.data.length">
                                    <td colspan="8" class="px-4 py-8 text-center text-gray-400">Aucune version créée</td>
                                </tr>
                                <tr
                                    v-for="v in versions.data"
                                    :key="v.id"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors"
                                >
                                    <!-- Version -->
                                    <td class="px-4 py-3 font-mono font-bold text-[#002D5B] dark:text-blue-400">
                                        v{{ v.version }}
                                    </td>

                                    <!-- Titre -->
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-200 max-w-xs truncate">
                                        {{ v.title }}
                                    </td>

                                    <!-- Type badge -->
                                    <td class="px-4 py-3">
                                        <span
                                            class="px-2 py-0.5 rounded-full text-xs font-bold"
                                            :style="`background:${typeConfig[v.type]?.bg ?? '#6b7280'}20; color:${typeConfig[v.type]?.bg ?? '#6b7280'}`"
                                        >
                                            {{ typeConfig[v.type]?.label ?? v.type }}
                                        </span>
                                    </td>

                                    <!-- Statut -->
                                    <td class="px-4 py-3">
                                        <span
                                            v-if="v.published_at"
                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-green-50 text-green-700"
                                        >
                                            <span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block"></span>
                                            Publiée
                                        </span>
                                        <span
                                            v-else
                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-yellow-50 text-yellow-700"
                                        >
                                            <span class="w-1.5 h-1.5 rounded-full bg-yellow-400 inline-block"></span>
                                            Brouillon
                                        </span>
                                    </td>

                                    <!-- Date publication -->
                                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                        {{ v.published_at ? new Date(v.published_at).toLocaleDateString('fr-FR') : '—' }}
                                    </td>

                                    <!-- Notifs envoyées -->
                                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                        <span v-if="v.notification_sent_at" class="text-green-600">
                                            {{ new Date(v.notification_sent_at).toLocaleDateString('fr-FR') }}
                                        </span>
                                        <span v-else class="text-gray-400">—</span>
                                    </td>

                                    <!-- Publié par -->
                                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400">
                                        {{ v.publisher?.name ?? '—' }}
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <!-- Publier -->
                                            <button
                                                v-if="!v.published_at"
                                                @click="openPublish(v)"
                                                class="px-3 py-1 rounded-lg bg-[#002D5B] text-white text-xs font-semibold hover:bg-[#0044a3] transition-colors"
                                            >
                                                Publier + Notifier
                                            </button>

                                            <!-- Supprimer brouillon -->
                                            <button
                                                v-if="!v.published_at"
                                                @click="destroy(v.id)"
                                                class="px-3 py-1 rounded-lg bg-red-50 text-red-600 text-xs font-semibold hover:bg-red-100 transition-colors"
                                            >
                                                Supprimer
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="versions.last_page > 1" class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex gap-2">
                        <a
                            v-for="link in versions.links"
                            :key="link.label"
                            :href="link.url ?? '#'"
                            v-html="link.label"
                            class="px-3 py-1 rounded text-xs"
                            :class="link.active
                                ? 'bg-[#002D5B] text-white font-bold'
                                : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'"
                        />
                    </div>
                </div>

            </div>
        </div>

        <!-- ── Modal confirmation publication ── -->
        <Teleport to="body">
            <div
                v-if="showPublishModal && publishTarget"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm"
                @click.self="showPublishModal = false"
            >
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-6 max-w-md w-full mx-4">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-2">
                        Publier v{{ publishTarget.version }} ?
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300 mb-1">
                        <strong>{{ publishTarget.title }}</strong>
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-5">
                        Cette action va publier la version et envoyer un email de notification
                        à tous les utilisateurs actifs
                        <template v-if="publishTarget.target_plans?.length">
                            des formules sélectionnées
                        </template>
                        . L'opération est irréversible.
                    </p>
                    <div class="flex justify-end gap-3">
                        <button
                            @click="showPublishModal = false"
                            class="px-4 py-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium hover:bg-gray-200 transition-colors"
                        >
                            Annuler
                        </button>
                        <button
                            @click="confirmPublish"
                            class="px-4 py-2 rounded-lg bg-[#002D5B] text-white text-sm font-semibold hover:bg-[#0044a3] transition-colors"
                        >
                            Confirmer la publication
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>

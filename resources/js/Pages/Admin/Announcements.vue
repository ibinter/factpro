<script setup>
import { computed } from 'vue';
import { Head, useForm, usePage, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdminTabs from '@/Components/AdminTabs.vue';

const props = defineProps({
    announcements: Array,
});

const page = usePage();
const flash = computed(() => page.props.flash ?? {});

const typeOptions = [
    { value: 'info',    label: 'ℹ️ Info',      bg: '#1a56db', text: '#fff' },
    { value: 'success', label: '✅ Succès',    bg: '#057a55', text: '#fff' },
    { value: 'warning', label: '⚠️ Avertissement', bg: '#F0C040', text: '#002D5B' },
    { value: 'danger',  label: '🚨 Alerte',    bg: '#c81e1e', text: '#fff' },
];

function typeOption(val) {
    return typeOptions.find(t => t.value === val) ?? typeOptions[0];
}

const form = useForm({
    title: '',
    message: '',
    type: 'info',
    link_text: '',
    link_url: '',
    starts_at: '',
    ends_at: '',
});

function submit() {
    form.post(route('admin.announcements.store'), { onSuccess: () => form.reset() });
}

function toggle(id) {
    router.patch(route('admin.announcements.toggle', id));
}

function destroy(id) {
    if (confirm('Supprimer cette annonce ?')) {
        router.delete(route('admin.announcements.destroy', id));
    }
}
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Annonces in-app" />

        <div class="py-8 px-4 max-w-6xl mx-auto space-y-8">
            <!-- Tabs admin -->
            <AdminTabs />

            <!-- Flash success -->
            <div v-if="flash.success"
                 class="rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-green-800 text-sm font-medium flex items-center gap-2">
                ✅ {{ flash.success }}
            </div>

            <!-- Formulaire création -->
            <div class="bg-white rounded-2xl shadow p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-5">Nouvelle annonce</h2>
                <form @submit.prevent="submit" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Titre -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Titre <span class="text-red-500">*</span></label>
                            <input v-model="form.title" type="text" maxlength="100"
                                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                                   placeholder="Ex : Maintenance programmée" />
                            <p v-if="form.errors.title" class="text-red-500 text-xs mt-1">{{ form.errors.title }}</p>
                        </div>

                        <!-- Type -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Type <span class="text-red-500">*</span></label>
                            <div class="flex gap-2 flex-wrap">
                                <button v-for="opt in typeOptions" :key="opt.value" type="button"
                                        @click="form.type = opt.value"
                                        class="px-3 py-1.5 rounded-full text-sm font-semibold border-2 transition"
                                        :style="form.type === opt.value
                                            ? { background: opt.bg, color: opt.text, borderColor: opt.bg }
                                            : { background: '#f9fafb', color: '#374151', borderColor: '#d1d5db' }">
                                    {{ opt.label }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Message -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Message <span class="text-red-500">*</span></label>
                        <textarea v-model="form.message" rows="3" maxlength="500"
                                  class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                                  placeholder="Décrivez l'annonce..."></textarea>
                        <p class="text-xs text-gray-400 mt-0.5">{{ form.message.length }}/500 caractères</p>
                        <p v-if="form.errors.message" class="text-red-500 text-xs mt-1">{{ form.errors.message }}</p>
                    </div>

                    <!-- Aperçu -->
                    <div v-if="form.title || form.message"
                         class="rounded-lg px-4 py-2.5 flex items-center gap-3 text-sm font-medium"
                         :style="{ background: typeOption(form.type).bg, color: typeOption(form.type).text }">
                        <span>{{ { info: 'ℹ️', success: '✅', warning: '⚠️', danger: '🚨' }[form.type] }}</span>
                        <span>
                            <strong v-if="form.title" class="mr-1">{{ form.title }} —</strong>
                            {{ form.message }}
                            <span v-if="form.link_url" class="ml-2 underline font-bold opacity-80">{{ form.link_text || 'En savoir plus' }}</span>
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Lien texte -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Texte du lien (optionnel)</label>
                            <input v-model="form.link_text" type="text" maxlength="50"
                                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500"
                                   placeholder="Ex : En savoir plus" />
                        </div>
                        <!-- Lien URL -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">URL du lien (optionnel)</label>
                            <input v-model="form.link_url" type="url"
                                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500"
                                   placeholder="https://..." />
                            <p v-if="form.errors.link_url" class="text-red-500 text-xs mt-1">{{ form.errors.link_url }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Début -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Début (optionnel)</label>
                            <input v-model="form.starts_at" type="datetime-local"
                                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500" />
                        </div>
                        <!-- Fin -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Fin (optionnel)</label>
                            <input v-model="form.ends_at" type="datetime-local"
                                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500" />
                            <p v-if="form.errors.ends_at" class="text-red-500 text-xs mt-1">{{ form.errors.ends_at }}</p>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" :disabled="form.processing"
                                class="px-6 py-2.5 rounded-xl bg-gold-400 text-brand-900 font-bold text-sm hover:bg-gold-300 transition disabled:opacity-50 shadow">
                            📢 Diffuser l'annonce
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tableau des annonces -->
            <div class="bg-white rounded-2xl shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-lg font-bold text-gray-800">Annonces existantes</h2>
                </div>

                <div v-if="announcements.length === 0" class="px-6 py-12 text-center text-gray-400 text-sm">
                    Aucune annonce pour l'instant.
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
                            <tr>
                                <th class="px-4 py-3 text-left">Titre</th>
                                <th class="px-4 py-3 text-left">Type</th>
                                <th class="px-4 py-3 text-left">Statut</th>
                                <th class="px-4 py-3 text-left">Période</th>
                                <th class="px-4 py-3 text-left">Créée</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="ann in announcements" :key="ann.id" class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-gray-800">{{ ann.title }}</div>
                                    <div class="text-xs text-gray-400 line-clamp-1 max-w-xs">{{ ann.message }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold"
                                          :style="{ background: typeOption(ann.type).bg + '20', color: typeOption(ann.type).bg }">
                                        {{ typeOption(ann.type).label }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <!-- Switch toggle -->
                                    <button @click="toggle(ann.id)"
                                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 focus:outline-none"
                                            :class="ann.active ? 'bg-green-500' : 'bg-gray-300'"
                                            :title="ann.active ? 'Désactiver' : 'Activer'">
                                        <span class="inline-block h-4 w-4 rounded-full bg-white shadow transform transition-transform duration-200"
                                              :class="ann.active ? 'translate-x-6' : 'translate-x-1'"></span>
                                    </button>
                                    <span class="ml-2 text-xs" :class="ann.active ? 'text-green-600 font-semibold' : 'text-gray-400'">
                                        {{ ann.active ? 'Actif' : 'Inactif' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-500">
                                    <span v-if="ann.starts_at || ann.ends_at">
                                        {{ ann.starts_at || '∞' }} → {{ ann.ends_at || '∞' }}
                                    </span>
                                    <span v-else class="text-gray-300">—</span>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-400">{{ ann.created_at }}</td>
                                <td class="px-4 py-3 text-right">
                                    <button @click="destroy(ann.id)"
                                            class="text-red-500 hover:text-red-700 transition text-xs font-semibold px-2 py-1 rounded hover:bg-red-50">
                                        Supprimer
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    community: { type: Array, default: () => [] },
    mine: { type: Array, default: () => [] },
    systemTemplates: { type: Array, default: () => [] },
    canShare: { type: Boolean, default: false },
});

const activeTab = ref('community');

// Filtres
const filterFamily = ref('');
const filterMinRating = ref(0);
const filterTag = ref('');

const allTags = computed(() => {
    const tags = new Set();
    props.community.forEach(t => (t.tags ?? []).forEach(tag => tags.add(tag)));
    return [...tags];
});

const filteredCommunity = computed(() => {
    return props.community.filter(t => {
        if (filterMinRating.value > 0 && t.average_rating < filterMinRating.value) return false;
        if (filterTag.value && !(t.tags ?? []).includes(filterTag.value)) return false;
        return true;
    });
});

// Formulaire de partage
const shareForm = useForm({
    base_template: '',
    name: '',
    description: '',
    primary_color: '#002D5B',
    secondary_color: '#0062CC',
    accent_color: '#F0C040',
    custom_css: '',
    tags: [],
    is_public: true,
});

const tagInput = ref('');
const addTag = () => {
    const t = tagInput.value.trim();
    if (t && !shareForm.tags.includes(t)) shareForm.tags.push(t);
    tagInput.value = '';
};
const removeTag = (tag) => {
    shareForm.tags = shareForm.tags.filter(t => t !== tag);
};

const submitShare = () => {
    shareForm.post(route('templates.marketplace.store'), {
        onSuccess: () => {
            shareForm.reset();
            activeTab.value = 'mine';
        },
    });
};

const doDownload = (template) => {
    useForm({}).post(route('templates.marketplace.download', template.id));
};

const doRate = (template, rating) => {
    useForm({ rating }).post(route('templates.marketplace.rate', template.id));
};

const renderStars = (rating) => {
    const full = Math.round(rating);
    return '★'.repeat(full) + '☆'.repeat(5 - full);
};
</script>

<template>
    <Head title="Marketplace Templates" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                🛒 Marketplace des Templates
            </h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">

                <!-- Onglets -->
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex gap-4">
                        <button
                            @click="activeTab = 'community'"
                            class="pb-3 text-sm font-medium border-b-2 transition"
                            :class="activeTab === 'community' ? 'border-blue-600 text-blue-700' : 'border-transparent text-gray-500 hover:text-gray-700'"
                        >🌍 Communauté ({{ community.length }})</button>
                        <button
                            @click="activeTab = 'mine'"
                            class="pb-3 text-sm font-medium border-b-2 transition"
                            :class="activeTab === 'mine' ? 'border-blue-600 text-blue-700' : 'border-transparent text-gray-500 hover:text-gray-700'"
                        >🏢 Mes templates ({{ mine.length }})</button>
                        <button
                            @click="activeTab = 'share'"
                            class="pb-3 text-sm font-medium border-b-2 transition"
                            :class="activeTab === 'share' ? 'border-blue-600 text-blue-700' : 'border-transparent text-gray-500 hover:text-gray-700'"
                        >➕ Partager le mien</button>
                    </nav>
                </div>

                <!-- Onglet COMMUNAUTÉ -->
                <div v-if="activeTab === 'community'">
                    <!-- Filtres -->
                    <div class="flex flex-wrap gap-4 mb-6">
                        <select v-model="filterMinRating" class="rounded-md border-gray-300 text-sm shadow-sm">
                            <option :value="0">Toutes les notes</option>
                            <option :value="3">3★ et plus</option>
                            <option :value="4">4★ et plus</option>
                            <option :value="5">5★ uniquement</option>
                        </select>
                        <select v-model="filterTag" class="rounded-md border-gray-300 text-sm shadow-sm">
                            <option value="">Tous les tags</option>
                            <option v-for="tag in allTags" :key="tag" :value="tag">{{ tag }}</option>
                        </select>
                    </div>

                    <div v-if="filteredCommunity.length === 0" class="text-center text-gray-400 py-16">
                        Aucun template communautaire pour l'instant.
                    </div>
                    <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div
                            v-for="t in filteredCommunity"
                            :key="t.id"
                            class="bg-white rounded-lg shadow p-4 space-y-3"
                        >
                            <!-- En-tête couleurs -->
                            <div class="h-10 rounded-md flex items-center px-4 gap-2"
                                 :style="{ backgroundColor: t.primary_color }">
                                <span class="h-4 w-4 rounded-full border border-white" :style="{ backgroundColor: t.secondary_color }"></span>
                                <span class="h-4 w-4 rounded-full border border-white" :style="{ backgroundColor: t.accent_color }"></span>
                                <span class="text-white text-xs font-semibold truncate ml-1">{{ t.name }}</span>
                            </div>

                            <div>
                                <p class="text-xs text-gray-500">Base : <span class="font-medium">{{ t.base_template }}</span></p>
                                <p v-if="t.description" class="text-xs text-gray-400 mt-1 line-clamp-2">{{ t.description }}</p>
                            </div>

                            <!-- Tags -->
                            <div class="flex flex-wrap gap-1">
                                <span v-for="tag in t.tags" :key="tag"
                                    class="text-[10px] bg-gray-100 text-gray-600 rounded-full px-2 py-0.5">{{ tag }}</span>
                            </div>

                            <!-- Rating + downloads -->
                            <div class="flex items-center justify-between text-xs text-gray-500">
                                <span class="text-yellow-500">{{ renderStars(t.average_rating) }}</span>
                                <span>{{ t.average_rating }} ({{ t.rating_count }})</span>
                                <span>⬇ {{ t.downloads_count }}</span>
                            </div>

                            <!-- Partagé par -->
                            <p class="text-[10px] text-gray-400">par {{ t.company?.name ?? '—' }}</p>

                            <!-- Actions -->
                            <div class="flex gap-2">
                                <button
                                    @click="doDownload(t)"
                                    class="flex-1 rounded-md bg-blue-600 text-white text-xs py-1.5 hover:bg-blue-700"
                                >Utiliser</button>
                                <div class="flex gap-1">
                                    <button v-for="star in [1,2,3,4,5]" :key="star"
                                        @click="doRate(t, star)"
                                        class="text-yellow-400 hover:text-yellow-500 text-xs">★</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Onglet MES TEMPLATES -->
                <div v-if="activeTab === 'mine'">
                    <div v-if="mine.length === 0" class="text-center text-gray-400 py-16">
                        Vous n'avez pas encore partagé de template.
                    </div>
                    <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div
                            v-for="t in mine"
                            :key="t.id"
                            class="bg-white rounded-lg shadow p-4 space-y-3"
                        >
                            <div class="h-10 rounded-md flex items-center px-4"
                                 :style="{ backgroundColor: t.primary_color }">
                                <span class="text-white text-xs font-semibold truncate">{{ t.name }}</span>
                            </div>

                            <div class="flex items-center gap-2 text-xs">
                                <span v-if="t.is_approved && t.is_public"
                                    class="bg-green-100 text-green-700 rounded-full px-2 py-0.5">✓ Approuvé</span>
                                <span v-else-if="t.is_public"
                                    class="bg-yellow-100 text-yellow-700 rounded-full px-2 py-0.5">⏳ En attente</span>
                                <span v-else
                                    class="bg-gray-100 text-gray-500 rounded-full px-2 py-0.5">Privé</span>
                            </div>

                            <p class="text-xs text-gray-500">Base : {{ t.base_template }}</p>
                            <p class="text-xs text-gray-500">⬇ {{ t.downloads_count }} · ★ {{ t.average_rating }}</p>

                            <div class="flex flex-wrap gap-1">
                                <span v-for="tag in t.tags" :key="tag"
                                    class="text-[10px] bg-gray-100 text-gray-600 rounded-full px-2 py-0.5">{{ tag }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Onglet PARTAGER -->
                <div v-if="activeTab === 'share'">
                    <div v-if="!canShare" class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
                        <p class="text-yellow-800 font-semibold">Forfait Business ou Enterprise requis</p>
                        <p class="text-yellow-600 text-sm mt-1">Passez à un forfait supérieur pour partager vos templates avec la communauté.</p>
                    </div>

                    <form v-else @submit.prevent="submitShare" class="bg-white rounded-lg shadow p-6 space-y-5 max-w-2xl">
                        <p class="text-sm text-amber-600 bg-amber-50 rounded p-3">
                            ⏳ En attente de modération par l'équipe FactPro avant publication.
                        </p>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Template système de base *</label>
                            <select v-model="shareForm.base_template" required
                                class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">— Choisir —</option>
                                <option v-for="st in systemTemplates" :key="st.key" :value="st.key">
                                    {{ st.name }} ({{ st.family }})
                                </option>
                            </select>
                            <p v-if="shareForm.errors.base_template" class="text-red-500 text-xs mt-1">{{ shareForm.errors.base_template }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nom du template *</label>
                            <input v-model="shareForm.name" type="text" maxlength="100" required
                                class="block w-full rounded-md border-gray-300 shadow-sm text-sm" />
                            <p v-if="shareForm.errors.name" class="text-red-500 text-xs mt-1">{{ shareForm.errors.name }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea v-model="shareForm.description" rows="2"
                                class="block w-full rounded-md border-gray-300 shadow-sm text-sm"></textarea>
                        </div>

                        <!-- Color pickers + preview -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Couleurs *</label>
                            <div class="flex gap-4 flex-wrap">
                                <div>
                                    <label class="text-xs text-gray-500">Principale</label>
                                    <input v-model="shareForm.primary_color" type="color" class="block mt-1 h-9 w-16 rounded cursor-pointer border-0" />
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500">Secondaire</label>
                                    <input v-model="shareForm.secondary_color" type="color" class="block mt-1 h-9 w-16 rounded cursor-pointer border-0" />
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500">Accent</label>
                                    <input v-model="shareForm.accent_color" type="color" class="block mt-1 h-9 w-16 rounded cursor-pointer border-0" />
                                </div>
                            </div>

                            <!-- Preview live -->
                            <div class="mt-3 rounded-md px-4 py-3 flex items-center gap-3"
                                 :style="{ backgroundColor: shareForm.primary_color }">
                                <span class="h-5 w-5 rounded-full border-2 border-white" :style="{ backgroundColor: shareForm.secondary_color }"></span>
                                <span class="text-white text-sm font-semibold">Prévisualisation</span>
                                <span class="h-3 w-3 rounded-full ml-auto" :style="{ backgroundColor: shareForm.accent_color }"></span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">CSS personnalisé (optionnel)</label>
                            <textarea v-model="shareForm.custom_css" rows="4" placeholder=".header { ... }"
                                class="block w-full rounded-md border-gray-300 shadow-sm text-sm font-mono"></textarea>
                        </div>

                        <!-- Tags -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tags</label>
                            <div class="flex gap-2">
                                <input v-model="tagInput" type="text" placeholder="ex: moderne"
                                    @keydown.enter.prevent="addTag"
                                    class="flex-1 rounded-md border-gray-300 shadow-sm text-sm" />
                                <button type="button" @click="addTag"
                                    class="px-3 rounded-md bg-gray-100 text-sm hover:bg-gray-200">+</button>
                            </div>
                            <div class="flex flex-wrap gap-1 mt-2">
                                <span v-for="tag in shareForm.tags" :key="tag"
                                    class="text-xs bg-blue-100 text-blue-700 rounded-full px-2 py-0.5 flex items-center gap-1">
                                    {{ tag }}
                                    <button type="button" @click="removeTag(tag)" class="hover:text-red-500">×</button>
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <input v-model="shareForm.is_public" type="checkbox" id="is_public" class="rounded border-gray-300" />
                            <label for="is_public" class="text-sm text-gray-700">Partager avec la communauté (après modération)</label>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" :disabled="shareForm.processing"
                                class="px-6 py-2 rounded-md bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 disabled:opacity-50">
                                Soumettre le template
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

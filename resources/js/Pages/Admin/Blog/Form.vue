<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdminTabs from '@/Components/AdminTabs.vue';

const props = defineProps({
    post: { type: Object, default: null },
});

const isEdit = computed(() => !!props.post);

const form = useForm({
    title:            props.post?.title            ?? '',
    excerpt:          props.post?.excerpt          ?? '',
    content:          props.post?.content          ?? '',
    category:         props.post?.category         ?? 'actualites',
    author_name:      props.post?.author_name      ?? 'Équipe IBIG',
    status:           props.post?.status           ?? 'draft',
    meta_title:       props.post?.meta_title       ?? '',
    meta_description: props.post?.meta_description ?? '',
});

function submit() {
    if (isEdit.value) {
        form.put(route('admin.blog.update', props.post.id));
    } else {
        form.post(route('admin.blog.store'));
    }
}
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="isEdit ? 'Modifier l\'article' : 'Nouvel article'" />

        <div class="mx-auto max-w-4xl px-4 py-8">
            <AdminTabs />

            <div class="mt-6 flex items-center gap-3">
                <Link :href="route('admin.blog.index')" class="text-gray-400 hover:text-gray-600 transition">←</Link>
                <h1 class="text-2xl font-extrabold text-gray-900">
                    {{ isEdit ? 'Modifier l\'article' : 'Nouvel article' }}
                </h1>
            </div>

            <form class="mt-6 space-y-6 rounded-2xl border border-gray-100 bg-white p-8 shadow-sm" @submit.prevent="submit">

                <!-- Titre -->
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-gray-700">Titre <span class="text-red-500">*</span></label>
                    <input
                        v-model="form.title"
                        type="text"
                        maxlength="200"
                        placeholder="Titre de l'article…"
                        class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500"
                    />
                    <p v-if="form.errors.title" class="mt-1 text-xs text-red-600">{{ form.errors.title }}</p>
                </div>

                <!-- Extrait -->
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-gray-700">Extrait</label>
                    <textarea
                        v-model="form.excerpt"
                        rows="3"
                        maxlength="400"
                        placeholder="Résumé court affiché dans la liste du blog…"
                        class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500"
                    />
                    <p v-if="form.errors.excerpt" class="mt-1 text-xs text-red-600">{{ form.errors.excerpt }}</p>
                </div>

                <!-- Contenu -->
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-gray-700">
                        Contenu <span class="text-red-500">*</span>
                        <span class="ml-2 font-normal text-gray-400 text-xs">(Markdown simple supporté : ## titres, **gras**, listes)</span>
                    </label>
                    <textarea
                        v-model="form.content"
                        rows="16"
                        placeholder="Corps de l'article…"
                        class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-brand-500"
                    />
                    <p v-if="form.errors.content" class="mt-1 text-xs text-red-600">{{ form.errors.content }}</p>
                </div>

                <!-- Catégorie + Auteur -->
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Catégorie <span class="text-red-500">*</span></label>
                        <select
                            v-model="form.category"
                            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500"
                        >
                            <option value="actualites">Actualités</option>
                            <option value="tutoriels">Tutoriels</option>
                            <option value="produit">Produit</option>
                            <option value="entreprise">Entreprise</option>
                        </select>
                        <p v-if="form.errors.category" class="mt-1 text-xs text-red-600">{{ form.errors.category }}</p>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Auteur <span class="text-red-500">*</span></label>
                        <input
                            v-model="form.author_name"
                            type="text"
                            maxlength="100"
                            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500"
                        />
                        <p v-if="form.errors.author_name" class="mt-1 text-xs text-red-600">{{ form.errors.author_name }}</p>
                    </div>
                </div>

                <!-- Statut -->
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-gray-700">Statut <span class="text-red-500">*</span></label>
                    <select
                        v-model="form.status"
                        class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500"
                    >
                        <option value="draft">Brouillon</option>
                        <option value="published">Publié</option>
                    </select>
                    <p v-if="form.errors.status" class="mt-1 text-xs text-red-600">{{ form.errors.status }}</p>
                </div>

                <!-- SEO -->
                <div class="rounded-xl border border-gray-100 bg-gray-50 p-5 space-y-4">
                    <h3 class="text-sm font-bold text-gray-700">SEO (optionnel)</h3>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-600">Meta title (max 70 car.)</label>
                        <input
                            v-model="form.meta_title"
                            type="text"
                            maxlength="70"
                            class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500"
                        />
                        <p v-if="form.errors.meta_title" class="mt-1 text-xs text-red-600">{{ form.errors.meta_title }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-600">Meta description (max 160 car.)</label>
                        <textarea
                            v-model="form.meta_description"
                            rows="2"
                            maxlength="160"
                            class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500"
                        />
                        <p v-if="form.errors.meta_description" class="mt-1 text-xs text-red-600">{{ form.errors.meta_description }}</p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-3 pt-2">
                    <Link
                        :href="route('admin.blog.index')"
                        class="rounded-xl border border-gray-200 px-5 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
                    >
                        Annuler
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-xl px-6 py-2.5 text-sm font-bold transition hover:brightness-110 disabled:opacity-60"
                        style="background:#F0C040;color:#001d3d"
                    >
                        {{ form.processing ? 'Enregistrement…' : 'Enregistrer' }}
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdminTabs from '@/Components/AdminTabs.vue';

defineProps({
    posts: { type: Array, default: () => [] },
});

const page = usePage();
const flash = () => page.props.flash?.success ?? null;

const statusStyle = (status) =>
    status === 'published'
        ? 'background:#dcfce7;color:#166534'
        : 'background:#f3f4f6;color:#374151';

const categoryColors = {
    actualites: { bg: '#e0f2fe', text: '#0369a1' },
    tutoriels:  { bg: '#fef9c3', text: '#92400e' },
    produit:    { bg: '#ede9fe', text: '#6d28d9' },
    entreprise: { bg: '#dcfce7', text: '#166534' },
};
const badgeStyle = (cat) => {
    const c = categoryColors[cat] || { bg: '#f3f4f6', text: '#374151' };
    return `background:${c.bg};color:${c.text}`;
};

function destroy(post) {
    if (!confirm(`Supprimer l'article "${post.title}" ? Cette action est irréversible.`)) return;
    router.delete(route('admin.blog.destroy', post.id));
}
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Blog — Administration" />

        <div class="mx-auto max-w-7xl px-4 py-8">
            <AdminTabs />

            <!-- Flash -->
            <div
                v-if="flash()"
                class="mt-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800"
            >
                {{ flash() }}
            </div>

            <!-- En-tête -->
            <div class="mt-6 flex items-center justify-between">
                <h1 class="text-2xl font-extrabold text-gray-900">📝 Articles du Blog</h1>
                <Link
                    :href="route('admin.blog.create')"
                    class="rounded-xl px-5 py-2.5 text-sm font-bold transition hover:brightness-110"
                    style="background:#F0C040;color:#001d3d"
                >
                    + Nouvel article
                </Link>
            </div>

            <!-- Tableau -->
            <div class="mt-6 overflow-x-auto rounded-2xl border border-gray-100 bg-white shadow-sm">
                <table class="min-w-full text-sm">
                    <thead class="border-b border-gray-100 bg-gray-50">
                        <tr>
                            <th class="px-5 py-3 text-left font-semibold text-gray-600">Titre</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Catégorie</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Statut</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Auteur</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Publié le</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-if="posts.length === 0">
                            <td colspan="6" class="px-5 py-10 text-center text-gray-400">Aucun article pour l'instant.</td>
                        </tr>
                        <tr v-for="post in posts" :key="post.id" class="hover:bg-gray-50 transition">
                            <td class="px-5 py-3 font-medium text-gray-900 max-w-xs truncate">{{ post.title }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold" :style="badgeStyle(post.category)">
                                    {{ post.category_label }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold" :style="statusStyle(post.status)">
                                    {{ post.status === 'published' ? 'Publié' : 'Brouillon' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ post.author_name }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ post.published_at }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <Link
                                        :href="route('admin.blog.edit', post.id)"
                                        class="rounded-lg px-3 py-1 text-xs font-semibold text-white transition hover:opacity-80"
                                        style="background:#001d3d"
                                    >
                                        Éditer
                                    </Link>
                                    <button
                                        class="rounded-lg px-3 py-1 text-xs font-semibold text-white transition hover:opacity-80"
                                        style="background:#dc2626"
                                        @click="destroy(post)"
                                    >
                                        Supprimer
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

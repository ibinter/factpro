<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import PublicNav from '@/Pages/Public/Partials/PublicNav.vue';
import PublicFooter from '@/Pages/Public/Partials/PublicFooter.vue';

const props = defineProps({
    posts:      { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
    canLogin:    { type: Boolean, default: true },
    canRegister: { type: Boolean, default: true },
});

const activeCategory = ref('all');

const allCategories = computed(() => {
    const cats = [{ value: 'all', label: 'Tout' }];
    const map = { actualites: 'Actualités', tutoriels: 'Tutoriels', produit: 'Produit', entreprise: 'Entreprise' };
    props.categories.forEach(c => { if (map[c]) cats.push({ value: c, label: map[c] }); });
    return cats;
});

const filteredPosts = computed(() =>
    activeCategory.value === 'all'
        ? props.posts
        : props.posts.filter(p => p.category === activeCategory.value)
);

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
</script>

<template>
    <Head title="Blog IBIG FactPro — Actualités, tutoriels et conseils">
        <meta name="description" content="Retrouvez nos actualités, tutoriels, conseils de gestion et informations produit pour les PME africaines." />
    </Head>

    <PublicNav :canLogin="canLogin" :canRegister="canRegister" />

    <!-- Hero -->
    <section style="background:#001d3d" class="py-16 text-center">
        <h1 class="text-4xl font-extrabold text-white mb-3">Notre Blog</h1>
        <p class="text-lg text-white/70 max-w-xl mx-auto">
            Actualités, tutoriels, conseils de gestion et informations produit pour les PME africaines.
        </p>
    </section>

    <!-- Filtres -->
    <section class="bg-gray-50 border-b border-gray-200 py-4">
        <div class="mx-auto max-w-7xl px-6 flex flex-wrap gap-2">
            <button
                v-for="cat in allCategories"
                :key="cat.value"
                class="rounded-full px-4 py-1.5 text-sm font-semibold transition"
                :style="activeCategory === cat.value
                    ? 'background:#001d3d;color:#fff'
                    : 'background:#fff;color:#374151;border:1px solid #e5e7eb'"
                @click="activeCategory = cat.value"
            >
                {{ cat.label }}
            </button>
        </div>
    </section>

    <!-- Grille -->
    <main class="mx-auto max-w-7xl px-6 py-12">
        <!-- État vide -->
        <div v-if="filteredPosts.length === 0" class="text-center py-20">
            <div class="text-5xl mb-4">📝</div>
            <h2 class="text-xl font-bold text-gray-700 mb-2">Bientôt disponible</h2>
            <p class="text-gray-500">Nos prochains articles arrivent très bientôt. Revenez nous voir !</p>
        </div>

        <!-- Cartes -->
        <div v-else class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
            <Link
                v-for="post in filteredPosts"
                :key="post.id"
                :href="route('blog.show', post.slug)"
                class="group flex flex-col rounded-2xl overflow-hidden shadow-sm border border-gray-100 bg-white transition hover:shadow-md hover:-translate-y-0.5"
            >
                <!-- Cover -->
                <div class="h-44 flex-shrink-0 overflow-hidden">
                    <img
                        v-if="post.cover_image"
                        :src="post.cover_image"
                        :alt="post.title"
                        class="h-full w-full object-cover group-hover:scale-105 transition duration-300"
                    />
                    <div
                        v-else
                        class="h-full w-full flex items-center justify-center"
                        style="background:linear-gradient(135deg,#001d3d 0%,#003566 100%)"
                    >
                        <span class="text-4xl">📰</span>
                    </div>
                </div>

                <div class="flex flex-col flex-1 p-5">
                    <!-- Badge catégorie -->
                    <span
                        class="self-start mb-3 rounded-full px-3 py-0.5 text-xs font-bold"
                        :style="badgeStyle(post.category)"
                    >{{ post.category_label }}</span>

                    <h2 class="text-base font-bold text-gray-900 mb-2 group-hover:text-brand-600 transition line-clamp-2">
                        {{ post.title }}
                    </h2>
                    <p v-if="post.excerpt" class="text-sm text-gray-500 line-clamp-3 flex-1 mb-4">{{ post.excerpt }}</p>

                    <div class="flex items-center justify-between text-xs text-gray-400 mt-auto pt-3 border-t border-gray-100">
                        <span>✍️ {{ post.author_name }}</span>
                        <span>{{ post.published_at }}</span>
                    </div>
                </div>
            </Link>
        </div>
    </main>

    <PublicFooter />
</template>

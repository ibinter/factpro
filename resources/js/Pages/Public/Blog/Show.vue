<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicNav from '@/Pages/Public/Partials/PublicNav.vue';
import PublicFooter from '@/Pages/Public/Partials/PublicFooter.vue';

const props = defineProps({
    post:        { type: Object, required: true },
    related:     { type: Array, default: () => [] },
    canLogin:    { type: Boolean, default: true },
    canRegister: { type: Boolean, default: true },
});

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
    <Head :title="post.meta_title">
        <meta name="description" :content="post.meta_description" />
    </Head>

    <PublicNav :canLogin="canLogin" :canRegister="canRegister" />

    <main class="mx-auto max-w-7xl px-6 py-10">
        <!-- Breadcrumb -->
        <nav class="flex items-center gap-2 text-sm text-gray-400 mb-8">
            <Link href="/" class="hover:text-brand-600 transition">Accueil</Link>
            <span>/</span>
            <Link :href="route('blog.index')" class="hover:text-brand-600 transition">Blog</Link>
            <span>/</span>
            <span class="text-gray-600 truncate max-w-xs">{{ post.title }}</span>
        </nav>

        <div class="flex flex-col lg:flex-row gap-10">
            <!-- Article principal -->
            <article class="flex-1 min-w-0">
                <!-- Cover -->
                <div v-if="post.cover_image" class="mb-8 rounded-2xl overflow-hidden h-64">
                    <img :src="post.cover_image" :alt="post.title" class="h-full w-full object-cover" />
                </div>
                <div
                    v-else
                    class="mb-8 rounded-2xl h-40 flex items-center justify-center"
                    style="background:linear-gradient(135deg,#001d3d 0%,#003566 100%)"
                >
                    <span class="text-5xl">📰</span>
                </div>

                <!-- Meta -->
                <div class="flex flex-wrap items-center gap-3 mb-4">
                    <span
                        class="rounded-full px-3 py-0.5 text-xs font-bold"
                        :style="badgeStyle(post.category)"
                    >{{ post.category_label }}</span>
                    <span class="text-sm text-gray-400">✍️ {{ post.author_name }}</span>
                    <span class="text-sm text-gray-400">📅 {{ post.published_at }}</span>
                </div>

                <h1 class="text-3xl font-extrabold mb-6" style="color:#001d3d">{{ post.title }}</h1>

                <p v-if="post.excerpt" class="text-lg text-gray-600 mb-8 italic border-l-4 pl-4" style="border-color:#F0C040">
                    {{ post.excerpt }}
                </p>

                <!-- Contenu -->
                <div
                    class="prose max-w-none text-gray-800 leading-relaxed"
                    style="white-space:pre-wrap"
                >{{ post.content }}</div>

                <!-- Articles liés en bas -->
                <div v-if="related.length" class="mt-16">
                    <h2 class="text-xl font-bold mb-6" style="color:#001d3d">Articles similaires</h2>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <Link
                            v-for="r in related"
                            :key="r.id"
                            :href="route('blog.show', r.slug)"
                            class="rounded-xl border border-gray-100 bg-gray-50 p-4 hover:bg-white hover:shadow-sm transition"
                        >
                            <h3 class="font-semibold text-gray-900 mb-1 text-sm line-clamp-2">{{ r.title }}</h3>
                            <p v-if="r.excerpt" class="text-xs text-gray-500 line-clamp-2 mb-2">{{ r.excerpt }}</p>
                            <span class="text-xs text-gray-400">{{ r.published_at }}</span>
                        </Link>
                    </div>
                </div>
            </article>

            <!-- Sidebar sticky -->
            <aside class="w-full lg:w-72 flex-shrink-0">
                <div class="sticky top-24 space-y-6">
                    <!-- CTA -->
                    <div class="rounded-2xl p-6 text-center" style="background:#001d3d">
                        <div class="text-3xl mb-3">🚀</div>
                        <h3 class="text-white font-bold text-lg mb-2">Essayez FactPro gratuitement</h3>
                        <p class="text-white/70 text-sm mb-5">7 jours gratuits, sans carte bancaire. Créez votre première facture en 2 minutes.</p>
                        <a
                            href="/register"
                            class="block rounded-xl px-4 py-3 font-bold text-sm transition hover:brightness-110"
                            style="background:#F0C040;color:#001d3d"
                        >
                            Commencer l'essai gratuit →
                        </a>
                    </div>

                    <!-- Articles liés sidebar -->
                    <div v-if="related.length" class="rounded-2xl border border-gray-100 bg-white p-5">
                        <h3 class="font-bold text-gray-900 mb-4 text-sm uppercase tracking-wide">Articles liés</h3>
                        <div class="space-y-3">
                            <Link
                                v-for="r in related"
                                :key="r.id"
                                :href="route('blog.show', r.slug)"
                                class="block text-sm text-gray-700 hover:text-brand-600 transition font-medium line-clamp-2"
                            >
                                {{ r.title }}
                            </Link>
                        </div>
                    </div>

                    <!-- Retour au blog -->
                    <Link
                        :href="route('blog.index')"
                        class="block text-center text-sm text-gray-500 hover:text-brand-600 transition"
                    >
                        ← Retour au blog
                    </Link>
                </div>
            </aside>
        </div>
    </main>

    <PublicFooter />
</template>

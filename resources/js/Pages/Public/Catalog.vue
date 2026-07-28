<script setup>
import { ref, computed } from 'vue'
import { Head } from '@inertiajs/vue3'

const props = defineProps({
    company: Object,
    products: Array,
})

const search = ref('')

const filteredProducts = computed(() => {
    if (!search.value.trim()) return props.products
    const q = search.value.trim().toLowerCase()
    return props.products.filter(p =>
        p.name.toLowerCase().includes(q) ||
        (p.catalog_description_public && p.catalog_description_public.toLowerCase().includes(q)) ||
        (p.description && p.description.toLowerCase().includes(q))
    )
})

function formatPrice(price, currency) {
    if (price == null) return ''
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: currency || 'XOF',
        minimumFractionDigits: 0,
    }).format(price)
}

function companyInitials(name) {
    return name
        .split(' ')
        .slice(0, 2)
        .map(w => w[0])
        .join('')
        .toUpperCase()
}
</script>

<template>
    <Head :title="company.catalog_title || company.name" />

    <div class="min-h-screen bg-gray-50 font-sans">

        <!-- Header -->
        <header :style="{ backgroundColor: company.catalog_cover_color || '#2563eb' }" class="text-white py-12 px-4">
            <div class="max-w-5xl mx-auto flex flex-col items-center text-center gap-4">
                <!-- Logo ou initiales -->
                <div v-if="company.logo" class="w-20 h-20 rounded-2xl overflow-hidden shadow-lg bg-white">
                    <img :src="company.logo" :alt="company.name" class="w-full h-full object-contain p-1" />
                </div>
                <div v-else
                    class="w-20 h-20 rounded-2xl bg-white bg-opacity-20 flex items-center justify-center text-3xl font-bold shadow-lg">
                    {{ companyInitials(company.name) }}
                </div>

                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight drop-shadow">
                        {{ company.catalog_title || company.name }}
                    </h1>
                    <p v-if="company.catalog_description" class="mt-2 text-white/80 text-base max-w-xl">
                        {{ company.catalog_description }}
                    </p>
                    <p v-else class="mt-1 text-white/60 text-sm">{{ company.name }}</p>
                </div>
            </div>
        </header>

        <!-- Barre de recherche -->
        <div class="max-w-5xl mx-auto px-4 -mt-5">
            <div class="bg-white rounded-2xl shadow-md flex items-center gap-3 px-4 py-3">
                <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z" />
                </svg>
                <input v-model="search" type="text" placeholder="Rechercher un produit..."
                    class="flex-1 outline-none text-gray-700 placeholder-gray-400 bg-transparent text-sm" />
                <button v-if="search" @click="search = ''" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Grille produits -->
        <main class="max-w-5xl mx-auto px-4 py-10">
            <p v-if="filteredProducts.length === 0" class="text-center text-gray-400 py-16 text-lg">
                Aucun produit trouvé.
            </p>

            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="product in filteredProducts" :key="product.id"
                    class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow border border-gray-100 overflow-hidden flex flex-col">

                    <!-- Badge featured -->
                    <div v-if="product.catalog_featured"
                        class="flex items-center gap-1 px-3 py-1 bg-amber-50 border-b border-amber-100">
                        <span class="text-amber-400 text-sm">⭐</span>
                        <span class="text-xs font-semibold text-amber-600 uppercase tracking-wide">Coup de cœur</span>
                    </div>

                    <div class="p-5 flex flex-col flex-1 gap-3">
                        <!-- Icône produit -->
                        <div class="w-12 h-12 rounded-xl bg-gray-50 flex items-center justify-center text-2xl border border-gray-100">
                            📦
                        </div>

                        <!-- Infos -->
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-800 text-base leading-snug">{{ product.name }}</h3>
                            <p v-if="product.catalog_description_public || product.description"
                                class="mt-1 text-sm text-gray-500 line-clamp-3">
                                {{ product.catalog_description_public || product.description }}
                            </p>
                        </div>

                        <!-- Unité -->
                        <div v-if="product.unit" class="text-xs text-gray-400 uppercase tracking-wide">
                            par {{ product.unit }}
                        </div>

                        <!-- Prix -->
                        <div v-if="company.catalog_show_prices && product.price != null"
                            class="mt-auto pt-3 border-t border-gray-50">
                            <span class="text-xl font-bold text-gray-900">
                                {{ formatPrice(product.price, company.currency) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="text-center py-6 text-xs text-gray-400 border-t border-gray-100 mt-4">
            Catalogue propulsé par <strong class="text-gray-500">IBIG FactPro</strong>
        </footer>
    </div>
</template>

<style scoped>
.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>

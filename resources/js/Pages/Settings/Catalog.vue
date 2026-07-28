<script setup>
import { ref, reactive } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    company: Object,
    products: Array,
})

// Formulaire configuration catalogue
const form = useForm({
    catalog_enabled:      props.company.catalog_enabled ?? false,
    catalog_slug:         props.company.catalog_slug ?? '',
    catalog_title:        props.company.catalog_title ?? '',
    catalog_description:  props.company.catalog_description ?? '',
    catalog_show_prices:  props.company.catalog_show_prices ?? true,
    catalog_allow_orders: props.company.catalog_allow_orders ?? false,
    catalog_cover_color:  props.company.catalog_cover_color ?? '#2563eb',
})

function saveSettings() {
    form.put(route('settings.catalog.update'))
}

// État local des produits (pour les toggles sans rechargement de page)
const productStates = reactive({})
props.products.forEach(p => {
    productStates[p.id] = {
        catalog_visible:  p.catalog_visible,
        catalog_featured: p.catalog_featured,
        loading:          false,
    }
})

async function toggleProduct(productId, field) {
    if (productStates[productId].loading) return
    productStates[productId].loading = true

    try {
        const response = await fetch(route('settings.catalog.toggle-product', productId), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ field }),
        })

        if (response.ok) {
            const data = await response.json()
            productStates[productId].catalog_visible  = data.visible
            productStates[productId].catalog_featured = data.featured
        }
    } finally {
        productStates[productId].loading = false
    }
}

function publicUrl() {
    if (!form.catalog_slug) return null
    return window.location.origin + '/catalog/' + form.catalog_slug
}
</script>

<template>
    <Head title="Paramètres Catalogue" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Catalogue Produits Public
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-8">

                <!-- Section A : Configuration générale -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-6">Configuration du catalogue</h3>

                    <form @submit.prevent="saveSettings" class="space-y-6">

                        <!-- Toggle activation -->
                        <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                            <div>
                                <p class="font-medium text-gray-800 dark:text-gray-100">Activer le catalogue public</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Rend votre catalogue accessible via une URL publique</p>
                            </div>
                            <button type="button" @click="form.catalog_enabled = !form.catalog_enabled"
                                :class="form.catalog_enabled ? 'bg-blue-600' : 'bg-gray-300 dark:bg-gray-600'"
                                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                <span :class="form.catalog_enabled ? 'translate-x-6' : 'translate-x-1'"
                                    class="inline-block h-4 w-4 transform rounded-full bg-white shadow-sm transition-transform" />
                            </button>
                        </div>

                        <!-- Slug -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Identifiant unique (slug)
                            </label>
                            <div class="flex rounded-lg overflow-hidden border border-gray-300 dark:border-gray-600 focus-within:ring-2 focus-within:ring-blue-500">
                                <span class="inline-flex items-center px-3 bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-400 text-sm border-r border-gray-300 dark:border-gray-600 whitespace-nowrap">
                                    /catalog/
                                </span>
                                <input v-model="form.catalog_slug" type="text"
                                    placeholder="ma-boutique"
                                    pattern="[a-z0-9\-_]+"
                                    class="flex-1 px-3 py-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm outline-none" />
                            </div>
                            <p v-if="form.errors.catalog_slug" class="mt-1 text-sm text-red-600">{{ form.errors.catalog_slug }}</p>
                            <p v-if="publicUrl()" class="mt-1.5 text-xs text-blue-600 dark:text-blue-400">
                                URL publique :
                                <a :href="publicUrl()" target="_blank" class="underline hover:text-blue-800">{{ publicUrl() }}</a>
                            </p>
                        </div>

                        <!-- Titre -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Titre du catalogue
                            </label>
                            <input v-model="form.catalog_title" type="text"
                                placeholder="Notre boutique en ligne"
                                class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Description
                            </label>
                            <textarea v-model="form.catalog_description" rows="3"
                                placeholder="Découvrez notre sélection de produits..."
                                class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none" />
                        </div>

                        <!-- Couleur de fond -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Couleur d'en-tête
                            </label>
                            <div class="flex items-center gap-3">
                                <input v-model="form.catalog_cover_color" type="color"
                                    class="h-10 w-16 rounded-lg border border-gray-300 dark:border-gray-600 cursor-pointer p-1 bg-white dark:bg-gray-800" />
                                <span class="text-sm text-gray-600 dark:text-gray-400 font-mono">{{ form.catalog_cover_color }}</span>
                                <div class="flex-1 h-8 rounded-lg" :style="{ backgroundColor: form.catalog_cover_color }"></div>
                            </div>
                        </div>

                        <!-- Toggles show_prices / allow_orders -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                                <div>
                                    <p class="font-medium text-sm text-gray-800 dark:text-gray-100">Afficher les prix</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Visible par les visiteurs</p>
                                </div>
                                <button type="button" @click="form.catalog_show_prices = !form.catalog_show_prices"
                                    :class="form.catalog_show_prices ? 'bg-blue-600' : 'bg-gray-300 dark:bg-gray-600'"
                                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                    <span :class="form.catalog_show_prices ? 'translate-x-6' : 'translate-x-1'"
                                        class="inline-block h-4 w-4 transform rounded-full bg-white shadow-sm transition-transform" />
                                </button>
                            </div>

                            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                                <div>
                                    <p class="font-medium text-sm text-gray-800 dark:text-gray-100">Autoriser les commandes</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Bouton commander visible</p>
                                </div>
                                <button type="button" @click="form.catalog_allow_orders = !form.catalog_allow_orders"
                                    :class="form.catalog_allow_orders ? 'bg-blue-600' : 'bg-gray-300 dark:bg-gray-600'"
                                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                    <span :class="form.catalog_allow_orders ? 'translate-x-6' : 'translate-x-1'"
                                        class="inline-block h-4 w-4 transform rounded-full bg-white shadow-sm transition-transform" />
                                </button>
                            </div>
                        </div>

                        <!-- Bouton sauvegarder -->
                        <div class="flex justify-end pt-2">
                            <button type="submit"
                                :disabled="form.processing"
                                class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 disabled:opacity-60 text-white text-sm font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                {{ form.processing ? 'Enregistrement...' : 'Enregistrer' }}
                            </button>
                        </div>

                        <!-- Message succès -->
                        <div v-if="$page.props.flash?.success"
                            class="flex items-center gap-2 text-sm text-green-700 bg-green-50 dark:bg-green-900/20 dark:text-green-400 border border-green-200 dark:border-green-800 rounded-lg px-4 py-3">
                            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            {{ $page.props.flash.success }}
                        </div>
                    </form>
                </div>

                <!-- Section B : Tableau produits -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Produits du catalogue</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                            Gérez la visibilité et la mise en avant de chaque produit
                        </p>
                    </div>

                    <div v-if="products.length === 0" class="px-6 py-10 text-center text-gray-400 dark:text-gray-500">
                        Aucun produit dans votre catalogue.
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700/50 text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                <tr>
                                    <th class="px-6 py-3 text-left">Produit</th>
                                    <th class="px-6 py-3 text-center w-28">Visible</th>
                                    <th class="px-6 py-3 text-center w-36">Coup de cœur</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <tr v-for="product in products" :key="product.id"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-base flex-shrink-0">
                                                📦
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-800 dark:text-gray-100">{{ product.name }}</p>
                                                <p v-if="product.sku" class="text-xs text-gray-400 dark:text-gray-500">{{ product.sku }}</p>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Toggle Visible -->
                                    <td class="px-6 py-4 text-center">
                                        <button type="button"
                                            @click="toggleProduct(product.id, 'catalog_visible')"
                                            :disabled="productStates[product.id].loading"
                                            :class="productStates[product.id].catalog_visible ? 'bg-green-500' : 'bg-gray-300 dark:bg-gray-600'"
                                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 disabled:opacity-60">
                                            <span :class="productStates[product.id].catalog_visible ? 'translate-x-6' : 'translate-x-1'"
                                                class="inline-block h-4 w-4 transform rounded-full bg-white shadow-sm transition-transform" />
                                        </button>
                                    </td>

                                    <!-- Toggle Coup de coeur -->
                                    <td class="px-6 py-4 text-center">
                                        <button type="button"
                                            @click="toggleProduct(product.id, 'catalog_featured')"
                                            :disabled="productStates[product.id].loading"
                                            :class="productStates[product.id].catalog_featured ? 'bg-amber-400' : 'bg-gray-300 dark:bg-gray-600'"
                                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-amber-400 focus:ring-offset-2 disabled:opacity-60">
                                            <span :class="productStates[product.id].catalog_featured ? 'translate-x-6' : 'translate-x-1'"
                                                class="inline-block h-4 w-4 transform rounded-full bg-white shadow-sm transition-transform" />
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

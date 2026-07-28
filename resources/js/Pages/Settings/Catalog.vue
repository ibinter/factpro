<script setup>
import { ref, reactive, computed } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    company: Object,
    products: Array,
})

const page = usePage()

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

const publicUrl = computed(() => {
    if (!form.catalog_slug) return null
    return window.location.origin + '/catalog/' + form.catalog_slug
})

const qrUrl = computed(() => {
    if (!publicUrl.value) return null
    return `https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=${encodeURIComponent(publicUrl.value)}`
})

// Stats
const totalProducts = computed(() => props.products.length)
const visibleProducts = computed(() => props.products.filter(p => productStates[p.id]?.catalog_visible).length)
const featuredProducts = computed(() => props.products.filter(p => productStates[p.id]?.catalog_featured).length)

// Produits
const search = ref('')
const productStates = reactive({})
props.products.forEach(p => {
    productStates[p.id] = {
        catalog_visible:  p.catalog_visible,
        catalog_featured: p.catalog_featured,
        loading: false,
    }
})

const filteredProducts = computed(() =>
    props.products.filter(p =>
        !search.value || p.name.toLowerCase().includes(search.value.toLowerCase()) || (p.sku ?? '').toLowerCase().includes(search.value.toLowerCase())
    )
)

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

async function showAll() {
    for (const p of props.products) {
        if (!productStates[p.id].catalog_visible) await toggleProduct(p.id, 'catalog_visible')
    }
}
async function hideAll() {
    for (const p of props.products) {
        if (productStates[p.id].catalog_visible) await toggleProduct(p.id, 'catalog_visible')
    }
}

function copyUrl() {
    if (!publicUrl.value) return
    navigator.clipboard.writeText(publicUrl.value)
    copied.value = true
    setTimeout(() => copied.value = false, 2000)
}
const copied = ref(false)

function shareWhatsApp() {
    window.open(`https://wa.me/?text=${encodeURIComponent('Découvrez notre catalogue : ' + publicUrl.value)}`, '_blank')
}

function previewTextColor(hex) {
    const r = parseInt(hex.slice(1,3),16), g = parseInt(hex.slice(3,5),16), b = parseInt(hex.slice(5,7),16)
    return (r*299 + g*587 + b*114) / 1000 > 128 ? '#1e293b' : '#ffffff'
}
</script>

<template>
    <Head title="Catalogue Public" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100">Catalogue public</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Votre vitrine en ligne accessible sans connexion</p>
                </div>
                <a v-if="publicUrl && form.catalog_enabled" :href="publicUrl" target="_blank"
                    class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white hover:bg-brand-700 transition-colors shadow-sm">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    Voir le catalogue
                </a>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <!-- Bandeau statut désactivé -->
                <div v-if="!form.catalog_enabled" class="flex items-center gap-3 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 px-5 py-4">
                    <span class="text-2xl">🔒</span>
                    <div>
                        <p class="font-semibold text-amber-800 dark:text-amber-300">Catalogue désactivé</p>
                        <p class="text-sm text-amber-700 dark:text-amber-400">Activez le catalogue pour que vos clients puissent consulter vos produits en ligne.</p>
                    </div>
                </div>

                <!-- KPIs -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div class="rounded-xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 p-4 text-center shadow-sm">
                        <p class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ totalProducts }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Produits total</p>
                    </div>
                    <div class="rounded-xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 p-4 text-center shadow-sm">
                        <p class="text-2xl font-bold text-green-600">{{ visibleProducts }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Visibles</p>
                    </div>
                    <div class="rounded-xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 p-4 text-center shadow-sm">
                        <p class="text-2xl font-bold text-amber-500">{{ featuredProducts }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Coups de cœur</p>
                    </div>
                    <div class="rounded-xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 p-4 text-center shadow-sm">
                        <p class="text-2xl font-bold" :class="form.catalog_enabled ? 'text-brand-600' : 'text-gray-400'">
                            {{ form.catalog_enabled ? 'EN LIGNE' : 'HORS LIGNE' }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Statut</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    <!-- Colonne gauche : configuration -->
                    <div class="lg:col-span-2 space-y-6">

                        <!-- Card config principale -->
                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                            <h3 class="text-base font-semibold text-gray-800 dark:text-gray-100 mb-5 flex items-center gap-2">
                                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand-50 text-brand-600 text-sm">⚙️</span>
                                Configuration générale
                            </h3>

                            <form @submit.prevent="saveSettings" class="space-y-5">

                                <!-- Toggle activation -->
                                <div class="flex items-center justify-between p-4 rounded-xl border-2 transition-colors"
                                    :class="form.catalog_enabled ? 'bg-green-50 dark:bg-green-900/10 border-green-200 dark:border-green-800' : 'bg-gray-50 dark:bg-gray-700/50 border-gray-200 dark:border-gray-600'">
                                    <div>
                                        <p class="font-semibold text-gray-800 dark:text-gray-100">Catalogue public actif</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Accessible via URL publique sans connexion</p>
                                    </div>
                                    <button type="button" @click="form.catalog_enabled = !form.catalog_enabled"
                                        :class="form.catalog_enabled ? 'bg-green-500' : 'bg-gray-300 dark:bg-gray-600'"
                                        class="relative inline-flex h-7 w-12 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                        <span :class="form.catalog_enabled ? 'translate-x-6' : 'translate-x-1'"
                                            class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition-transform" />
                                    </button>
                                </div>

                                <!-- Slug -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Identifiant unique (slug)</label>
                                    <div class="flex rounded-lg overflow-hidden border border-gray-300 dark:border-gray-600 focus-within:ring-2 focus-within:ring-brand-500">
                                        <span class="inline-flex items-center px-3 bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-400 text-sm border-r border-gray-300 dark:border-gray-600 whitespace-nowrap">/catalog/</span>
                                        <input v-model="form.catalog_slug" type="text" placeholder="ma-boutique" pattern="[a-z0-9\-_]+"
                                            class="flex-1 px-3 py-2.5 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm outline-none" />
                                    </div>
                                    <p v-if="form.errors.catalog_slug" class="mt-1 text-sm text-red-600">{{ form.errors.catalog_slug }}</p>
                                </div>

                                <!-- Titre -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Titre du catalogue</label>
                                    <input v-model="form.catalog_title" type="text" placeholder="Notre boutique en ligne"
                                        class="w-full px-3 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500" />
                                </div>

                                <!-- Description -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                                    <textarea v-model="form.catalog_description" rows="3" placeholder="Découvrez notre sélection de produits..."
                                        class="w-full px-3 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 resize-none" />
                                </div>

                                <!-- Couleur + aperçu header -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Couleur d'en-tête</label>
                                    <div class="flex items-center gap-4">
                                        <input v-model="form.catalog_cover_color" type="color"
                                            class="h-11 w-16 rounded-lg border border-gray-300 dark:border-gray-600 cursor-pointer p-1 bg-white" />
                                        <span class="font-mono text-sm text-gray-600 dark:text-gray-400">{{ form.catalog_cover_color }}</span>
                                    </div>
                                    <!-- Mini-aperçu header -->
                                    <div class="mt-3 rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700">
                                        <div class="px-4 py-3 flex items-center justify-between" :style="{ backgroundColor: form.catalog_cover_color }">
                                            <span class="font-bold text-sm" :style="{ color: previewTextColor(form.catalog_cover_color) }">
                                                {{ form.catalog_title || 'Votre boutique' }}
                                            </span>
                                            <span class="text-xs px-2 py-0.5 rounded-full bg-white/20" :style="{ color: previewTextColor(form.catalog_cover_color) }">Aperçu</span>
                                        </div>
                                        <div class="bg-gray-50 dark:bg-gray-700/50 px-4 py-2 text-xs text-gray-400">
                                            {{ form.catalog_description || 'Description du catalogue...' }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Options -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl border border-gray-200 dark:border-gray-600">
                                        <div>
                                            <p class="font-medium text-sm text-gray-800 dark:text-gray-100">Afficher les prix</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Visible par les visiteurs</p>
                                        </div>
                                        <button type="button" @click="form.catalog_show_prices = !form.catalog_show_prices"
                                            :class="form.catalog_show_prices ? 'bg-brand-600' : 'bg-gray-300 dark:bg-gray-600'"
                                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none">
                                            <span :class="form.catalog_show_prices ? 'translate-x-6' : 'translate-x-1'"
                                                class="inline-block h-4 w-4 transform rounded-full bg-white shadow-sm transition-transform" />
                                        </button>
                                    </div>
                                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl border border-gray-200 dark:border-gray-600">
                                        <div>
                                            <p class="font-medium text-sm text-gray-800 dark:text-gray-100">Autoriser commandes</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Bouton "Commander" visible</p>
                                        </div>
                                        <button type="button" @click="form.catalog_allow_orders = !form.catalog_allow_orders"
                                            :class="form.catalog_allow_orders ? 'bg-brand-600' : 'bg-gray-300 dark:bg-gray-600'"
                                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none">
                                            <span :class="form.catalog_allow_orders ? 'translate-x-6' : 'translate-x-1'"
                                                class="inline-block h-4 w-4 transform rounded-full bg-white shadow-sm transition-transform" />
                                        </button>
                                    </div>
                                </div>

                                <!-- Bouton -->
                                <div class="flex items-center justify-between pt-2">
                                    <div v-if="page.props.flash?.success" class="flex items-center gap-2 text-sm text-green-700 dark:text-green-400">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                        Paramètres enregistrés !
                                    </div>
                                    <div v-else></div>
                                    <button type="submit" :disabled="form.processing"
                                        class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 disabled:opacity-60 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
                                        {{ form.processing ? 'Enregistrement...' : 'Enregistrer les paramètres' }}
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Tableau des produits -->
                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between gap-4">
                                <div>
                                    <h3 class="text-base font-semibold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                                        <span>📦</span> Produits du catalogue
                                    </h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ visibleProducts }} sur {{ totalProducts }} produits visibles</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button @click="showAll" type="button" class="text-xs px-3 py-1.5 rounded-lg bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 hover:bg-green-100 transition-colors font-medium">
                                        Tout afficher
                                    </button>
                                    <button @click="hideAll" type="button" class="text-xs px-3 py-1.5 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-200 transition-colors font-medium">
                                        Tout masquer
                                    </button>
                                </div>
                            </div>

                            <!-- Recherche -->
                            <div class="px-6 py-3 border-b border-gray-100 dark:border-gray-700">
                                <div class="relative">
                                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35"/></svg>
                                    <input v-model="search" type="text" placeholder="Rechercher un produit..."
                                        class="w-full pl-9 pr-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 text-sm text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-brand-500" />
                                </div>
                            </div>

                            <div v-if="filteredProducts.length === 0" class="px-6 py-10 text-center text-gray-400 dark:text-gray-500">
                                <p class="text-4xl mb-3">📭</p>
                                <p class="text-sm">Aucun produit trouvé.</p>
                            </div>

                            <div v-else class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead class="bg-gray-50 dark:bg-gray-700/50 text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        <tr>
                                            <th class="px-6 py-3 text-left">Produit</th>
                                            <th class="px-6 py-3 text-right">Prix</th>
                                            <th class="px-6 py-3 text-center w-28">Visible</th>
                                            <th class="px-6 py-3 text-center w-36">Coup de cœur ⭐</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                        <tr v-for="product in filteredProducts" :key="product.id"
                                            class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors"
                                            :class="productStates[product.id].catalog_featured ? 'bg-amber-50/30 dark:bg-amber-900/5' : ''">
                                            <td class="px-6 py-3">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-9 h-9 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-lg flex-shrink-0">
                                                        📦
                                                    </div>
                                                    <div>
                                                        <p class="font-medium text-gray-800 dark:text-gray-100">{{ product.name }}</p>
                                                        <p v-if="product.sku" class="text-xs text-gray-400">REF: {{ product.sku }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-3 text-right font-mono text-gray-700 dark:text-gray-300 text-sm">
                                                {{ product.price ? new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 0 }).format(product.price) : '—' }}
                                                <span v-if="product.price" class="text-xs text-gray-400"> FCFA</span>
                                            </td>
                                            <td class="px-6 py-3 text-center">
                                                <button type="button" @click="toggleProduct(product.id, 'catalog_visible')"
                                                    :disabled="productStates[product.id].loading"
                                                    :class="productStates[product.id].catalog_visible ? 'bg-green-500' : 'bg-gray-300 dark:bg-gray-600'"
                                                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none disabled:opacity-60">
                                                    <span :class="productStates[product.id].catalog_visible ? 'translate-x-6' : 'translate-x-1'"
                                                        class="inline-block h-4 w-4 transform rounded-full bg-white shadow-sm transition-transform" />
                                                </button>
                                            </td>
                                            <td class="px-6 py-3 text-center">
                                                <button type="button" @click="toggleProduct(product.id, 'catalog_featured')"
                                                    :disabled="productStates[product.id].loading"
                                                    :class="productStates[product.id].catalog_featured ? 'bg-amber-400' : 'bg-gray-300 dark:bg-gray-600'"
                                                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none disabled:opacity-60">
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

                    <!-- Colonne droite : partage & QR -->
                    <div class="space-y-6">

                        <!-- Card URL + partage -->
                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
                            <h3 class="text-base font-semibold text-gray-800 dark:text-gray-100 mb-4 flex items-center gap-2">
                                <span>🔗</span> Lien public
                            </h3>

                            <div v-if="publicUrl">
                                <!-- URL avec bouton copier -->
                                <div class="flex items-center gap-2 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600 mb-3">
                                    <p class="flex-1 text-xs text-gray-600 dark:text-gray-300 font-mono truncate">{{ publicUrl }}</p>
                                    <button @click="copyUrl" type="button"
                                        class="flex-shrink-0 text-xs px-2 py-1 rounded-md transition-colors"
                                        :class="copied ? 'bg-green-100 text-green-700' : 'bg-white dark:bg-gray-600 text-gray-600 dark:text-gray-300 hover:bg-gray-100 border border-gray-200 dark:border-gray-500'">
                                        {{ copied ? '✓ Copié' : 'Copier' }}
                                    </button>
                                </div>

                                <!-- Boutons partage -->
                                <div class="grid grid-cols-2 gap-2 mb-4">
                                    <a :href="publicUrl" target="_blank"
                                        class="flex items-center justify-center gap-1.5 rounded-lg border border-gray-200 dark:border-gray-600 px-3 py-2 text-xs font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        Ouvrir
                                    </a>
                                    <button @click="shareWhatsApp" type="button"
                                        class="flex items-center justify-center gap-1.5 rounded-lg bg-green-500 hover:bg-green-600 px-3 py-2 text-xs font-medium text-white transition-colors">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 00-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                        WhatsApp
                                    </button>
                                </div>

                                <!-- QR Code -->
                                <div class="text-center">
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-2 font-medium">QR Code du catalogue</p>
                                    <div class="inline-block p-2 bg-white border border-gray-200 dark:border-gray-600 rounded-xl shadow-sm">
                                        <img :src="qrUrl" alt="QR Code catalogue" class="w-36 h-36 rounded-lg" />
                                    </div>
                                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-2">Scannez pour accéder au catalogue</p>
                                    <a :href="qrUrl" download="qr-catalogue.png"
                                        class="mt-2 inline-block text-xs text-brand-600 hover:text-brand-800 dark:text-brand-400 underline">
                                        Télécharger le QR code
                                    </a>
                                </div>
                            </div>

                            <div v-else class="text-center py-6 text-gray-400 dark:text-gray-500">
                                <p class="text-3xl mb-2">🔗</p>
                                <p class="text-sm">Configurez un slug pour obtenir votre lien public.</p>
                            </div>
                        </div>

                        <!-- Card conseils -->
                        <div class="bg-brand-50 dark:bg-brand-900/20 rounded-2xl border border-brand-100 dark:border-brand-800 p-5">
                            <h3 class="text-sm font-semibold text-brand-800 dark:text-brand-300 mb-3 flex items-center gap-2">
                                <span>💡</span> Conseils
                            </h3>
                            <ul class="space-y-2 text-xs text-brand-700 dark:text-brand-400">
                                <li class="flex items-start gap-2">
                                    <span class="mt-0.5">✓</span>
                                    <span>Marquez les produits phares comme "Coup de cœur" pour les mettre en avant</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="mt-0.5">✓</span>
                                    <span>Partagez le QR code sur vos supports imprimés (cartes de visite, flyers)</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="mt-0.5">✓</span>
                                    <span>Désactivez "Afficher les prix" pour un catalogue sur devis</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="mt-0.5">✓</span>
                                    <span>Activez "Autoriser commandes" pour recevoir des demandes directement</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

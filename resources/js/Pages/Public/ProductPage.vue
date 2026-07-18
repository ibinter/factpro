<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- En-tête company -->
    <header class="bg-white dark:bg-gray-800 shadow-sm">
      <div class="max-w-4xl mx-auto px-4 py-4 flex items-center gap-4">
        <img
          v-if="company.logo"
          :src="company.logo"
          :alt="company.name"
          class="h-12 w-auto object-contain"
        />
        <div
          v-else
          class="h-12 w-12 rounded-full bg-indigo-600 flex items-center justify-center text-white font-bold text-xl"
        >
          {{ company.name.charAt(0).toUpperCase() }}
        </div>
        <div>
          <h1 class="text-lg font-bold text-gray-900 dark:text-white">{{ company.name }}</h1>
          <p v-if="company.address" class="text-sm text-gray-500 dark:text-gray-400">{{ company.address }}</p>
        </div>
      </div>
    </header>

    <!-- Contenu produit -->
    <main class="max-w-4xl mx-auto px-4 py-8">
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden">
        <div class="md:flex">
          <!-- Image produit -->
          <div class="md:w-1/2 bg-gray-100 dark:bg-gray-700 flex items-center justify-center min-h-64">
            <img
              v-if="product.public_images && product.public_images.length > 0"
              :src="product.public_images[0]"
              :alt="product.name"
              class="object-contain max-h-80 w-full"
            />
            <div v-else class="flex flex-col items-center justify-center py-16 text-gray-400 dark:text-gray-500">
              <svg class="w-24 h-24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                  d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
              </svg>
              <span class="mt-2 text-sm">Aucune image</span>
            </div>
          </div>

          <!-- Infos produit -->
          <div class="md:w-1/2 p-6 flex flex-col gap-4">
            <!-- Badges -->
            <div class="flex gap-2 flex-wrap">
              <span
                v-if="product.category"
                class="px-2 py-1 bg-indigo-100 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-300 rounded-full text-xs font-medium"
              >
                {{ product.category }}
              </span>
              <span :class="stockBadgeClass" class="px-2 py-1 rounded-full text-xs font-medium">
                {{ stockLabel }}
              </span>
            </div>

            <!-- Nom & référence -->
            <div>
              <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ product.name }}</h2>
              <p v-if="product.sku" class="text-sm text-gray-500 dark:text-gray-400 mt-1">Réf. : {{ product.sku }}</p>
            </div>

            <!-- Prix -->
            <div v-if="product.price" class="mt-2">
              <p class="text-3xl font-extrabold text-indigo-600 dark:text-indigo-400">
                {{ formatPrice(product.price) }}
                <span class="text-base font-normal text-gray-500 dark:text-gray-400">HT</span>
                <span v-if="product.unit" class="text-base font-normal text-gray-500 dark:text-gray-400"> / {{ product.unit }}</span>
              </p>
              <p v-if="product.tax_rate > 0" class="text-sm text-gray-500 dark:text-gray-400">
                {{ formatPrice(product.price * (1 + product.tax_rate / 100)) }} TTC (TVA {{ product.tax_rate }}%)
              </p>
            </div>

            <!-- Description -->
            <div v-if="product.description" class="text-gray-700 dark:text-gray-300 text-sm leading-relaxed">
              {{ product.description }}
            </div>

            <!-- Quantité min -->
            <p v-if="product.minimum_order_qty > 1" class="text-xs text-gray-500 dark:text-gray-400">
              Quantité minimale de commande : {{ product.minimum_order_qty }}
            </p>

            <!-- Bouton Commander -->
            <div class="mt-auto pt-4">
              <button
                v-if="product.allow_online_order"
                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-6 rounded-xl transition-colors"
                @click="handleOrder"
              >
                Commander
              </button>
              <div v-if="company.phone || company.email" class="mt-3 text-sm text-center text-gray-500 dark:text-gray-400">
                <span v-if="company.phone">Tél. : {{ company.phone }}</span>
                <span v-if="company.phone && company.email"> · </span>
                <a v-if="company.email" :href="'mailto:'+company.email" class="underline">{{ company.email }}</a>
              </div>
            </div>
          </div>
        </div>

        <!-- Images supplémentaires -->
        <div v-if="product.public_images && product.public_images.length > 1" class="border-t dark:border-gray-700 p-4">
          <div class="flex gap-3 overflow-x-auto">
            <img
              v-for="(img, i) in product.public_images"
              :key="i"
              :src="img"
              :alt="product.name + ' image ' + (i+1)"
              class="h-20 w-20 object-cover rounded-lg cursor-pointer border-2 border-transparent hover:border-indigo-500"
            />
          </div>
        </div>

        <!-- QR code -->
        <div class="border-t dark:border-gray-700 p-6 flex flex-col sm:flex-row items-center gap-4">
          <div class="flex-shrink-0">
            <img :src="qrCode" alt="QR Code page publique" class="w-24 h-24" />
          </div>
          <div class="text-sm text-gray-500 dark:text-gray-400">
            <p class="font-medium text-gray-700 dark:text-gray-300">Scannez pour partager cette page</p>
            <p class="mt-1 break-all text-xs">{{ publicUrl }}</p>
          </div>
        </div>
      </div>
    </main>

    <!-- Pied de page -->
    <footer class="text-center py-6 text-xs text-gray-400 dark:text-gray-600">
      Propulsé par <span class="font-semibold text-indigo-500">FactPro</span>
    </footer>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  product: Object,
  company: Object,
  qrCode: String,
  publicUrl: String,
})

const stockBadgeClass = computed(() => {
  switch (props.product.stock_status) {
    case 'out_of_stock':
      return 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300'
    case 'low_stock':
      return 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300'
    default:
      return 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300'
  }
})

const stockLabel = computed(() => {
  switch (props.product.stock_status) {
    case 'out_of_stock':
      return 'Rupture de stock'
    case 'low_stock':
      return 'Stock limité'
    default:
      return 'En stock'
  }
})

function formatPrice(value) {
  return new Intl.NumberFormat('fr-CI', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(value)
}

function handleOrder() {
  // Redirige vers le portail client ou formulaire de contact
  window.location.href = `mailto:${props.company.email || ''}?subject=Commande : ${props.product.name}`
}
</script>

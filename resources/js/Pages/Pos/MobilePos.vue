<template>
  <Head title="POS Mobile" />

  <div class="mobile-pos min-h-screen bg-gray-950 text-white flex flex-col select-none">

    <!-- ============================================================ -->
    <!-- Header -->
    <!-- ============================================================ -->
    <header class="flex items-center justify-between px-4 py-3 bg-gray-900 border-b border-gray-800">
      <a :href="route('pos.index')" class="text-gray-400 hover:text-white text-sm flex items-center gap-1">
        ← POS Desktop
      </a>
      <span class="font-bold text-green-400 text-sm">📱 POS Mobile</span>
      <button
        @click="openScanner"
        class="bg-green-600 hover:bg-green-500 text-white text-sm px-3 py-1.5 rounded-lg flex items-center gap-1"
      >
        📷 Scanner
      </button>
    </header>

    <!-- ============================================================ -->
    <!-- Zone principale : recherche + panier -->
    <!-- ============================================================ -->
    <main class="flex-1 flex flex-col overflow-hidden">

      <!-- Recherche manuelle -->
      <div class="px-4 py-3 bg-gray-900 border-b border-gray-800">
        <input
          v-model="searchQuery"
          @keydown.enter="searchManual"
          type="search"
          placeholder="Rechercher un produit ou saisir un code…"
          class="w-full bg-gray-800 text-white placeholder-gray-500 rounded-xl px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-green-500"
        />
      </div>

      <!-- Résultats de recherche (dropdown) -->
      <div v-if="searchResults.length && searchQuery" class="absolute z-30 left-4 right-4 mt-20 bg-gray-800 border border-gray-700 rounded-xl overflow-hidden shadow-2xl max-h-64 overflow-y-auto">
        <button
          v-for="p in searchResults"
          :key="p.id"
          @click="addToCart(p)"
          class="w-full flex items-center justify-between px-4 py-3 text-left hover:bg-gray-700 border-b border-gray-700 last:border-0"
        >
          <div>
            <div class="text-sm font-medium text-white">{{ p.name }}</div>
            <div class="text-xs text-gray-400">{{ p.sku || p.barcode }}</div>
          </div>
          <div class="text-right">
            <div class="text-sm font-bold text-green-400">{{ fmtPrice(p.price) }}</div>
            <div v-if="p.stock !== undefined" class="text-xs text-gray-400">Stock : {{ p.stock }}</div>
          </div>
        </button>
      </div>

      <!-- Panier -->
      <div class="flex-1 overflow-y-auto px-4 py-3 space-y-2">
        <div v-if="cart.length === 0" class="flex flex-col items-center justify-center h-40 text-gray-600">
          <div class="text-4xl mb-3">🛒</div>
          <p class="text-sm">Panier vide — scannez un produit</p>
        </div>

        <div
          v-for="item in cart"
          :key="item.id"
          class="flex items-center gap-3 bg-gray-900 rounded-xl px-4 py-3"
        >
          <!-- Infos produit -->
          <div class="flex-1 min-w-0">
            <div class="text-sm font-medium text-white truncate">{{ item.name }}</div>
            <div class="text-xs text-gray-400">{{ fmtPrice(item.price) }} × {{ item.qty }}</div>
          </div>

          <!-- Contrôle quantité -->
          <div class="flex items-center gap-2">
            <button
              @click="decrementQty(item)"
              class="w-8 h-8 flex items-center justify-center bg-gray-700 hover:bg-gray-600 rounded-full text-lg font-bold"
            >−</button>
            <span class="text-base font-bold w-6 text-center">{{ item.qty }}</span>
            <button
              @click="incrementQty(item)"
              class="w-8 h-8 flex items-center justify-center bg-gray-700 hover:bg-gray-600 rounded-full text-lg font-bold"
            >+</button>
          </div>

          <!-- Sous-total -->
          <div class="text-right min-w-[70px]">
            <div class="text-sm font-bold text-green-400">{{ fmtPrice(item.price * item.qty) }}</div>
          </div>

          <!-- Supprimer -->
          <button
            @click="removeFromCart(item.id)"
            class="text-gray-600 hover:text-red-400 text-lg"
          >✕</button>
        </div>
      </div>
    </main>

    <!-- ============================================================ -->
    <!-- Footer : totaux + paiement -->
    <!-- ============================================================ -->
    <footer class="bg-gray-900 border-t border-gray-800">
      <!-- Totaux -->
      <div class="px-4 py-3 space-y-1">
        <div class="flex justify-between text-sm text-gray-400">
          <span>Sous-total HT</span>
          <span>{{ fmtPrice(totalHT) }}</span>
        </div>
        <div class="flex justify-between text-sm text-gray-400">
          <span>TVA</span>
          <span>{{ fmtPrice(totalTVA) }}</span>
        </div>
        <div class="flex justify-between text-base font-bold text-white border-t border-gray-700 pt-2 mt-2">
          <span>TOTAL TTC</span>
          <span class="text-green-400 text-xl">{{ fmtPrice(totalTTC) }}</span>
        </div>
      </div>

      <!-- Boutons de paiement rapide -->
      <div class="grid grid-cols-2 gap-2 px-4 pb-4">
        <button
          v-for="method in paymentMethods"
          :key="method.key"
          @click="pay(method.key)"
          :disabled="cart.length === 0 || paying"
          class="flex items-center justify-center gap-2 py-4 rounded-xl text-sm font-bold disabled:opacity-40 disabled:cursor-not-allowed transition-all active:scale-95"
          :class="method.class"
        >
          <span class="text-xl">{{ method.icon }}</span>
          {{ method.label }}
        </button>
      </div>
    </footer>

    <!-- ============================================================ -->
    <!-- Scanner caméra (plein écran) -->
    <!-- ============================================================ -->
    <BarcodeScanner
      v-if="scannerVisible"
      :autoStart="true"
      @detected="onBarcodeDetected"
      @close="closeScanner"
      @error="onScanError"
    />

    <!-- Toast notification -->
    <Transition name="toast">
      <div
        v-if="toast"
        class="fixed top-4 left-4 right-4 z-50 bg-gray-800 border rounded-xl px-4 py-3 text-sm text-white shadow-2xl"
        :class="toast.type === 'error' ? 'border-red-500' : 'border-green-500'"
      >
        <div class="flex items-center gap-2">
          <span>{{ toast.type === 'error' ? '❌' : '✅' }}</span>
          <span>{{ toast.message }}</span>
        </div>
      </div>
    </Transition>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import BarcodeScanner from '@/Components/BarcodeScanner.vue'
import axios from 'axios'

// ----------------------------------------------------------------
// Props (données passées depuis le serveur via Inertia)
// ----------------------------------------------------------------
const props = defineProps({
  currency: { type: String, default: 'FCFA' },
})

// ----------------------------------------------------------------
// État
// ----------------------------------------------------------------
const cart = ref([])
const scannerVisible = ref(false)
const searchQuery = ref('')
const searchResults = ref([])
const paying = ref(false)
const toast = ref(null)

// Cache local IndexedDB (produits)
const DB_NAME = 'factpro_pos_mobile'
const DB_VERSION = 1
let db = null

// ----------------------------------------------------------------
// Formatage
// ----------------------------------------------------------------
const fmtPrice = (n) =>
  new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(Math.round(Number(n ?? 0)))

// ----------------------------------------------------------------
// Totaux
// ----------------------------------------------------------------
const totalHT = computed(() =>
  cart.value.reduce((sum, item) => {
    const ht = item.price / (1 + (item.tax_rate ?? 0) / 100)
    return sum + ht * item.qty
  }, 0)
)

const totalTVA = computed(() => {
  return cart.value.reduce((sum, item) => {
    const ht = item.price / (1 + (item.tax_rate ?? 0) / 100)
    return sum + (item.price - ht) * item.qty
  }, 0)
})

const totalTTC = computed(() =>
  cart.value.reduce((sum, item) => sum + item.price * item.qty, 0)
)

// ----------------------------------------------------------------
// Méthodes de paiement
// ----------------------------------------------------------------
const paymentMethods = [
  { key: 'cash', label: 'Espèces', icon: '💵', class: 'bg-yellow-700 hover:bg-yellow-600 text-white' },
  { key: 'card', label: 'Carte', icon: '💳', class: 'bg-blue-700 hover:bg-blue-600 text-white' },
  { key: 'mobile_money', label: 'Mobile Money', icon: '📱', class: 'bg-orange-700 hover:bg-orange-600 text-white' },
  { key: 'portal', label: 'Portail', icon: '🌐', class: 'bg-purple-700 hover:bg-purple-600 text-white' },
]

// ----------------------------------------------------------------
// Scanner
// ----------------------------------------------------------------
function openScanner() {
  scannerVisible.value = true
}

function closeScanner() {
  scannerVisible.value = false
}

async function onBarcodeDetected(code) {
  closeScanner()

  // 1. Chercher dans le cache IndexedDB
  const cached = await getFromCache(code)
  if (cached) {
    addToCart(cached)
    showToast(`${cached.name} ajouté ✓`, 'success')
    return
  }

  // 2. Appel API
  try {
    const response = await axios.get('/barcode/lookup', { params: { code } })
    if (response.data.found) {
      const p = response.data.product
      addToCart(p)
      saveToCache(p) // Mettre en cache pour offline
      showToast(`${p.name} ajouté ✓`, 'success')
    } else {
      showToast(`Code inconnu : ${code}`, 'error')
    }
  } catch (e) {
    if (e.response?.status === 404) {
      showToast(`Produit non trouvé : ${code}`, 'error')
    } else {
      showToast('Erreur réseau — mode offline', 'error')
    }
  }
}

function onScanError(err) {
  console.error('Scanner error:', err)
}

// ----------------------------------------------------------------
// Panier
// ----------------------------------------------------------------
function addToCart(product) {
  const existing = cart.value.find(i => i.id === product.id)
  if (existing) {
    existing.qty++
  } else {
    cart.value.push({
      id: product.id,
      name: product.name,
      sku: product.sku,
      barcode: product.barcode,
      price: parseFloat(product.price),
      tax_rate: parseFloat(product.tax_rate ?? 0),
      unit: product.unit ?? 'unité',
      stock: product.stock,
      qty: 1,
    })
  }
  if (navigator.vibrate) navigator.vibrate(50)
  searchQuery.value = ''
  searchResults.value = []
}

function incrementQty(item) {
  item.qty++
}

function decrementQty(item) {
  if (item.qty <= 1) {
    removeFromCart(item.id)
  } else {
    item.qty--
  }
}

function removeFromCart(id) {
  cart.value = cart.value.filter(i => i.id !== id)
}

// ----------------------------------------------------------------
// Recherche manuelle
// ----------------------------------------------------------------
async function searchManual() {
  const q = searchQuery.value.trim()
  if (!q) return

  // Essai barcode exact d'abord
  try {
    const response = await axios.get('/barcode/lookup', { params: { code: q } })
    if (response.data.found) {
      addToCart(response.data.product)
      return
    }
  } catch (e) { /* continuer */ }

  // Recherche dans le cache
  const results = await searchCache(q)
  searchResults.value = results
}

// ----------------------------------------------------------------
// Paiement
// ----------------------------------------------------------------
async function pay(method) {
  if (cart.value.length === 0 || paying.value) return
  paying.value = true

  try {
    const items = cart.value.map(i => ({
      product_id: i.id,
      name: i.name,
      qty: i.qty,
      unit_price: i.price,
      tax_rate: i.tax_rate,
      unit: i.unit,
    }))

    await axios.post('/pos/checkout', {
      items,
      payment_method: method,
      total: totalTTC.value,
    })

    showToast(`Paiement ${method} enregistré ✓`, 'success')
    cart.value = []
  } catch (e) {
    showToast('Erreur lors du paiement', 'error')
  } finally {
    paying.value = false
  }
}

// ----------------------------------------------------------------
// IndexedDB — cache produits offline
// ----------------------------------------------------------------
async function openDb() {
  return new Promise((resolve, reject) => {
    const req = indexedDB.open(DB_NAME, DB_VERSION)
    req.onupgradeneeded = (e) => {
      const database = e.target.result
      if (!database.objectStoreNames.contains('products')) {
        const store = database.createObjectStore('products', { keyPath: 'id' })
        store.createIndex('barcode', 'barcode', { unique: false })
        store.createIndex('sku', 'sku', { unique: false })
        store.createIndex('name', 'name', { unique: false })
      }
    }
    req.onsuccess = (e) => resolve(e.target.result)
    req.onerror = (e) => reject(e)
  })
}

async function getFromCache(code) {
  if (!db) return null
  return new Promise((resolve) => {
    const tx = db.transaction('products', 'readonly')
    const store = tx.objectStore('products')

    // Essayer barcode
    const barcodeIdx = store.index('barcode')
    const req = barcodeIdx.get(code)
    req.onsuccess = (e) => {
      if (e.target.result) {
        resolve(e.target.result)
        return
      }
      // Essayer sku
      const skuIdx = store.index('sku')
      const req2 = skuIdx.get(code)
      req2.onsuccess = (e2) => resolve(e2.target.result ?? null)
      req2.onerror = () => resolve(null)
    }
    req.onerror = () => resolve(null)
  })
}

async function saveToCache(product) {
  if (!db) return
  const tx = db.transaction('products', 'readwrite')
  tx.objectStore('products').put(product)
}

async function searchCache(query) {
  if (!db) return []
  return new Promise((resolve) => {
    const tx = db.transaction('products', 'readonly')
    const store = tx.objectStore('products')
    const results = []
    const q = query.toLowerCase()
    const req = store.openCursor()
    req.onsuccess = (e) => {
      const cursor = e.target.result
      if (cursor) {
        const p = cursor.value
        if (
          (p.name ?? '').toLowerCase().includes(q) ||
          (p.sku ?? '').toLowerCase().includes(q) ||
          (p.barcode ?? '').toLowerCase().includes(q)
        ) {
          results.push(p)
        }
        cursor.continue()
      } else {
        resolve(results.slice(0, 10))
      }
    }
    req.onerror = () => resolve([])
  })
}

// ----------------------------------------------------------------
// Toast
// ----------------------------------------------------------------
let toastTimer = null
function showToast(message, type = 'success') {
  toast.value = { message, type }
  clearTimeout(toastTimer)
  toastTimer = setTimeout(() => { toast.value = null }, 3000)
}

// ----------------------------------------------------------------
// Init
// ----------------------------------------------------------------
onMounted(async () => {
  try {
    db = await openDb()
  } catch (e) {
    console.warn('IndexedDB non disponible — mode offline désactivé')
  }
})
</script>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}
.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>

<script setup>
import { ref, computed } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
const page = usePage();
const showMore = ref(false);
const currentUrl = computed(() => page.url);
</script>

<template>
    <nav class="fixed bottom-0 inset-x-0 z-40 md:hidden border-t" style="background:#fff;border-color:#e5e7eb;padding-bottom:env(safe-area-inset-bottom)">
        <div class="grid grid-cols-5 h-14">
            <Link :href="route('dashboard')" class="flex flex-col items-center justify-center gap-0.5 text-xs transition" :class="currentUrl === '/dashboard' ? 'text-blue-600' : 'text-gray-500'">
                <span class="text-xl">🏠</span><span>Accueil</span>
            </Link>
            <Link :href="route('documents.index')" class="flex flex-col items-center justify-center gap-0.5 text-xs transition" :class="currentUrl.startsWith('/documents') ? 'text-blue-600' : 'text-gray-500'">
                <span class="text-xl">📄</span><span>Factures</span>
            </Link>
            <!-- Bouton central accentué -->
            <Link :href="route('documents.create')" class="flex flex-col items-center justify-center">
                <span class="flex h-12 w-12 items-center justify-center rounded-full text-white text-2xl shadow-lg" style="background:#002D5B;margin-top:-20px">＋</span>
            </Link>
            <Link :href="route('customers.index')" class="flex flex-col items-center justify-center gap-0.5 text-xs transition" :class="currentUrl.startsWith('/customers') ? 'text-blue-600' : 'text-gray-500'">
                <span class="text-xl">👥</span><span>Clients</span>
            </Link>
            <button @click="showMore = !showMore" class="flex flex-col items-center justify-center gap-0.5 text-xs text-gray-500">
                <span class="text-xl">☰</span><span>Plus</span>
            </button>
        </div>
    </nav>

    <!-- Drawer "Plus" -->
    <Transition name="slide-up">
        <div v-if="showMore" class="fixed inset-0 z-50 md:hidden" @click.self="showMore = false">
            <div class="absolute bottom-0 inset-x-0 rounded-t-2xl bg-white shadow-2xl p-4" style="padding-bottom:calc(1rem + env(safe-area-inset-bottom))">
                <div class="grid grid-cols-4 gap-3 mb-4">
                    <Link
                        v-for="item in [
                            { icon: '📦', label: 'Catalogue', href: route('products.index') },
                            { icon: '🏪', label: 'Caisse', href: route('pos.index') },
                            { icon: '📊', label: 'Rapports', href: route('reports.index') },
                            { icon: '🛒', label: 'Achats', href: route('purchases.index') },
                            { icon: '📋', label: 'Récurrents', href: route('recurring.index') },
                            { icon: '⚙️', label: 'Paramètres', href: route('companies.settings') },
                            { icon: '🎫', label: 'Support', href: route('support.index') },
                            { icon: '👤', label: 'Profil', href: route('profile.edit') },
                        ]"
                        :key="item.href"
                        :href="item.href"
                        @click="showMore = false"
                        class="flex flex-col items-center gap-1 rounded-xl p-3 text-xs text-gray-700 hover:bg-gray-50"
                    >
                        <span class="text-2xl">{{ item.icon }}</span>
                        <span>{{ item.label }}</span>
                    </Link>
                </div>
                <button @click="showMore = false" class="w-full py-2.5 rounded-xl bg-gray-100 text-sm font-medium text-gray-600">Fermer</button>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.slide-up-enter-active { transition: transform .25s ease, opacity .25s ease; }
.slide-up-leave-active { transition: transform .2s ease, opacity .2s ease; }
.slide-up-enter-from  { transform: translateY(100%); opacity: 0; }
.slide-up-leave-to    { transform: translateY(100%); opacity: 0; }
</style>

<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';

const open = ref(false);
const query = ref('');
const results = ref([]);
const loading = ref(false);
const selected = ref(0);
const inputEl = ref(null);

// Keyboard shortcut: Ctrl+K or ⌘K
function onKeydown(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        open.value = !open.value;
        if (open.value) {
            query.value = '';
            results.value = [];
            selected.value = 0;
        }
    }
    if (e.key === 'Escape') open.value = false;
}

onMounted(() => document.addEventListener('keydown', onKeydown));
onUnmounted(() => document.removeEventListener('keydown', onKeydown));

let debounceTimer = null;
watch(query, (q) => {
    clearTimeout(debounceTimer);
    selected.value = 0;
    if (q.length < 2) { results.value = []; return; }
    loading.value = true;
    debounceTimer = setTimeout(async () => {
        try {
            const { data } = await axios.get('/search', { params: { q } });
            results.value = data.results;
        } catch { results.value = []; }
        finally { loading.value = false; }
    }, 200);
});

function navigate(url) {
    open.value = false;
    router.visit(url);
}

function onArrow(dir) {
    selected.value = Math.max(0, Math.min(results.value.length - 1, selected.value + dir));
}
function onEnter() {
    if (results.value[selected.value]) navigate(results.value[selected.value].url);
}
</script>

<template>
    <Teleport to="body">
        <Transition name="search-overlay">
            <div v-if="open" class="fixed inset-0 z-[200] flex items-start justify-center pt-[10vh] px-4"
                 style="background:rgba(0,0,0,0.5);backdrop-filter:blur(4px)"
                 @click.self="open = false">
                <div class="w-full max-w-xl rounded-2xl shadow-2xl overflow-hidden" style="background:#fff">
                    <!-- Input -->
                    <div class="flex items-center gap-3 px-4 py-3 border-b border-gray-100">
                        <svg class="h-5 w-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35"/></svg>
                        <input
                            ref="inputEl"
                            v-model="query"
                            @keydown.up.prevent="onArrow(-1)"
                            @keydown.down.prevent="onArrow(1)"
                            @keydown.enter.prevent="onEnter"
                            @keydown.escape="open = false"
                            placeholder="Rechercher clients, factures, produits…"
                            class="flex-1 outline-none text-sm text-gray-800 placeholder:text-gray-400 bg-transparent"
                            autofocus
                        />
                        <span class="text-xs text-gray-400 bg-gray-100 rounded px-1.5 py-0.5">Esc</span>
                    </div>

                    <!-- Results -->
                    <div class="max-h-80 overflow-y-auto">
                        <!-- Loading -->
                        <div v-if="loading" class="flex items-center justify-center py-8 text-sm text-gray-400">
                            <svg class="h-5 w-5 animate-spin mr-2 text-blue-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            Recherche en cours…
                        </div>

                        <!-- Empty -->
                        <div v-else-if="query.length >= 2 && results.length === 0" class="py-10 text-center text-sm text-gray-400">
                            Aucun résultat pour "<strong class="text-gray-600">{{ query }}</strong>"
                        </div>

                        <!-- Results list -->
                        <div v-else-if="results.length > 0" class="py-2">
                            <button
                                v-for="(r, i) in results"
                                :key="i"
                                @click="navigate(r.url)"
                                @mouseenter="selected = i"
                                class="flex w-full items-center gap-3 px-4 py-2.5 text-left transition"
                                :class="selected === i ? 'bg-blue-50' : 'hover:bg-gray-50'"
                            >
                                <span class="text-xl">{{ r.icon }}</span>
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-medium text-gray-800 truncate">{{ r.label }}</div>
                                    <div class="text-xs text-gray-400 truncate">{{ r.sub }}</div>
                                </div>
                                <svg class="h-4 w-4 text-gray-300 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/></svg>
                            </button>
                        </div>

                        <!-- Hint when empty query -->
                        <div v-else class="py-6 px-4 text-center text-xs text-gray-400">
                            Tapez au moins 2 caractères · ↑↓ pour naviguer · Entrée pour ouvrir · Esc pour fermer
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.search-overlay-enter-active,.search-overlay-leave-active{transition:all .15s}
.search-overlay-enter-from,.search-overlay-leave-to{opacity:0;transform:scale(.97)}
</style>

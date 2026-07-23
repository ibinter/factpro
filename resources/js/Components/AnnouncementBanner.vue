<script setup>
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const announcements = computed(() => page.props.announcements || []);

const dismissed = ref(() => {
    try { return JSON.parse(localStorage.getItem('factpro_dismissed_announcements') || '[]'); }
    catch { return []; }
});

function isDismissed(id) {
    return dismissed.value.includes(id);
}

function dismiss(id) {
    const d = [...dismissed.value, id];
    dismissed.value = d;
    localStorage.setItem('factpro_dismissed_announcements', JSON.stringify(d));
}

const visible = computed(() => announcements.value.filter(a => !isDismissed(a.id)));

const typeColors = {
    info:    { bg: '#1a56db', text: '#fff', border: '#1a56db' },
    success: { bg: '#057a55', text: '#fff', border: '#057a55' },
    warning: { bg: '#F0C040', text: '#002D5B', border: '#e6b800' },
    danger:  { bg: '#c81e1e', text: '#fff', border: '#c81e1e' },
};

const typeIcons = { info: 'ℹ️', success: '✅', warning: '⚠️', danger: '🚨' };
</script>

<template>
    <div v-if="visible.length > 0">
        <div v-for="ann in visible" :key="ann.id"
             class="w-full px-4 py-2.5 flex items-center gap-3 text-sm font-medium"
             :style="{ background: typeColors[ann.type]?.bg, color: typeColors[ann.type]?.text }">
            <span class="flex-shrink-0 text-base">{{ typeIcons[ann.type] }}</span>
            <span class="flex-1 min-w-0">
                <strong v-if="ann.title" class="mr-1">{{ ann.title }} —</strong>
                {{ ann.message }}
                <a v-if="ann.link_url" :href="ann.link_url"
                   class="ml-2 underline font-bold opacity-90 hover:opacity-100"
                   target="_blank" rel="noopener">
                    {{ ann.link_text || 'En savoir plus' }}
                </a>
            </span>
            <button @click="dismiss(ann.id)"
                    class="flex-shrink-0 ml-2 opacity-70 hover:opacity-100 transition"
                    :aria-label="'Fermer l\'annonce'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';

const page = usePage();
const open = ref(false);

const languages = [
    { code: 'fr', label: 'FR', flag: '🇫🇷', name: 'Français' },
    { code: 'en', label: 'EN', flag: '🇬🇧', name: 'English' },
    { code: 'ar', label: 'AR', flag: '🇲🇦', name: 'العربية' },
    { code: 'pt', label: 'PT', flag: '🇦🇴', name: 'Português' },
    { code: 'es', label: 'ES', flag: '🇪🇸', name: 'Español' },
];

const currentLocale = () => page.props.locale || 'fr';

const currentLang = () => languages.find(l => l.code === currentLocale()) || languages[0];

function switchLanguage(code) {
    open.value = false;
    router.post(route('language.switch'), { locale: code }, {
        preserveScroll: true,
        preserveState: false,
    });
}

function toggle() {
    open.value = !open.value;
}

function closeOnClickOutside(e) {
    open.value = false;
}
</script>

<template>
    <div class="relative" v-click-outside="closeOnClickOutside">
        <button
            type="button"
            class="inline-flex items-center gap-1.5 rounded-md border border-gray-200 bg-white px-2.5 py-1.5 text-sm font-medium text-gray-600 transition duration-150 ease-in-out hover:border-brand-300 hover:text-gray-800 focus:outline-none"
            @click="toggle"
            :aria-expanded="open"
            aria-haspopup="listbox"
        >
            <span>{{ currentLang().flag }}</span>
            <span>{{ currentLang().label }}</span>
            <svg class="h-4 w-4 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
        </button>

        <div
            v-if="open"
            class="absolute right-0 z-50 mt-1 w-36 origin-top-right rounded-md border border-gray-100 bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5"
            role="listbox"
        >
            <button
                v-for="lang in languages"
                :key="lang.code"
                type="button"
                class="flex w-full items-center gap-2 px-4 py-2 text-start text-sm leading-5 transition duration-150 ease-in-out hover:bg-gray-100 focus:bg-gray-100 focus:outline-none"
                :class="lang.code === currentLocale() ? 'font-semibold text-brand-700' : 'text-gray-700'"
                @click="switchLanguage(lang.code)"
                role="option"
                :aria-selected="lang.code === currentLocale()"
            >
                <span>{{ lang.flag }}</span>
                <span>{{ lang.name }}</span>
                <span v-if="lang.code === currentLocale()" class="ml-auto text-brand-600">✓</span>
            </button>
        </div>
    </div>
</template>

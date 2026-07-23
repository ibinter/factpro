<script setup>
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    supplier_name: String,
});
</script>

<template>
    <Head title="Offre soumise" />
    <div class="min-h-screen bg-gray-50 flex items-center justify-center px-4">
        <div class="max-w-md w-full bg-white rounded-2xl shadow-lg p-10 text-center space-y-4">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Merci, {{ supplier_name }} !</h1>
            <p class="text-gray-600">Votre offre a bien été reçue. L'acheteur vous contactera si votre offre est retenue.</p>
            <p class="text-sm text-gray-400 pt-4">Vous pouvez fermer cette fenêtre.</p>
        </div>
    </div>
</template>

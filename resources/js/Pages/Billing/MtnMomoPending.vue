<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref } from 'vue';

const props = defineProps({
    order: Object,
});

const fmt = (n) => new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(n ?? 0);

const checking = ref(false);
let pollInterval = null;

function checkStatus() {
    checking.value = true;
    router.reload({
        onSuccess: (page) => {
            const order = page.props.order;
            if (order && order.status === 'paid') {
                clearInterval(pollInterval);
                router.visit(route('billing.index'), {
                    replace: true,
                    data: { success: 'Paiement confirmé ! Votre licence est active.' },
                });
            }
            checking.value = false;
        },
        onError: () => { checking.value = false; },
    });
}

onMounted(() => {
    // Vérifier le statut toutes les 5 secondes
    pollInterval = setInterval(checkStatus, 5000);
});

onUnmounted(() => {
    if (pollInterval) clearInterval(pollInterval);
});
</script>

<template>
    <Head title="Paiement MTN MoMo en attente" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Paiement MTN Mobile Money
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
                    <div class="p-8 text-center">
                        <!-- Icône d'attente -->
                        <div class="mb-6 flex justify-center">
                            <div class="flex h-20 w-20 items-center justify-center rounded-full bg-yellow-100 dark:bg-yellow-900">
                                <svg class="h-10 w-10 text-yellow-600 dark:text-yellow-400 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                        </div>

                        <h3 class="mb-2 text-2xl font-bold text-gray-900 dark:text-white">
                            Paiement en attente de confirmation
                        </h3>

                        <p class="mb-6 text-gray-600 dark:text-gray-400">
                            Une demande de paiement MTN Mobile Money a été envoyée à votre téléphone.<br>
                            Veuillez valider la transaction sur votre téléphone pour finaliser le paiement.
                        </p>

                        <!-- Détails de la commande -->
                        <div class="mb-6 rounded-lg bg-gray-50 p-4 text-left dark:bg-gray-700">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500 dark:text-gray-400">Commande</span>
                                <span class="font-medium text-gray-900 dark:text-white">#{{ order.order_number }}</span>
                            </div>
                            <div class="mt-2 flex justify-between text-sm">
                                <span class="text-gray-500 dark:text-gray-400">Montant</span>
                                <span class="font-bold text-gray-900 dark:text-white">
                                    {{ fmt(order.total_amount) }} {{ order.currency }}
                                </span>
                            </div>
                        </div>

                        <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">
                            Cette page se met à jour automatiquement. Vous pouvez aussi
                            <a href="#" class="text-indigo-600 underline dark:text-indigo-400" @click.prevent="checkStatus">
                                vérifier manuellement
                            </a>.
                        </p>

                        <a
                            :href="route('billing.index')"
                            class="text-sm text-gray-500 underline hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                        >
                            Retour à la facturation
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

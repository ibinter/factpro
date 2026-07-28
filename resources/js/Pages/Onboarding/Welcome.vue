<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const company = computed(() => page.props.company);
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Bienvenue sur FactPro" />

        <div class="min-h-screen bg-gradient-to-br from-brand-50 to-white py-16 px-4">
            <div class="max-w-3xl mx-auto">

                <!-- En-tête de bienvenue -->
                <div class="text-center mb-12">
                    <div class="text-5xl mb-4">🎉</div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-3">
                        Bienvenue sur FactPro, {{ user?.name }} !
                    </h1>
                    <p class="text-lg text-gray-600">
                        Votre espace
                        <span class="font-semibold text-brand-700">{{ company?.name }}</span>
                        est prêt.
                    </p>
                    <div class="mt-4 inline-flex items-center gap-2 bg-amber-50 border border-amber-200 rounded-full px-5 py-2 text-sm font-medium text-amber-800">
                        <span>⏱</span>
                        Essai gratuit de 7 jours actif — sans carte bancaire
                    </div>
                </div>

                <!-- 3 cartes d'action -->
                <div class="grid gap-5 sm:grid-cols-3 mb-10">

                    <!-- Carte 1 : Configurer la société -->
                    <Link
                        :href="route('companies.settings')"
                        class="group flex flex-col bg-white rounded-2xl border border-gray-200 shadow-sm hover:shadow-md hover:border-brand-300 transition-all duration-200 p-6 text-left"
                    >
                        <div class="text-3xl mb-3">🏢</div>
                        <h2 class="text-base font-semibold text-gray-900 mb-1 group-hover:text-brand-700 transition-colors">
                            Configurer ma société
                        </h2>
                        <p class="text-sm text-gray-500 leading-relaxed flex-1">
                            Logo, adresse, coordonnées fiscales (RCCM, NIF) — en moins de 2 minutes.
                        </p>
                        <span class="mt-4 text-sm font-medium text-brand-600 group-hover:underline">
                            Commencer →
                        </span>
                    </Link>

                    <!-- Carte 2 : Créer un client -->
                    <Link
                        :href="route('customers.index')"
                        class="group flex flex-col bg-white rounded-2xl border border-gray-200 shadow-sm hover:shadow-md hover:border-brand-300 transition-all duration-200 p-6 text-left"
                    >
                        <div class="text-3xl mb-3">👥</div>
                        <h2 class="text-base font-semibold text-gray-900 mb-1 group-hover:text-brand-700 transition-colors">
                            Créer mon premier client
                        </h2>
                        <p class="text-sm text-gray-500 leading-relaxed flex-1">
                            Ajoutez un client manuellement ou importez depuis un fichier Excel.
                        </p>
                        <span class="mt-4 text-sm font-medium text-brand-600 group-hover:underline">
                            Ajouter un client →
                        </span>
                    </Link>

                    <!-- Carte 3 : Créer une facture -->
                    <Link
                        :href="route('documents.create')"
                        class="group flex flex-col bg-white rounded-2xl border border-gray-200 shadow-sm hover:shadow-md hover:border-brand-300 transition-all duration-200 p-6 text-left"
                    >
                        <div class="text-3xl mb-3">📄</div>
                        <h2 class="text-base font-semibold text-gray-900 mb-1 group-hover:text-brand-700 transition-colors">
                            Créer ma première facture
                        </h2>
                        <p class="text-sm text-gray-500 leading-relaxed flex-1">
                            Émettez une facture professionnelle avec QR code d'authenticité en quelques clics.
                        </p>
                        <span class="mt-4 text-sm font-medium text-brand-600 group-hover:underline">
                            Créer une facture →
                        </span>
                    </Link>

                </div>

                <!-- Lien vers le dashboard -->
                <div class="text-center">
                    <Link
                        :href="route('dashboard')"
                        class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-8 py-3 text-white font-semibold text-sm hover:bg-brand-700 transition-colors shadow-sm"
                    >
                        Accéder à mon tableau de bord
                    </Link>
                    <p class="mt-3 text-xs text-gray-400">
                        Vous pouvez aussi retrouver ces étapes dans la checklist de démarrage sur votre dashboard.
                    </p>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

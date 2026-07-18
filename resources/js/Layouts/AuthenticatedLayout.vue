<script setup>
import { ref, computed } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import NotificationBell from '@/Components/NotificationBell.vue';
import LanguageSelector from '@/Components/LanguageSelector.vue';
import OfflineBanner from '@/Components/OfflineBanner.vue';
import PushOptIn from '@/Components/PushOptIn.vue';
import { Link, router, usePage } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
const page = usePage();

const license = computed(() => page.props.license);
const flash = computed(() => page.props.flash ?? {});
const isSuperadmin = computed(() => page.props.auth.user?.is_superadmin);
const isRtl = computed(() => page.props.locale === 'ar');

// Multi-sociétés : société courante + liste des sociétés du compte
const currentCompany = computed(() => page.props.company);
const companies = computed(() => page.props.companies ?? []);

const switchCompany = (company) => {
    if (!company.is_current) {
        router.post(route('companies.switch', company.id));
    }
};
</script>

<template>
    <div :dir="isRtl ? 'rtl' : 'ltr'" :class="{ 'text-right': isRtl }">
        <div class="min-h-screen bg-gray-100">
            <!-- Bannière hors-ligne (Phase 12 PWA) -->
            <OfflineBanner />

            <!-- Push opt-in (Phase 16) -->
            <PushOptIn />

            <!-- Bandeau essai gratuit -->
            <div
                v-if="license && license.is_trial"
                class="bg-gradient-to-r from-brand-900 to-brand-600 px-4 py-2 text-center text-sm text-white"
            >
                <span class="font-semibold">VERSION ESSAI</span>
                — il vous reste <span class="font-bold text-gold-400">{{ license.days_remaining }} jour(s)</span>.
                Vos documents portent un filigrane.
                <Link :href="route('billing.plans')" class="ml-2 rounded bg-gold-400 px-2 py-0.5 font-semibold text-brand-900 hover:bg-gold-300">
                    Choisir un forfait
                </Link>
            </div>
            <div
                v-else-if="license && !license.is_usable"
                class="bg-red-600 px-4 py-2 text-center text-sm text-white"
            >
                Votre licence a expiré — vos données sont conservées.
                <Link :href="route('billing.plans')" class="ml-2 underline font-semibold">Renouveler maintenant</Link>
            </div>

            <nav class="border-b border-gray-100 bg-white">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 justify-between">
                        <div class="flex">
                            <div class="flex shrink-0 items-center">
                                <Link :href="route('dashboard')">
                                    <img src="/logo.svg" alt="IBIG FactPro" class="block h-10 w-auto" />
                                </Link>
                            </div>

                            <div class="hidden space-x-6 sm:-my-px sm:ms-10 sm:flex">
                                <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                                    Tableau de bord
                                </NavLink>
                                <NavLink :href="route('documents.index')" :active="route().current('documents.*')">
                                    Documents
                                </NavLink>
                                <NavLink :href="route('customers.index')" :active="route().current('customers.*')">
                                    Clients
                                </NavLink>
                                <NavLink :href="route('products.index')" :active="route().current('products.*')">
                                    Produits
                                </NavLink>
                                <NavLink :href="route('pos.index')" :active="route().current('pos.*')">
                                    Caisse
                                </NavLink>
                                <NavLink :href="route('stock.index')" :active="route().current('stock.*')">
                                    Stocks
                                </NavLink>
                                <NavLink :href="route('reminders.index')" :active="route().current('reminders.*')">
                                    Relances
                                </NavLink>
                                <!-- Menu Plus : modules avancés (Phase 4) -->
                                <div class="inline-flex items-center">
                                    <Dropdown align="left" width="56">
                                        <template #trigger>
                                            <button
                                                type="button"
                                                class="inline-flex h-full items-center border-b-2 px-1 pt-1 text-sm font-medium leading-5 transition duration-150 ease-in-out focus:outline-none"
                                                :class="route().current('projects.*') || route().current('expenses.*') || route().current('accounting.*') || route().current('recurring.*') || route().current('reports.*')
                                                    ? 'border-brand-500 text-gray-900'
                                                    : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                                            >
                                                Plus
                                                <svg class="ms-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </template>
                                        <template #content>
                                            <DropdownLink :href="route('projects.index')">⏱ Projets & Temps</DropdownLink>
                                            <DropdownLink :href="route('expenses.index')">🧾 Notes de frais</DropdownLink>
                                            <DropdownLink :href="route('recurring.index')">🔁 Factures récurrentes</DropdownLink>
                                            <DropdownLink :href="route('purchases.index')">🛒 Achats fournisseurs</DropdownLink>
                                            <DropdownLink :href="route('commissions.index')">🤝 Commissions vendeurs</DropdownLink>
                                            <DropdownLink :href="route('accounting.index')">📚 Comptabilité</DropdownLink>
                                            <DropdownLink :href="route('reports.index')">📊 Rapports & Exports</DropdownLink>
                                        </template>
                                    </Dropdown>
                                </div>
                                <NavLink :href="route('billing.index')" :active="route().current('billing.*')">
                                    Abonnement
                                </NavLink>
                                <NavLink v-if="isSuperadmin" :href="route('admin.payments')" :active="route().current('admin.*')">
                                    <span class="text-gold-600 font-semibold">Admin</span>
                                </NavLink>
                            </div>
                        </div>

                        <div class="hidden sm:ms-6 sm:flex sm:items-center">
                            <!-- Sélecteur de langue (Phase 11 i18n) -->
                            <div class="relative mr-3">
                                <LanguageSelector />
                            </div>
                            <!-- Cloche de notifications (Phase 11) -->
                            <div class="relative mr-3">
                                <NotificationBell />
                            </div>
                            <!-- Sélecteur de société (multi-sociétés §3 MLT) -->
                            <div class="relative mr-3">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <button
                                            type="button"
                                            class="inline-flex max-w-[190px] items-center gap-1.5 rounded-md border border-gray-200 bg-white px-2.5 py-1.5 text-sm font-medium text-gray-600 transition duration-150 ease-in-out hover:border-brand-300 hover:text-gray-800 focus:outline-none"
                                        >
                                            <img
                                                v-if="currentCompany?.logo_path"
                                                :src="`/storage/${currentCompany.logo_path}`"
                                                alt=""
                                                class="h-5 w-5 shrink-0 rounded-full object-cover"
                                            />
                                            <span v-else class="shrink-0">🏢</span>
                                            <span class="truncate">{{ currentCompany?.name ?? 'Société' }}</span>
                                            <svg class="h-4 w-4 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </template>

                                    <template #content>
                                        <div class="px-4 py-1.5 text-xs font-semibold uppercase tracking-wide text-gray-400">
                                            Mes sociétés
                                        </div>
                                        <button
                                            v-for="c in companies"
                                            :key="c.id"
                                            type="button"
                                            class="flex w-full items-center gap-2 px-4 py-2 text-start text-sm leading-5 text-gray-700 transition duration-150 ease-in-out hover:bg-gray-100 focus:bg-gray-100 focus:outline-none"
                                            @click="switchCompany(c)"
                                        >
                                            <span class="w-4 shrink-0 font-bold text-brand-600">{{ c.is_current ? '✓' : '' }}</span>
                                            <span class="truncate" :class="{ 'font-semibold': c.is_current }">{{ c.name }}</span>
                                        </button>
                                        <div class="my-1 border-t border-gray-100"></div>
                                        <DropdownLink :href="route('companies.settings')">⚙ Paramètres de la société</DropdownLink>
                                        <DropdownLink :href="route('companies.index')">+ Nouvelle société</DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>

                            <span
                                v-if="license"
                                class="mr-3 rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                :class="license.is_trial ? 'bg-gold-400/20 text-gold-600' : 'bg-brand-50 text-brand-700'"
                            >
                                {{ license.plan }}{{ license.is_trial ? ' · ESSAI' : '' }}
                            </span>

                            <div class="relative ms-3">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none"
                                            >
                                                {{ $page.props.auth.user.name }}
                                                <svg class="-me-0.5 ms-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <DropdownLink :href="route('profile.edit')">Mon profil</DropdownLink>
                                        <DropdownLink :href="route('companies.index')">Mes sociétés</DropdownLink>
                                        <DropdownLink :href="route('team.index')">Mon équipe</DropdownLink>
                                        <DropdownLink :href="route('billing.index')">Abonnement & Factures</DropdownLink>
                                        <DropdownLink :href="route('referral.index')">🎁 Parrainage</DropdownLink>
                                        <DropdownLink :href="route('labels.index')">Étiquettes & codes-barres</DropdownLink>
                                        <DropdownLink :href="route('api-tokens.index')">API & Intégrations</DropdownLink>
                                        <DropdownLink :href="route('gdpr.index')">Mes données & RGPD</DropdownLink>
                                        <DropdownLink :href="route('logout')" method="post" as="button">
                                            Déconnexion
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <div class="-me-2 flex items-center sm:hidden">
                            <button
                                @click="showingNavigationDropdown = !showingNavigationDropdown"
                                class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none"
                            >
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path
                                        :class="{ hidden: showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }"
                                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{ hidden: !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"
                                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div
                    :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }"
                    class="sm:hidden"
                >
                    <div class="space-y-1 pb-3 pt-2">
                        <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">
                            Tableau de bord
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('documents.index')" :active="route().current('documents.*')">
                            Documents
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('customers.index')" :active="route().current('customers.*')">
                            Clients
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('products.index')" :active="route().current('products.*')">
                            Produits
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('pos.index')" :active="route().current('pos.*')">
                            Caisse
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('stock.index')" :active="route().current('stock.*')">
                            Stocks
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('reminders.index')" :active="route().current('reminders.*')">
                            Relances
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('billing.index')" :active="route().current('billing.*')">
                            Abonnement
                        </ResponsiveNavLink>
                        <ResponsiveNavLink v-if="isSuperadmin" :href="route('admin.payments')" :active="route().current('admin.*')">
                            Admin
                        </ResponsiveNavLink>
                    </div>

                    <!-- Multi-sociétés (mobile) -->
                    <div class="border-t border-gray-200 pb-2 pt-2">
                        <div class="px-4 py-1.5 text-xs font-semibold uppercase tracking-wide text-gray-400">
                            Sociétés
                        </div>
                        <div class="space-y-1">
                            <ResponsiveNavLink
                                v-for="c in companies"
                                :key="c.id"
                                :href="route('companies.switch', c.id)"
                                method="post"
                                as="button"
                                :active="c.is_current"
                            >
                                {{ c.is_current ? '✓ ' : '' }}{{ c.name }}
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('companies.settings')">
                                ⚙ Paramètres de la société
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('companies.index')">
                                + Mes sociétés
                            </ResponsiveNavLink>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pb-1 pt-4">
                        <div class="px-4">
                            <div class="text-base font-medium text-gray-800">
                                {{ $page.props.auth.user.name }}
                            </div>
                            <div class="text-sm font-medium text-gray-500">
                                {{ $page.props.auth.user.email }}
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')">Mon profil</ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('logout')" method="post" as="button">
                                Déconnexion
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Flash messages -->
            <div v-if="flash.success" class="mx-auto mt-4 max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                    ✓ {{ flash.success }}
                </div>
            </div>
            <div v-if="flash.error" class="mx-auto mt-4 max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    ✗ {{ flash.error }}
                </div>
            </div>

            <header class="bg-white shadow" v-if="$slots.header">
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <main>
                <slot />
            </main>
        </div>
    </div>
</template>

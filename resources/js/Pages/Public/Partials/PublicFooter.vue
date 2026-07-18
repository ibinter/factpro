<script setup>
import { Link } from '@inertiajs/vue3';

const year = new Date().getFullYear();
</script>

<template>
    <footer class="bg-brand-950 text-white/70">
        <div class="mx-auto grid max-w-7xl gap-10 px-6 py-14 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <img src="/logo_icon.svg" alt="IBIG FactPro" class="h-11 w-11" />
                <p class="mt-4 max-w-xs text-sm text-white/60">
                    La facturation professionnelle des multinationales, accessible à chaque entrepreneur africain.
                </p>
            </div>

            <div>
                <h4 class="text-sm font-bold uppercase tracking-wide text-white">Produit</h4>
                <ul class="mt-4 space-y-2 text-sm">
                    <li><a href="/#fonctionnalites" class="hover:text-gold-400">Fonctionnalités</a></li>
                    <li><Link :href="route('public.pricing')" class="hover:text-gold-400">Tarifs</Link></li>
                    <li><a href="/#faq" class="hover:text-gold-400">FAQ</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-sm font-bold uppercase tracking-wide text-white">Conformité</h4>
                <ul class="mt-4 space-y-2 text-sm">
                    <li>OHADA</li>
                    <li>Factur-X / FEC</li>
                    <li>Archivage légal 10 ans</li>
                </ul>
            </div>

            <div>
                <h4 class="text-sm font-bold uppercase tracking-wide text-white">Contact</h4>
                <ul class="mt-4 space-y-2 text-sm">
                    <li><a href="mailto:factpro@ibigsoft.com" class="hover:text-gold-400">factpro@ibigsoft.com</a></li>
                    <li>+225 27 22 27 60 14</li>
                    <li>+225 05 55 05 99 01</li>
                </ul>
            </div>
        </div>

        <div class="border-t border-white/10 px-6 py-6 text-center text-xs text-white/50">
            © {{ year }} IBIG SARL — factpro.ibigsoft.com · Tous droits réservés.
        </div>
    </footer>
</template>

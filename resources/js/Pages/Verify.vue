<script setup>
import { Head } from '@inertiajs/vue3';

defineProps({
    result: Object,
});
</script>

<template>
    <Head title="Vérification de document" />

    <div class="flex min-h-screen flex-col items-center bg-gradient-to-b from-brand-950 to-brand-800 px-4 py-12">
        <img src="/logo_dark.svg" alt="IBIG FactPro" class="h-14 w-auto" onerror="this.src='/logo.svg'" />

        <div class="mt-8 w-full max-w-lg rounded-2xl bg-white p-8 shadow-2xl">
            <h1 class="text-center text-lg font-bold text-gray-800">Vérification d'authenticité</h1>
            <p class="mt-1 text-center text-xs text-gray-400">verify · IBIG FactPro</p>

            <!-- Document introuvable -->
            <div v-if="!result.found" class="mt-8 text-center">
                <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-red-100 text-4xl">✗</div>
                <h2 class="mt-4 text-xl font-extrabold text-red-600">DOCUMENT INCONNU</h2>
                <p class="mt-2 text-sm text-gray-500">
                    Aucun document ne correspond à ce code. Ce document n'a pas été émis
                    par IBIG FactPro ou le lien est invalide. <b>Méfiez-vous d'une possible falsification.</b>
                </p>
            </div>

            <!-- Document authentique -->
            <div v-else-if="result.authentic" class="mt-8">
                <div class="text-center">
                    <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-green-100 text-4xl">✓</div>
                    <h2 class="mt-4 text-xl font-extrabold text-green-600">DOCUMENT AUTHENTIQUE</h2>
                    <p v-if="result.is_trial" class="mt-2 rounded bg-amber-50 px-3 py-1.5 text-xs text-amber-700">
                        ⚠ Émis avec un compte en VERSION ESSAI
                    </p>
                </div>
                <dl class="mt-6 space-y-3 rounded-lg bg-gray-50 p-5 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500">Émetteur</dt><dd class="font-semibold text-gray-800">{{ result.issuer }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Document</dt><dd class="font-semibold">{{ result.type_label }} {{ result.number }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Date d'émission</dt><dd>{{ result.issue_date }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Montant total</dt><dd class="font-bold text-brand-900">{{ result.total }} {{ result.currency }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Statut</dt><dd class="uppercase">{{ result.status }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Scellé le</dt><dd>{{ result.sealed_at }}</dd></div>
                </dl>
                <div v-if="result.signed" class="mt-4 rounded-lg bg-brand-50 px-4 py-3 text-center text-sm text-brand-900 ring-1 ring-brand-100">
                    ✍ Accepté et signé par <b>{{ result.signed_by }}</b>
                    <template v-if="result.signed_at"> le {{ result.signed_at }}</template>
                </div>
                <p class="mt-4 text-center text-xs text-gray-400">
                    Ce document n'a subi aucune modification depuis son émission (hash SHA-256 vérifié).
                </p>
            </div>

            <!-- Document modifié / falsifié -->
            <div v-else class="mt-8 text-center">
                <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-red-100 text-4xl">⚠</div>
                <h2 class="mt-4 text-xl font-extrabold text-red-600">DOCUMENT NON VÉRIFIABLE</h2>
                <p class="mt-2 text-sm text-gray-500">
                    Ce document existe mais son contenu <b>ne correspond plus</b> à la version scellée
                    (ou il n'a jamais été finalisé). Il a peut-être été modifié depuis son émission.
                </p>
                <dl class="mt-6 space-y-2 rounded-lg bg-red-50 p-4 text-left text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500">Émetteur déclaré</dt><dd>{{ result.issuer }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Numéro</dt><dd>{{ result.number }}</dd></div>
                </dl>
            </div>
        </div>

        <p class="mt-6 text-center text-xs text-white/50">
            © {{ new Date().getFullYear() }} IBIG SARL — factpro.ibigsoft.com<br>
            Système QR Anti-Falsification : hash SHA-256 + horodatage certifié
        </p>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import JsSdk from './JsSdk.vue'

const props = defineProps({
    hasBusiness: {
        type: Boolean,
        default: false,
    },
})

const spec = ref(null)
const loading = ref(true)
const error = ref(null)
const activeTab = ref('overview')
const tabLabels = { overview: 'Vue d\'ensemble', endpoints: 'Endpoints', sdk: 'SDK PHP', 'sdk-js': 'SDK JavaScript', spec: 'Spec JSON' }

onMounted(async () => {
    if (!props.hasBusiness) {
        loading.value = false
        return
    }
    try {
        const res = await fetch('/api/openapi.json')
        spec.value = await res.json()
    } catch (e) {
        error.value = 'Impossible de charger la spécification OpenAPI.'
    } finally {
        loading.value = false
    }
})

const curlExample = `curl -X GET https://app.ibigfactpro.com/api/v1/documents \\
  -H "Authorization: Bearer VOTRE_TOKEN" \\
  -H "Accept: application/json"`

const phpExample = `<?php
require 'vendor/autoload.php';

$client = new \\FactPro\\FactProClient('VOTRE_TOKEN');

// Lister les factures
$invoices = $client->documents()->list(['type' => 'invoice']);
foreach ($invoices['data'] as $inv) {
    echo $inv['number'] . ' — ' . $inv['total_ttc'] . PHP_EOL;
}

// Créer et finaliser une facture
$invoice = $client->documents()->create([
    'type'       => 'invoice',
    'issue_date' => date('Y-m-d'),
    'currency'   => 'XOF',
    'lines'      => [[
        'description' => 'Prestation de service',
        'quantity'    => 1,
        'unit_price'  => 50000,
        'tax_rate'    => 18,
    ]],
]);
$client->documents()->finalize($invoice['data']['id']);

// Télécharger le PDF
$pdf = $client->documents()->pdf($invoice['data']['id']);
file_put_contents('facture.pdf', $pdf);`
</script>

<template>
    <AppLayout title="Documentation API">
        <div class="max-w-6xl mx-auto px-4 py-8">
            <!-- En-tête -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">API IBIG FactPro</h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">
                    Intégrez la facturation dans vos applications via notre API REST et notre SDK PHP officiel.
                </p>
            </div>

            <!-- Upsell si pas BUSINESS+ -->
            <div v-if="!hasBusiness" class="rounded-xl border-2 border-dashed border-indigo-300 dark:border-indigo-700 bg-indigo-50 dark:bg-indigo-950/30 p-10 text-center">
                <div class="text-5xl mb-4">🔒</div>
                <h2 class="text-xl font-semibold text-indigo-800 dark:text-indigo-300 mb-2">
                    Fonctionnalité réservée au forfait BUSINESS+
                </h2>
                <p class="text-indigo-700 dark:text-indigo-400 mb-6">
                    L'accès à l'API REST et au SDK PHP est disponible à partir du forfait BUSINESS (1 000 requêtes/heure).
                </p>
                <a href="/billing" class="inline-flex items-center px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition">
                    Passer à BUSINESS+
                </a>
            </div>

            <!-- Contenu BUSINESS+ -->
            <template v-else>
                <!-- Onglets -->
                <div class="flex gap-2 mb-6 border-b border-gray-200 dark:border-gray-700 flex-wrap">
                    <button
                        v-for="tab in ['overview', 'endpoints', 'sdk', 'sdk-js', 'spec']"
                        :key="tab"
                        @click="activeTab = tab"
                        class="px-4 py-2 text-sm font-medium capitalize transition border-b-2"
                        :class="activeTab === tab
                            ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400'
                            : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400'"
                    >
                        {{ tabLabels[tab] }}
                    </button>
                </div>

                <!-- Onglet Vue d'ensemble -->
                <div v-if="activeTab === 'overview'" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-5">
                            <div class="text-2xl mb-2">🔑</div>
                            <h3 class="font-semibold text-gray-900 dark:text-white">Authentification</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Token Sanctum via <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">Authorization: Bearer</code></p>
                        </div>
                        <div class="rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-5">
                            <div class="text-2xl mb-2">⚡</div>
                            <h3 class="font-semibold text-gray-900 dark:text-white">Limite</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">1 000 requêtes/heure (BUSINESS) · Illimité (ENTERPRISE)</p>
                        </div>
                        <div class="rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-5">
                            <div class="text-2xl mb-2">📦</div>
                            <h3 class="font-semibold text-gray-900 dark:text-white">Format</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">JSON — <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">Accept: application/json</code></p>
                        </div>
                    </div>

                    <!-- Curl rapide -->
                    <div class="rounded-lg bg-gray-900 dark:bg-gray-950 p-5">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-mono text-gray-400">Exemple cURL</span>
                        </div>
                        <pre class="text-sm text-green-400 font-mono whitespace-pre-wrap">{{ curlExample }}</pre>
                    </div>

                    <!-- PHP rapide -->
                    <div class="rounded-lg bg-gray-900 dark:bg-gray-950 p-5">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-mono text-gray-400">Exemple PHP (SDK)</span>
                        </div>
                        <pre class="text-sm text-blue-300 font-mono whitespace-pre-wrap overflow-x-auto">{{ phpExample }}</pre>
                    </div>
                </div>

                <!-- Onglet Endpoints -->
                <div v-if="activeTab === 'endpoints'" class="space-y-4">
                    <div v-if="loading" class="text-gray-500 dark:text-gray-400 text-center py-10">Chargement de la spec…</div>
                    <template v-else-if="spec">
                        <div v-for="(pathItem, path) in spec.paths" :key="path" class="rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 overflow-hidden">
                            <div v-for="(operation, method) in pathItem" :key="method" class="border-b border-gray-100 dark:border-gray-700 last:border-0 p-4 flex items-start gap-4">
                                <span
                                    class="inline-block font-mono text-xs font-bold px-2 py-1 rounded uppercase min-w-[60px] text-center"
                                    :class="{
                                        'bg-green-100 text-green-700': method === 'get',
                                        'bg-blue-100 text-blue-700': method === 'post',
                                        'bg-yellow-100 text-yellow-700': method === 'put',
                                        'bg-red-100 text-red-700': method === 'delete',
                                    }"
                                >{{ method }}</span>
                                <div>
                                    <code class="text-sm font-mono text-gray-800 dark:text-gray-200">/api/v1{{ path }}</code>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ operation.summary }}</p>
                                </div>
                            </div>
                        </div>
                    </template>
                    <p v-else class="text-red-500">{{ error }}</p>
                </div>

                <!-- Onglet SDK PHP -->
                <div v-if="activeTab === 'sdk'" class="space-y-6">
                    <div class="rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-6">
                        <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Installation</h3>
                        <pre class="bg-gray-900 rounded p-4 text-green-400 font-mono text-sm">composer require ibigsoft/factpro-sdk</pre>

                        <h3 class="font-semibold text-gray-900 dark:text-white mt-6 mb-4">Utilisation complète</h3>
                        <pre class="bg-gray-900 rounded p-4 text-blue-300 font-mono text-sm overflow-x-auto whitespace-pre-wrap">{{ phpExample }}</pre>
                    </div>

                    <div class="flex gap-4">
                        <a
                            href="https://packagist.org/packages/ibigsoft/factpro-sdk"
                            target="_blank"
                            class="inline-flex items-center px-5 py-2.5 bg-orange-500 hover:bg-orange-600 text-white font-semibold rounded-lg transition text-sm"
                        >
                            Voir sur Packagist
                        </a>
                        <a
                            href="/api/openapi.json"
                            download="factpro-openapi.json"
                            class="inline-flex items-center px-5 py-2.5 bg-gray-700 hover:bg-gray-800 text-white font-semibold rounded-lg transition text-sm"
                        >
                            Télécharger la spec OpenAPI
                        </a>
                    </div>
                </div>

                <!-- Onglet SDK JavaScript -->
                <div v-if="activeTab === 'sdk-js'">
                    <JsSdk />
                </div>

                <!-- Onglet Spec JSON -->
                <div v-if="activeTab === 'spec'">
                    <div v-if="loading" class="text-gray-500 dark:text-gray-400 text-center py-10">Chargement…</div>
                    <pre v-else-if="spec" class="bg-gray-900 rounded-lg p-5 text-xs text-green-300 font-mono overflow-x-auto max-h-[70vh] overflow-y-auto">{{ JSON.stringify(spec, null, 2) }}</pre>
                    <p v-else class="text-red-500">{{ error }}</p>
                </div>
            </template>
        </div>
    </AppLayout>
</template>

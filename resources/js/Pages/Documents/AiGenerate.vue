<script setup>
import { ref, computed } from 'vue'
import { useForm, router } from '@inertiajs/vue3'

const props = defineProps({
    company: Object,
    docType: String,
    catLabel: String,
    catIcon: String,
})

const activeTab = ref(0)
const generatedHtml = ref(null)
const generating = ref(false)
const generateError = ref(null)

const tabs = [
    { label: 'Société', icon: '🏢' },
    { label: 'Client', icon: '👤' },
    { label: 'Document', icon: '📄' },
    { label: 'Lignes', icon: '📋' },
]

const today = new Date().toISOString().slice(0, 10)

const form = useForm({
    // Onglet Société
    company_name: props.company?.name ?? '',
    company_address: props.company?.address ?? '',
    company_phone: props.company?.phone ?? '',
    company_email: props.company?.email ?? '',
    company_website: '',
    company_rccm: props.company?.rccm ?? '',
    company_cc: props.company?.cc ?? '',
    company_bank_name: props.company?.bank_name ?? '',
    company_bank_account: props.company?.bank_account ?? '',

    // Onglet Client
    client_name: '',
    client_address: '',
    client_phone: '',
    client_email: '',
    client_rccm: '',

    // Onglet Document
    doc_number: '',
    doc_date: today,
    doc_due_date: '',
    doc_currency: 'XOF',
    doc_vat_rate: '18',
    doc_subject: '',
    doc_notes: '',

    // Onglet Lignes
    lines: [
        { description: '', qty: 1, unit: 'Forfait', unit_price: 0, discount: 0 },
    ],

    doc_type: props.docType,
})

function addLine() {
    form.lines.push({ description: '', qty: 1, unit: 'Forfait', unit_price: 0, discount: 0 })
}

function removeLine(index) {
    if (form.lines.length > 1) {
        form.lines.splice(index, 1)
    }
}

const totalHT = computed(() => {
    return form.lines.reduce((sum, line) => {
        const base = parseFloat(line.qty || 0) * parseFloat(line.unit_price || 0)
        const disc = parseFloat(line.discount || 0)
        return sum + base * (1 - disc / 100)
    }, 0)
})

const totalVAT = computed(() => {
    return totalHT.value * (parseFloat(form.doc_vat_rate || 0) / 100)
})

const totalTTC = computed(() => {
    return totalHT.value + totalVAT.value
})

function fmt(n) {
    return n.toLocaleString('fr-FR', { minimumFractionDigits: 0, maximumFractionDigits: 0 })
}

async function generate() {
    generating.value = true
    generateError.value = null

    try {
        const payload = { ...form.data() }

        const response = await fetch('/documents/ai-generate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                'Accept': 'application/json',
            },
            body: JSON.stringify(payload),
        })

        const data = await response.json()

        if (!response.ok) {
            generateError.value = data.message ?? 'Une erreur est survenue.'
        } else if (data.html) {
            generatedHtml.value = data.html
        } else {
            generateError.value = 'Réponse inattendue du serveur.'
        }
    } catch (e) {
        generateError.value = e.message ?? 'Erreur réseau.'
    } finally {
        generating.value = false
    }
}

function printDoc() {
    const iframe = document.getElementById('ai-result-iframe')
    if (iframe) {
        iframe.contentWindow.print()
    }
}

function backToCatalog() {
    router.visit('/documents/ai-catalog')
}

function resetResult() {
    generatedHtml.value = null
}

const iframeSrc = computed(() => {
    if (!generatedHtml.value) return ''
    const blob = new Blob([generatedHtml.value], { type: 'text/html' })
    return URL.createObjectURL(blob)
})
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Header gradient -->
        <div class="bg-gradient-to-r from-blue-900 via-blue-800 to-indigo-900 text-white px-6 py-6 shadow-lg">
            <div class="max-w-5xl mx-auto flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2 text-blue-300 text-sm mb-1">
                        <span>{{ catIcon }}</span>
                        <span>{{ catLabel }}</span>
                        <span class="text-blue-400">›</span>
                        <span>Génération IA</span>
                    </div>
                    <h1 class="text-2xl font-bold flex items-center gap-2">
                        <span>✨</span>
                        Générer un document IA
                        <span class="ml-2 text-sm font-normal bg-blue-700 rounded-full px-3 py-0.5 capitalize">{{ docType }}</span>
                    </h1>
                </div>
                <button
                    @click="backToCatalog"
                    class="flex items-center gap-2 bg-white/10 hover:bg-white/20 border border-white/20 text-white rounded-lg px-4 py-2 text-sm transition"
                >
                    ← Catalogue
                </button>
            </div>
        </div>

        <!-- Result view -->
        <div v-if="generatedHtml" class="flex flex-col" style="height: calc(100vh - 100px)">
            <div class="bg-white border-b px-6 py-3 flex items-center gap-3 shadow-sm">
                <button
                    @click="resetResult"
                    class="flex items-center gap-2 text-blue-700 hover:text-blue-900 border border-blue-200 rounded-lg px-4 py-2 text-sm transition"
                >
                    ← Modifier
                </button>
                <button
                    @click="printDoc"
                    class="flex items-center gap-2 bg-blue-700 hover:bg-blue-800 text-white rounded-lg px-4 py-2 text-sm transition"
                >
                    🖨️ Imprimer / PDF
                </button>
                <button
                    @click="backToCatalog"
                    class="flex items-center gap-2 text-gray-600 hover:text-gray-800 border border-gray-200 rounded-lg px-4 py-2 text-sm transition"
                >
                    ← Catalogue
                </button>
            </div>
            <iframe
                id="ai-result-iframe"
                :src="iframeSrc"
                class="flex-1 w-full border-0"
                style="background:#f8f9fa"
            ></iframe>
        </div>

        <!-- Form view -->
        <div v-else class="max-w-5xl mx-auto px-4 py-8">

            <!-- Tabs -->
            <div class="flex gap-1 bg-white rounded-xl shadow-sm border border-gray-200 p-1 mb-6">
                <button
                    v-for="(tab, i) in tabs"
                    :key="i"
                    @click="activeTab = i"
                    :class="[
                        'flex-1 flex items-center justify-center gap-2 py-2.5 px-3 rounded-lg text-sm font-medium transition',
                        activeTab === i
                            ? 'bg-blue-50 text-blue-800 border border-blue-200 shadow-sm'
                            : 'text-gray-500 hover:bg-gray-50'
                    ]"
                >
                    <span>{{ tab.icon }}</span>
                    <span>{{ tab.label }}</span>
                </button>
            </div>

            <!-- Error banner -->
            <div v-if="generateError" class="mb-4 bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm">
                {{ generateError }}
            </div>

            <!-- Onglet 1 : Société -->
            <div v-show="activeTab === 0" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-5 flex items-center gap-2">
                    <span>🏢</span> Informations de la société
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Raison sociale</label>
                        <input v-model="form.company_name" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" placeholder="Nom de la société" />
                        <p v-if="form.errors.company_name" class="text-red-500 text-xs mt-1">{{ form.errors.company_name }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Adresse</label>
                        <textarea v-model="form.company_address" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" placeholder="Adresse complète"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
                        <input v-model="form.company_phone" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" placeholder="+225 00 00 00 00" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input v-model="form.company_email" type="email" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" placeholder="contact@societe.ci" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Site web</label>
                        <input v-model="form.company_website" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" placeholder="www.societe.ci" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">RCCM</label>
                        <input v-model="form.company_rccm" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" placeholder="CI-ABJ-2023-B-XXXXX" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Compte Contribuable (CC)</label>
                        <input v-model="form.company_cc" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" placeholder="CC XXXXXXXX" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Banque</label>
                        <input v-model="form.company_bank_name" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" placeholder="Nom de la banque" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">IBAN / Compte bancaire</label>
                        <input v-model="form.company_bank_account" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" placeholder="CI XX XXXX XXXX XXXX" />
                    </div>
                </div>
                <div class="flex justify-end mt-6">
                    <button @click="activeTab = 1" class="bg-blue-700 hover:bg-blue-800 text-white rounded-lg px-5 py-2 text-sm font-medium transition">
                        Suivant : Client →
                    </button>
                </div>
            </div>

            <!-- Onglet 2 : Client -->
            <div v-show="activeTab === 1" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-5 flex items-center gap-2">
                    <span>👤</span> Informations du client
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nom / Raison sociale <span class="text-red-500">*</span></label>
                        <input v-model="form.client_name" type="text" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" placeholder="Nom du client ou de la société" />
                        <p v-if="form.errors.client_name" class="text-red-500 text-xs mt-1">{{ form.errors.client_name }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Adresse</label>
                        <textarea v-model="form.client_address" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" placeholder="Adresse du client"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
                        <input v-model="form.client_phone" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" placeholder="+225 00 00 00 00" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input v-model="form.client_email" type="email" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" placeholder="client@email.com" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">RCCM client</label>
                        <input v-model="form.client_rccm" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" placeholder="CI-ABJ-XXXX-X-XXXXX" />
                    </div>
                </div>
                <div class="flex justify-between mt-6">
                    <button @click="activeTab = 0" class="border border-gray-300 text-gray-600 hover:bg-gray-50 rounded-lg px-5 py-2 text-sm font-medium transition">
                        ← Société
                    </button>
                    <button @click="activeTab = 2" class="bg-blue-700 hover:bg-blue-800 text-white rounded-lg px-5 py-2 text-sm font-medium transition">
                        Suivant : Document →
                    </button>
                </div>
            </div>

            <!-- Onglet 3 : Document -->
            <div v-show="activeTab === 2" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-5 flex items-center gap-2">
                    <span>📄</span> Paramètres du document
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Numéro de document</label>
                        <input v-model="form.doc_number" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" placeholder="DEV-2025-001" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date du document</label>
                        <input v-model="form.doc_date" type="date" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date d'échéance</label>
                        <input v-model="form.doc_due_date" type="date" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Devise</label>
                        <select v-model="form.doc_currency" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white">
                            <option value="XOF">XOF — Franc CFA UEMOA</option>
                            <option value="XAF">XAF — Franc CFA CEMAC</option>
                            <option value="EUR">EUR — Euro</option>
                            <option value="USD">USD — Dollar américain</option>
                            <option value="GHS">GHS — Cedi ghanéen</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Taux TVA</label>
                        <select v-model="form.doc_vat_rate" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white">
                            <option value="0">0% — Exonéré</option>
                            <option value="10">10%</option>
                            <option value="18">18%</option>
                            <option value="20">20%</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Objet / Titre du document</label>
                        <input v-model="form.doc_subject" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" placeholder="Ex: Prestation de développement web" />
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Conditions / Notes</label>
                        <textarea v-model="form.doc_notes" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" placeholder="Conditions de paiement, délais, notes particulières..."></textarea>
                    </div>
                </div>
                <div class="flex justify-between mt-6">
                    <button @click="activeTab = 1" class="border border-gray-300 text-gray-600 hover:bg-gray-50 rounded-lg px-5 py-2 text-sm font-medium transition">
                        ← Client
                    </button>
                    <button @click="activeTab = 3" class="bg-blue-700 hover:bg-blue-800 text-white rounded-lg px-5 py-2 text-sm font-medium transition">
                        Suivant : Lignes →
                    </button>
                </div>
            </div>

            <!-- Onglet 4 : Lignes -->
            <div v-show="activeTab === 3" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-5 flex items-center gap-2">
                    <span>📋</span> Lignes du document
                </h2>

                <!-- Tableau des lignes -->
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 border border-gray-200 rounded-lg">
                                <th class="text-left px-3 py-2 font-medium text-gray-600 w-[35%]">Description</th>
                                <th class="text-center px-2 py-2 font-medium text-gray-600 w-[8%]">Qté</th>
                                <th class="text-center px-2 py-2 font-medium text-gray-600 w-[10%]">Unité</th>
                                <th class="text-right px-2 py-2 font-medium text-gray-600 w-[15%]">P.U. HT</th>
                                <th class="text-center px-2 py-2 font-medium text-gray-600 w-[10%]">Remise %</th>
                                <th class="text-right px-2 py-2 font-medium text-gray-600 w-[12%]">Total HT</th>
                                <th class="text-center px-2 py-2 font-medium text-gray-600 w-[6%]"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(line, i) in form.lines"
                                :key="i"
                                class="border-b border-gray-100 hover:bg-gray-50/50"
                            >
                                <td class="px-1 py-1.5">
                                    <input
                                        v-model="line.description"
                                        type="text"
                                        placeholder="Description de la prestation"
                                        class="w-full border border-gray-200 rounded px-2 py-1.5 text-sm focus:ring-1 focus:ring-blue-400 focus:border-blue-400 outline-none"
                                    />
                                </td>
                                <td class="px-1 py-1.5">
                                    <input
                                        v-model="line.qty"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        class="w-full border border-gray-200 rounded px-2 py-1.5 text-sm text-center focus:ring-1 focus:ring-blue-400 focus:border-blue-400 outline-none"
                                    />
                                </td>
                                <td class="px-1 py-1.5">
                                    <select
                                        v-model="line.unit"
                                        class="w-full border border-gray-200 rounded px-2 py-1.5 text-sm focus:ring-1 focus:ring-blue-400 focus:border-blue-400 outline-none bg-white"
                                    >
                                        <option>Forfait</option>
                                        <option>Heure</option>
                                        <option>Jour</option>
                                        <option>Mois</option>
                                        <option>Unité</option>
                                        <option>Kg</option>
                                        <option>m²</option>
                                        <option>m³</option>
                                    </select>
                                </td>
                                <td class="px-1 py-1.5">
                                    <input
                                        v-model="line.unit_price"
                                        type="number"
                                        min="0"
                                        step="1"
                                        class="w-full border border-gray-200 rounded px-2 py-1.5 text-sm text-right focus:ring-1 focus:ring-blue-400 focus:border-blue-400 outline-none"
                                    />
                                </td>
                                <td class="px-1 py-1.5">
                                    <input
                                        v-model="line.discount"
                                        type="number"
                                        min="0"
                                        max="100"
                                        step="0.01"
                                        class="w-full border border-gray-200 rounded px-2 py-1.5 text-sm text-center focus:ring-1 focus:ring-blue-400 focus:border-blue-400 outline-none"
                                    />
                                </td>
                                <td class="px-2 py-1.5 text-right text-gray-700 font-medium whitespace-nowrap">
                                    {{ fmt(line.qty * line.unit_price * (1 - line.discount / 100)) }}
                                </td>
                                <td class="px-1 py-1.5 text-center">
                                    <button
                                        @click="removeLine(i)"
                                        :disabled="form.lines.length === 1"
                                        class="text-red-400 hover:text-red-600 disabled:opacity-30 disabled:cursor-not-allowed transition text-base"
                                        title="Supprimer la ligne"
                                    >🗑️</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Bouton ajouter ligne -->
                <button
                    @click="addLine"
                    class="mt-3 flex items-center gap-2 text-blue-700 hover:text-blue-900 border border-dashed border-blue-300 hover:border-blue-500 rounded-lg px-4 py-2 text-sm transition w-full justify-center"
                >
                    + Ajouter une ligne
                </button>

                <!-- Totaux -->
                <div class="mt-6 flex justify-end">
                    <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 w-full max-w-xs space-y-2 text-sm">
                        <div class="flex justify-between text-gray-600">
                            <span>Total HT</span>
                            <span class="font-medium">{{ fmt(totalHT) }} {{ form.doc_currency }}</span>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span>TVA ({{ form.doc_vat_rate }}%)</span>
                            <span class="font-medium">{{ fmt(totalVAT) }} {{ form.doc_currency }}</span>
                        </div>
                        <div class="border-t border-gray-300 pt-2 flex justify-between font-bold text-blue-900 text-base">
                            <span>Total TTC</span>
                            <span>{{ fmt(totalTTC) }} {{ form.doc_currency }}</span>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-between items-center mt-6">
                    <button @click="activeTab = 2" class="border border-gray-300 text-gray-600 hover:bg-gray-50 rounded-lg px-5 py-2 text-sm font-medium transition">
                        ← Document
                    </button>
                    <button
                        @click="generate"
                        :disabled="generating || !form.client_name"
                        class="flex items-center gap-2 bg-gradient-to-r from-blue-700 to-indigo-700 hover:from-blue-800 hover:to-indigo-800 text-white rounded-lg px-6 py-2.5 text-sm font-semibold shadow transition disabled:opacity-60 disabled:cursor-not-allowed"
                    >
                        <span v-if="generating" class="animate-spin">⏳</span>
                        <span v-else>✨</span>
                        <span>{{ generating ? 'Génération en cours...' : 'Générer le document' }}</span>
                    </button>
                </div>
            </div>

            <!-- Step indicator -->
            <div class="flex items-center justify-center gap-2 mt-6">
                <button
                    v-for="(tab, i) in tabs"
                    :key="i"
                    @click="activeTab = i"
                    :class="[
                        'w-2.5 h-2.5 rounded-full transition',
                        activeTab === i ? 'bg-blue-700 w-5' : 'bg-gray-300 hover:bg-gray-400'
                    ]"
                ></button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

/* -------------------------------------------------------------------------
 * Props — mêmes données que PortalController::show() + détail document
 * ---------------------------------------------------------------------- */
const props = defineProps({
    /** Token du portail client (utilisé pour construire l'URL PDF). */
    token:    { type: String, required: true },
    company:  { type: Object, required: true },
    customer: { type: Object, default: null },
    document: { type: Object, required: true },
});

/* -------------------------------------------------------------------------
 * Formatters
 * ---------------------------------------------------------------------- */
const money = (amount) =>
    new Intl.NumberFormat('fr-FR', {
        maximumFractionDigits: props.document.currency === 'XOF' ? 0 : 2,
    }).format(amount ?? 0);

const fmtDate = (value) =>
    value
        ? new Date(value).toLocaleDateString('fr-FR', {
              day: '2-digit',
              month: 'short',
              year: 'numeric',
          })
        : null;

/* -------------------------------------------------------------------------
 * Badge de statut
 * ---------------------------------------------------------------------- */
const STATUS_CONFIG = {
    draft:    { label: 'Brouillon',             cls: 'bg-gray-100 text-gray-600 ring-gray-200' },
    sent:     { label: 'Envoyé',                cls: 'bg-blue-100 text-blue-700 ring-blue-200' },
    viewed:   { label: 'Consulté',              cls: 'bg-indigo-100 text-indigo-700 ring-indigo-200' },
    accepted: { label: 'Accepté',               cls: 'bg-green-100 text-green-700 ring-green-200' },
    rejected: { label: 'Refusé',               cls: 'bg-red-100 text-red-700 ring-red-200' },
    partial:  { label: 'Partiellement payé',    cls: 'bg-amber-100 text-amber-700 ring-amber-200' },
    paid:     { label: 'Payé',                  cls: 'bg-green-100 text-green-700 ring-green-200' },
    overdue:  { label: 'En retard',             cls: 'bg-red-100 text-red-700 ring-red-200' },
    cancelled:{ label: 'Annulé',               cls: 'bg-gray-100 text-gray-500 ring-gray-200' },
    converted:{ label: 'Converti',              cls: 'bg-gray-100 text-gray-600 ring-gray-200' },
};

const statusCfg = computed(() => STATUS_CONFIG[props.document.status] ?? { label: props.document.status, cls: 'bg-gray-100 text-gray-600 ring-gray-200' });

/* -------------------------------------------------------------------------
 * Timeline 4 étapes
 * ---------------------------------------------------------------------- */
const timelineSteps = computed(() => {
    const d = props.document;
    return [
        {
            key:   'created',
            label: 'Créé',
            date:  fmtDate(d.created_at),
            done:  !!d.created_at,
            icon:  'M12 3C7.03 3 3 7.03 3 12s4.03 9 9 9 9-4.03 9-9-4.03-9-9-9zm0 2a7 7 0 110 14A7 7 0 0112 5zm0 3a1 1 0 00-1 1v3H8a1 1 0 100 2h4a1 1 0 001-1V9a1 1 0 00-1-1z',
        },
        {
            key:   'sent',
            label: 'Envoyé',
            date:  fmtDate(d.sent_at),
            done:  !!d.sent_at || ['sent','viewed','accepted','rejected','partial','paid','overdue'].includes(d.status),
            icon:  'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
        },
        {
            key:   'viewed',
            label: 'Consulté',
            date:  fmtDate(d.viewed_at),
            done:  !!d.viewed_at || ['viewed','accepted','rejected','partial','paid','overdue'].includes(d.status),
            icon:  'M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z',
        },
        {
            key:   'paid',
            label: 'Payé',
            date:  fmtDate(d.paid_at),
            done:  !!d.paid_at || d.status === 'paid',
            icon:  'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        },
    ];
});

/* -------------------------------------------------------------------------
 * PDF
 * ---------------------------------------------------------------------- */
const pdfUrl = computed(() =>
    `/portal/${props.token}/documents/${props.document.uuid}/pdf`,
);

/* -------------------------------------------------------------------------
 * Paiement conditionnel
 * ---------------------------------------------------------------------- */
const paymentEnabled = computed(() => !!props.company.payment_methods?.enabled);
const paymentMethods = computed(() => props.company.payment_methods ?? {});
</script>

<template>
    <Head :title="`${document.type_label} ${document.number} — ${company.name}`" />

    <div class="min-h-screen bg-gray-100">

        <!-- ================================================================
             En-tête société
             ============================================================= -->
        <header class="bg-white shadow-sm">
            <div class="mx-auto flex max-w-4xl items-center justify-between gap-4 px-4 py-5 sm:px-6">
                <div class="flex items-center gap-3">
                    <img
                        v-if="company.logo_path"
                        :src="'/storage/' + company.logo_path"
                        :alt="company.name"
                        class="h-10 w-auto max-w-[120px] rounded object-contain"
                    />
                    <div>
                        <p class="text-base font-bold text-gray-900">{{ company.name }}</p>
                        <p v-if="company.email" class="text-xs text-gray-400">{{ company.email }}</p>
                    </div>
                </div>

                <!-- Bouton PDF — proéminent, accessible depuis le header -->
                <a
                    :href="pdfUrl"
                    target="_blank"
                    rel="noopener"
                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                    Télécharger PDF
                </a>
            </div>
        </header>

        <main class="mx-auto max-w-4xl space-y-5 px-4 py-8 sm:px-6">

            <!-- ============================================================
                 Card : numéro + badge statut
                 ========================================================= -->
            <div class="rounded-2xl bg-white p-6 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ document.type_label }}</p>
                        <h1 class="mt-1 text-2xl font-extrabold text-gray-900">{{ document.number }}</h1>
                        <p v-if="customer" class="mt-1 text-sm text-gray-500">
                            Adressé à <span class="font-semibold text-gray-700">{{ customer.name }}</span>
                        </p>
                    </div>
                    <span
                        class="mt-1 inline-flex items-center rounded-full px-3 py-1 text-sm font-semibold ring-1"
                        :class="statusCfg.cls"
                    >
                        {{ statusCfg.label }}
                    </span>
                </div>

                <!-- Dates en ligne -->
                <dl class="mt-5 grid grid-cols-2 gap-4 border-t pt-5 text-sm sm:grid-cols-4">
                    <div>
                        <dt class="text-xs text-gray-400">Date d'émission</dt>
                        <dd class="mt-0.5 font-semibold text-gray-800">{{ fmtDate(document.issue_date) ?? '—' }}</dd>
                    </div>
                    <div v-if="document.due_date">
                        <dt class="text-xs text-gray-400">Échéance</dt>
                        <dd class="mt-0.5 font-semibold text-gray-800">{{ fmtDate(document.due_date) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400">Devise</dt>
                        <dd class="mt-0.5 font-semibold text-gray-800">{{ document.currency }}</dd>
                    </div>
                    <div v-if="Number(document.balance_due) > 0">
                        <dt class="text-xs text-gray-400">Reste à payer</dt>
                        <dd class="mt-0.5 font-semibold text-red-600">{{ money(document.balance_due) }} {{ document.currency }}</dd>
                    </div>
                </dl>
            </div>

            <!-- ============================================================
                 Timeline 4 étapes
                 ========================================================= -->
            <div class="rounded-2xl bg-white p-6 shadow-sm">
                <h2 class="mb-5 text-xs font-semibold uppercase tracking-wide text-gray-400">Suivi du document</h2>
                <ol class="relative flex justify-between">
                    <!-- Ligne de fond -->
                    <li
                        v-for="(step, i) in timelineSteps"
                        :key="step.key"
                        class="flex flex-1 flex-col items-center"
                    >
                        <!-- Connecteur (sauf premier) -->
                        <div class="relative flex w-full items-center">
                            <div
                                v-if="i > 0"
                                class="absolute left-0 right-1/2 top-4 h-0.5"
                                :class="step.done ? 'bg-indigo-500' : 'bg-gray-200'"
                            ></div>
                            <div
                                v-if="i < timelineSteps.length - 1"
                                class="absolute left-1/2 right-0 top-4 h-0.5"
                                :class="timelineSteps[i + 1]?.done ? 'bg-indigo-500' : 'bg-gray-200'"
                            ></div>

                            <!-- Cercle -->
                            <div class="relative z-10 mx-auto flex h-8 w-8 items-center justify-center rounded-full border-2 transition-colors"
                                :class="step.done
                                    ? 'border-indigo-600 bg-indigo-600 text-white'
                                    : 'border-gray-300 bg-white text-gray-300'"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" :d="step.icon" />
                                </svg>
                            </div>
                        </div>

                        <!-- Étiquette -->
                        <p class="mt-2 text-center text-xs font-semibold"
                           :class="step.done ? 'text-indigo-700' : 'text-gray-400'">
                            {{ step.label }}
                        </p>
                        <p v-if="step.date" class="mt-0.5 text-center text-[10px] text-gray-400">
                            {{ step.date }}
                        </p>
                    </li>
                </ol>
            </div>

            <!-- ============================================================
                 Tableau des lignes
                 ========================================================= -->
            <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
                <div class="border-b px-6 py-4">
                    <h2 class="font-bold text-gray-800">Détail des prestations</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[560px] text-sm">
                        <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-6 py-3">Désignation</th>
                                <th class="px-6 py-3 text-right">Qté</th>
                                <th class="px-6 py-3 text-right">P.U. HT</th>
                                <th class="px-6 py-3 text-right">Remise</th>
                                <th class="px-6 py-3 text-right">Total HT</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr
                                v-for="line in document.lines"
                                :key="line.id"
                                class="hover:bg-gray-50"
                            >
                                <td class="px-6 py-3">
                                    <span class="font-medium text-gray-800">{{ line.description }}</span>
                                    <span v-if="line.unit" class="ml-1 text-xs text-gray-400">({{ line.unit }})</span>
                                </td>
                                <td class="px-6 py-3 text-right text-gray-600">{{ line.quantity }}</td>
                                <td class="px-6 py-3 text-right text-gray-600">{{ money(line.unit_price) }}</td>
                                <td class="px-6 py-3 text-right text-gray-400">
                                    <span v-if="Number(line.discount_percent) > 0">{{ line.discount_percent }}%</span>
                                    <span v-else>—</span>
                                </td>
                                <td class="px-6 py-3 text-right font-semibold text-gray-800">
                                    {{ money(line.line_total) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- --------------------------------------------------------
                     Card totaux
                     ----------------------------------------------------- -->
                <div class="border-t bg-gray-50 px-6 py-5">
                    <div class="ml-auto max-w-xs space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Sous-total HT</span>
                            <span class="font-semibold text-gray-800">{{ money(document.subtotal) }} {{ document.currency }}</span>
                        </div>
                        <div v-if="Number(document.discount_amount) > 0" class="flex justify-between">
                            <span class="text-gray-500">Remise</span>
                            <span class="font-semibold text-red-600">−{{ money(document.discount_amount) }} {{ document.currency }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">TVA</span>
                            <span class="font-semibold text-gray-800">{{ money(document.tax_amount) }} {{ document.currency }}</span>
                        </div>

                        <!-- TOTAL TTC mis en valeur -->
                        <div class="mt-3 flex items-center justify-between rounded-xl bg-indigo-600 px-4 py-3 shadow-sm">
                            <span class="text-sm font-bold text-indigo-100">TOTAL TTC</span>
                            <span class="text-xl font-extrabold text-white">{{ money(document.total) }} {{ document.currency }}</span>
                        </div>

                        <!-- Restant dû si paiement partiel -->
                        <div v-if="Number(document.balance_due) > 0 && Number(document.amount_paid) > 0"
                             class="flex justify-between rounded-lg bg-red-50 px-3 py-2 text-sm ring-1 ring-red-100">
                            <span class="text-red-600">Déjà payé</span>
                            <span class="font-semibold text-gray-800">{{ money(document.amount_paid) }} {{ document.currency }}</span>
                        </div>
                        <div v-if="Number(document.balance_due) > 0"
                             class="flex justify-between rounded-lg bg-red-50 px-3 py-2 text-sm ring-1 ring-red-100">
                            <span class="font-semibold text-red-600">Reste à payer</span>
                            <span class="font-bold text-red-700">{{ money(document.balance_due) }} {{ document.currency }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================================
                 Section paiement — conditionnelle
                 ========================================================= -->
            <div
                v-if="paymentEnabled && Number(document.balance_due) > 0"
                class="rounded-2xl bg-white p-6 shadow-sm"
            >
                <h2 class="mb-4 flex items-center gap-2 font-bold text-gray-800">
                    <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                    Moyens de paiement acceptés
                </h2>

                <div class="grid gap-3 sm:grid-cols-2">
                    <!-- Mobile Money -->
                    <div v-if="paymentMethods.mobile_money?.enabled" class="flex items-start gap-3 rounded-xl bg-green-50 p-4 ring-1 ring-green-100">
                        <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full bg-green-600 text-white text-base font-bold">M</div>
                        <div>
                            <p class="text-sm font-semibold text-green-800">Mobile Money</p>
                            <p v-if="paymentMethods.mobile_money.number" class="mt-0.5 text-xs text-green-700">
                                {{ paymentMethods.mobile_money.number }}
                            </p>
                            <p v-if="paymentMethods.mobile_money.name" class="text-xs text-green-600">
                                {{ paymentMethods.mobile_money.name }}
                            </p>
                        </div>
                    </div>

                    <!-- Wave -->
                    <div v-if="paymentMethods.wave?.enabled" class="flex items-start gap-3 rounded-xl bg-blue-50 p-4 ring-1 ring-blue-100">
                        <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full bg-blue-500 text-white text-base font-bold">W</div>
                        <div>
                            <p class="text-sm font-semibold text-blue-800">Wave</p>
                            <p v-if="paymentMethods.wave.number" class="mt-0.5 text-xs text-blue-700">{{ paymentMethods.wave.number }}</p>
                            <p v-if="paymentMethods.wave.name" class="text-xs text-blue-600">{{ paymentMethods.wave.name }}</p>
                        </div>
                    </div>

                    <!-- Virement bancaire -->
                    <div v-if="paymentMethods.bank_transfer?.enabled" class="flex items-start gap-3 rounded-xl bg-gray-50 p-4 ring-1 ring-gray-200 sm:col-span-2">
                        <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full bg-gray-600 text-white text-base font-bold">B</div>
                        <div class="min-w-0 flex-1 text-xs text-gray-700">
                            <p class="text-sm font-semibold text-gray-800">Virement bancaire</p>
                            <dl class="mt-1 grid grid-cols-2 gap-x-4 gap-y-1">
                                <template v-if="paymentMethods.bank_transfer.iban">
                                    <dt class="text-gray-500">IBAN</dt>
                                    <dd class="font-mono font-semibold">{{ paymentMethods.bank_transfer.iban }}</dd>
                                </template>
                                <template v-if="paymentMethods.bank_transfer.bic">
                                    <dt class="text-gray-500">BIC</dt>
                                    <dd class="font-mono font-semibold">{{ paymentMethods.bank_transfer.bic }}</dd>
                                </template>
                                <template v-if="paymentMethods.bank_transfer.bank_name">
                                    <dt class="text-gray-500">Banque</dt>
                                    <dd class="font-semibold">{{ paymentMethods.bank_transfer.bank_name }}</dd>
                                </template>
                            </dl>
                        </div>
                    </div>

                    <!-- Espèces -->
                    <div v-if="paymentMethods.cash?.enabled" class="flex items-start gap-3 rounded-xl bg-amber-50 p-4 ring-1 ring-amber-100">
                        <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full bg-amber-500 text-white text-base font-bold">€</div>
                        <div>
                            <p class="text-sm font-semibold text-amber-800">Espèces</p>
                            <p v-if="paymentMethods.cash.note" class="mt-0.5 text-xs text-amber-700">{{ paymentMethods.cash.note }}</p>
                        </div>
                    </div>

                    <!-- Message si aucun moyen spécifique -->
                    <div v-if="!paymentMethods.mobile_money?.enabled && !paymentMethods.wave?.enabled && !paymentMethods.bank_transfer?.enabled && !paymentMethods.cash?.enabled"
                         class="rounded-xl bg-gray-50 p-4 text-sm text-gray-600 sm:col-span-2">
                        Contactez <strong>{{ company.name }}</strong> pour connaître les modalités de paiement.
                    </div>
                </div>

                <!-- Contact société -->
                <div class="mt-4 rounded-xl bg-indigo-50 px-4 py-3 text-sm text-indigo-700">
                    Pour toute question, contactez <strong>{{ company.name }}</strong>
                    <template v-if="company.email"> à <a :href="'mailto:' + company.email" class="underline">{{ company.email }}</a></template>
                    <template v-if="company.phone"> ou au <strong>{{ company.phone }}</strong></template>.
                </div>
            </div>

            <!-- ============================================================
                 Bouton PDF répété en bas (mobile-friendly)
                 ========================================================= -->
            <div class="flex justify-center pt-2">
                <a
                    :href="pdfUrl"
                    target="_blank"
                    rel="noopener"
                    class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-6 py-3 text-sm font-bold text-white shadow hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                    Télécharger le PDF — {{ document.type_label }} {{ document.number }}
                </a>
            </div>

            <!-- ============================================================
                 Footer
                 ========================================================= -->
            <footer class="pb-4 text-center text-xs text-gray-400">
                Propulsé par <strong class="text-gray-500">IBIG FactPro</strong> — documents vérifiables par QR code
            </footer>
        </main>
    </div>
</template>

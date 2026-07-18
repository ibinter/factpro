<template>
  <div
    class="min-h-screen bg-gray-50 flex flex-col"
    :dir="t.dir || 'ltr'"
    :lang="lang"
  >
    <!-- En-tête -->
    <header class="bg-white border-b border-gray-200 shadow-sm">
      <div class="max-w-2xl mx-auto px-4 py-4 flex items-center justify-between flex-wrap gap-3">
        <!-- Logo / titre -->
        <div class="flex items-center gap-3">
          <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center">
            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
          </div>
          <div>
            <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">FactPro</p>
            <p class="text-sm font-semibold text-gray-800">{{ t.title }}</p>
          </div>
        </div>

        <!-- Sélecteur de langue -->
        <div class="flex items-center gap-1">
          <button
            v-for="l in langs"
            :key="l.code"
            @click="setLang(l.code)"
            :class="[
              'px-2 py-1 rounded text-xs font-medium transition-colors',
              lang === l.code
                ? 'bg-indigo-100 text-indigo-700 ring-1 ring-indigo-300'
                : 'text-gray-500 hover:bg-gray-100'
            ]"
            :title="l.label"
          >
            {{ l.flag }} {{ l.code.toUpperCase() }}
          </button>
        </div>
      </div>
    </header>

    <!-- Contenu principal -->
    <main class="flex-1 max-w-2xl mx-auto w-full px-4 py-8 space-y-6">

      <!-- Bandeau statut -->
      <div :class="['rounded-xl p-6 flex items-start gap-4 shadow-sm border', statusStyle.bg, statusStyle.border]">
        <span class="text-4xl leading-none mt-1" aria-hidden="true">{{ statusStyle.icon }}</span>
        <div>
          <h1 :class="['text-xl font-bold', statusStyle.text]">{{ statusStyle.label }}</h1>
          <p class="text-sm mt-1" :class="statusStyle.subtext">{{ statusStyle.description }}</p>
        </div>
      </div>

      <!-- Hash vérifié -->
      <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
        <p class="text-xs text-gray-400 font-mono uppercase tracking-wide mb-1">{{ t.hashLabel }}</p>
        <p class="font-mono text-xs text-gray-600 break-all">{{ hash }}</p>
      </div>

      <!-- Informations du document -->
      <div v-if="document" class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-5 py-3 bg-gray-50 border-b border-gray-100">
          <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">{{ t.documentInfo }}</h2>
        </div>
        <dl class="divide-y divide-gray-100">
          <div class="px-5 py-3 flex justify-between gap-4">
            <dt class="text-sm text-gray-500">{{ t.docType }}</dt>
            <dd class="text-sm font-medium text-gray-800">{{ document.type_label }}</dd>
          </div>
          <div class="px-5 py-3 flex justify-between gap-4">
            <dt class="text-sm text-gray-500">{{ t.docNumber }}</dt>
            <dd class="text-sm font-mono font-medium text-gray-800">{{ document.number }}</dd>
          </div>
          <div class="px-5 py-3 flex justify-between gap-4">
            <dt class="text-sm text-gray-500">{{ t.issueDate }}</dt>
            <dd class="text-sm font-medium text-gray-800">{{ document.date }}</dd>
          </div>
          <div class="px-5 py-3 flex justify-between gap-4">
            <dt class="text-sm text-gray-500">{{ t.total }}</dt>
            <dd class="text-sm font-bold text-gray-900">
              {{ formatAmount(document.total) }}
              <span class="font-normal text-gray-500 ml-1">{{ document.currency }}</span>
            </dd>
          </div>
          <div v-if="document.finalized_at" class="px-5 py-3 flex justify-between gap-4">
            <dt class="text-sm text-gray-500">{{ t.finalizedAt }}</dt>
            <dd class="text-sm font-medium text-gray-800">{{ document.finalized_at }}</dd>
          </div>
        </dl>
      </div>

      <!-- Informations émetteur -->
      <div v-if="company && company.name" class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-5 py-3 bg-gray-50 border-b border-gray-100">
          <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">{{ t.issuerInfo }}</h2>
        </div>
        <div class="px-5 py-4 flex items-start gap-4">
          <img
            v-if="company.logo"
            :src="company.logo"
            :alt="company.name"
            class="w-14 h-14 rounded-lg object-contain border border-gray-100 flex-shrink-0"
          />
          <div class="w-14 h-14 rounded-lg bg-indigo-50 flex items-center justify-center flex-shrink-0" v-else>
            <span class="text-xl font-bold text-indigo-400">{{ company.name?.charAt(0) }}</span>
          </div>
          <div class="space-y-1">
            <p class="font-semibold text-gray-900">{{ company.name }}</p>
            <p v-if="company.address" class="text-sm text-gray-500">{{ company.address }}</p>
            <p v-if="company.phone" class="text-sm text-gray-500">{{ company.phone }}</p>
            <p v-if="company.email" class="text-sm text-gray-500">{{ company.email }}</p>
          </div>
        </div>
      </div>

    </main>

    <!-- Pied de page -->
    <footer class="border-t border-gray-200 bg-white py-5">
      <div class="max-w-2xl mx-auto px-4 text-center space-y-2">
        <p class="text-xs text-gray-400">{{ t.poweredBy }}</p>
        <a
          href="/register"
          class="inline-block text-xs font-medium text-indigo-600 hover:text-indigo-800 underline underline-offset-2"
        >{{ t.createAccount }}</a>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  status: { type: String, required: true },
  hash: { type: String, required: true },
  document: { type: Object, default: null },
  company: { type: Object, default: null },
})

// ── Traductions inline (5 langues) ────────────────────────────────────────────
const translations = {
  fr: {
    title: 'Vérification de Document',
    hashLabel: 'Identifiant du document',
    documentInfo: 'Informations du document',
    issuerInfo: "Informations de l'émetteur",
    docType: 'Type de document',
    docNumber: 'Numéro',
    issueDate: "Date d'émission",
    total: 'Montant total',
    finalizedAt: 'Finalisé le',
    poweredBy: 'Vérifié par FactPro — La plateforme de facturation de confiance',
    createAccount: 'Créer votre compte FactPro',
    statuses: {
      authentic:  { label: 'Document Authentique', description: 'Ce document a été émis et certifié par FactPro. Son contenu n\'a pas été modifié.' },
      paid:       { label: 'Document Authentique — Payé', description: 'Ce document est authentique et a été réglé.' },
      cancelled:  { label: 'Document Annulé', description: 'Ce document a été annulé par l\'émetteur.' },
      not_found:  { label: 'Document Introuvable', description: 'Aucun document ne correspond à cet identifiant. Le hash est peut-être invalide ou le document a été supprimé.' },
      draft:      { label: 'Document Non Finalisé', description: 'Ce document est en cours d\'édition. Il n\'est pas encore certifié.' },
      tampered:   { label: 'Document Potentiellement Falsifié', description: 'Le contenu de ce document ne correspond pas à la signature d\'origine.' },
    },
  },
  en: {
    title: 'Document Verification',
    hashLabel: 'Document identifier',
    documentInfo: 'Document information',
    issuerInfo: 'Issuer information',
    docType: 'Document type',
    docNumber: 'Number',
    issueDate: 'Issue date',
    total: 'Total amount',
    finalizedAt: 'Finalized on',
    poweredBy: 'Verified by FactPro — The trusted invoicing platform',
    createAccount: 'Create your FactPro account',
    statuses: {
      authentic:  { label: 'Authentic Document', description: 'This document was issued and certified by FactPro. Its content has not been altered.' },
      paid:       { label: 'Authentic Document — Paid', description: 'This document is authentic and has been paid.' },
      cancelled:  { label: 'Cancelled Document', description: 'This document has been cancelled by the issuer.' },
      not_found:  { label: 'Document Not Found', description: 'No document matches this identifier. The hash may be invalid or the document has been deleted.' },
      draft:      { label: 'Unfinalized Document', description: 'This document is still being edited and has not been certified yet.' },
      tampered:   { label: 'Potentially Tampered Document', description: 'The document content does not match the original signature.' },
    },
  },
  ar: {
    dir: 'rtl',
    title: 'التحقق من المستند',
    hashLabel: 'معرّف المستند',
    documentInfo: 'معلومات المستند',
    issuerInfo: 'معلومات المُصدِر',
    docType: 'نوع المستند',
    docNumber: 'الرقم',
    issueDate: 'تاريخ الإصدار',
    total: 'المبلغ الإجمالي',
    finalizedAt: 'تاريخ الاعتماد',
    poweredBy: 'تم التحقق بواسطة FactPro — منصة الفوترة الموثوقة',
    createAccount: 'أنشئ حسابك على FactPro',
    statuses: {
      authentic:  { label: 'مستند أصيل', description: 'تم إصدار هذا المستند والتحقق منه عبر FactPro. لم يُعدَّل محتواه.' },
      paid:       { label: 'مستند أصيل — مدفوع', description: 'هذا المستند أصيل وقد تم دفعه.' },
      cancelled:  { label: 'مستند ملغى', description: 'تم إلغاء هذا المستند من قِبَل المُصدِر.' },
      not_found:  { label: 'المستند غير موجود', description: 'لا يوجد مستند يطابق هذا المعرّف.' },
      draft:      { label: 'مستند غير مكتمل', description: 'هذا المستند قيد التحرير ولم يُعتمد بعد.' },
      tampered:   { label: 'مستند يُشتبه في تزويره', description: 'لا يتطابق محتوى المستند مع التوقيع الأصلي.' },
    },
  },
  pt: {
    title: 'Verificação de Documento',
    hashLabel: 'Identificador do documento',
    documentInfo: 'Informações do documento',
    issuerInfo: 'Informações do emissor',
    docType: 'Tipo de documento',
    docNumber: 'Número',
    issueDate: 'Data de emissão',
    total: 'Valor total',
    finalizedAt: 'Finalizado em',
    poweredBy: 'Verificado pelo FactPro — A plataforma de faturação confiável',
    createAccount: 'Crie a sua conta FactPro',
    statuses: {
      authentic:  { label: 'Documento Autêntico', description: 'Este documento foi emitido e certificado pelo FactPro. O seu conteúdo não foi alterado.' },
      paid:       { label: 'Documento Autêntico — Pago', description: 'Este documento é autêntico e foi pago.' },
      cancelled:  { label: 'Documento Cancelado', description: 'Este documento foi cancelado pelo emissor.' },
      not_found:  { label: 'Documento Não Encontrado', description: 'Nenhum documento corresponde a este identificador.' },
      draft:      { label: 'Documento Não Finalizado', description: 'Este documento está em edição e ainda não foi certificado.' },
      tampered:   { label: 'Documento Potencialmente Falsificado', description: 'O conteúdo do documento não corresponde à assinatura original.' },
    },
  },
  es: {
    title: 'Verificación de Documento',
    hashLabel: 'Identificador del documento',
    documentInfo: 'Información del documento',
    issuerInfo: 'Información del emisor',
    docType: 'Tipo de documento',
    docNumber: 'Número',
    issueDate: 'Fecha de emisión',
    total: 'Importe total',
    finalizedAt: 'Finalizado el',
    poweredBy: 'Verificado por FactPro — La plataforma de facturación de confianza',
    createAccount: 'Crea tu cuenta FactPro',
    statuses: {
      authentic:  { label: 'Documento Auténtico', description: 'Este documento fue emitido y certificado por FactPro. Su contenido no ha sido alterado.' },
      paid:       { label: 'Documento Auténtico — Pagado', description: 'Este documento es auténtico y ha sido pagado.' },
      cancelled:  { label: 'Documento Cancelado', description: 'Este documento ha sido cancelado por el emisor.' },
      not_found:  { label: 'Documento No Encontrado', description: 'Ningún documento corresponde a este identificador.' },
      draft:      { label: 'Documento No Finalizado', description: 'Este documento está en edición y aún no ha sido certificado.' },
      tampered:   { label: 'Documento Potencialmente Falsificado', description: 'El contenido del documento no corresponde a la firma original.' },
    },
  },
}

const langs = [
  { code: 'fr', flag: '🇫🇷', label: 'Français' },
  { code: 'en', flag: '🇬🇧', label: 'English' },
  { code: 'ar', flag: '🇸🇦', label: 'العربية' },
  { code: 'pt', flag: '🇵🇹', label: 'Português' },
  { code: 'es', flag: '🇪🇸', label: 'Español' },
]

// Langue persistante via localStorage
const storedLang = typeof localStorage !== 'undefined'
  ? (localStorage.getItem('factpro_verify_lang') || 'fr')
  : 'fr'
const validLangs = ['fr', 'en', 'ar', 'pt', 'es']
const lang = ref(validLangs.includes(storedLang) ? storedLang : 'fr')

function setLang(code) {
  lang.value = code
  if (typeof localStorage !== 'undefined') {
    localStorage.setItem('factpro_verify_lang', code)
  }
}

const t = computed(() => translations[lang.value] || translations.fr)

// ── Style selon le statut ──────────────────────────────────────────────────────
const STATUS_STYLES = {
  authentic: {
    icon: '✅',
    bg: 'bg-green-50',
    border: 'border-green-200',
    text: 'text-green-800',
    subtext: 'text-green-700',
  },
  paid: {
    icon: '💰',
    bg: 'bg-green-50',
    border: 'border-green-200',
    text: 'text-green-800',
    subtext: 'text-green-700',
  },
  cancelled: {
    icon: '❌',
    bg: 'bg-red-50',
    border: 'border-red-200',
    text: 'text-red-800',
    subtext: 'text-red-700',
  },
  not_found: {
    icon: '⚠️',
    bg: 'bg-orange-50',
    border: 'border-orange-200',
    text: 'text-orange-800',
    subtext: 'text-orange-700',
  },
  draft: {
    icon: '📝',
    bg: 'bg-gray-50',
    border: 'border-gray-200',
    text: 'text-gray-700',
    subtext: 'text-gray-600',
  },
  tampered: {
    icon: '🚨',
    bg: 'bg-red-50',
    border: 'border-red-200',
    text: 'text-red-800',
    subtext: 'text-red-700',
  },
}

const statusStyle = computed(() => {
  const base = STATUS_STYLES[props.status] || STATUS_STYLES.not_found
  const statusTrans = t.value.statuses?.[props.status] || t.value.statuses?.not_found || {}
  return {
    ...base,
    label: statusTrans.label || props.status,
    description: statusTrans.description || '',
  }
})

// ── Formatage montant ─────────────────────────────────────────────────────────
function formatAmount(amount) {
  if (amount == null) return '—'
  return new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(amount)
}
</script>

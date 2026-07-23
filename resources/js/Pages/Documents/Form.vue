<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref, onMounted, watch } from 'vue';
import { useAiAssist } from '@/Composables/useAiAssist';
import axios from 'axios';

const props = defineProps({
    document: { type: Object, default: null },
    documentType: String,
    customers: Array,
    products: Array,
    defaults: Object,
    types: Array,
    categories: { type: Object, default: () => ({}) },
    templates: { type: Array, default: () => [] },
    defaultTemplate: { type: String, default: null },
});

const isEdit = computed(() => !!props.document);
const typeLabel = computed(() => props.types.find((t) => t.value === (props.document?.type ?? props.documentType))?.label ?? 'Document');

// Préfixe "Nouveau/Nouvelle/Nouvel" selon le genre grammatical du type de document
const NOUVEAU_PREFIX = {
    invoice:              'Nouvelle',
    simple_invoice:       'Nouvelle',
    export_invoice:       'Nouvelle',
    tax_exempt_invoice:   'Nouvelle',
    rectification_invoice:'Nouvelle',
    supplier_invoice:     'Nouvelle',
    medical_invoice:      'Nouvelle',
    deposit_invoice:      'Nouvelle',
    balance_invoice:      'Nouvelle',
    credit_note:          'Nouvel',
    supplier_credit_note: 'Nouvel',
    debit_note:           'Nouvelle',
    delivery_note:        null,       // pas de préfixe
    dispatch_order:       null,
    picking_order:        null,
};
const createPrefix = computed(() => {
    const t = props.document?.type ?? props.documentType;
    if (t in NOUVEAU_PREFIX) return NOUVEAU_PREFIX[t];
    return 'Nouveau';
});
const pageTitle = computed(() => {
    if (isEdit.value) return 'Modifier — ' + typeLabel.value;
    if (createPrefix.value === null) return typeLabel.value;
    return createPrefix.value + ' ' + typeLabel.value;
});

// Types groupés par catégorie pour le sélecteur
const groupedTypes = computed(() => {
    const groups = {};
    props.types.forEach(t => {
        const cat = t.category ?? 'vente';
        if (!groups[cat]) groups[cat] = { label: props.categories[cat] ?? cat, types: [] };
        groups[cat].types.push(t);
    });
    return groups;
});

// ── Schéma par type ───────────────────────────────────────────────────────────
const TYPE_SCHEMAS = {
    // Vente & Facturation
    invoice:              { showLines: true,  showPrices: true,  showTax: true,  showDiscount: true,  showDueDate: true,  clientLabel: 'Client',                    extraSection: null },
    simple_invoice:       { showLines: true,  showPrices: true,  showTax: true,  showDiscount: true,  showDueDate: true,  clientLabel: 'Client',                    extraSection: null },
    export_invoice:       { showLines: true,  showPrices: true,  showTax: false, showDiscount: false, showDueDate: true,  clientLabel: 'Client Import',             extraSection: 'export' },
    tax_exempt_invoice:   { showLines: true,  showPrices: true,  showTax: false, showDiscount: true,  showDueDate: true,  clientLabel: 'Client',                    extraSection: null },
    rectification_invoice:{ showLines: true,  showPrices: true,  showTax: true,  showDiscount: false, showDueDate: true,  clientLabel: 'Client',                    extraSection: 'credit_note' },
    quote:                { showLines: true,  showPrices: true,  showTax: true,  showDiscount: true,  showDueDate: false, clientLabel: 'Prospect / Client',         extraSection: 'quote' },
    commercial_offer:     { showLines: true,  showPrices: true,  showTax: true,  showDiscount: true,  showDueDate: false, clientLabel: 'Prospect / Client',         extraSection: 'quote' },
    commercial_proposal:  { showLines: true,  showPrices: true,  showTax: true,  showDiscount: true,  showDueDate: false, clientLabel: 'Prospect / Client',         extraSection: 'quote' },
    proforma:             { showLines: true,  showPrices: true,  showTax: true,  showDiscount: true,  showDueDate: true,  clientLabel: 'Client',                    extraSection: null },
    sales_order:          { showLines: true,  showPrices: true,  showTax: true,  showDiscount: true,  showDueDate: false, clientLabel: 'Client',                    extraSection: 'sales_order' },
    reservation_order:    { showLines: true,  showPrices: true,  showTax: true,  showDiscount: false, showDueDate: false, clientLabel: 'Client',                    extraSection: 'sales_order' },
    picking_order:        { showLines: true,  showPrices: false, showTax: false, showDiscount: false, showDueDate: false, clientLabel: 'Client',                    extraSection: 'delivery_note' },
    delivery_note:        { showLines: true,  showPrices: false, showTax: false, showDiscount: false, showDueDate: false, clientLabel: 'Client destinataire',       extraSection: 'delivery_note' },
    dispatch_order:       { showLines: true,  showPrices: false, showTax: false, showDiscount: false, showDueDate: false, clientLabel: 'Client destinataire',       extraSection: 'delivery_note' },
    credit_note:          { showLines: true,  showPrices: true,  showTax: true,  showDiscount: false, showDueDate: false, clientLabel: 'Client',                    extraSection: 'credit_note' },
    payment_receipt:      { showLines: false, showPrices: false, showTax: false, showDiscount: false, showDueDate: false, clientLabel: 'Reçu de',                   extraSection: 'payment_receipt' },
    deposit_invoice:      { showLines: true,  showPrices: true,  showTax: true,  showDiscount: false, showDueDate: true,  clientLabel: 'Client',                    extraSection: 'deposit_invoice' },
    balance_invoice:      { showLines: true,  showPrices: true,  showTax: true,  showDiscount: false, showDueDate: true,  clientLabel: 'Client',                    extraSection: 'balance_invoice' },
    pos_ticket:           { showLines: true,  showPrices: true,  showTax: true,  showDiscount: false, showDueDate: false, clientLabel: 'Client (optionnel)',         extraSection: 'pos_ticket' },
    quittance:            { showLines: false, showPrices: false, showTax: false, showDiscount: false, showDueDate: false, clientLabel: 'Locataire',                 extraSection: 'quittance' },
    stock_exit_sale:      { showLines: true,  showPrices: false, showTax: false, showDiscount: false, showDueDate: false, clientLabel: 'Client',                    extraSection: 'delivery_note' },
    commercial_contract:  { showLines: false, showPrices: false, showTax: false, showDiscount: false, showDueDate: true,  clientLabel: 'Partie contractante',       extraSection: 'admin' },
    remittance:           { showLines: true,  showPrices: true,  showTax: false, showDiscount: false, showDueDate: false, clientLabel: 'Bénéficiaire',              extraSection: 'remittance' },

    // Achats & Fournisseurs
    purchase_request:     { showLines: true,  showPrices: true,  showTax: false, showDiscount: false, showDueDate: false, clientLabel: 'Fournisseur souhaité',      extraSection: 'achats_gen' },
    price_request:        { showLines: true,  showPrices: false, showTax: false, showDiscount: false, showDueDate: false, clientLabel: 'Fournisseur consulté',      extraSection: 'achats_gen' },
    supplier_consultation:{ showLines: true,  showPrices: false, showTax: false, showDiscount: false, showDueDate: false, clientLabel: 'Fournisseur',               extraSection: 'achats_gen' },
    purchase_order:       { showLines: true,  showPrices: true,  showTax: true,  showDiscount: false, showDueDate: false, clientLabel: 'Fournisseur',               extraSection: 'purchase_order' },
    goods_receipt:        { showLines: true,  showPrices: true,  showTax: false, showDiscount: false, showDueDate: false, clientLabel: 'Fournisseur',               extraSection: 'achats_gen' },
    supplier_invoice:     { showLines: true,  showPrices: true,  showTax: true,  showDiscount: false, showDueDate: true,  clientLabel: 'Fournisseur',               extraSection: null },
    supplier_credit:      { showLines: true,  showPrices: true,  showTax: true,  showDiscount: false, showDueDate: false, clientLabel: 'Fournisseur',               extraSection: 'credit_note' },
    supplier_return:      { showLines: true,  showPrices: false, showTax: false, showDiscount: false, showDueDate: false, clientLabel: 'Fournisseur',               extraSection: 'achats_gen' },
    debit_note:           { showLines: false, showPrices: false, showTax: false, showDiscount: false, showDueDate: false, clientLabel: 'Fournisseur',               extraSection: 'finance_gen' },
    supplier_credit_note: { showLines: false, showPrices: false, showTax: false, showDiscount: false, showDueDate: false, clientLabel: 'Fournisseur',               extraSection: 'finance_gen' },

    // Stocks & Inventaire
    stock_entry:          { showLines: true,  showPrices: true,  showTax: false, showDiscount: false, showDueDate: false, clientLabel: 'Fournisseur (optionnel)',   extraSection: 'stocks' },
    stock_exit:           { showLines: true,  showPrices: false, showTax: false, showDiscount: false, showDueDate: false, clientLabel: 'Destinataire (optionnel)', extraSection: 'stocks' },
    stock_transfer:       { showLines: true,  showPrices: false, showTax: false, showDiscount: false, showDueDate: false, clientLabel: 'Responsable (optionnel)',  extraSection: 'stocks' },
    stock_consumption:    { showLines: true,  showPrices: false, showTax: false, showDiscount: false, showDueDate: false, clientLabel: 'Responsable (optionnel)',  extraSection: 'stocks' },
    inventory:            { showLines: true,  showPrices: false, showTax: false, showDiscount: false, showDueDate: false, clientLabel: 'Responsable (optionnel)',  extraSection: 'stocks' },
    stock_adjustment:     { showLines: true,  showPrices: false, showTax: false, showDiscount: false, showDueDate: false, clientLabel: 'Responsable (optionnel)',  extraSection: 'stocks' },
    destruction_note:     { showLines: true,  showPrices: false, showTax: false, showDiscount: false, showDueDate: false, clientLabel: 'Responsable (optionnel)',  extraSection: 'stocks' },
    manufacturing_order:  { showLines: true,  showPrices: false, showTax: false, showDiscount: false, showDueDate: false, clientLabel: 'Responsable (optionnel)',  extraSection: 'stocks' },
    transformation_note:  { showLines: true,  showPrices: false, showTax: false, showDiscount: false, showDueDate: false, clientLabel: 'Responsable (optionnel)',  extraSection: 'stocks' },

    // SAV & Maintenance
    rma:                  { showLines: true,  showPrices: false, showTax: false, showDiscount: false, showDueDate: false, clientLabel: 'Client',                    extraSection: 'rma' },
    sav_sheet:            { showLines: true,  showPrices: false, showTax: false, showDiscount: false, showDueDate: false, clientLabel: 'Client',                    extraSection: 'sav' },
    repair_order:         { showLines: true,  showPrices: true,  showTax: true,  showDiscount: false, showDueDate: false, clientLabel: 'Client',                    extraSection: 'sav' },
    intervention_report:  { showLines: true,  showPrices: true,  showTax: false, showDiscount: false, showDueDate: false, clientLabel: 'Client',                    extraSection: 'sav' },
    maintenance_order:    { showLines: true,  showPrices: true,  showTax: false, showDiscount: false, showDueDate: false, clientLabel: 'Client',                    extraSection: 'sav' },
    warranty_certificate: { showLines: false, showPrices: false, showTax: false, showDiscount: false, showDueDate: false, clientLabel: 'Client',                    extraSection: 'sav' },
    maintenance_contract: { showLines: true,  showPrices: true,  showTax: true,  showDiscount: false, showDueDate: true,  clientLabel: 'Client',                    extraSection: 'sav' },

    // BTP & Travaux
    work_order:           { showLines: true,  showPrices: true,  showTax: true,  showDiscount: false, showDueDate: false, clientLabel: "Maître d'ouvrage",          extraSection: 'btp' },
    service_order:        { showLines: true,  showPrices: true,  showTax: true,  showDiscount: false, showDueDate: false, clientLabel: "Maître d'ouvrage",          extraSection: 'btp' },
    progress_statement:   { showLines: true,  showPrices: true,  showTax: true,  showDiscount: false, showDueDate: false, clientLabel: "Maître d'ouvrage",          extraSection: 'btp' },
    provisional_account:  { showLines: true,  showPrices: true,  showTax: true,  showDiscount: false, showDueDate: false, clientLabel: "Maître d'ouvrage",          extraSection: 'btp' },
    final_account:        { showLines: true,  showPrices: true,  showTax: true,  showDiscount: false, showDueDate: false, clientLabel: "Maître d'ouvrage",          extraSection: 'btp' },
    provisional_acceptance:{ showLines: false, showPrices: false, showTax: false, showDiscount: false, showDueDate: false, clientLabel: "Maître d'ouvrage",         extraSection: 'btp' },
    final_acceptance:     { showLines: false, showPrices: false, showTax: false, showDiscount: false, showDueDate: false, clientLabel: "Maître d'ouvrage",          extraSection: 'btp' },
    site_report:          { showLines: false, showPrices: false, showTax: false, showDiscount: false, showDueDate: false, clientLabel: "Maître d'ouvrage",          extraSection: 'btp' },

    // Logistique & Transport
    shipping_note:        { showLines: true,  showPrices: false, showTax: false, showDiscount: false, showDueDate: false, clientLabel: 'Destinataire',              extraSection: 'logistique' },
    waybill:              { showLines: true,  showPrices: false, showTax: false, showDiscount: false, showDueDate: false, clientLabel: 'Destinataire',              extraSection: 'logistique' },
    packing_list:         { showLines: true,  showPrices: false, showTax: false, showDiscount: false, showDueDate: false, clientLabel: 'Destinataire',              extraSection: 'logistique' },
    loading_slip:         { showLines: true,  showPrices: false, showTax: false, showDiscount: false, showDueDate: false, clientLabel: 'Destinataire',              extraSection: 'logistique' },
    inter_depot_transfer: { showLines: true,  showPrices: false, showTax: false, showDiscount: false, showDueDate: false, clientLabel: 'Responsable',               extraSection: 'logistique' },
    delivery_manifest:    { showLines: true,  showPrices: false, showTax: false, showDiscount: false, showDueDate: false, clientLabel: 'Destinataire',              extraSection: 'logistique' },

    // Finance & Trésorerie
    expense_report:       { showLines: true,  showPrices: true,  showTax: false, showDiscount: false, showDueDate: false, clientLabel: 'Employé / Bénéficiaire',   extraSection: 'finance_gen' },
    cash_voucher:         { showLines: false, showPrices: false, showTax: false, showDiscount: false, showDueDate: false, clientLabel: 'Bénéficiaire',              extraSection: 'finance_gen' },
    bank_deposit:         { showLines: true,  showPrices: true,  showTax: false, showDiscount: false, showDueDate: false, clientLabel: 'Banque',                    extraSection: 'finance_gen' },
    bank_withdrawal:      { showLines: false, showPrices: false, showTax: false, showDiscount: false, showDueDate: false, clientLabel: 'Bénéficiaire',              extraSection: 'finance_gen' },
    bill_of_exchange:     { showLines: false, showPrices: false, showTax: false, showDiscount: false, showDueDate: true,  clientLabel: 'Tireur / Tiré',             extraSection: 'finance_gen' },
    promissory_note:      { showLines: false, showPrices: false, showTax: false, showDiscount: false, showDueDate: true,  clientLabel: 'Souscripteur',              extraSection: 'finance_gen' },

    // Ressources Humaines
    mission_order:        { showLines: false, showPrices: false, showTax: false, showDiscount: false, showDueDate: false, clientLabel: 'Employé',                   extraSection: 'rh' },
    leave_request:        { showLines: false, showPrices: false, showTax: false, showDiscount: false, showDueDate: false, clientLabel: 'Employé',                   extraSection: 'rh' },
    payslip:              { showLines: false, showPrices: false, showTax: false, showDiscount: false, showDueDate: false, clientLabel: 'Employé',                   extraSection: 'rh' },
    salary_advance:       { showLines: false, showPrices: false, showTax: false, showDiscount: false, showDueDate: false, clientLabel: 'Employé',                   extraSection: 'rh' },
    service_note:         { showLines: false, showPrices: false, showTax: false, showDiscount: false, showDueDate: false, clientLabel: 'Employé / Destinataire',    extraSection: 'admin' },
    absence_authorization:{ showLines: false, showPrices: false, showTax: false, showDiscount: false, showDueDate: false, clientLabel: 'Employé',                   extraSection: 'rh' },

    // Administratif & Juridique
    contract:             { showLines: false, showPrices: false, showTax: false, showDiscount: false, showDueDate: true,  clientLabel: 'Partie contractante',       extraSection: 'admin' },
    minutes:              { showLines: false, showPrices: false, showTax: false, showDiscount: false, showDueDate: false, clientLabel: 'Participants / Signataires', extraSection: 'admin' },
    attestation:          { showLines: false, showPrices: false, showTax: false, showDiscount: false, showDueDate: false, clientLabel: 'Bénéficiaire',              extraSection: 'admin' },
    certificate:          { showLines: false, showPrices: false, showTax: false, showDiscount: false, showDueDate: false, clientLabel: 'Bénéficiaire',              extraSection: 'admin' },
    reminder_letter:      { showLines: false, showPrices: false, showTax: false, showDiscount: false, showDueDate: false, clientLabel: 'Client',                    extraSection: 'admin' },
    formal_notice:        { showLines: false, showPrices: false, showTax: false, showDiscount: false, showDueDate: false, clientLabel: 'Client',                    extraSection: 'admin' },
    acknowledgement:      { showLines: false, showPrices: false, showTax: false, showDiscount: false, showDueDate: false, clientLabel: 'Expéditeur',                extraSection: 'admin' },
    authorization:        { showLines: false, showPrices: false, showTax: false, showDiscount: false, showDueDate: false, clientLabel: 'Bénéficiaire',              extraSection: 'admin' },

    // Immobilier & Location
    lease_contract:       { showLines: false, showPrices: false, showTax: false, showDiscount: false, showDueDate: true,  clientLabel: 'Locataire',                 extraSection: 'immobilier_gen' },
    entry_inventory:      { showLines: true,  showPrices: false, showTax: false, showDiscount: false, showDueDate: false, clientLabel: 'Locataire',                 extraSection: 'immobilier_gen' },
    exit_inventory:       { showLines: true,  showPrices: false, showTax: false, showDiscount: false, showDueDate: false, clientLabel: 'Locataire',                 extraSection: 'immobilier_gen' },
    rent_notice:          { showLines: false, showPrices: false, showTax: false, showDiscount: false, showDueDate: true,  clientLabel: 'Locataire',                 extraSection: 'quittance' },
    deposit_receipt:      { showLines: false, showPrices: false, showTax: false, showDiscount: false, showDueDate: false, clientLabel: 'Locataire',                 extraSection: 'payment_receipt' },

    // Export & Douane
    origin_certificate:   { showLines: false, showPrices: false, showTax: false, showDiscount: false, showDueDate: false, clientLabel: 'Exportateur',               extraSection: 'export' },
    customs_declaration:  { showLines: true,  showPrices: true,  showTax: false, showDiscount: false, showDueDate: false, clientLabel: 'Importateur / Exportateur', extraSection: 'export' },
    boarding_pass_doc:    { showLines: true,  showPrices: false, showTax: false, showDiscount: false, showDueDate: false, clientLabel: 'Expéditeur',                extraSection: 'export' },
    export_invoice_custom:{ showLines: true,  showPrices: true,  showTax: false, showDiscount: false, showDueDate: true,  clientLabel: 'Client Import',             extraSection: 'export' },

    // Santé & Médical
    medical_invoice:      { showLines: true,  showPrices: true,  showTax: false, showDiscount: false, showDueDate: false, clientLabel: 'Patient',                   extraSection: 'sante' },
    prescription:         { showLines: false, showPrices: false, showTax: false, showDiscount: false, showDueDate: false, clientLabel: 'Patient',                   extraSection: 'sante' },
    lab_order:            { showLines: false, showPrices: false, showTax: false, showDiscount: false, showDueDate: false, clientLabel: 'Patient',                   extraSection: 'sante' },
    care_sheet:           { showLines: true,  showPrices: true,  showTax: false, showDiscount: false, showDueDate: false, clientLabel: 'Patient',                   extraSection: 'sante' },
};

// Schéma générique pour les types non mappés explicitement
const defaultSchema = { showLines: true, showPrices: true, showTax: true, showDiscount: false, showDueDate: false, clientLabel: 'Client / Tiers', extraSection: 'generic' };
const schema = computed(() => TYPE_SCHEMAS[form.type] ?? defaultSchema);

// ── Customers list ────────────────────────────────────────────────────────────
const customersList = ref([...(props.customers ?? [])]);

// ── Lines ─────────────────────────────────────────────────────────────────────
const emptyLine = () => ({
    product_id: null,
    description: '',
    quantity: 1,
    unit: 'unité',
    unit_price: 0,
    line_discount_type: 'percent',
    discount_percent: 0,
    tax_rate: props.defaults?.tax_rate ?? 18,
});

// ── Form ──────────────────────────────────────────────────────────────────────
const form = useForm({
    type:           props.document?.type ?? props.documentType,
    customer_id:    props.document?.customer_id ?? null,
    reference:      props.document?.reference ?? '',
    issue_date:     props.document?.issue_date?.slice(0, 10) ?? new Date().toISOString().slice(0, 10),
    due_date:       props.document?.due_date?.slice(0, 10) ?? null,
    currency:       props.document?.currency ?? props.defaults?.currency ?? 'XOF',
    template_key:   props.document?.template_key ?? props.defaultTemplate ?? null,
    discount_type:  props.document?.discount_type ?? null,
    discount_value: Number(props.document?.discount_value ?? 0),
    notes:          props.document?.notes ?? '',
    terms:          props.document?.terms ?? '',
    meta:           props.document?.meta ?? {},
    lines: props.document?.lines?.map((l) => ({
        product_id:        l.product_id,
        description:       l.description,
        quantity:          Number(l.quantity),
        unit:              l.unit,
        unit_price:        Number(l.unit_price),
        line_discount_type: l.line_discount_type ?? 'percent',
        discount_percent:  Number(l.discount_percent),
        tax_rate:          Number(l.tax_rate),
    })) ?? [emptyLine()],
});

const addLine    = () => form.lines.push(emptyLine());
const removeLine = (i) => form.lines.splice(i, 1);

const onProductSelect = (line) => {
    const p = props.products.find((p) => p.id === line.product_id);
    if (p) {
        line.description = p.name + (p.description ? ' — ' + p.description : '');
        line.unit_price  = Number(p.price);
        line.tax_rate    = Number(p.tax_rate);
        line.unit        = p.unit;
    }
};

// ── Calculs ───────────────────────────────────────────────────────────────────
const lineTotal = (line) => {
    const base = line.quantity * line.unit_price;
    if (line.line_discount_type === 'fixed')
        return Math.max(0, Math.round((base - (line.discount_percent || 0)) * 100) / 100);
    return Math.round(base * (1 - (line.discount_percent || 0) / 100) * 100) / 100;
};

const subtotal = computed(() => {
    if (!schema.value.showLines) {
        // Pour quittance / reçu : calculer depuis meta
        if (form.type === 'quittance') return (Number(form.meta.rent_amount) || 0) + (Number(form.meta.charges_amount) || 0);
        if (form.type === 'payment_receipt') return Number(form.meta.amount_received) || 0;
    }
    return form.lines.reduce((s, l) => s + lineTotal(l), 0);
});

const discountAmount = computed(() => {
    if (form.discount_type === 'percent') return (subtotal.value * (form.discount_value || 0)) / 100;
    if (form.discount_type === 'fixed')   return Math.min(form.discount_value || 0, subtotal.value);
    return 0;
});

const taxAmount = computed(() => {
    if (!schema.value.showTax) return 0;
    const base = subtotal.value - discountAmount.value;
    if (subtotal.value <= 0) return 0;
    return form.lines.reduce((s, l) => {
        const share = lineTotal(l) / subtotal.value;
        return s + base * share * ((l.tax_rate || 0) / 100);
    }, 0);
});

const total = computed(() => subtotal.value - discountAmount.value + taxAmount.value);
const fmt = (n) => new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(n ?? 0);

// ── IA ────────────────────────────────────────────────────────────────────────
const { loading: aiLoading, suggestDescription, suggestPrice } = useAiAssist();
const aiAvailable = ref(false);
onMounted(async () => {
    try { const { data } = await axios.get('/ai/status'); aiAvailable.value = data.available && data.plan_ok; } catch {}
});
const fillDescription = async (line) => {
    if (!aiAvailable.value) return;
    const name = props.products.find(p => p.id === line.product_id)?.name || line.description;
    if (!name) return;
    const desc = await suggestDescription(name);
    if (desc && !line.description) line.description = desc;
};
const fillPrice = async (line) => {
    if (!aiAvailable.value) return;
    const name = props.products.find(p => p.id === line.product_id)?.name || line.description;
    if (!name) return;
    const price = await suggestPrice(name, form.currency);
    if (price !== null && price > 0) line.unit_price = price;
};

// ── Modal création rapide client ──────────────────────────────────────────────
const showQuickModal = ref(false);
const quickSaving   = ref(false);
const quickError    = ref('');
const quickForm     = ref({ type: 'company', name: '', email: '', phone: '', address: '' });
const openQuickModal  = () => { quickForm.value = { type: 'company', name: '', email: '', phone: '', address: '' }; quickError.value = ''; showQuickModal.value = true; };
const closeQuickModal = () => { showQuickModal.value = false; };
const saveQuickCustomer = async () => {
    quickError.value = '';
    if (!quickForm.value.name.trim()) { quickError.value = 'Le nom est requis.'; return; }
    quickSaving.value = true;
    try {
        const { data } = await axios.post(route('customers.quick'), quickForm.value);
        customersList.value.push(data);
        form.customer_id = data.id;
        closeQuickModal();
    } catch (e) {
        quickError.value = e.response?.data?.message || e.response?.data?.error || 'Erreur lors de la création.';
    } finally { quickSaving.value = false; }
};

// ── Templates ─────────────────────────────────────────────────────────────────
const COSMETIC_TEMPLATE_TYPES = [
    'invoice','credit_note','proforma','advance_invoice','deposit_invoice',
    'recurring_invoice','final_invoice','corrective_invoice','tax_invoice','commercial_invoice',
    'quote','price_offer','service_quote','work_quote','repair_estimate',
    'delivery_note','packing_list','shipping_order','picking_list',
    'transfer_note','goods_receipt','return_note','goods_return',
    'purchase_order','supplier_order','rfq',
    'payment_receipt','cash_receipt','petty_cash_receipt','advance_receipt','refund_receipt',
    'contract','service_contract','lease_agreement','maintenance_contract',
    'partnership_agreement','nda','framework_agreement','subcontracting_contract',
    'meeting_minutes','pv_reception','pv_handover','acceptance_report',
    'conflict_pv','general_assembly_pv',
    'mission_order','travel_request','expense_report',
    'site_report','inspection_report','progress_report','daily_report',
    'rental_inventory','inventory_check','property_inspection',
];
const typeAcceptsCosmeticTemplate = computed(() => COSMETIC_TEMPLATE_TYPES.includes(form.type));
const activeFamily      = ref(null);
const families          = computed(() => [...new Set(props.templates.map(t => t.family))]);
const filteredTemplates = computed(() => {
    if (!typeAcceptsCosmeticTemplate.value) return [];
    return activeFamily.value ? props.templates.filter(t => t.family === activeFamily.value) : props.templates;
});
// Réinitialiser le template sélectionné si le type choisi ne supporte pas les templates cosmétiques
watch(() => form.type, () => {
    if (!typeAcceptsCosmeticTemplate.value) {
        form.template_key = '';
        activeFamily.value = null;
    }
});

// Modal prévisualisation
const previewTemplate   = ref(null);
const showPreviewModal  = ref(false);
function openPreview(t) { previewTemplate.value = t; showPreviewModal.value = true; }
function closePreview()  { showPreviewModal.value = false; }
function selectFromPreview() {
    if (previewTemplate.value) form.template_key = previewTemplate.value.key;
    closePreview();
}

// ── Submit ────────────────────────────────────────────────────────────────────
const submit = () => {
    const opts = {
        onFinish: () => {
            if (!form.hasErrors) window.location.href = '/documents';
        },
    };
    if (isEdit.value) form.put(route('documents.update', props.document?.id), opts);
    else              form.post(route('documents.store'), opts);
};

// ── Mois disponibles pour quittance ──────────────────────────────────────────
const MOIS = ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
const currentYear = new Date().getFullYear();
const years = Array.from({ length: 5 }, (_, i) => currentYear - 2 + i);

const MODES_PAIEMENT = ['Espèces','Virement bancaire','Chèque','Mobile Money (Wave)','Mobile Money (Orange Money)','Carte bancaire','Autre'];
</script>

<template>
    <Head :title="pageTitle" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    {{ pageTitle }}
                    <span v-if="isEdit" class="ml-2 text-sm font-normal text-gray-400">{{ document.number }}</span>
                </h2>
                <Link :href="route('documents.index')" class="text-sm font-semibold text-gray-500 hover:underline">← Retour</Link>
            </div>
        </template>

        <!-- Modal création rapide client -->
        <Teleport to="body">
            <div v-if="showQuickModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4" @click.self="closeQuickModal">
                <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl">
                    <div class="flex items-center justify-between border-b px-6 py-4">
                        <h3 class="text-base font-bold text-gray-900">Nouveau client rapide</h3>
                        <button type="button" @click="closeQuickModal" class="text-gray-400 hover:text-gray-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="space-y-4 px-6 py-5">
                        <p v-if="quickError" class="rounded-lg bg-red-50 px-3 py-2 text-xs text-red-700">{{ quickError }}</p>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Type *</label>
                            <select v-model="quickForm.type" class="block w-full rounded-lg border-gray-300 text-sm shadow-sm">
                                <option value="company">Société / Entreprise</option>
                                <option value="individual">Particulier</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Nom *</label>
                            <input v-model="quickForm.name" type="text" placeholder="Nom du client" class="block w-full rounded-lg border-gray-300 text-sm shadow-sm" @keyup.enter="saveQuickCustomer" />
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Email</label>
                                <input v-model="quickForm.email" type="email" class="block w-full rounded-lg border-gray-300 text-sm shadow-sm" />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Téléphone</label>
                                <input v-model="quickForm.phone" type="tel" class="block w-full rounded-lg border-gray-300 text-sm shadow-sm" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Adresse</label>
                            <input v-model="quickForm.address" type="text" class="block w-full rounded-lg border-gray-300 text-sm shadow-sm" />
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 border-t px-6 py-4">
                        <button type="button" @click="closeQuickModal" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Annuler</button>
                        <button type="button" @click="saveQuickCustomer" :disabled="quickSaving"
                            class="rounded-lg bg-brand-600 px-5 py-2 text-sm font-semibold text-white hover:bg-brand-700 disabled:opacity-60 flex items-center gap-2">
                            <span v-if="quickSaving" class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                            {{ quickSaving ? 'Enregistrement…' : 'Créer et sélectionner' }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <div class="py-8">
            <form @submit.prevent="submit" class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">

                <!-- ── En-tête commun ────────────────────────────────────── -->
                <div class="rounded-xl bg-white p-6 shadow-sm border border-gray-100 space-y-4">
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <!-- Type -->
                        <div v-if="!isEdit">
                            <InputLabel value="Type de document" />
                            <select v-model="form.type" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
                                <optgroup v-for="(group, catKey) in groupedTypes" :key="catKey" :label="group.label">
                                    <option v-for="t in group.types" :key="t.value" :value="t.value">{{ t.label }}</option>
                                </optgroup>
                            </select>
                        </div>

                        <!-- Client -->
                        <div>
                            <InputLabel :value="schema.clientLabel" />
                            <div class="mt-1 flex items-center gap-1.5">
                                <select v-model="form.customer_id" class="block min-w-0 flex-1 rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
                                    <option :value="null">— Aucun —</option>
                                    <option v-for="c in customersList" :key="c.id" :value="c.id">{{ c.name }}</option>
                                </select>
                                <button type="button" @click="openQuickModal"
                                    class="flex-shrink-0 rounded-lg border border-brand-300 bg-brand-50 p-2 text-brand-700 hover:bg-brand-100 transition-colors" title="Ajouter">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                                </button>
                            </div>
                            <InputError :message="form.errors.customer_id" class="mt-1" />
                        </div>

                        <!-- Date émission -->
                        <div>
                            <InputLabel value="Date d'émission *" />
                            <TextInput v-model="form.issue_date" type="date" class="mt-1 block w-full" required />
                            <InputError :message="form.errors.issue_date" class="mt-1" />
                        </div>

                        <!-- Échéance (conditionnelle) -->
                        <div v-if="schema.showDueDate">
                            <InputLabel value="Échéance" />
                            <TextInput v-model="form.due_date" type="date" class="mt-1 block w-full" />
                            <InputError :message="form.errors.due_date" class="mt-1" />
                        </div>

                        <!-- Référence client (pas pour ticket/quittance) -->
                        <div v-if="!['pos_ticket','quittance'].includes(form.type)">
                            <InputLabel value="Référence client" />
                            <TextInput v-model="form.reference" class="mt-1 block w-full" />
                        </div>

                        <!-- Devise (pas pour quittance/reçu simple) -->
                        <div v-if="!['quittance'].includes(form.type)">
                            <InputLabel value="Devise" />
                            <TextInput v-model="form.currency" maxlength="3" class="mt-1 block w-full uppercase" />
                        </div>
                    </div>

                    <!-- ═══════════════════════════════════════════════════════ -->
                    <!-- ── GALERIE DE MODÈLES PDF avec aperçu ─────────────── -->
                    <!-- ═══════════════════════════════════════════════════════ -->
                    <div v-if="templates.length && typeAcceptsCosmeticTemplate" class="pt-3 border-t border-gray-100">
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <InputLabel value="Modèle visuel du document" class="!mb-0" />
                                <p class="text-xs text-gray-400 mt-0.5">Cliquez sur 👁 pour voir l'aperçu complet avant de valider</p>
                            </div>
                            <span class="text-xs font-medium text-brand-600 bg-brand-50 px-2 py-0.5 rounded-full">{{ templates.length }} modèles</span>
                        </div>

                        <!-- Filtres par famille -->
                        <div class="mb-3 flex flex-wrap gap-1.5">
                            <button type="button" @click="activeFamily = null"
                                class="rounded-full px-3 py-1 text-xs font-medium transition-colors"
                                :class="!activeFamily ? 'bg-brand-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'">
                                Tous ({{ templates.length }})
                            </button>
                            <button v-for="fam in families" :key="fam" type="button" @click="activeFamily = fam"
                                class="rounded-full px-3 py-1 text-xs font-medium transition-colors capitalize"
                                :class="activeFamily === fam ? 'bg-brand-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'">
                                {{ fam }}
                            </button>
                        </div>

                        <!-- Grille de cartes -->
                        <div class="max-h-72 overflow-y-auto rounded-xl border border-gray-100 bg-gray-50 p-2.5">
                            <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                                <div v-for="t in filteredTemplates" :key="t.key"
                                    class="group relative rounded-xl border-2 overflow-hidden cursor-pointer transition-all duration-150"
                                    :class="form.template_key === t.key
                                        ? 'border-brand-500 shadow-md ring-1 ring-brand-400'
                                        : 'border-gray-200 bg-white hover:border-brand-300 hover:shadow-sm'"
                                    @click="form.template_key = t.key">

                                    <!-- Mini aperçu document -->
                                    <div class="relative h-24 w-full overflow-hidden" :style="{ background: t.secondary || '#f8fafc' }">
                                        <!-- Bande header -->
                                        <div class="absolute inset-x-0 top-0 h-5 flex items-center px-2 gap-1.5"
                                            :style="{ backgroundColor: t.primary }">
                                            <div class="h-2.5 w-2.5 rounded-full bg-white/30 flex-shrink-0"></div>
                                            <div class="h-1.5 rounded bg-white/50 flex-grow"></div>
                                            <div class="h-1.5 w-6 rounded bg-white/70"></div>
                                        </div>
                                        <!-- Corps simulé -->
                                        <div class="absolute inset-x-2 top-7 space-y-1">
                                            <div class="h-1 rounded" :style="{ backgroundColor: t.primary, opacity: 0.15, width: '70%' }"></div>
                                            <div class="h-px rounded bg-gray-300 w-full"></div>
                                            <div v-for="i in 3" :key="i" class="flex gap-1">
                                                <div class="h-1 rounded bg-gray-300 flex-grow"></div>
                                                <div class="h-1 w-6 rounded bg-gray-300"></div>
                                                <div class="h-1 w-7 rounded bg-gray-300"></div>
                                            </div>
                                            <div class="h-px rounded bg-gray-300 w-full"></div>
                                        </div>
                                        <!-- Total bar -->
                                        <div class="absolute inset-x-2 bottom-3 h-3 rounded flex items-center justify-end px-2"
                                            :style="{ backgroundColor: t.primary }">
                                            <div class="h-1 w-10 rounded bg-white/70"></div>
                                        </div>
                                        <!-- Bouton aperçu au survol -->
                                        <button type="button"
                                            class="absolute inset-0 flex items-center justify-center bg-black/0 group-hover:bg-black/30 transition-all opacity-0 group-hover:opacity-100"
                                            @click.stop="openPreview(t)">
                                            <span class="bg-white text-gray-800 text-xs font-semibold rounded-full px-3 py-1 shadow flex items-center gap-1">
                                                👁 Aperçu
                                            </span>
                                        </button>
                                        <!-- Badge sélectionné -->
                                        <div v-if="form.template_key === t.key"
                                            class="absolute top-1.5 right-1.5 h-4 w-4 rounded-full bg-white flex items-center justify-center shadow">
                                            <svg class="h-2.5 w-2.5 text-brand-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                    </div>

                                    <!-- Nom & famille -->
                                    <div class="px-2 py-1.5 bg-white">
                                        <div class="text-[10px] font-semibold leading-tight truncate"
                                            :class="form.template_key === t.key ? 'text-brand-700' : 'text-gray-700'">
                                            {{ t.name }}
                                        </div>
                                        <div class="flex items-center gap-1 mt-0.5">
                                            <div class="flex -space-x-0.5">
                                                <span class="h-2 w-2 rounded-full border border-white" :style="{ backgroundColor: t.primary }"></span>
                                                <span class="h-2 w-2 rounded-full border border-white" :style="{ backgroundColor: t.accent }"></span>
                                            </div>
                                            <span class="text-[9px] text-gray-400 capitalize truncate">{{ t.family }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modèle sélectionné -->
                        <div v-if="form.template_key" class="mt-2 flex items-center gap-2 text-xs text-brand-700 bg-brand-50 rounded-lg px-3 py-2">
                            <svg class="h-3.5 w-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            Modèle sélectionné : <strong>{{ templates.find(t => t.key === form.template_key)?.name }}</strong>
                            <button type="button" @click="form.template_key = ''" class="ml-auto text-gray-400 hover:text-red-500 text-xs">✕</button>
                        </div>
                    </div>

                    <!-- ═══════════════════════════════════════════════════════ -->
                    <!-- ── MODAL APERÇU TEMPLATE ───────────────────────────── -->
                    <!-- ═══════════════════════════════════════════════════════ -->
                    <Teleport to="body">
                        <Transition name="modal-fade">
                        <div v-if="showPreviewModal && previewTemplate"
                            class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
                            @click.self="closePreview">
                            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col">

                                <!-- Header modal -->
                                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                                    <div class="flex items-center gap-3">
                                        <div class="flex -space-x-1">
                                            <span class="h-5 w-5 rounded-full border-2 border-white shadow" :style="{ backgroundColor: previewTemplate.primary }"></span>
                                            <span class="h-5 w-5 rounded-full border-2 border-white shadow" :style="{ backgroundColor: previewTemplate.secondary }"></span>
                                            <span class="h-4 w-4 rounded-full border-2 border-white shadow" :style="{ backgroundColor: previewTemplate.accent }"></span>
                                        </div>
                                        <div>
                                            <h3 class="font-bold text-gray-900 text-sm">{{ previewTemplate.name }}</h3>
                                            <p class="text-xs text-gray-400 capitalize">{{ previewTemplate.family }} · {{ previewTemplate.description }}</p>
                                        </div>
                                    </div>
                                    <button type="button" @click="closePreview" class="text-gray-400 hover:text-gray-600 p-1 rounded-full hover:bg-gray-100">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>

                                <!-- Aperçu simulé A4 -->
                                <div class="flex-1 overflow-y-auto bg-gray-200 p-6">
                                    <div class="mx-auto bg-white shadow-xl" style="width:100%;max-width:520px;min-height:680px;padding:28px;">

                                        <!-- Header doc -->
                                        <div class="flex justify-between items-start mb-5">
                                            <div>
                                                <div class="h-8 w-24 rounded mb-2" :style="{ backgroundColor: previewTemplate.primary + '22' }"></div>
                                                <div class="font-bold text-sm" :style="{ color: previewTemplate.primary }">KOFFI & ASSOCIÉS SARL</div>
                                                <div class="text-xs text-gray-400">12 Rue du Commerce, Abidjan-Plateau<br>Tél : +225 07 00 00 00 · RC CI-ABJ-2019</div>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-2xl font-black" :style="{ color: previewTemplate.primary }">FACTURE</div>
                                                <div class="text-xs text-gray-500 mt-1">N° FAC-2024-0842</div>
                                                <div class="text-xs text-gray-500">Date : 23/07/2024</div>
                                                <div class="text-xs text-gray-500">Échéance : 22/08/2024</div>
                                            </div>
                                        </div>

                                        <!-- Divider -->
                                        <div class="h-0.5 mb-4" :style="{ backgroundColor: previewTemplate.primary }"></div>

                                        <!-- Client -->
                                        <div class="mb-4 p-3 rounded-lg" :style="{ backgroundColor: previewTemplate.primary + '10' }">
                                            <div class="text-[10px] font-bold uppercase tracking-wide mb-1" :style="{ color: previewTemplate.primary }">Facturé à</div>
                                            <div class="font-semibold text-sm text-gray-800">ORANGE CÔTE D'IVOIRE S.A.</div>
                                            <div class="text-xs text-gray-500">Direction Achats · Abidjan-Plateau, CI</div>
                                        </div>

                                        <!-- Tableau -->
                                        <table class="w-full text-xs mb-4" style="border-collapse:collapse;">
                                            <thead>
                                                <tr :style="{ backgroundColor: previewTemplate.primary }">
                                                    <th class="text-left text-white px-3 py-2 text-[10px]">Désignation</th>
                                                    <th class="text-right text-white px-2 py-2 text-[10px] w-8">Qté</th>
                                                    <th class="text-right text-white px-2 py-2 text-[10px] w-20">P.U. HT</th>
                                                    <th class="text-right text-white px-3 py-2 text-[10px] w-20">Total HT</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="(row, i) in [
                                                    { d:'Audit infrastructure réseau & sécurité', q:'1', pu:'350 000', t:'350 000' },
                                                    { d:'Déploiement VLAN multi-sites', q:'3', pu:'85 000', t:'255 000' },
                                                    { d:'Formation équipe IT (5 techniciens)', q:'15j', pu:'18 000', t:'270 000' },
                                                ]" :key="i" :style="{ backgroundColor: i % 2 === 0 ? previewTemplate.secondary + '40' : 'white' }">
                                                    <td class="px-3 py-1.5 text-gray-700">{{ row.d }}</td>
                                                    <td class="px-2 py-1.5 text-right text-gray-600">{{ row.q }}</td>
                                                    <td class="px-2 py-1.5 text-right text-gray-600">{{ row.pu }}</td>
                                                    <td class="px-3 py-1.5 text-right font-medium text-gray-800">{{ row.t }}</td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <!-- Totaux -->
                                        <div class="flex justify-between items-end gap-4">
                                            <!-- QR simulé -->
                                            <div class="flex items-center gap-3 p-2 border border-gray-200 rounded-lg bg-gray-50">
                                                <div class="grid grid-cols-3 gap-px w-10 h-10 bg-gray-200 rounded">
                                                    <template v-for="i in 9" :key="i">
                                                        <div class="rounded-sm" :style="{ backgroundColor: [1,2,4,5,6,8].includes(i) ? previewTemplate.primary : 'white' }"></div>
                                                    </template>
                                                </div>
                                                <div class="text-[9px] text-gray-500">Scan pour<br>vérifier</div>
                                            </div>
                                            <!-- Totaux -->
                                            <div class="flex-1 max-w-xs">
                                                <div class="flex justify-between text-xs py-1 border-b border-gray-100">
                                                    <span class="text-gray-500">Sous-total HT</span>
                                                    <span class="font-medium">875 000 XOF</span>
                                                </div>
                                                <div class="flex justify-between text-xs py-1 border-b border-gray-100">
                                                    <span class="text-gray-500">TVA (18%)</span>
                                                    <span>157 500 XOF</span>
                                                </div>
                                                <div class="flex justify-between px-3 py-2 rounded text-sm font-bold text-white mt-1"
                                                    :style="{ backgroundColor: previewTemplate.primary }">
                                                    <span>TOTAL TTC</span>
                                                    <span>1 032 500 XOF</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Footer -->
                                        <div class="mt-5 pt-3 border-t text-center text-[9px] text-gray-400">
                                            KOFFI & ASSOCIÉS SARL · 12 Rue du Commerce, Abidjan-Plateau · RCCM CI-ABJ-2019-B-15234<br>
                                            Pénalités de retard applicables au taux légal · Généré par <strong>IBIG FactPro</strong>
                                        </div>
                                    </div>
                                </div>

                                <!-- Footer modal -->
                                <div class="flex items-center justify-between px-6 py-4 border-t border-gray-100 bg-gray-50">
                                    <button type="button" @click="closePreview" class="text-sm text-gray-500 hover:text-gray-700 font-medium">
                                        ← Continuer à parcourir
                                    </button>
                                    <button type="button" @click="selectFromPreview"
                                        class="px-5 py-2 rounded-xl text-sm font-semibold text-white shadow transition-opacity hover:opacity-90"
                                        :style="{ backgroundColor: previewTemplate.primary }">
                                        ✓ Choisir ce modèle
                                    </button>
                                </div>
                            </div>
                        </div>
                        </Transition>
                    </Teleport>
                </div>

                <!-- ══════════════════════════════════════════════════════════ -->
                <!-- ── SECTION SPÉCIFIQUE : QUITTANCE ─────────────────────── -->
                <!-- ══════════════════════════════════════════════════════════ -->
                <div v-if="schema.extraSection === 'quittance'"
                    class="rounded-xl bg-white p-6 shadow-sm border border-amber-100">
                    <div class="flex items-center gap-2 mb-5">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-amber-100 text-amber-600 text-lg">🏠</span>
                        <h3 class="text-base font-bold text-gray-800">Détails de la quittance de loyer</h3>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div class="lg:col-span-3">
                            <InputLabel value="Adresse du bien immobilier *" />
                            <TextInput v-model="form.meta.property_address" class="mt-1 block w-full" placeholder="Ex: Appartement N°12, Résidence Les Flamboyants, Cocody, Abidjan" />
                        </div>
                        <div>
                            <InputLabel value="Mois concerné *" />
                            <select v-model="form.meta.rental_month" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
                                <option v-for="m in MOIS" :key="m" :value="m">{{ m }}</option>
                            </select>
                        </div>
                        <div>
                            <InputLabel value="Année *" />
                            <select v-model="form.meta.rental_year" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
                                <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
                            </select>
                        </div>
                        <div>
                            <InputLabel value="Mode de paiement" />
                            <select v-model="form.meta.payment_method" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
                                <option v-for="m in MODES_PAIEMENT" :key="m" :value="m">{{ m }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- Montants -->
                    <div class="mt-5 grid gap-4 sm:grid-cols-3">
                        <div>
                            <InputLabel value="Loyer de base (hors charges) *" />
                            <div class="mt-1 flex items-center gap-2">
                                <TextInput v-model.number="form.meta.rent_amount" type="number" min="0" class="block w-full" placeholder="0" />
                                <span class="text-sm font-medium text-gray-500">{{ form.currency }}</span>
                            </div>
                        </div>
                        <div>
                            <InputLabel value="Charges locatives" />
                            <div class="mt-1 flex items-center gap-2">
                                <TextInput v-model.number="form.meta.charges_amount" type="number" min="0" class="block w-full" placeholder="0" />
                                <span class="text-sm font-medium text-gray-500">{{ form.currency }}</span>
                            </div>
                        </div>
                        <!-- Total calculé -->
                        <div class="flex flex-col justify-end">
                            <p class="text-xs font-medium text-gray-500 mb-1">TOTAL REÇU</p>
                            <div class="rounded-xl bg-brand-900 px-4 py-3 text-center">
                                <p class="text-xl font-bold text-white">{{ fmt(subtotal) }}</p>
                                <p class="text-xs text-brand-200">{{ form.currency }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ══════════════════════════════════════════════════════════ -->
                <!-- ── SECTION SPÉCIFIQUE : REÇU DE PAIEMENT ──────────────── -->
                <!-- ══════════════════════════════════════════════════════════ -->
                <div v-if="schema.extraSection === 'payment_receipt'"
                    class="rounded-xl bg-white p-6 shadow-sm border border-emerald-100">
                    <div class="flex items-center gap-2 mb-5">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 text-lg">💳</span>
                        <h3 class="text-base font-bold text-gray-800">Détails du reçu de paiement</h3>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <InputLabel value="Montant reçu *" />
                            <div class="mt-1 flex items-center gap-2">
                                <TextInput v-model.number="form.meta.amount_received" type="number" min="0" class="block w-full" placeholder="0" />
                                <span class="text-sm font-medium text-gray-500">{{ form.currency }}</span>
                            </div>
                        </div>
                        <div>
                            <InputLabel value="Mode de paiement *" />
                            <select v-model="form.meta.payment_method" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
                                <option v-for="m in MODES_PAIEMENT" :key="m" :value="m">{{ m }}</option>
                            </select>
                        </div>
                        <div>
                            <InputLabel value="Référence facture / document payé" />
                            <TextInput v-model="form.meta.document_reference" class="mt-1 block w-full" placeholder="Ex: FAC-2026-0012" />
                        </div>
                        <div>
                            <InputLabel value="Objet / motif du paiement *" />
                            <TextInput v-model="form.meta.payment_purpose" class="mt-1 block w-full" placeholder="Ex: Règlement facture Juillet 2026" />
                        </div>
                    </div>
                    <div class="mt-4 flex items-center justify-end">
                        <div class="rounded-xl bg-brand-900 px-8 py-3 text-center">
                            <p class="text-xs font-medium text-brand-200 mb-1">MONTANT REÇU</p>
                            <p class="text-2xl font-bold text-white">{{ fmt(subtotal) }} {{ form.currency }}</p>
                        </div>
                    </div>
                </div>

                <!-- ══════════════════════════════════════════════════════════ -->
                <!-- ── SECTION SPÉCIFIQUE : BON DE LIVRAISON ──────────────── -->
                <!-- ══════════════════════════════════════════════════════════ -->
                <div v-if="schema.extraSection === 'delivery_note'"
                    class="rounded-xl bg-white p-6 shadow-sm border border-teal-100">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-teal-100 text-teal-600 text-lg">📦</span>
                        <h3 class="text-base font-bold text-gray-800">Informations de livraison</h3>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div class="sm:col-span-2">
                            <InputLabel value="Adresse de livraison" />
                            <TextInput v-model="form.meta.delivery_address" class="mt-1 block w-full" placeholder="Adresse complète de destination" />
                        </div>
                        <div>
                            <InputLabel value="Date de livraison prévue" />
                            <TextInput v-model="form.meta.delivery_date" type="date" class="mt-1 block w-full" />
                        </div>
                        <div>
                            <InputLabel value="Transporteur / Livreur" />
                            <TextInput v-model="form.meta.carrier" class="mt-1 block w-full" placeholder="Ex: DHL, livreur interne…" />
                        </div>
                        <div>
                            <InputLabel value="N° de suivi / référence expédition" />
                            <TextInput v-model="form.meta.tracking_number" class="mt-1 block w-full" placeholder="Ex: TRK-2026-..." />
                        </div>
                    </div>
                </div>

                <!-- ══════════════════════════════════════════════════════════ -->
                <!-- ── SECTION SPÉCIFIQUE : BON DE COMMANDE (VENTE) ───────── -->
                <!-- ══════════════════════════════════════════════════════════ -->
                <div v-if="schema.extraSection === 'sales_order'"
                    class="rounded-xl bg-white p-6 shadow-sm border border-sky-100">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-sky-100 text-sky-600 text-lg">🛒</span>
                        <h3 class="text-base font-bold text-gray-800">Informations de commande</h3>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <InputLabel value="Date de livraison souhaitée" />
                            <TextInput v-model="form.meta.delivery_date_expected" type="date" class="mt-1 block w-full" />
                        </div>
                        <div>
                            <InputLabel value="Conditions de livraison" />
                            <select v-model="form.meta.shipping_terms" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
                                <option value="">— Choisir —</option>
                                <option>Franco domicile (frais inclus)</option>
                                <option>Départ entrepôt (frais à la charge de l'acheteur)</option>
                                <option>Point de retrait</option>
                                <option>À convenir</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <InputLabel value="Adresse de livraison" />
                            <TextInput v-model="form.meta.delivery_address" class="mt-1 block w-full" placeholder="Si différente de l'adresse du client" />
                        </div>
                    </div>
                </div>

                <!-- ══════════════════════════════════════════════════════════ -->
                <!-- ── SECTION SPÉCIFIQUE : COMMANDE FOURNISSEUR ──────────── -->
                <!-- ══════════════════════════════════════════════════════════ -->
                <div v-if="schema.extraSection === 'purchase_order'"
                    class="rounded-xl bg-white p-6 shadow-sm border border-indigo-100">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100 text-indigo-600 text-lg">📋</span>
                        <h3 class="text-base font-bold text-gray-800">Informations de la commande fournisseur</h3>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <InputLabel value="Date de livraison attendue" />
                            <TextInput v-model="form.meta.delivery_date_expected" type="date" class="mt-1 block w-full" />
                        </div>
                        <div>
                            <InputLabel value="Contact chez le fournisseur" />
                            <TextInput v-model="form.meta.supplier_contact" class="mt-1 block w-full" placeholder="Nom, email ou tél." />
                        </div>
                        <div class="sm:col-span-2">
                            <InputLabel value="Adresse de livraison (réception)" />
                            <TextInput v-model="form.meta.delivery_address" class="mt-1 block w-full" placeholder="Adresse de votre entrepôt / siège" />
                        </div>
                    </div>
                </div>

                <!-- ══════════════════════════════════════════════════════════ -->
                <!-- ── SECTION SPÉCIFIQUE : BON DE TRAVAUX ────────────────── -->
                <!-- ══════════════════════════════════════════════════════════ -->
                <div v-if="schema.extraSection === 'work_order'"
                    class="rounded-xl bg-white p-6 shadow-sm border border-orange-100">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-orange-100 text-orange-600 text-lg">🔧</span>
                        <h3 class="text-base font-bold text-gray-800">Informations d'intervention</h3>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div class="sm:col-span-2">
                            <InputLabel value="Lieu d'intervention *" />
                            <TextInput v-model="form.meta.intervention_location" class="mt-1 block w-full" placeholder="Adresse ou description du lieu" />
                        </div>
                        <div>
                            <InputLabel value="Technicien responsable" />
                            <TextInput v-model="form.meta.technician" class="mt-1 block w-full" placeholder="Nom du technicien" />
                        </div>
                        <div>
                            <InputLabel value="Date d'intervention" />
                            <TextInput v-model="form.meta.intervention_date" type="date" class="mt-1 block w-full" />
                        </div>
                        <div>
                            <InputLabel value="Date de fin prévue" />
                            <TextInput v-model="form.meta.intervention_end_date" type="date" class="mt-1 block w-full" />
                        </div>
                    </div>
                </div>

                <!-- ══════════════════════════════════════════════════════════ -->
                <!-- ── SECTION SPÉCIFIQUE : TICKET DE CAISSE ─────────────── -->
                <!-- ══════════════════════════════════════════════════════════ -->
                <div v-if="schema.extraSection === 'pos_ticket'"
                    class="rounded-xl bg-white p-6 shadow-sm border border-gray-100">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 text-gray-600 text-lg">🧾</span>
                        <h3 class="text-base font-bold text-gray-800">Informations caisse</h3>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <InputLabel value="Mode de paiement" />
                            <select v-model="form.meta.payment_method" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
                                <option v-for="m in MODES_PAIEMENT" :key="m" :value="m">{{ m }}</option>
                            </select>
                        </div>
                        <div>
                            <InputLabel value="Caissier" />
                            <TextInput v-model="form.meta.cashier_name" class="mt-1 block w-full" placeholder="Nom du caissier" />
                        </div>
                    </div>
                </div>

                <!-- ══════════════════════════════════════════════════════════ -->
                <!-- ── SECTION SPÉCIFIQUE : AVOIR ─────────────────────────── -->
                <!-- ══════════════════════════════════════════════════════════ -->
                <div v-if="schema.extraSection === 'credit_note'"
                    class="rounded-xl bg-white p-6 shadow-sm border border-rose-100">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-rose-100 text-rose-600 text-lg">↩️</span>
                        <h3 class="text-base font-bold text-gray-800">Informations de l'avoir</h3>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <InputLabel value="Référence facture d'origine" />
                            <TextInput v-model="form.meta.original_document_ref" class="mt-1 block w-full" placeholder="Ex: FAC-2026-0012" />
                        </div>
                        <div>
                            <InputLabel value="Motif de l'avoir" />
                            <TextInput v-model="form.meta.credit_reason" class="mt-1 block w-full" placeholder="Ex: Retour marchandise, erreur de facturation…" />
                        </div>
                    </div>
                </div>

                <!-- ══════════════════════════════════════════════════════════ -->
                <!-- ── SECTION SPÉCIFIQUE : DEVIS ─────────────────────────── -->
                <!-- ══════════════════════════════════════════════════════════ -->
                <div v-if="schema.extraSection === 'quote'"
                    class="rounded-xl bg-white p-6 shadow-sm border border-amber-100">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-amber-100 text-amber-600 text-lg">⏳</span>
                        <h3 class="text-base font-bold text-gray-800">Validité du devis</h3>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <InputLabel value="Valable jusqu'au" />
                            <TextInput v-model="form.meta.validity_date" type="date" class="mt-1 block w-full" />
                        </div>
                        <div>
                            <InputLabel value="Conditions d'acceptation" />
                            <TextInput v-model="form.meta.acceptance_conditions" class="mt-1 block w-full" placeholder="Ex: Sous réserve de disponibilité" />
                        </div>
                    </div>
                </div>

                <!-- ══════════════════════════════════════════════════════════ -->
                <!-- ── SECTION SPÉCIFIQUE : RETOUR RMA ────────────────────── -->
                <!-- ══════════════════════════════════════════════════════════ -->
                <div v-if="schema.extraSection === 'rma'"
                    class="rounded-xl bg-white p-6 shadow-sm border border-pink-100">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-pink-100 text-pink-600 text-lg">🔄</span>
                        <h3 class="text-base font-bold text-gray-800">Informations de retour</h3>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <InputLabel value="Référence commande / facture d'origine" />
                            <TextInput v-model="form.meta.original_document_ref" class="mt-1 block w-full" placeholder="Ex: BC-2026-0003" />
                        </div>
                        <div>
                            <InputLabel value="Motif du retour" />
                            <TextInput v-model="form.meta.return_reason" class="mt-1 block w-full" placeholder="Ex: Produit défectueux, non conforme…" />
                        </div>
                    </div>
                </div>

                <!-- ══════════════════════════════════════════════════════════ -->
                <!-- ── SECTION SPÉCIFIQUE : FACTURE D'ACOMPTE ─────────────── -->
                <!-- ══════════════════════════════════════════════════════════ -->
                <div v-if="schema.extraSection === 'deposit_invoice'"
                    class="rounded-xl bg-white p-6 shadow-sm border border-cyan-100">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-cyan-100 text-cyan-600 text-lg">💰</span>
                        <h3 class="text-base font-bold text-gray-800">Informations de l'acompte</h3>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <InputLabel value="Pourcentage d'acompte (%)" />
                            <div class="mt-1 flex items-center gap-2">
                                <TextInput v-model.number="form.meta.deposit_percent" type="number" min="1" max="100" class="block w-full" placeholder="Ex: 30" />
                                <span class="text-sm font-medium text-gray-500">%</span>
                            </div>
                        </div>
                        <div>
                            <InputLabel value="Réf. devis / bon de commande associé" />
                            <TextInput v-model="form.meta.deposit_reference" class="mt-1 block w-full" placeholder="Ex: DEV-2026-0001" />
                        </div>
                    </div>
                </div>

                <!-- ══════════════════════════════════════════════════════════ -->
                <!-- ── SECTION SPÉCIFIQUE : FACTURE DE SOLDE ──────────────── -->
                <!-- ══════════════════════════════════════════════════════════ -->
                <div v-if="schema.extraSection === 'balance_invoice'"
                    class="rounded-xl bg-white p-6 shadow-sm border border-blue-100">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 text-blue-600 text-lg">✅</span>
                        <h3 class="text-base font-bold text-gray-800">Informations de la facture de solde</h3>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <InputLabel value="Référence facture d'acompte" />
                            <TextInput v-model="form.meta.deposit_reference" class="mt-1 block w-full" placeholder="Ex: FA-2026-0001" />
                        </div>
                        <div>
                            <InputLabel value="Montant déjà versé (acompte)" />
                            <div class="mt-1 flex items-center gap-2">
                                <TextInput v-model.number="form.meta.amount_already_paid" type="number" min="0" class="block w-full" placeholder="0" />
                                <span class="text-sm font-medium text-gray-500">{{ form.currency }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ══════════════════════════════════════════════════════════ -->
                <!-- ── SECTION SPÉCIFIQUE : BORDEREAU DE REMISE ───────────── -->
                <!-- ══════════════════════════════════════════════════════════ -->
                <div v-if="schema.extraSection === 'remittance'"
                    class="rounded-xl bg-white p-6 shadow-sm border border-purple-100">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-purple-100 text-purple-600 text-lg">🏦</span>
                        <h3 class="text-base font-bold text-gray-800">Informations bancaires</h3>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <InputLabel value="Banque" />
                            <TextInput v-model="form.meta.bank_name" class="mt-1 block w-full" placeholder="Ex: Ecobank, SGBCI…" />
                        </div>
                        <div>
                            <InputLabel value="Numéro de compte" />
                            <TextInput v-model="form.meta.account_number" class="mt-1 block w-full" placeholder="Ex: CI123..." />
                        </div>
                    </div>
                </div>

                <!-- ══════════════════════════════════════════════════════════ -->
                <!-- ── SECTION GÉNÉRIQUE (types non mappés) ───────────────── -->
                <!-- ══════════════════════════════════════════════════════════ -->
                <div v-if="schema.extraSection === 'generic'"
                    class="rounded-xl bg-white p-5 shadow-sm border border-gray-100">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-brand-50 text-brand-600 text-base">📄</span>
                        <h3 class="text-sm font-bold text-gray-700">{{ typeLabel }}</h3>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <InputLabel value="Référence interne" />
                            <TextInput v-model="form.meta.internal_ref" class="mt-1 block w-full" placeholder="Numéro ou référence interne" />
                        </div>
                        <div>
                            <InputLabel value="Objet / Motif" />
                            <TextInput v-model="form.meta.purpose" class="mt-1 block w-full" placeholder="Objet ou motif du document" />
                        </div>
                    </div>
                </div>

                <!-- ══════════════════════════════════════════════════════════ -->
                <!-- ── STOCKS & INVENTAIRE ────────────────────────────────── -->
                <!-- ══════════════════════════════════════════════════════════ -->
                <div v-if="schema.extraSection === 'stocks'"
                    class="rounded-xl bg-white p-6 shadow-sm border border-green-100">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-green-100 text-green-600 text-lg">📦</span>
                        <h3 class="text-base font-bold text-gray-800">Informations de mouvement de stock</h3>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <InputLabel value="Entrepôt / Dépôt source" />
                            <TextInput v-model="form.meta.warehouse_from" class="mt-1 block w-full" placeholder="Ex: Dépôt principal" />
                        </div>
                        <div>
                            <InputLabel value="Entrepôt / Dépôt destination" />
                            <TextInput v-model="form.meta.warehouse_to" class="mt-1 block w-full" placeholder="Ex: Dépôt secondaire, Production…" />
                        </div>
                        <div>
                            <InputLabel value="Motif du mouvement" />
                            <select v-model="form.meta.movement_reason" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
                                <option value="">— Choisir —</option>
                                <option>Réapprovisionnement</option>
                                <option>Transfert interne</option>
                                <option>Inventaire annuel</option>
                                <option>Inventaire tournant</option>
                                <option>Ajustement d'écart</option>
                                <option>Destruction / Casse</option>
                                <option>Production / Transformation</option>
                                <option>Retour fournisseur</option>
                                <option>Autre</option>
                            </select>
                        </div>
                        <div>
                            <InputLabel value="Responsable inventaire" />
                            <TextInput v-model="form.meta.inventory_manager" class="mt-1 block w-full" placeholder="Nom du responsable" />
                        </div>
                        <div>
                            <InputLabel value="Date de comptage / mouvement" />
                            <TextInput v-model="form.meta.stock_date" type="date" class="mt-1 block w-full" />
                        </div>
                    </div>
                    <p class="mt-3 text-xs text-green-700 bg-green-50 rounded-lg px-3 py-2">
                        📋 <strong>{{ typeLabel }} :</strong> Listez les articles ci-dessous avec les quantités. Les prix ne sont pas affichés sur ce type de document.
                    </p>
                </div>

                <!-- ══════════════════════════════════════════════════════════ -->
                <!-- ── SAV & MAINTENANCE ──────────────────────────────────── -->
                <!-- ══════════════════════════════════════════════════════════ -->
                <div v-if="schema.extraSection === 'sav'"
                    class="rounded-xl bg-white p-6 shadow-sm border border-yellow-100">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-yellow-100 text-yellow-600 text-lg">🔧</span>
                        <h3 class="text-base font-bold text-gray-800">Informations SAV / Maintenance</h3>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <InputLabel value="Appareil / Équipement" />
                            <TextInput v-model="form.meta.device_name" class="mt-1 block w-full" placeholder="Ex: Imprimante HP LaserJet 400" />
                        </div>
                        <div>
                            <InputLabel value="Numéro de série" />
                            <TextInput v-model="form.meta.serial_number" class="mt-1 block w-full" placeholder="N° de série ou référence" />
                        </div>
                        <div>
                            <InputLabel value="Technicien responsable" />
                            <TextInput v-model="form.meta.technician" class="mt-1 block w-full" placeholder="Nom du technicien" />
                        </div>
                        <div>
                            <InputLabel value="Date d'intervention" />
                            <TextInput v-model="form.meta.intervention_date" type="date" class="mt-1 block w-full" />
                        </div>
                        <div class="sm:col-span-2">
                            <InputLabel value="Symptôme / Panne décrite par le client" />
                            <TextInput v-model="form.meta.symptom_description" class="mt-1 block w-full" placeholder="Description du problème signalé" />
                        </div>
                    </div>
                </div>

                <!-- ══════════════════════════════════════════════════════════ -->
                <!-- ── BTP & TRAVAUX ──────────────────────────────────────── -->
                <!-- ══════════════════════════════════════════════════════════ -->
                <div v-if="schema.extraSection === 'btp'"
                    class="rounded-xl bg-white p-6 shadow-sm border border-orange-100">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-orange-100 text-orange-600 text-lg">🏗️</span>
                        <h3 class="text-base font-bold text-gray-800">Informations chantier / travaux</h3>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div class="sm:col-span-2">
                            <InputLabel value="Nom / Description du chantier *" />
                            <TextInput v-model="form.meta.site_name" class="mt-1 block w-full" placeholder="Ex: Construction villa R+2 — Cocody" />
                        </div>
                        <div>
                            <InputLabel value="Lieu du chantier" />
                            <TextInput v-model="form.meta.site_location" class="mt-1 block w-full" placeholder="Adresse ou commune" />
                        </div>
                        <div>
                            <InputLabel value="Date de début des travaux" />
                            <TextInput v-model="form.meta.work_start_date" type="date" class="mt-1 block w-full" />
                        </div>
                        <div>
                            <InputLabel value="Date de fin prévue" />
                            <TextInput v-model="form.meta.work_end_date" type="date" class="mt-1 block w-full" />
                        </div>
                        <div>
                            <InputLabel value="Chef de chantier / Technicien" />
                            <TextInput v-model="form.meta.technician" class="mt-1 block w-full" placeholder="Nom du responsable" />
                        </div>
                    </div>
                </div>

                <!-- ══════════════════════════════════════════════════════════ -->
                <!-- ── LOGISTIQUE & TRANSPORT ─────────────────────────────── -->
                <!-- ══════════════════════════════════════════════════════════ -->
                <div v-if="schema.extraSection === 'logistique'"
                    class="rounded-xl bg-white p-6 shadow-sm border border-sky-100">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-sky-100 text-sky-600 text-lg">🚚</span>
                        <h3 class="text-base font-bold text-gray-800">Informations de transport</h3>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div class="sm:col-span-2">
                            <InputLabel value="Adresse de départ" />
                            <TextInput v-model="form.meta.origin_address" class="mt-1 block w-full" placeholder="Point de départ / entrepôt expéditeur" />
                        </div>
                        <div>
                            <InputLabel value="Date d'expédition" />
                            <TextInput v-model="form.meta.shipment_date" type="date" class="mt-1 block w-full" />
                        </div>
                        <div class="sm:col-span-2">
                            <InputLabel value="Adresse de destination" />
                            <TextInput v-model="form.meta.delivery_address" class="mt-1 block w-full" placeholder="Adresse de livraison / réception" />
                        </div>
                        <div>
                            <InputLabel value="Transporteur" />
                            <TextInput v-model="form.meta.carrier" class="mt-1 block w-full" placeholder="Ex: DHL, livreur interne…" />
                        </div>
                        <div>
                            <InputLabel value="N° de tracking" />
                            <TextInput v-model="form.meta.tracking_number" class="mt-1 block w-full" placeholder="Référence d'expédition" />
                        </div>
                    </div>
                </div>

                <!-- ══════════════════════════════════════════════════════════ -->
                <!-- ── FINANCE & TRÉSORERIE ───────────────────────────────── -->
                <!-- ══════════════════════════════════════════════════════════ -->
                <div v-if="schema.extraSection === 'finance_gen'"
                    class="rounded-xl bg-white p-6 shadow-sm border border-emerald-100">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 text-lg">💵</span>
                        <h3 class="text-base font-bold text-gray-800">Informations financières</h3>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <InputLabel value="Montant" />
                            <div class="mt-1 flex items-center gap-2">
                                <TextInput v-model.number="form.meta.amount" type="number" min="0" class="block w-full" placeholder="0" />
                                <span class="text-sm font-medium text-gray-500">{{ form.currency }}</span>
                            </div>
                        </div>
                        <div>
                            <InputLabel value="Mode de paiement" />
                            <select v-model="form.meta.payment_method" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
                                <option v-for="m in MODES_PAIEMENT" :key="m" :value="m">{{ m }}</option>
                            </select>
                        </div>
                        <div>
                            <InputLabel value="Référence document associé" />
                            <TextInput v-model="form.meta.document_reference" class="mt-1 block w-full" placeholder="Ex: FAC-2026-0012" />
                        </div>
                        <div>
                            <InputLabel value="Motif / Objet" />
                            <TextInput v-model="form.meta.payment_purpose" class="mt-1 block w-full" placeholder="Objet du versement ou de l'opération" />
                        </div>
                    </div>
                </div>

                <!-- ══════════════════════════════════════════════════════════ -->
                <!-- ── RESSOURCES HUMAINES ────────────────────────────────── -->
                <!-- ══════════════════════════════════════════════════════════ -->
                <div v-if="schema.extraSection === 'rh'"
                    class="rounded-xl bg-white p-6 shadow-sm border border-violet-100">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-violet-100 text-violet-600 text-lg">👤</span>
                        <h3 class="text-base font-bold text-gray-800">Informations RH</h3>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <InputLabel value="Poste / Fonction" />
                            <TextInput v-model="form.meta.job_title" class="mt-1 block w-full" placeholder="Ex: Comptable, Technicien…" />
                        </div>
                        <div>
                            <InputLabel value="Département / Service" />
                            <TextInput v-model="form.meta.department" class="mt-1 block w-full" placeholder="Ex: Finance, Logistique…" />
                        </div>
                        <div>
                            <InputLabel value="Date de début" />
                            <TextInput v-model="form.meta.start_date" type="date" class="mt-1 block w-full" />
                        </div>
                        <div>
                            <InputLabel value="Date de fin" />
                            <TextInput v-model="form.meta.end_date" type="date" class="mt-1 block w-full" />
                        </div>
                        <div class="sm:col-span-2">
                            <InputLabel value="Motif / Objet" />
                            <TextInput v-model="form.meta.purpose" class="mt-1 block w-full" placeholder="Motif de la demande ou de la mission" />
                        </div>
                    </div>
                </div>

                <!-- ══════════════════════════════════════════════════════════ -->
                <!-- ── ADMINISTRATIF ──────────────────────────────────────── -->
                <!-- ══════════════════════════════════════════════════════════ -->
                <div v-if="schema.extraSection === 'admin'"
                    class="rounded-xl bg-white p-6 shadow-sm border border-gray-100">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 text-gray-600 text-lg">📋</span>
                        <h3 class="text-base font-bold text-gray-800">Informations administratives</h3>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <InputLabel value="Objet / Titre du document" />
                            <TextInput v-model="form.meta.purpose" class="mt-1 block w-full" placeholder="Objet ou titre principal" />
                        </div>
                        <div>
                            <InputLabel value="Signataire(s) / Autorité" />
                            <TextInput v-model="form.meta.signatories" class="mt-1 block w-full" placeholder="Ex: DG, RH, Direction…" />
                        </div>
                    </div>
                </div>

                <!-- ══════════════════════════════════════════════════════════ -->
                <!-- ── ACHATS GÉNÉRIQUES ──────────────────────────────────── -->
                <!-- ══════════════════════════════════════════════════════════ -->
                <div v-if="schema.extraSection === 'achats_gen'"
                    class="rounded-xl bg-white p-6 shadow-sm border border-indigo-100">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100 text-indigo-600 text-lg">🛒</span>
                        <h3 class="text-base font-bold text-gray-800">Informations achat / fournisseur</h3>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <InputLabel value="Date de livraison souhaitée" />
                            <TextInput v-model="form.meta.delivery_date_expected" type="date" class="mt-1 block w-full" />
                        </div>
                        <div>
                            <InputLabel value="Contact fournisseur" />
                            <TextInput v-model="form.meta.supplier_contact" class="mt-1 block w-full" placeholder="Nom, email ou tél." />
                        </div>
                        <div class="sm:col-span-2">
                            <InputLabel value="Adresse de livraison (réception)" />
                            <TextInput v-model="form.meta.delivery_address" class="mt-1 block w-full" placeholder="Votre adresse d'entrepôt / siège" />
                        </div>
                    </div>
                </div>

                <!-- ══════════════════════════════════════════════════════════ -->
                <!-- ── IMMOBILIER GÉNÉRIQUE ───────────────────────────────── -->
                <!-- ══════════════════════════════════════════════════════════ -->
                <div v-if="schema.extraSection === 'immobilier_gen'"
                    class="rounded-xl bg-white p-6 shadow-sm border border-amber-100">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-amber-100 text-amber-600 text-lg">🏠</span>
                        <h3 class="text-base font-bold text-gray-800">Informations immobilières</h3>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <InputLabel value="Adresse du bien" />
                            <TextInput v-model="form.meta.property_address" class="mt-1 block w-full" placeholder="Adresse complète du bien immobilier" />
                        </div>
                        <div>
                            <InputLabel value="Type de bien" />
                            <select v-model="form.meta.property_type" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
                                <option value="">— Choisir —</option>
                                <option>Appartement</option><option>Villa</option><option>Studio</option>
                                <option>Bureau</option><option>Local commercial</option><option>Entrepôt</option><option>Terrain</option>
                            </select>
                        </div>
                        <div>
                            <InputLabel value="Superficie (m²)" />
                            <TextInput v-model="form.meta.surface_area" type="number" min="0" class="mt-1 block w-full" placeholder="Ex: 85" />
                        </div>
                    </div>
                </div>

                <!-- ══════════════════════════════════════════════════════════ -->
                <!-- ── EXPORT & DOUANE ────────────────────────────────────── -->
                <!-- ══════════════════════════════════════════════════════════ -->
                <div v-if="schema.extraSection === 'export'"
                    class="rounded-xl bg-white p-6 shadow-sm border border-teal-100">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-teal-100 text-teal-600 text-lg">🌐</span>
                        <h3 class="text-base font-bold text-gray-800">Informations export / douane</h3>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <InputLabel value="Pays de destination" />
                            <TextInput v-model="form.meta.destination_country" class="mt-1 block w-full" placeholder="Ex: France, Sénégal…" />
                        </div>
                        <div>
                            <InputLabel value="Incoterm" />
                            <select v-model="form.meta.incoterm" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
                                <option value="">— Choisir —</option>
                                <option>EXW</option><option>FOB</option><option>CIF</option><option>DAP</option><option>DDP</option>
                            </select>
                        </div>
                        <div>
                            <InputLabel value="N° de déclaration douanière" />
                            <TextInput v-model="form.meta.customs_ref" class="mt-1 block w-full" placeholder="Référence douanière" />
                        </div>
                        <div>
                            <InputLabel value="Pays d'origine des marchandises" />
                            <TextInput v-model="form.meta.origin_country" class="mt-1 block w-full" placeholder="Ex: Côte d'Ivoire" />
                        </div>
                        <div>
                            <InputLabel value="Port / Aéroport d'embarquement" />
                            <TextInput v-model="form.meta.port_of_loading" class="mt-1 block w-full" placeholder="Ex: Port d'Abidjan" />
                        </div>
                    </div>
                </div>

                <!-- ══════════════════════════════════════════════════════════ -->
                <!-- ── SANTÉ & MÉDICAL ────────────────────────────────────── -->
                <!-- ══════════════════════════════════════════════════════════ -->
                <div v-if="schema.extraSection === 'sante'"
                    class="rounded-xl bg-white p-6 shadow-sm border border-rose-100">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-rose-100 text-rose-600 text-lg">🏥</span>
                        <h3 class="text-base font-bold text-gray-800">Informations médicales</h3>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <InputLabel value="Date de naissance du patient" />
                            <TextInput v-model="form.meta.patient_dob" type="date" class="mt-1 block w-full" />
                        </div>
                        <div>
                            <InputLabel value="Médecin / Praticien" />
                            <TextInput v-model="form.meta.doctor_name" class="mt-1 block w-full" placeholder="Nom du médecin ou praticien" />
                        </div>
                        <div>
                            <InputLabel value="Date de consultation / acte" />
                            <TextInput v-model="form.meta.consultation_date" type="date" class="mt-1 block w-full" />
                        </div>
                        <div class="sm:col-span-2">
                            <InputLabel value="Diagnostic / Motif de consultation" />
                            <TextInput v-model="form.meta.diagnosis" class="mt-1 block w-full" placeholder="Motif ou diagnostic principal" />
                        </div>
                    </div>
                </div>

                <!-- ══════════════════════════════════════════════════════════ -->
                <!-- ── TABLE DES LIGNES (si applicable) ───────────────────── -->
                <!-- ══════════════════════════════════════════════════════════ -->
                <div v-if="schema.showLines" class="overflow-hidden rounded-xl bg-white shadow-sm border border-gray-100">
                    <div class="flex items-start gap-2 border-b border-blue-100 bg-blue-50 px-4 py-2.5 text-xs text-blue-700">
                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>
                            <template v-if="schema.extraSection === 'delivery_note'">
                                <strong>Bon de livraison :</strong> Indiquez les articles livrés avec les quantités. Les prix ne sont pas affichés sur ce document.
                            </template>
                            <template v-else-if="schema.extraSection === 'rma'">
                                <strong>Articles retournés :</strong> Listez les produits retournés avec les quantités concernées.
                            </template>
                            <template v-else>
                                <strong>Saisie libre :</strong> Tapez directement votre description sans sélectionner de produit catalogue. La colonne "Produit" est optionnelle.
                            </template>
                        </span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-brand-900 text-left text-xs uppercase tracking-wide text-white">
                                <tr>
                                    <th class="px-3 py-3" style="width:17%">Produit <span class="font-normal opacity-60">(optionnel)</span></th>
                                    <th class="px-3 py-3" style="width:30%">Description *</th>
                                    <th class="px-3 py-3 text-right" style="width:8%">Qté <span class="text-red-400">*</span></th>
                                    <th class="px-3 py-3 text-right" style="width:8%">Unité</th>
                                    <th v-if="schema.showPrices" class="px-3 py-3 text-right" style="width:12%">P.U. HT <span class="text-red-400">*</span></th>
                                    <th v-if="schema.showPrices && schema.showDiscount" class="px-3 py-3 text-right" style="width:11%">Remise</th>
                                    <th v-if="schema.showTax" class="px-3 py-3 text-right" style="width:7%">TVA %</th>
                                    <th v-if="schema.showPrices" class="px-3 py-3 text-right" style="width:11%">Total HT</th>
                                    <th class="px-2 py-3" style="width:4%"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <tr v-for="(line, index) in form.lines" :key="index" class="align-top hover:bg-gray-50/50 transition-colors">
                                    <td class="px-3 py-2">
                                        <select v-model="line.product_id" @change="onProductSelect(line)"
                                            class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                                            <option :value="null">✏ Saisie libre</option>
                                            <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                                        </select>
                                    </td>
                                    <td class="px-3 py-2">
                                        <textarea v-model="line.description" rows="1" required
                                            class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500"></textarea>
                                        <button v-if="aiAvailable && !line.description" type="button" @click="fillDescription(line)" :disabled="aiLoading"
                                            class="mt-1 flex items-center gap-1 text-xs text-purple-600 hover:text-purple-800 disabled:opacity-50">
                                            <span v-if="aiLoading" class="inline-block h-3 w-3 animate-spin rounded-full border border-purple-600 border-t-transparent"></span>
                                            <span v-else>✨</span> Suggestion IA
                                        </button>
                                    </td>
                                    <td class="px-3 py-2">
                                        <input v-model.number="line.quantity" type="number" step="0.01" min="0"
                                            class="block w-full rounded-lg border-gray-300 text-right text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500" />
                                    </td>
                                    <td class="px-3 py-2">
                                        <input v-model="line.unit" type="text" maxlength="15"
                                            class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500" />
                                    </td>
                                    <td v-if="schema.showPrices" class="px-3 py-2">
                                        <input v-model.number="line.unit_price" type="number" step="0.01" min="0"
                                            class="block w-full rounded-lg border-gray-300 text-right text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500" />
                                        <button v-if="aiAvailable && !line.unit_price" type="button" @click="fillPrice(line)" :disabled="aiLoading"
                                            class="mt-1 flex items-center gap-1 text-xs text-green-600 hover:text-green-800 disabled:opacity-50">
                                            <span v-if="aiLoading" class="inline-block h-3 w-3 animate-spin rounded-full border border-green-600 border-t-transparent"></span>
                                            <span v-else>💰</span> Prix IA
                                        </button>
                                    </td>
                                    <td v-if="schema.showPrices && schema.showDiscount" class="px-3 py-2">
                                        <div class="flex items-center gap-1">
                                            <button type="button"
                                                @click="line.line_discount_type = line.line_discount_type === 'percent' ? 'fixed' : 'percent'; line.discount_percent = 0"
                                                class="flex-shrink-0 rounded-md border px-1.5 py-1 text-[10px] font-bold transition-colors"
                                                :class="line.line_discount_type === 'fixed' ? 'border-amber-300 bg-amber-50 text-amber-700' : 'border-gray-200 bg-gray-50 text-gray-600 hover:bg-gray-100'">
                                                {{ line.line_discount_type === 'fixed' ? '€' : '%' }}
                                            </button>
                                            <input v-model.number="line.discount_percent" type="number" step="0.01" min="0"
                                                class="block w-full rounded-lg border-gray-300 text-right text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500" />
                                        </div>
                                    </td>
                                    <td v-if="schema.showTax" class="px-3 py-2">
                                        <input v-model.number="line.tax_rate" type="number" step="0.1" min="0" max="100"
                                            class="block w-full rounded-lg border-gray-300 text-right text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500" />
                                    </td>
                                    <td v-if="schema.showPrices" class="px-3 py-2 text-right font-semibold text-gray-800">{{ fmt(lineTotal(line)) }}</td>
                                    <td class="px-2 py-2 text-center">
                                        <button type="button" @click="removeLine(index)" :disabled="form.lines.length === 1"
                                            class="rounded-full p-1 text-red-400 hover:bg-red-50 hover:text-red-600 disabled:opacity-30 transition-colors">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="flex items-center justify-between border-t px-4 py-3">
                        <button type="button" @click="addLine" class="flex items-center gap-1.5 text-sm font-semibold text-brand-600 hover:text-brand-700">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Ajouter une ligne
                        </button>
                        <span class="text-xs text-gray-400">{{ form.lines.length }} ligne{{ form.lines.length > 1 ? 's' : '' }}</span>
                    </div>
                </div>

                <!-- ── Notes + Récapitulatif ───────────────────────────────── -->
                <div class="grid gap-6 lg:grid-cols-2">
                    <div class="space-y-4 rounded-xl bg-white p-6 shadow-sm border border-gray-100">
                        <div>
                            <InputLabel value="Notes (visibles sur le document)" />
                            <textarea v-model="form.notes" rows="3" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 text-sm"></textarea>
                        </div>
                        <div v-if="!['quittance','payment_receipt','pos_ticket'].includes(form.type)">
                            <InputLabel value="Conditions de paiement" />
                            <textarea v-model="form.terms" rows="3" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 text-sm"></textarea>
                        </div>
                    </div>

                    <!-- Totaux -->
                    <div class="rounded-xl bg-white p-6 shadow-sm border border-gray-100">
                        <h3 class="mb-4 text-sm font-semibold text-gray-700">Récapitulatif</h3>

                        <!-- Remise globale (uniquement si le type la supporte) -->
                        <div v-if="schema.showDiscount" class="mb-5 rounded-lg bg-gray-50 p-3">
                            <p class="mb-2 text-xs font-medium text-gray-600">Remise globale (sur sous-total)</p>
                            <div class="flex items-center gap-2">
                                <select v-model="form.discount_type" class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                                    <option :value="null">Aucune remise</option>
                                    <option value="percent">En pourcentage (%)</option>
                                    <option value="fixed">Montant fixe ({{ form.currency }})</option>
                                </select>
                                <input v-if="form.discount_type" v-model.number="form.discount_value" type="number" step="0.01" min="0"
                                    class="w-28 rounded-lg border-gray-300 text-right text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500" />
                            </div>
                        </div>

                        <dl class="space-y-2 text-sm">
                            <div v-if="schema.showPrices || !schema.showLines" class="flex justify-between">
                                <dt class="text-gray-500">{{ schema.showLines ? 'Sous-total HT' : 'Montant' }}</dt>
                                <dd class="font-semibold">{{ fmt(subtotal) }} {{ form.currency }}</dd>
                            </div>
                            <div v-if="discountAmount > 0" class="flex justify-between text-red-600">
                                <dt>Remise</dt>
                                <dd>−{{ fmt(discountAmount) }} {{ form.currency }}</dd>
                            </div>
                            <div v-if="schema.showTax" class="flex justify-between">
                                <dt class="text-gray-500">TVA</dt>
                                <dd class="font-semibold">{{ fmt(taxAmount) }} {{ form.currency }}</dd>
                            </div>
                            <div class="flex justify-between rounded-lg bg-brand-900 px-3 py-2.5 text-base text-white">
                                <dt class="font-bold">TOTAL TTC</dt>
                                <dd class="font-bold">{{ fmt(total) }} {{ form.currency }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- ── Actions ────────────────────────────────────────────── -->
                <div class="flex justify-end gap-3">
                    <Link :href="isEdit ? route('documents.show', document.id) : route('documents.index')">
                        <SecondaryButton type="button">Annuler</SecondaryButton>
                    </Link>
                    <PrimaryButton :disabled="form.processing" class="flex items-center gap-2">
                        <span v-if="form.processing" class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                        {{ isEdit ? 'Enregistrer les modifications' : 'Créer le document' }}
                    </PrimaryButton>
                </div>

            </form>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity .2s ease; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }
</style>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { Head, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const CATS = [
  { id: 'vente', label: 'Vente & Facturation', icon: '💰', color: '#2563EB', docs: [
    { id: 'devis',       factproType: 'quote',             name: 'Devis',                    icon: '📋', desc: 'Proposition de prix détaillée et professionnelle',          pop: true  },
    { id: 'offre',       factproType: 'quote',             name: 'Offre Commerciale',        icon: '🤝', desc: 'Présentation d\'offre percutante avec argumentaire',         pop: false },
    { id: 'proposition', factproType: 'quote',             name: 'Proposition Commerciale',  icon: '💼', desc: 'Proposition complète et structurée',                        pop: false },
    { id: 'proforma',    factproType: 'proforma_invoice',  name: 'Facture Proforma',         icon: '📄', desc: 'Facture prévisionnelle avant commande officielle',           pop: true  },
    { id: 'bc_client',   factproType: 'purchase_order',    name: 'Bon de Commande Client',   icon: '🛒', desc: 'Confirmation officielle des commandes reçues',               pop: true  },
    { id: 'bon_resa',    factproType: 'quote',             name: 'Bon de Réservation',       icon: '📅', desc: 'Confirmation de réservation produit ou service',             pop: false },
    { id: 'bon_prep',    factproType: 'delivery_note',     name: 'Bon de Préparation',       icon: '📦', desc: 'Ordre interne de préparation de commande',                  pop: false },
    { id: 'bon_liv',     factproType: 'delivery_note',     name: 'Bon de Livraison',         icon: '🚚', desc: 'Attestation officielle de livraison marchandises',          pop: true  },
    { id: 'ordre_liv',   factproType: 'delivery_note',     name: 'Ordre de Livraison',       icon: '📮', desc: 'Autorisation de libération des marchandises',               pop: false },
    { id: 'facture',     factproType: 'invoice',           name: 'Facture',                  icon: '🧾', desc: 'Facture commerciale standard — normes OHADA',               pop: true  },
    { id: 'fac_simple',  factproType: 'invoice',           name: 'Facture Simplifiée',       icon: '🗒️', desc: 'Facture allégée pour petits montants',                      pop: false },
    { id: 'fac_export',  factproType: 'invoice',           name: 'Facture Export',           icon: '🌍', desc: 'Facture pour transactions internationales',                 pop: false },
    { id: 'fac_exo',     factproType: 'invoice',           name: 'Facture Exonérée TVA',     icon: '🔖', desc: 'Facture sans TVA pour entreprises exonérées',               pop: false },
    { id: 'fac_rect',    factproType: 'credit_note',       name: 'Facture Rectificative',    icon: '✏️', desc: 'Correction d\'une facture déjà émise',                      pop: false },
    { id: 'fac_acompte', factproType: 'advance_invoice',   name: 'Facture d\'Acompte',       icon: '💸', desc: 'Facturation d\'acompte sur commande en cours',              pop: true  },
    { id: 'fac_solde',   factproType: 'balance_invoice',   name: 'Facture de Solde',         icon: '✔️', desc: 'Solde de facturation après versement d\'acompte',           pop: false },
    { id: 'avoir',       factproType: 'credit_note',       name: 'Avoir / Note de Crédit',   icon: '↩️', desc: 'Note de crédit pour remboursement client',                  pop: false },
    { id: 'recu',        factproType: 'receipt',           name: 'Reçu de Paiement',         icon: '✅', desc: 'Confirmation officielle de réception de paiement',          pop: true  },
    { id: 'ticket',      factproType: 'receipt',           name: 'Ticket de Caisse',         icon: '🖨️', desc: 'Reçu de vente au comptant simplifié',                       pop: false },
    { id: 'contrat_com', factproType: 'invoice',           name: 'Contrat Commercial',       icon: '📝', desc: 'Accord contractuel entre parties commerciales',             pop: false },
  ]},
  { id: 'achat', label: 'Achats & Fournisseurs', icon: '🏪', color: '#7C3AED', docs: [
    { id: 'dem_achat',      factproType: 'supplier_order', name: 'Demande d\'Achat',             icon: '📨', desc: 'Demande interne d\'approvisionnement',             pop: false },
    { id: 'dem_prix',       factproType: 'quote',          name: 'Demande de Prix',              icon: '💬', desc: 'Consultation de prix auprès des fournisseurs',     pop: false },
    { id: 'consult_f',      factproType: 'quote',          name: 'Consultation Fournisseur',     icon: '📞', desc: 'Appel d\'offres fournisseur structuré',            pop: false },
    { id: 'bc_f',           factproType: 'supplier_order', name: 'Bon de Commande Fournisseur',  icon: '📮', desc: 'Commande officielle passée à un fournisseur',      pop: true  },
    { id: 'br_f',           factproType: 'goods_receipt',  name: 'Bon de Réception',             icon: '📥', desc: 'Réception de marchandises fournisseur',            pop: true  },
    { id: 'fac_f',          factproType: 'invoice',        name: 'Facture Fournisseur',          icon: '🧾', desc: 'Enregistrement d\'une facture fournisseur',        pop: false },
    { id: 'avoir_f',        factproType: 'credit_note',    name: 'Avoir Fournisseur',            icon: '↩️', desc: 'Note de crédit reçue d\'un fournisseur',           pop: false },
    { id: 'retour_f',       factproType: 'delivery_note',  name: 'Bon de Retour Fournisseur',    icon: '🔄', desc: 'Retour de marchandises au fournisseur',             pop: false },
    { id: 'note_debit',     factproType: 'invoice',        name: 'Note de Débit',                icon: '📊', desc: 'Débit complémentaire sur une facture existante',   pop: false },
    { id: 'note_credit_f',  factproType: 'credit_note',    name: 'Note de Crédit Fournisseur',   icon: '📊', desc: 'Crédit accordé par le fournisseur',                pop: false },
  ]},
  { id: 'stock', label: 'Stocks & Inventaire', icon: '📦', color: '#D97706', docs: [
    { id: 'be',           factproType: 'goods_receipt',  name: 'Bon d\'Entrée de Stock',       icon: '⬆️', desc: 'Enregistrement d\'une entrée en stock',           pop: false },
    { id: 'bs',           factproType: 'delivery_note',  name: 'Bon de Sortie de Stock',       icon: '⬇️', desc: 'Enregistrement d\'une sortie de stock',           pop: false },
    { id: 'transfert',    factproType: 'delivery_note',  name: 'Bon de Transfert',             icon: '↔️', desc: 'Transfert de stock entre entrepôts',              pop: false },
    { id: 'consommation', factproType: 'delivery_note',  name: 'Bon de Consommation',          icon: '🔧', desc: 'Consommation interne de matières ou produits',    pop: false },
    { id: 'inventaire',   factproType: 'invoice',        name: 'Bon d\'Inventaire',            icon: '📊', desc: 'Fiche de comptage et valorisation du stock',      pop: true  },
    { id: 'ajustement',   factproType: 'invoice',        name: 'Ajustement de Stock',          icon: '⚖️', desc: 'Correction des écarts constatés à l\'inventaire', pop: false },
    { id: 'destruction',  factproType: 'invoice',        name: 'Bon de Destruction / Casse',   icon: '🗑️', desc: 'Mise au rebut de stock détérioré ou obsolète',    pop: false },
    { id: 'of',           factproType: 'invoice',        name: 'Ordre de Fabrication',         icon: '🏭', desc: 'Lancement d\'un ordre de production',             pop: false },
    { id: 'transform',    factproType: 'invoice',        name: 'Bon de Transformation',        icon: '🔄', desc: 'Transformation de produits en stock',             pop: false },
  ]},
  { id: 'sav', label: 'SAV & Maintenance', icon: '🔧', color: '#0891B2', docs: [
    { id: 'rma',          factproType: 'service_report', name: 'Bon de Retour RMA',       icon: '📦', desc: 'Retour de produit en garantie client',                pop: false },
    { id: 'fiche_sav',    factproType: 'service_report', name: 'Fiche SAV',               icon: '🔧', desc: 'Dossier complet de service après-vente',             pop: true  },
    { id: 'bon_rep',      factproType: 'service_report', name: 'Bon de Réparation',       icon: '⚙️', desc: 'Ordre de réparation d\'équipement ou matériel',      pop: true  },
    { id: 'rapport_int',  factproType: 'service_report', name: 'Rapport d\'Intervention', icon: '📋', desc: 'Compte-rendu d\'intervention technique',             pop: true  },
    { id: 'bon_maint',    factproType: 'service_report', name: 'Bon de Maintenance',      icon: '🛠️', desc: 'Ordre de maintenance préventive ou curative',        pop: false },
    { id: 'cert_gar',     factproType: 'invoice',        name: 'Certificat de Garantie',  icon: '🏅', desc: 'Attestation de garantie produit ou service',         pop: false },
    { id: 'contrat_maint',factproType: 'invoice',        name: 'Contrat de Maintenance',  icon: '📝', desc: 'Contrat de maintenance périodique',                  pop: false },
  ]},
  { id: 'btp', label: 'BTP & Travaux', icon: '🏗️', color: '#DC2626', docs: [
    { id: 'bon_trav',    factproType: 'invoice',         name: 'Bon de Travaux',               icon: '🔨', desc: 'Ordre d\'exécution de travaux de construction',   pop: true  },
    { id: 'os',          factproType: 'invoice',         name: 'Ordre de Service',             icon: '📋', desc: 'Instruction officielle de démarrage de chantier', pop: true  },
    { id: 'situation',   factproType: 'advance_invoice', name: 'Situation de Travaux',         icon: '📐', desc: 'Facturation d\'avancement de chantier',           pop: true  },
    { id: 'decompte_p',  factproType: 'advance_invoice', name: 'Décompte Provisoire',          icon: '🔢', desc: 'Décompte intermédiaire des travaux réalisés',     pop: false },
    { id: 'decompte_d',  factproType: 'balance_invoice', name: 'Décompte Définitif',           icon: '✔️', desc: 'Décompte final en fin de chantier',               pop: false },
    { id: 'pv_prov',     factproType: 'invoice',         name: 'PV Réception Provisoire',      icon: '📋', desc: 'Réception provisoire des travaux achevés',        pop: true  },
    { id: 'pv_def',      factproType: 'invoice',         name: 'PV Réception Définitive',      icon: '🏆', desc: 'Réception définitive — fin de période de garantie',pop: true  },
    { id: 'rapport_ch',  factproType: 'service_report',  name: 'Rapport de Chantier',          icon: '📊', desc: 'Compte-rendu journalier ou hebdo de chantier',    pop: false },
  ]},
  { id: 'logistique', label: 'Logistique & Transport', icon: '🚛', color: '#059669', docs: [
    { id: 'bon_exp',     factproType: 'delivery_note', name: 'Bon d\'Expédition',         icon: '📤', desc: 'Bon d\'envoi officiel de marchandises',              pop: true  },
    { id: 'lettre_v',    factproType: 'delivery_note', name: 'Lettre de Voiture',         icon: '📜', desc: 'Document de transport routier officiel',             pop: true  },
    { id: 'packing',     factproType: 'delivery_note', name: 'Packing List',              icon: '📋', desc: 'Liste de colisage pour expédition internationale',   pop: true  },
    { id: 'bord_ch',     factproType: 'delivery_note', name: 'Bordereau de Chargement',   icon: '📊', desc: 'Récapitulatif détaillé du chargement véhicule',     pop: false },
    { id: 'transfert_d', factproType: 'delivery_note', name: 'Transfert Inter-Dépôts',    icon: '↔️', desc: 'Transfert de stock entre dépôts et entrepôts',       pop: false },
    { id: 'manifeste',   factproType: 'delivery_note', name: 'Manifeste de Livraison',    icon: '📊', desc: 'Récapitulatif de toutes les livraisons effectuées',  pop: false },
  ]},
  { id: 'finance', label: 'Finance & Trésorerie', icon: '💳', color: '#0284C7', docs: [
    { id: 'bord_rem',     factproType: 'receipt',        name: 'Bordereau de Remise',          icon: '📄', desc: 'Remise de chèques ou effets en banque',              pop: false },
    { id: 'note_frais',   factproType: 'expense_report', name: 'Note de Frais',                icon: '🧾', desc: 'Remboursement de frais professionnels engagés',      pop: true  },
    { id: 'bon_caisse',   factproType: 'cash_voucher',   name: 'Bon de Caisse',                icon: '💵', desc: 'Mouvement d\'entrée ou sortie de caisse',           pop: true  },
    { id: 'depot_banc',   factproType: 'receipt',        name: 'Bordereau de Dépôt Bancaire',  icon: '🏦', desc: 'Dépôt de fonds en agence bancaire',                 pop: false },
    { id: 'retrait_banc', factproType: 'receipt',        name: 'Bon de Retrait Bancaire',      icon: '🏧', desc: 'Retrait de fonds en agence bancaire',               pop: false },
    { id: 'effet',        factproType: 'invoice',        name: 'Effet de Commerce / Traite',   icon: '📄', desc: 'Lettre de change — instrument de paiement différé', pop: false },
    { id: 'billet_ordre', factproType: 'invoice',        name: 'Billet à Ordre',               icon: '📝', desc: 'Engagement de paiement à une date fixée',           pop: false },
  ]},
  { id: 'rh', label: 'Ressources Humaines', icon: '👥', color: '#7C3AED', docs: [
    { id: 'ordre_miss',  factproType: 'service_report', name: 'Ordre de Mission',         icon: '✈️', desc: 'Autorisation officielle de déplacement professionnel', pop: true  },
    { id: 'dem_conge',   factproType: 'invoice',        name: 'Demande de Congé',         icon: '🏖️', desc: 'Formulaire de demande de congé payé',                 pop: true  },
    { id: 'bulletin',    factproType: 'payslip',        name: 'Bulletin de Paie',         icon: '💰', desc: 'Fiche de salaire mensuelle détaillée',                pop: true  },
    { id: 'avance_sal',  factproType: 'advance_invoice',name: 'Avance sur Salaire',       icon: '💵', desc: 'Demande d\'avance sur rémunération mensuelle',        pop: false },
    { id: 'note_serv',   factproType: 'invoice',        name: 'Note de Service',          icon: '📢', desc: 'Communication interne officielle de direction',       pop: true  },
    { id: 'auto_abs',    factproType: 'invoice',        name: 'Autorisation d\'Absence',  icon: '📝', desc: 'Autorisation d\'absence exceptionnelle motivée',      pop: false },
  ]},
  { id: 'admin', label: 'Administratif & Juridique', icon: '⚖️', color: '#4F46E5', docs: [
    { id: 'contrat',   factproType: 'invoice',        name: 'Contrat',               icon: '📝', desc: 'Contrat commercial ou de prestation de services', pop: true  },
    { id: 'pv_reun',   factproType: 'service_report', name: 'Procès-Verbal',         icon: '📋', desc: 'PV de réunion, d\'assemblée ou de décision',     pop: true  },
    { id: 'attest',    factproType: 'invoice',        name: 'Attestation',           icon: '🏅', desc: 'Attestation officielle toutes natures',           pop: true  },
    { id: 'certif',    factproType: 'invoice',        name: 'Certificat',            icon: '🎓', desc: 'Certificat professionnel ou de conformité',       pop: false },
    { id: 'relance',   factproType: 'invoice',        name: 'Lettre de Relance',     icon: '📩', desc: 'Relance amiable d\'un impayé ou d\'un document',  pop: true  },
    { id: 'mise_dem',  factproType: 'invoice',        name: 'Mise en Demeure',       icon: '⚠️', desc: 'Mise en demeure formelle avant contentieux',      pop: false },
    { id: 'accuse',    factproType: 'invoice',        name: 'Accusé de Réception',   icon: '✅', desc: 'Confirmation officielle de réception de document', pop: false },
    { id: 'autoris',   factproType: 'invoice',        name: 'Autorisation',          icon: '🔓', desc: 'Autorisation administrative ou opérationnelle',    pop: false },
  ]},
  { id: 'immobilier', label: 'Immobilier & Location', icon: '🏠', color: '#DB2777', docs: [
    { id: 'bail',        factproType: 'invoice', name: 'Contrat de Bail',            icon: '🔑', desc: 'Contrat de location immobilière résidentiel ou commercial', pop: true  },
    { id: 'edle',        factproType: 'invoice', name: 'État des Lieux d\'Entrée',   icon: '🏡', desc: 'Constat d\'état à l\'entrée du locataire',                 pop: true  },
    { id: 'edls',        factproType: 'invoice', name: 'État des Lieux de Sortie',   icon: '🚪', desc: 'Constat d\'état au départ du locataire',                   pop: true  },
    { id: 'appel_l',     factproType: 'invoice', name: 'Appel de Loyer',             icon: '💳', desc: 'Avis mensuel de loyer et charges à régler',               pop: true  },
    { id: 'quittance_l', factproType: 'receipt', name: 'Quittance de Loyer',         icon: '✅', desc: 'Reçu de paiement de loyer mensuel',                       pop: true  },
  ]},
  { id: 'export', label: 'Export & Douane', icon: '🌍', color: '#B45309', docs: [
    { id: 'cert_orig',  factproType: 'invoice',       name: 'Certificat d\'Origine',   icon: '📜', desc: 'Attestation de l\'origine des marchandises exportées', pop: true  },
    { id: 'decl_d',     factproType: 'invoice',       name: 'Déclaration Douanière',   icon: '🏛️', desc: 'Déclaration en douane import ou export',             pop: true  },
    { id: 'bon_emb',    factproType: 'delivery_note', name: 'Bon d\'Embarquement',     icon: '🚢', desc: 'Autorisation d\'embarquement marchandises',           pop: false },
    { id: 'fac_exp_d',  factproType: 'invoice',       name: 'Facture Export (Douane)', icon: '🧾', desc: 'Facture conforme aux exigences douanières',           pop: true  },
  ]},
  { id: 'sante', label: 'Santé & Médical', icon: '🏥', color: '#0891B2', docs: [
    { id: 'fac_med',   factproType: 'invoice', name: 'Facture Médicale',   icon: '🏥', desc: 'Facture de consultations et soins médicaux',             pop: true  },
    { id: 'ordo',      factproType: 'invoice', name: 'Ordonnance',         icon: '💊', desc: 'Prescription médicale du praticien',                     pop: true  },
    { id: 'bon_labo',  factproType: 'invoice', name: 'Bon de Laboratoire', icon: '🔬', desc: 'Prescription d\'analyses biologiques',                   pop: false },
    { id: 'feuille_s', factproType: 'invoice', name: 'Feuille de Soins',   icon: '📋', desc: 'Feuille de soins pour remboursement assurance',          pop: false },
  ]},
  { id: 'education', label: 'Éducation & Formation', icon: '🎓', color: '#7C3AED', docs: [
    { id: 'recu_scol',   factproType: 'receipt', name: 'Reçu de Scolarité',      icon: '🏫', desc: 'Reçu de paiement de frais de scolarité',                pop: true  },
    { id: 'fac_form',    factproType: 'invoice', name: 'Facture de Formation',    icon: '📚', desc: 'Facture de prestation de formation professionnelle',    pop: true  },
    { id: 'attest_paie', factproType: 'receipt', name: 'Attestation de Paiement', icon: '✅', desc: 'Attestation de règlement de frais scolaires',          pop: true  },
    { id: 'bul_notes',   factproType: 'invoice', name: 'Bulletin de Notes',       icon: '📝', desc: 'Bulletin scolaire trimestriel ou semestriel',          pop: false },
  ]},
]

const ALL_DOCS = CATS.flatMap(c => c.docs.map(d => ({ ...d, catId: c.id, catLabel: c.label, catColor: c.color, catIcon: c.icon })))

const search  = ref('')
const selCat  = ref(null)
const preview = ref(null)
const sidebarOpen = ref(false)

const filtered = computed(() => {
  return ALL_DOCS.filter(d => {
    const matchCat = !selCat.value || d.catId === selCat.value
    const q = search.value.toLowerCase()
    const matchSearch = !q || [d.name, d.desc, d.catLabel].some(x => x.toLowerCase().includes(q))
    return matchCat && matchSearch
  })
})

const popularDocs = computed(() => ALL_DOCS.filter(d => d.pop).slice(0, 8))

function openPreview(doc) { preview.value = doc }
function closePreview()   { preview.value = null }
function createDoc(doc)   { router.visit(route('documents.create', { type: doc.factproType })) }
function selectCat(id)    { selCat.value = id; sidebarOpen.value = false }

function previewHTML(doc) {
  return `<!DOCTYPE html><html><head><meta charset="UTF-8"><style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Segoe UI',sans-serif;color:#1e293b;background:#fff;font-size:13px;padding:32px}
.h{display:flex;justify-content:space-between;align-items:flex-start;padding-bottom:18px;border-bottom:3px solid ${doc.catColor};margin-bottom:24px}
.log{width:46px;height:46px;background:linear-gradient(135deg,#1E3A5F,#2563EB);border-radius:9px;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:17px;margin-bottom:8px}
.cn{font-size:16px;font-weight:800;color:#1E3A5F}.ci{font-size:11px;color:#64748b;margin-top:5px;line-height:1.7}
.dt{font-size:22px;font-weight:800;color:${doc.catColor};text-align:right;text-transform:uppercase}
.dr{text-align:right;font-size:11px;color:#64748b;margin-top:5px;line-height:1.7}
.bdg{display:inline-block;padding:3px 11px;border-radius:20px;font-size:9px;font-weight:700;background:${doc.catColor}22;color:${doc.catColor};margin-top:6px}
.pts{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:22px}
.pt{background:#f8fafc;border-radius:8px;padding:13px;border-left:3px solid #3b82f6}.pt.r{border-left-color:#10b981}
.pl{font-size:9px;text-transform:uppercase;letter-spacing:.1em;color:#94a3b8;font-weight:700;margin-bottom:5px}
.pn{font-size:14px;font-weight:700}.pi{font-size:11px;color:#64748b;margin-top:3px;line-height:1.5}
table{width:100%;border-collapse:collapse;margin-bottom:14px}
th{background:#1E3A5F;color:#fff;padding:9px 11px;text-align:left;font-size:10px;font-weight:700;text-transform:uppercase}
th:last-child,td:last-child{text-align:right}
td{padding:9px 11px;border-bottom:1px solid #e2e8f0;font-size:12px}
tr:nth-child(even) td{background:#f8fafc}
.tw{display:flex;justify-content:flex-end;margin-bottom:14px}
.tots{width:270px}.tr-{display:flex;justify-content:space-between;padding:7px 13px;font-size:12px;border-bottom:1px solid #e2e8f0}
.tf{display:flex;justify-content:space-between;padding:11px 13px;font-size:15px;font-weight:800;color:#fff;background:#1E3A5F;border-radius:0 0 7px 7px}
.sg{display:flex;justify-content:space-between;margin-top:30px;gap:16px}
.s{flex:1;border:1px dashed #cbd5e1;border-radius:7px;padding:10px 14px;text-align:center;font-size:10px;color:#94a3b8}
.sp{height:46px}
.ft{margin-top:24px;padding-top:12px;border-top:1px solid #e2e8f0;text-align:center;font-size:10px;color:#94a3b8}
</style></head><body>
<div class="h">
<div>
  <div class="log">VS</div>
  <div class="cn">VOTRE SOCIÉTÉ SARL</div>
  <div class="ci">📍 Plateau, Abidjan 01, Côte d'Ivoire<br>📞 +225 27 22 33 44 55<br>✉️ contact@votresociete.ci · RCCM: CI-ABJ-2024-B-12345</div>
</div>
<div>
  <div class="dt">${doc.name}</div>
  <div class="dr">Réf. N° <b>2026-0001</b><br>Date: <b>${new Date().toLocaleDateString('fr-FR')}</b><br>Échéance: <b>30 jours</b></div>
  <div class="bdg">Aperçu modèle</div>
</div>
</div>
<div class="pts">
<div class="pt"><div class="pl">Émetteur</div><div class="pn">VOTRE SOCIÉTÉ SARL</div><div class="pi">Plateau, Abidjan<br>RCCM: CI-ABJ-2024-B-12345</div></div>
<div class="pt r"><div class="pl">Client / Destinataire</div><div class="pn">CLIENT EXEMPLE & ASSOCIÉS</div><div class="pi">Cocody Riviera 3, Abidjan<br>+225 05 00 11 22 33</div></div>
</div>
<table><thead><tr><th>#</th><th>Description</th><th>Qté</th><th>P.U. HT</th><th>Total HT</th></tr></thead>
<tbody>
<tr><td>01</td><td>Prestation de conseil et expertise professionnelle</td><td>5 j</td><td>200 000 XOF</td><td>1 000 000 XOF</td></tr>
<tr><td>02</td><td>Fournitures et matériaux</td><td>10 u</td><td>35 000 XOF</td><td>350 000 XOF</td></tr>
<tr><td>03</td><td>Transport et logistique — forfait</td><td>1</td><td>85 000 XOF</td><td>85 000 XOF</td></tr>
</tbody></table>
<div class="tw"><div class="tots">
<div class="tr-"><span style="color:#64748b">Sous-total HT</span><span>1 435 000 XOF</span></div>
<div class="tr-"><span style="color:#64748b">TVA 18%</span><span>258 300 XOF</span></div>
<div class="tf"><span>TOTAL TTC</span><span>1 693 300 XOF</span></div>
</div></div>
<div class="sg">
<div class="s"><div class="sp"></div>Signature du Client<br><em>Bon pour accord</em></div>
<div class="s"><div class="sp"></div>Cachet & Signature<br><em>Émetteur</em></div>
</div>
<div class="ft">Généré par <b style="color:#1E3A5F">IBIG FactPro</b> · ibigsoft.com · Aperçu de démonstration</div>
</body></html>`
}
</script>

<template>
  <Head title="Catalogue de documents — IBIG FactPro" />
  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between gap-4">
        <div>
          <h2 class="text-xl font-bold text-gray-800">Catalogue de documents</h2>
          <p class="text-sm text-gray-500 mt-0.5">{{ ALL_DOCS.length }} modèles · {{ CATS.length }} catégories · Normes OHADA</p>
        </div>
        <Link
          :href="route('documents.create')"
          class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-bold text-white shadow-md transition hover:shadow-lg hover:brightness-110 shrink-0"
          style="background:linear-gradient(135deg,#1E3A5F 0%,#2563EB 100%)"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
          </svg>
          Nouveau document
        </Link>
      </div>
    </template>

    <!-- ═══ HERO SEARCH ═══ -->
    <div class="relative overflow-hidden" style="background:linear-gradient(135deg,#0f2356 0%,#1E3A5F 40%,#2563EB 100%)">
      <!-- Decorative circles -->
      <div class="absolute -top-12 -right-12 h-48 w-48 rounded-full opacity-10" style="background:#fff"></div>
      <div class="absolute -bottom-8 -left-8 h-32 w-32 rounded-full opacity-10" style="background:#60a5fa"></div>

      <div class="relative mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <!-- Stats row -->
        <div class="mb-6 flex flex-wrap items-center gap-4 sm:gap-8">
          <div class="text-white">
            <span class="text-3xl font-black">{{ ALL_DOCS.length }}</span>
            <span class="ml-1.5 text-sm font-medium text-blue-200">modèles</span>
          </div>
          <div class="hidden h-8 w-px bg-white/20 sm:block"></div>
          <div class="text-white">
            <span class="text-3xl font-black">{{ CATS.length }}</span>
            <span class="ml-1.5 text-sm font-medium text-blue-200">catégories</span>
          </div>
          <div class="hidden h-8 w-px bg-white/20 sm:block"></div>
          <div class="text-white">
            <span class="text-sm font-medium text-blue-200">✅ Conformes OHADA</span>
          </div>
        </div>

        <!-- Search bar -->
        <div class="relative max-w-2xl">
          <svg class="absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"/><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35"/>
          </svg>
          <input
            v-model="search"
            type="text"
            placeholder="Rechercher un document (facture, devis, contrat de bail, bulletin de paie…)"
            class="w-full rounded-2xl border-0 bg-white/95 py-4 pl-12 pr-4 text-sm font-medium text-gray-700 shadow-xl backdrop-blur transition focus:bg-white focus:outline-none focus:ring-4 focus:ring-white/30"
          />
          <button
            v-if="search"
            class="absolute right-3 top-1/2 -translate-y-1/2 rounded-full bg-gray-100 p-1.5 text-gray-400 hover:text-gray-600 transition"
            @click="search = ''"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- ═══ CATEGORY CHIPS (mobile horizontal scroll) ═══ -->
    <div class="sticky top-0 z-20 border-b border-gray-100 bg-white shadow-sm">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-2 overflow-x-auto py-3 scrollbar-none">
          <button
            :class="['shrink-0 rounded-full px-3.5 py-1.5 text-xs font-semibold transition-all',
                     !selCat ? 'bg-blue-600 text-white shadow-md' : 'bg-gray-100 text-gray-600 hover:bg-gray-200']"
            @click="selectCat(null)"
          >
            🗂️ Tous ({{ ALL_DOCS.length }})
          </button>
          <button
            v-for="cat in CATS"
            :key="cat.id"
            :class="['shrink-0 rounded-full px-3.5 py-1.5 text-xs font-semibold transition-all',
                     selCat === cat.id ? 'text-white shadow-md' : 'bg-gray-100 text-gray-600 hover:bg-gray-200']"
            :style="selCat === cat.id ? `background:${cat.color}` : ''"
            @click="selectCat(cat.id)"
          >
            {{ cat.icon }} {{ cat.label }} ({{ cat.docs.length }})
          </button>
        </div>
      </div>
    </div>

    <!-- ═══ MAIN CONTENT ═══ -->
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8" style="background:#F0F4FB;min-height:70vh">

      <!-- Populaires (shown only when no filter) -->
      <template v-if="!selCat && !search">
        <div class="mb-8">
          <div class="mb-4 flex items-center justify-between">
            <h3 class="text-base font-bold text-gray-800">⭐ Modèles populaires</h3>
            <span class="text-xs text-gray-400">Les plus utilisés</span>
          </div>
          <div class="grid gap-3" style="grid-template-columns:repeat(auto-fill,minmax(180px,1fr))">
            <div
              v-for="doc in popularDocs"
              :key="'pop_' + doc.id"
              class="catalog-card group flex cursor-pointer flex-col rounded-2xl bg-white p-4 shadow-sm transition-all duration-200"
              @click="createDoc(doc)"
            >
              <div class="mb-3 flex items-center justify-between">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl text-xl"
                     :style="`background:${doc.catColor}15`">{{ doc.icon }}</div>
                <span class="rounded-full bg-amber-50 px-2 py-0.5 text-[9px] font-bold text-amber-600">⭐ TOP</span>
              </div>
              <p class="text-[9px] font-bold uppercase tracking-wider mb-1" :style="`color:${doc.catColor}`">{{ doc.catLabel }}</p>
              <p class="text-sm font-bold text-gray-800 leading-tight mb-3 flex-1">{{ doc.name }}</p>
              <div class="flex items-center gap-1.5">
                <button
                  class="flex-none rounded-lg border border-gray-200 px-2.5 py-1.5 text-[10px] font-semibold text-gray-600 hover:bg-gray-50 transition"
                  @click.stop="openPreview(doc)"
                >👁️</button>
                <button
                  class="flex-1 rounded-lg py-1.5 text-[10px] font-bold text-white transition hover:brightness-110"
                  :style="`background:${doc.catColor}`"
                  @click.stop="createDoc(doc)"
                >Créer</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Divider -->
        <div class="mb-6 flex items-center gap-3">
          <div class="h-px flex-1 bg-gray-200"></div>
          <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Tous les modèles</span>
          <div class="h-px flex-1 bg-gray-200"></div>
        </div>
      </template>

      <!-- Results header -->
      <div class="mb-4 flex items-center justify-between">
        <p class="text-sm text-gray-600">
          <strong class="text-gray-800">{{ filtered.length }}</strong>
          modèle{{ filtered.length > 1 ? 's' : '' }}
          <span v-if="selCat" class="font-medium"> · {{ CATS.find(c => c.id === selCat)?.label }}</span>
          <span v-if="search" class="font-medium"> · « {{ search }} »</span>
        </p>
        <button
          v-if="selCat || search"
          class="text-xs font-semibold text-blue-600 hover:text-blue-800 transition"
          @click="selCat = null; search = ''"
        >
          ✕ Effacer filtres
        </button>
      </div>

      <!-- ─── Category sections (when no filter) ─── -->
      <template v-if="!selCat && !search">
        <div v-for="cat in CATS" :key="cat.id" class="mb-10">
          <!-- Category header -->
          <div class="mb-4 flex items-center gap-3">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl text-lg shrink-0"
                 :style="`background:${cat.color}18`">{{ cat.icon }}</div>
            <div class="flex-1 min-w-0">
              <h4 class="text-sm font-bold text-gray-800">{{ cat.label }}</h4>
              <p class="text-xs text-gray-400">{{ cat.docs.length }} modèles</p>
            </div>
            <div class="h-px flex-1 bg-gray-200 hidden sm:block"></div>
          </div>

          <div class="grid gap-3" style="grid-template-columns:repeat(auto-fill,minmax(200px,1fr))">
            <div
              v-for="doc in cat.docs"
              :key="doc.id"
              class="catalog-card group flex flex-col rounded-2xl bg-white p-4 shadow-sm transition-all duration-200 cursor-default"
              :data-color="cat.color"
              @mouseenter="e => { e.currentTarget.style.boxShadow=`0 8px 25px ${cat.color}25`; e.currentTarget.style.transform='translateY(-2px)' }"
              @mouseleave="e => { e.currentTarget.style.boxShadow=''; e.currentTarget.style.transform='' }"
            >
              <div class="mb-3 flex items-start justify-between">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl text-xl shrink-0"
                     :style="`background:${cat.color}15`">{{ doc.icon }}</div>
                <span v-if="doc.pop" class="rounded-full bg-amber-50 px-2 py-0.5 text-[9px] font-bold text-amber-600 shrink-0">⭐ Pop</span>
              </div>
              <p class="mb-1 text-[10px] font-bold text-gray-800 leading-snug flex-1">{{ doc.name }}</p>
              <p class="mb-3 text-[11px] leading-relaxed text-gray-400 line-clamp-2">{{ doc.desc }}</p>
              <div class="flex gap-1.5">
                <button
                  class="flex-none rounded-xl border border-gray-200 px-3 py-1.5 text-[10px] font-semibold text-gray-600 transition hover:border-gray-300 hover:bg-gray-50"
                  @click="openPreview(doc)"
                >👁️ Aperçu</button>
                <button
                  class="flex-1 rounded-xl py-1.5 text-[10px] font-bold text-white transition hover:brightness-110 hover:shadow-md"
                  :style="`background:${cat.color}`"
                  @click="createDoc(doc)"
                >Créer le document</button>
              </div>
            </div>
          </div>
        </div>
      </template>

      <!-- ─── Flat grid (filtered) ─── -->
      <template v-else>
        <div class="grid gap-3" style="grid-template-columns:repeat(auto-fill,minmax(200px,1fr))">
          <div
            v-for="doc in filtered"
            :key="doc.catId + '__' + doc.id"
            class="catalog-card group flex flex-col rounded-2xl bg-white p-4 shadow-sm transition-all duration-200 cursor-default"
            @mouseenter="e => { e.currentTarget.style.boxShadow=`0 8px 25px ${doc.catColor}25`; e.currentTarget.style.transform='translateY(-2px)' }"
            @mouseleave="e => { e.currentTarget.style.boxShadow=''; e.currentTarget.style.transform='' }"
          >
            <div class="mb-3 flex items-start justify-between">
              <div class="flex h-11 w-11 items-center justify-center rounded-xl text-xl shrink-0"
                   :style="`background:${doc.catColor}15`">{{ doc.icon }}</div>
              <span v-if="doc.pop" class="rounded-full bg-amber-50 px-2 py-0.5 text-[9px] font-bold text-amber-600 shrink-0">⭐ Pop</span>
            </div>
            <p class="mb-0.5 text-[9px] font-bold uppercase tracking-wider" :style="`color:${doc.catColor}`">{{ doc.catIcon }} {{ doc.catLabel }}</p>
            <p class="mb-1 text-[11px] font-bold text-gray-800 leading-snug flex-1">{{ doc.name }}</p>
            <p class="mb-3 text-[11px] leading-relaxed text-gray-400 line-clamp-2">{{ doc.desc }}</p>
            <div class="flex gap-1.5">
              <button
                class="flex-none rounded-xl border border-gray-200 px-3 py-1.5 text-[10px] font-semibold text-gray-600 transition hover:bg-gray-50"
                @click="openPreview(doc)"
              >👁️ Aperçu</button>
              <button
                class="flex-1 rounded-xl py-1.5 text-[10px] font-bold text-white transition hover:brightness-110 hover:shadow-md"
                :style="`background:${doc.catColor}`"
                @click="createDoc(doc)"
              >Créer le document</button>
            </div>
          </div>

          <!-- Aucun résultat -->
          <div v-if="filtered.length === 0" class="col-span-full py-20 text-center">
            <div class="mb-4 text-6xl">🔍</div>
            <p class="text-lg font-bold text-gray-600">Aucun modèle trouvé</p>
            <p class="mt-2 text-sm text-gray-400">Essayez un autre mot-clé ou <button class="text-blue-600 font-semibold hover:underline" @click="search = ''; selCat = null">effacez les filtres</button></p>
          </div>
        </div>
      </template>
    </div>

    <!-- ═══ MODAL APERÇU ═══ -->
    <Teleport to="body">
      <Transition name="modal">
        <div
          v-if="preview"
          class="fixed inset-0 z-50 flex items-end justify-center sm:items-center sm:p-4"
          style="background:rgba(15,35,86,.75);backdrop-filter:blur(4px)"
          @click.self="closePreview"
        >
          <div class="flex w-full max-w-4xl flex-col overflow-hidden rounded-t-3xl sm:rounded-2xl bg-white shadow-2xl"
               style="max-height:92vh">
            <!-- Modal header -->
            <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
              <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl text-xl"
                     :style="`background:${preview.catColor}15`">{{ preview.icon }}</div>
                <div>
                  <p class="font-bold text-gray-800">{{ preview.name }}</p>
                  <p class="text-xs text-gray-400">{{ preview.catLabel }} · Données de démonstration</p>
                </div>
              </div>
              <div class="flex items-center gap-2">
                <button
                  class="hidden sm:inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-bold text-white shadow-md transition hover:brightness-110"
                  :style="`background:${preview.catColor}`"
                  @click="createDoc(preview); closePreview()"
                >
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                  </svg>
                  Créer ce document
                </button>
                <button
                  class="rounded-xl bg-gray-100 p-2.5 text-gray-500 transition hover:bg-gray-200"
                  @click="closePreview"
                >
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                  </svg>
                </button>
              </div>
            </div>

            <!-- Iframe -->
            <div class="flex-1 overflow-hidden">
              <iframe
                class="h-full w-full border-none"
                style="min-height:500px"
                sandbox="allow-same-origin"
                :srcdoc="previewHTML(preview)"
              />
            </div>

            <!-- Mobile CTA -->
            <div class="border-t border-gray-100 p-4 sm:hidden">
              <button
                class="w-full rounded-xl py-3 text-sm font-bold text-white shadow-md transition hover:brightness-110"
                :style="`background:${preview.catColor}`"
                @click="createDoc(preview); closePreview()"
              >
                ✏️ Créer ce document
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </AuthenticatedLayout>
</template>

<style scoped>
.catalog-card {
  will-change: transform, box-shadow;
}
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.scrollbar-none {
  scrollbar-width: none;
  -ms-overflow-style: none;
}
.scrollbar-none::-webkit-scrollbar { display: none; }

/* Modal animation */
.modal-enter-active  { transition: all .25s ease; }
.modal-leave-active  { transition: all .2s ease; }
.modal-enter-from    { opacity: 0; }
.modal-leave-to      { opacity: 0; }
.modal-enter-from .flex { transform: translateY(30px); }
.modal-leave-to .flex   { transform: translateY(30px); }
</style>

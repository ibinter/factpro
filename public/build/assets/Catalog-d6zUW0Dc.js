import{o as n,c as r,a as w,u as C,h as te,w as O,b as i,t as a,j as se,v as ae,d as m,n as L,F as x,r as _,x as v,i as R,f as S,e as de,T as oe,I as le,l as ne,k as F,q as B,s as re}from"./app-BzDBUfkx.js";import{A as ce}from"./AuthenticatedLayout-B87CqXEN.js";import{_ as ve}from"./_plugin-vue_export-helper-DlAUqK2U.js";import"./Sara-DtbYlIPD.js";import"./Analytics-Bo7fLLiB.js";const pe={class:"flex items-center justify-between gap-4"},ue={class:"text-sm text-gray-500 mt-0.5"},fe={class:"relative overflow-hidden",style:{background:"linear-gradient(135deg,#0f2356 0%,#1E3A5F 40%,#2563EB 100%)"}},me={class:"relative mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8"},be={class:"mb-6 flex flex-wrap items-center gap-4 sm:gap-8"},xe={class:"text-white"},ge={class:"text-3xl font-black"},he={class:"text-white"},ye={class:"text-3xl font-black"},Ce={class:"relative max-w-2xl"},_e={class:"sticky top-0 z-20 border-b border-gray-100 bg-white shadow-sm"},Te={class:"mx-auto max-w-7xl px-4 sm:px-6 lg:px-8"},Ae={class:"flex items-center gap-2 overflow-x-auto py-3 scrollbar-none"},we=["onClick"],Oe={class:"mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8",style:{background:"#F0F4FB","min-height":"70vh"}},Se={class:"mb-8"},Fe={class:"grid gap-3",style:{"grid-template-columns":"repeat(auto-fill,minmax(180px,1fr))"}},ke=["onClick"],Re={class:"mb-3 flex items-center justify-between"},Ee={class:"text-sm font-bold text-gray-800 leading-tight mb-3 flex-1"},Ie={class:"flex items-center gap-1.5"},De=["onClick"],Le=["onClick"],Be={class:"mb-4 flex items-center justify-between"},Pe={class:"text-sm text-gray-600"},Me={class:"text-gray-800"},Xe={key:0,class:"font-medium"},$e={key:1,class:"font-medium"},je={class:"mb-4 flex items-center gap-3"},Ne={class:"flex-1 min-w-0"},Ve={class:"text-sm font-bold text-gray-800"},ze={class:"text-xs text-gray-400"},qe={class:"grid gap-3",style:{"grid-template-columns":"repeat(auto-fill,minmax(200px,1fr))"}},Ue=["data-color","onMouseenter"],He={class:"mb-3 flex items-start justify-between"},Ge={key:0,class:"rounded-full bg-amber-50 px-2 py-0.5 text-[9px] font-bold text-amber-600 shrink-0"},Qe={class:"mb-1 text-[10px] font-bold text-gray-800 leading-snug flex-1"},Ke={class:"mb-3 text-[11px] leading-relaxed text-gray-400 line-clamp-2"},Je={class:"flex gap-1.5"},Ye=["onClick"],Ze=["onClick"],We={key:2,class:"grid gap-3",style:{"grid-template-columns":"repeat(auto-fill,minmax(200px,1fr))"}},ei=["onMouseenter"],ii={class:"mb-3 flex items-start justify-between"},ti={key:0,class:"rounded-full bg-amber-50 px-2 py-0.5 text-[9px] font-bold text-amber-600 shrink-0"},si={class:"mb-1 text-[11px] font-bold text-gray-800 leading-snug flex-1"},ai={class:"mb-3 text-[11px] leading-relaxed text-gray-400 line-clamp-2"},di={class:"flex gap-1.5"},oi=["onClick"],li=["onClick"],ni={key:0,class:"col-span-full py-20 text-center"},ri={class:"mt-2 text-sm text-gray-400"},ci={class:"flex w-full max-w-4xl flex-col overflow-hidden rounded-t-3xl sm:rounded-2xl bg-white shadow-2xl",style:{"max-height":"92vh"}},vi={class:"flex items-center justify-between border-b border-gray-100 px-5 py-4"},pi={class:"flex items-center gap-3"},ui={class:"font-bold text-gray-800"},fi={class:"text-xs text-gray-400"},mi={class:"flex items-center gap-2"},bi={class:"flex-1 overflow-hidden"},xi=["srcdoc"],gi={class:"border-t border-gray-100 p-4 sm:hidden"},hi={__name:"Catalog",setup(yi){const g=[{id:"vente",label:"Vente & Facturation",icon:"💰",color:"#2563EB",docs:[{id:"devis",factproType:"quote",name:"Devis",icon:"📋",desc:"Proposition de prix détaillée et professionnelle",pop:!0},{id:"offre",factproType:"quote",name:"Offre Commerciale",icon:"🤝",desc:"Présentation d'offre percutante avec argumentaire",pop:!1},{id:"proposition",factproType:"quote",name:"Proposition Commerciale",icon:"💼",desc:"Proposition complète et structurée",pop:!1},{id:"proforma",factproType:"proforma_invoice",name:"Facture Proforma",icon:"📄",desc:"Facture prévisionnelle avant commande officielle",pop:!0},{id:"bc_client",factproType:"purchase_order",name:"Bon de Commande Client",icon:"🛒",desc:"Confirmation officielle des commandes reçues",pop:!0},{id:"bon_resa",factproType:"quote",name:"Bon de Réservation",icon:"📅",desc:"Confirmation de réservation produit ou service",pop:!1},{id:"bon_prep",factproType:"delivery_note",name:"Bon de Préparation",icon:"📦",desc:"Ordre interne de préparation de commande",pop:!1},{id:"bon_liv",factproType:"delivery_note",name:"Bon de Livraison",icon:"🚚",desc:"Attestation officielle de livraison marchandises",pop:!0},{id:"ordre_liv",factproType:"delivery_note",name:"Ordre de Livraison",icon:"📮",desc:"Autorisation de libération des marchandises",pop:!1},{id:"facture",factproType:"invoice",name:"Facture",icon:"🧾",desc:"Facture commerciale standard — normes OHADA",pop:!0},{id:"fac_simple",factproType:"invoice",name:"Facture Simplifiée",icon:"🗒️",desc:"Facture allégée pour petits montants",pop:!1},{id:"fac_export",factproType:"invoice",name:"Facture Export",icon:"🌍",desc:"Facture pour transactions internationales",pop:!1},{id:"fac_exo",factproType:"invoice",name:"Facture Exonérée TVA",icon:"🔖",desc:"Facture sans TVA pour entreprises exonérées",pop:!1},{id:"fac_rect",factproType:"credit_note",name:"Facture Rectificative",icon:"✏️",desc:"Correction d'une facture déjà émise",pop:!1},{id:"fac_acompte",factproType:"advance_invoice",name:"Facture d'Acompte",icon:"💸",desc:"Facturation d'acompte sur commande en cours",pop:!0},{id:"fac_solde",factproType:"balance_invoice",name:"Facture de Solde",icon:"✔️",desc:"Solde de facturation après versement d'acompte",pop:!1},{id:"avoir",factproType:"credit_note",name:"Avoir / Note de Crédit",icon:"↩️",desc:"Note de crédit pour remboursement client",pop:!1},{id:"recu",factproType:"receipt",name:"Reçu de Paiement",icon:"✅",desc:"Confirmation officielle de réception de paiement",pop:!0},{id:"ticket",factproType:"receipt",name:"Ticket de Caisse",icon:"🖨️",desc:"Reçu de vente au comptant simplifié",pop:!1},{id:"contrat_com",factproType:"invoice",name:"Contrat Commercial",icon:"📝",desc:"Accord contractuel entre parties commerciales",pop:!1}]},{id:"achat",label:"Achats & Fournisseurs",icon:"🏪",color:"#7C3AED",docs:[{id:"dem_achat",factproType:"supplier_order",name:"Demande d'Achat",icon:"📨",desc:"Demande interne d'approvisionnement",pop:!1},{id:"dem_prix",factproType:"quote",name:"Demande de Prix",icon:"💬",desc:"Consultation de prix auprès des fournisseurs",pop:!1},{id:"consult_f",factproType:"quote",name:"Consultation Fournisseur",icon:"📞",desc:"Appel d'offres fournisseur structuré",pop:!1},{id:"bc_f",factproType:"supplier_order",name:"Bon de Commande Fournisseur",icon:"📮",desc:"Commande officielle passée à un fournisseur",pop:!0},{id:"br_f",factproType:"goods_receipt",name:"Bon de Réception",icon:"📥",desc:"Réception de marchandises fournisseur",pop:!0},{id:"fac_f",factproType:"invoice",name:"Facture Fournisseur",icon:"🧾",desc:"Enregistrement d'une facture fournisseur",pop:!1},{id:"avoir_f",factproType:"credit_note",name:"Avoir Fournisseur",icon:"↩️",desc:"Note de crédit reçue d'un fournisseur",pop:!1},{id:"retour_f",factproType:"delivery_note",name:"Bon de Retour Fournisseur",icon:"🔄",desc:"Retour de marchandises au fournisseur",pop:!1},{id:"note_debit",factproType:"invoice",name:"Note de Débit",icon:"📊",desc:"Débit complémentaire sur une facture existante",pop:!1},{id:"note_credit_f",factproType:"credit_note",name:"Note de Crédit Fournisseur",icon:"📊",desc:"Crédit accordé par le fournisseur",pop:!1}]},{id:"stock",label:"Stocks & Inventaire",icon:"📦",color:"#D97706",docs:[{id:"be",factproType:"goods_receipt",name:"Bon d'Entrée de Stock",icon:"⬆️",desc:"Enregistrement d'une entrée en stock",pop:!1},{id:"bs",factproType:"delivery_note",name:"Bon de Sortie de Stock",icon:"⬇️",desc:"Enregistrement d'une sortie de stock",pop:!1},{id:"transfert",factproType:"delivery_note",name:"Bon de Transfert",icon:"↔️",desc:"Transfert de stock entre entrepôts",pop:!1},{id:"consommation",factproType:"delivery_note",name:"Bon de Consommation",icon:"🔧",desc:"Consommation interne de matières ou produits",pop:!1},{id:"inventaire",factproType:"invoice",name:"Bon d'Inventaire",icon:"📊",desc:"Fiche de comptage et valorisation du stock",pop:!0},{id:"ajustement",factproType:"invoice",name:"Ajustement de Stock",icon:"⚖️",desc:"Correction des écarts constatés à l'inventaire",pop:!1},{id:"destruction",factproType:"invoice",name:"Bon de Destruction / Casse",icon:"🗑️",desc:"Mise au rebut de stock détérioré ou obsolète",pop:!1},{id:"of",factproType:"invoice",name:"Ordre de Fabrication",icon:"🏭",desc:"Lancement d'un ordre de production",pop:!1},{id:"transform",factproType:"invoice",name:"Bon de Transformation",icon:"🔄",desc:"Transformation de produits en stock",pop:!1}]},{id:"sav",label:"SAV & Maintenance",icon:"🔧",color:"#0891B2",docs:[{id:"rma",factproType:"service_report",name:"Bon de Retour RMA",icon:"📦",desc:"Retour de produit en garantie client",pop:!1},{id:"fiche_sav",factproType:"service_report",name:"Fiche SAV",icon:"🔧",desc:"Dossier complet de service après-vente",pop:!0},{id:"bon_rep",factproType:"service_report",name:"Bon de Réparation",icon:"⚙️",desc:"Ordre de réparation d'équipement ou matériel",pop:!0},{id:"rapport_int",factproType:"service_report",name:"Rapport d'Intervention",icon:"📋",desc:"Compte-rendu d'intervention technique",pop:!0},{id:"bon_maint",factproType:"service_report",name:"Bon de Maintenance",icon:"🛠️",desc:"Ordre de maintenance préventive ou curative",pop:!1},{id:"cert_gar",factproType:"invoice",name:"Certificat de Garantie",icon:"🏅",desc:"Attestation de garantie produit ou service",pop:!1},{id:"contrat_maint",factproType:"invoice",name:"Contrat de Maintenance",icon:"📝",desc:"Contrat de maintenance périodique",pop:!1}]},{id:"btp",label:"BTP & Travaux",icon:"🏗️",color:"#DC2626",docs:[{id:"bon_trav",factproType:"invoice",name:"Bon de Travaux",icon:"🔨",desc:"Ordre d'exécution de travaux de construction",pop:!0},{id:"os",factproType:"invoice",name:"Ordre de Service",icon:"📋",desc:"Instruction officielle de démarrage de chantier",pop:!0},{id:"situation",factproType:"advance_invoice",name:"Situation de Travaux",icon:"📐",desc:"Facturation d'avancement de chantier",pop:!0},{id:"decompte_p",factproType:"advance_invoice",name:"Décompte Provisoire",icon:"🔢",desc:"Décompte intermédiaire des travaux réalisés",pop:!1},{id:"decompte_d",factproType:"balance_invoice",name:"Décompte Définitif",icon:"✔️",desc:"Décompte final en fin de chantier",pop:!1},{id:"pv_prov",factproType:"invoice",name:"PV Réception Provisoire",icon:"📋",desc:"Réception provisoire des travaux achevés",pop:!0},{id:"pv_def",factproType:"invoice",name:"PV Réception Définitive",icon:"🏆",desc:"Réception définitive — fin de période de garantie",pop:!0},{id:"rapport_ch",factproType:"service_report",name:"Rapport de Chantier",icon:"📊",desc:"Compte-rendu journalier ou hebdo de chantier",pop:!1}]},{id:"logistique",label:"Logistique & Transport",icon:"🚛",color:"#059669",docs:[{id:"bon_exp",factproType:"delivery_note",name:"Bon d'Expédition",icon:"📤",desc:"Bon d'envoi officiel de marchandises",pop:!0},{id:"lettre_v",factproType:"delivery_note",name:"Lettre de Voiture",icon:"📜",desc:"Document de transport routier officiel",pop:!0},{id:"packing",factproType:"delivery_note",name:"Packing List",icon:"📋",desc:"Liste de colisage pour expédition internationale",pop:!0},{id:"bord_ch",factproType:"delivery_note",name:"Bordereau de Chargement",icon:"📊",desc:"Récapitulatif détaillé du chargement véhicule",pop:!1},{id:"transfert_d",factproType:"delivery_note",name:"Transfert Inter-Dépôts",icon:"↔️",desc:"Transfert de stock entre dépôts et entrepôts",pop:!1},{id:"manifeste",factproType:"delivery_note",name:"Manifeste de Livraison",icon:"📊",desc:"Récapitulatif de toutes les livraisons effectuées",pop:!1}]},{id:"finance",label:"Finance & Trésorerie",icon:"💳",color:"#0284C7",docs:[{id:"bord_rem",factproType:"receipt",name:"Bordereau de Remise",icon:"📄",desc:"Remise de chèques ou effets en banque",pop:!1},{id:"note_frais",factproType:"expense_report",name:"Note de Frais",icon:"🧾",desc:"Remboursement de frais professionnels engagés",pop:!0},{id:"bon_caisse",factproType:"cash_voucher",name:"Bon de Caisse",icon:"💵",desc:"Mouvement d'entrée ou sortie de caisse",pop:!0},{id:"depot_banc",factproType:"receipt",name:"Bordereau de Dépôt Bancaire",icon:"🏦",desc:"Dépôt de fonds en agence bancaire",pop:!1},{id:"retrait_banc",factproType:"receipt",name:"Bon de Retrait Bancaire",icon:"🏧",desc:"Retrait de fonds en agence bancaire",pop:!1},{id:"effet",factproType:"invoice",name:"Effet de Commerce / Traite",icon:"📄",desc:"Lettre de change — instrument de paiement différé",pop:!1},{id:"billet_ordre",factproType:"invoice",name:"Billet à Ordre",icon:"📝",desc:"Engagement de paiement à une date fixée",pop:!1}]},{id:"rh",label:"Ressources Humaines",icon:"👥",color:"#7C3AED",docs:[{id:"ordre_miss",factproType:"service_report",name:"Ordre de Mission",icon:"✈️",desc:"Autorisation officielle de déplacement professionnel",pop:!0},{id:"dem_conge",factproType:"invoice",name:"Demande de Congé",icon:"🏖️",desc:"Formulaire de demande de congé payé",pop:!0},{id:"bulletin",factproType:"payslip",name:"Bulletin de Paie",icon:"💰",desc:"Fiche de salaire mensuelle détaillée",pop:!0},{id:"avance_sal",factproType:"advance_invoice",name:"Avance sur Salaire",icon:"💵",desc:"Demande d'avance sur rémunération mensuelle",pop:!1},{id:"note_serv",factproType:"invoice",name:"Note de Service",icon:"📢",desc:"Communication interne officielle de direction",pop:!0},{id:"auto_abs",factproType:"invoice",name:"Autorisation d'Absence",icon:"📝",desc:"Autorisation d'absence exceptionnelle motivée",pop:!1}]},{id:"admin",label:"Administratif & Juridique",icon:"⚖️",color:"#4F46E5",docs:[{id:"contrat",factproType:"invoice",name:"Contrat",icon:"📝",desc:"Contrat commercial ou de prestation de services",pop:!0},{id:"pv_reun",factproType:"service_report",name:"Procès-Verbal",icon:"📋",desc:"PV de réunion, d'assemblée ou de décision",pop:!0},{id:"attest",factproType:"invoice",name:"Attestation",icon:"🏅",desc:"Attestation officielle toutes natures",pop:!0},{id:"certif",factproType:"invoice",name:"Certificat",icon:"🎓",desc:"Certificat professionnel ou de conformité",pop:!1},{id:"relance",factproType:"invoice",name:"Lettre de Relance",icon:"📩",desc:"Relance amiable d'un impayé ou d'un document",pop:!0},{id:"mise_dem",factproType:"invoice",name:"Mise en Demeure",icon:"⚠️",desc:"Mise en demeure formelle avant contentieux",pop:!1},{id:"accuse",factproType:"invoice",name:"Accusé de Réception",icon:"✅",desc:"Confirmation officielle de réception de document",pop:!1},{id:"autoris",factproType:"invoice",name:"Autorisation",icon:"🔓",desc:"Autorisation administrative ou opérationnelle",pop:!1}]},{id:"immobilier",label:"Immobilier & Location",icon:"🏠",color:"#DB2777",docs:[{id:"bail",factproType:"invoice",name:"Contrat de Bail",icon:"🔑",desc:"Contrat de location immobilière résidentiel ou commercial",pop:!0},{id:"edle",factproType:"invoice",name:"État des Lieux d'Entrée",icon:"🏡",desc:"Constat d'état à l'entrée du locataire",pop:!0},{id:"edls",factproType:"invoice",name:"État des Lieux de Sortie",icon:"🚪",desc:"Constat d'état au départ du locataire",pop:!0},{id:"appel_l",factproType:"invoice",name:"Appel de Loyer",icon:"💳",desc:"Avis mensuel de loyer et charges à régler",pop:!0},{id:"quittance_l",factproType:"receipt",name:"Quittance de Loyer",icon:"✅",desc:"Reçu de paiement de loyer mensuel",pop:!0}]},{id:"export",label:"Export & Douane",icon:"🌍",color:"#B45309",docs:[{id:"cert_orig",factproType:"invoice",name:"Certificat d'Origine",icon:"📜",desc:"Attestation de l'origine des marchandises exportées",pop:!0},{id:"decl_d",factproType:"invoice",name:"Déclaration Douanière",icon:"🏛️",desc:"Déclaration en douane import ou export",pop:!0},{id:"bon_emb",factproType:"delivery_note",name:"Bon d'Embarquement",icon:"🚢",desc:"Autorisation d'embarquement marchandises",pop:!1},{id:"fac_exp_d",factproType:"invoice",name:"Facture Export (Douane)",icon:"🧾",desc:"Facture conforme aux exigences douanières",pop:!0}]},{id:"sante",label:"Santé & Médical",icon:"🏥",color:"#0891B2",docs:[{id:"fac_med",factproType:"invoice",name:"Facture Médicale",icon:"🏥",desc:"Facture de consultations et soins médicaux",pop:!0},{id:"ordo",factproType:"invoice",name:"Ordonnance",icon:"💊",desc:"Prescription médicale du praticien",pop:!0},{id:"bon_labo",factproType:"invoice",name:"Bon de Laboratoire",icon:"🔬",desc:"Prescription d'analyses biologiques",pop:!1},{id:"feuille_s",factproType:"invoice",name:"Feuille de Soins",icon:"📋",desc:"Feuille de soins pour remboursement assurance",pop:!1}]},{id:"education",label:"Éducation & Formation",icon:"🎓",color:"#7C3AED",docs:[{id:"recu_scol",factproType:"receipt",name:"Reçu de Scolarité",icon:"🏫",desc:"Reçu de paiement de frais de scolarité",pop:!0},{id:"fac_form",factproType:"invoice",name:"Facture de Formation",icon:"📚",desc:"Facture de prestation de formation professionnelle",pop:!0},{id:"attest_paie",factproType:"receipt",name:"Attestation de Paiement",icon:"✅",desc:"Attestation de règlement de frais scolaires",pop:!0},{id:"bul_notes",factproType:"invoice",name:"Bulletin de Notes",icon:"📝",desc:"Bulletin scolaire trimestriel ou semestriel",pop:!1}]}],y=g.flatMap(e=>e.docs.map(t=>({...t,catId:e.id,catLabel:e.label,catColor:e.color,catIcon:e.icon}))),p=F(""),c=F(null),u=F(null),P=F(!1),T=B(()=>y.filter(e=>{const t=!c.value||e.catId===c.value,b=p.value.toLowerCase(),s=!b||[e.name,e.desc,e.catLabel].some(d=>d.toLowerCase().includes(b));return t&&s})),M=B(()=>y.filter(e=>e.pop).slice(0,8));function k(e){u.value=e}function A(){u.value=null}function h(e){re.visit(route("documents.create",{type:e.factproType}))}function E(e){c.value=e,P.value=!1}const X=e=>`
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Segoe UI',Arial,sans-serif;color:#1e293b;background:#fff;font-size:13px;padding:28px}
.hdr{display:flex;justify-content:space-between;align-items:flex-start;padding-bottom:16px;border-bottom:3px solid ${e};margin-bottom:20px}
.logo{width:44px;height:44px;background:linear-gradient(135deg,#1E3A5F,#2563EB);border-radius:8px;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:16px;margin-bottom:6px}
.co-name{font-size:15px;font-weight:800;color:#1E3A5F}
.co-info{font-size:11px;color:#64748b;margin-top:4px;line-height:1.6}
.doc-title{font-size:20px;font-weight:900;color:${e};text-align:right;text-transform:uppercase;letter-spacing:.03em}
.doc-ref{text-align:right;font-size:11px;color:#64748b;margin-top:4px;line-height:1.6}
.badge{display:inline-block;padding:2px 10px;border-radius:20px;font-size:9px;font-weight:700;background:${e}20;color:${e};margin-top:5px}
.two-col{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:18px}
.box{background:#f8fafc;border-radius:8px;padding:12px;border-left:3px solid ${e}}
.box-label{font-size:9px;text-transform:uppercase;letter-spacing:.1em;color:#94a3b8;font-weight:700;margin-bottom:4px}
.box-name{font-size:13px;font-weight:700}
.box-info{font-size:11px;color:#64748b;margin-top:2px;line-height:1.5}
table{width:100%;border-collapse:collapse;margin-bottom:12px}
th{background:${e};color:#fff;padding:8px 10px;text-align:left;font-size:10px;font-weight:700;text-transform:uppercase}
th:last-child,td:last-child{text-align:right}
td{padding:8px 10px;border-bottom:1px solid #e2e8f0;font-size:12px}
tr:nth-child(even)>td{background:#f8fafc}
.totals-wrap{display:flex;justify-content:flex-end;margin-bottom:12px}
.totals{width:260px;border:1px solid #e2e8f0;border-radius:8px;overflow:hidden}
.tot-row{display:flex;justify-content:space-between;padding:6px 12px;font-size:12px;border-bottom:1px solid #e2e8f0}
.tot-final{display:flex;justify-content:space-between;padding:10px 12px;font-size:14px;font-weight:800;color:#fff;background:${e}}
.sigs{display:flex;gap:14px;margin-top:24px}
.sig{flex:1;border:1px dashed #cbd5e1;border-radius:6px;padding:8px 12px;text-align:center;font-size:10px;color:#94a3b8}
.sig-space{height:44px}
.field-row{display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid #f1f5f9;font-size:12px}
.field-label{color:#94a3b8;font-size:11px}
.section-title{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:${e};margin:14px 0 6px}
.text-block{background:#f8fafc;border-radius:8px;padding:14px;font-size:12px;line-height:1.8;color:#374151;border-left:3px solid ${e};margin-bottom:14px}
.info-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:16px}
.info-cell{background:#f8fafc;border-radius:6px;padding:10px}
.info-cell-label{font-size:9px;text-transform:uppercase;letter-spacing:.08em;color:#94a3b8;font-weight:700;margin-bottom:3px}
.info-cell-val{font-size:12px;font-weight:600;color:#1e293b}
.status-badge{display:inline-flex;align-items:center;gap:5px;padding:4px 12px;border-radius:20px;font-size:11px;font-weight:700}
.footer{margin-top:20px;padding-top:10px;border-top:1px solid #e2e8f0;text-align:center;font-size:10px;color:#94a3b8}
`;function o(e){return`<div class="hdr">
<div>
  <div class="logo">VS</div>
  <div class="co-name">VOTRE SOCIÉTÉ SARL</div>
  <div class="co-info">📍 Plateau, Abidjan 01 · Côte d'Ivoire<br>📞 +225 27 22 33 44 55 · ✉️ contact@vs.ci<br>RCCM CI-ABJ-2024-B-12345 · CC 2405812 A</div>
</div>
<div>
  <div class="doc-title">${e.name}</div>
  <div class="doc-ref">N° <b>2026-0042</b><br>Date : <b>27/07/2026</b></div>
  <div class="badge">Aperçu de démonstration</div>
</div>
</div>`}function $(){return'<div class="footer">Document généré par <b style="color:#1E3A5F">IBIG FactPro</b> · ibigsoft.com · Conforme OHADA</div>'}function l(e,t){return`<!DOCTYPE html><html><head><meta charset="UTF-8"><style>${X(e)}</style></head><body>${t}${$()}</body></html>`}function I(e){return l(e.catColor,`
${o(e)}
<div class="two-col">
  <div class="box"><div class="box-label">Émetteur</div><div class="box-name">VOTRE SOCIÉTÉ SARL</div><div class="box-info">Plateau, Abidjan<br>RCCM CI-ABJ-2024-B-12345</div></div>
  <div class="box"><div class="box-label">Client / Destinataire</div><div class="box-name">CLIENT EXEMPLE & ASSOCIÉS</div><div class="box-info">Cocody Riviera 3, Abidjan<br>+225 05 00 11 22 33</div></div>
</div>
<table><thead><tr><th>#</th><th>Désignation</th><th>Qté</th><th>P.U. HT</th><th>Total HT</th></tr></thead>
<tbody>
<tr><td>01</td><td>Prestation de conseil professionnel</td><td>5 j</td><td>200 000 XOF</td><td>1 000 000 XOF</td></tr>
<tr><td>02</td><td>Fournitures et matériaux</td><td>10 u</td><td>35 000 XOF</td><td>350 000 XOF</td></tr>
<tr><td>03</td><td>Transport et logistique</td><td>1 fft</td><td>85 000 XOF</td><td>85 000 XOF</td></tr>
</tbody></table>
<div class="totals-wrap"><div class="totals">
  <div class="tot-row"><span style="color:#64748b">Sous-total HT</span><span>1 435 000 XOF</span></div>
  <div class="tot-row"><span style="color:#64748b">TVA 18 %</span><span>258 300 XOF</span></div>
  <div class="tot-final"><span>TOTAL TTC</span><span>1 693 300 XOF</span></div>
</div></div>
<div class="sigs">
  <div class="sig"><div class="sig-space"></div>Signature Client<br><em>Bon pour accord</em></div>
  <div class="sig"><div class="sig-space"></div>Cachet & Signature<br><em>Émetteur</em></div>
</div>`)}function D(e){return l(e.catColor,`
${o(e)}
<div class="two-col">
  <div class="box"><div class="box-label">Expéditeur</div><div class="box-name">VOTRE SOCIÉTÉ SARL</div><div class="box-info">Plateau, Abidjan · Entrepôt principal</div></div>
  <div class="box"><div class="box-label">Destinataire</div><div class="box-name">CLIENT EXEMPLE & ASSOCIÉS</div><div class="box-info">Cocody Riviera 3, Abidjan<br>Contact : M. Kouassi — 07 11 22 33</div></div>
</div>
<div class="info-grid">
  <div class="info-cell"><div class="info-cell-label">Bon de commande lié</div><div class="info-cell-val">BC-2026-0018</div></div>
  <div class="info-cell"><div class="info-cell-label">Date de livraison</div><div class="info-cell-val">27/07/2026</div></div>
  <div class="info-cell"><div class="info-cell-label">Transporteur</div><div class="info-cell-val">LOGIS EXPRESS CI</div></div>
  <div class="info-cell"><div class="info-cell-label">Mode de transport</div><div class="info-cell-val">🚚 Camion — Réfrigéré</div></div>
</div>
<table><thead><tr><th>#</th><th>Désignation</th><th>Qté commandée</th><th>Qté livrée</th><th>Unité</th><th>Obs.</th></tr></thead>
<tbody>
<tr><td>01</td><td>Ciment Portland CPA 42.5</td><td>50</td><td>50</td><td>Sac 50 kg</td><td>✅ Conforme</td></tr>
<tr><td>02</td><td>Fers à béton Ø12</td><td>20</td><td>18</td><td>Barre 6m</td><td>⚠️ 2 manquants</td></tr>
<tr><td>03</td><td>Parpaings creux 15×20×40</td><td>500</td><td>500</td><td>Unité</td><td>✅ Conforme</td></tr>
</tbody></table>
<p style="font-size:11px;color:#64748b;margin-bottom:16px">Observations : 2 barres de fer manquantes — livraison complémentaire prévue le 30/07/2026</p>
<div class="sigs">
  <div class="sig"><div class="sig-space"></div>Signature Livreur<br><em>Nom & Date</em></div>
  <div class="sig"><div class="sig-space"></div>Signature Réceptionnaire<br><em>Bon pour réception</em></div>
</div>`)}function j(e){return e.id==="bulletin"?l(e.catColor,`
${o(e)}
<div class="two-col">
  <div class="box"><div class="box-label">Employeur</div><div class="box-name">VOTRE SOCIÉTÉ SARL</div><div class="box-info">Plateau, Abidjan · CNPS : 123456789</div></div>
  <div class="box"><div class="box-label">Employé</div><div class="box-name">KONÉ Aminata</div><div class="box-info">Poste : Responsable Commerciale<br>Mat. : EMP-2024-0042 · Embauche : 01/03/2022</div></div>
</div>
<div class="section-title">Éléments de rémunération — Juillet 2026</div>
<table><thead><tr><th>Libellé</th><th>Base</th><th>Taux</th><th>Montant</th></tr></thead>
<tbody>
<tr><td>Salaire de base</td><td>—</td><td>—</td><td>350 000 XOF</td></tr>
<tr><td>Prime de transport</td><td>—</td><td>—</td><td>30 000 XOF</td></tr>
<tr><td>Prime de rendement</td><td>350 000</td><td>10 %</td><td>35 000 XOF</td></tr>
<tr><td style="color:#dc2626">CNPS salarié</td><td>415 000</td><td>3.2 %</td><td style="color:#dc2626">-13 280 XOF</td></tr>
<tr><td style="color:#dc2626">Impôt sur Salaire (ITS)</td><td>415 000</td><td>—</td><td style="color:#dc2626">-18 500 XOF</td></tr>
</tbody></table>
<div class="totals-wrap"><div class="totals">
  <div class="tot-row"><span style="color:#64748b">Brut imposable</span><span>415 000 XOF</span></div>
  <div class="tot-row"><span style="color:#64748b">Total retenues</span><span style="color:#dc2626">-31 780 XOF</span></div>
  <div class="tot-final"><span>NET À PAYER</span><span>383 220 XOF</span></div>
</div></div>
<div class="sigs">
  <div class="sig"><div class="sig-space"></div>Signature Employé<br><em>Reçu le ___________</em></div>
  <div class="sig"><div class="sig-space"></div>Cachet & Signature DRH</div>
</div>`):e.id==="ordre_miss"?l(e.catColor,`
${o(e)}
<div class="info-grid">
  <div class="info-cell"><div class="info-cell-label">Agent en mission</div><div class="info-cell-val">KONÉ Aminata</div></div>
  <div class="info-cell"><div class="info-cell-label">Poste</div><div class="info-cell-val">Responsable Commerciale</div></div>
  <div class="info-cell"><div class="info-cell-label">Destination</div><div class="info-cell-val">Bouaké, Côte d'Ivoire</div></div>
  <div class="info-cell"><div class="info-cell-label">Durée</div><div class="info-cell-val">28/07 → 30/07/2026 (3 jours)</div></div>
  <div class="info-cell"><div class="info-cell-label">Objet de la mission</div><div class="info-cell-val">Prospection commerciale région Centre</div></div>
  <div class="info-cell"><div class="info-cell-label">Moyen de transport</div><div class="info-cell-val">✈️ Avion / 🚗 Véhicule société</div></div>
</div>
<div class="section-title">Frais prévisionnels autorisés</div>
<table><thead><tr><th>Poste</th><th>Montant alloué</th></tr></thead>
<tbody>
<tr><td>Hébergement (2 nuits × 40 000)</td><td>80 000 XOF</td></tr>
<tr><td>Perdiem repas (3 jours × 15 000)</td><td>45 000 XOF</td></tr>
<tr><td>Transport local</td><td>25 000 XOF</td></tr>
</tbody></table>
<div class="totals-wrap"><div class="totals">
  <div class="tot-final"><span>TOTAL AVANCE</span><span>150 000 XOF</span></div>
</div></div>
<div class="sigs">
  <div class="sig"><div class="sig-space"></div>Signature Agent<br><em>Lu et approuvé</em></div>
  <div class="sig"><div class="sig-space"></div>Cachet & Visa Direction</div>
</div>`):l(e.catColor,`
${o(e)}
<div class="two-col">
  <div class="box"><div class="box-label">Employeur</div><div class="box-name">VOTRE SOCIÉTÉ SARL</div><div class="box-info">Département RH · Plateau, Abidjan</div></div>
  <div class="box"><div class="box-label">Concernant</div><div class="box-name">KONÉ Aminata</div><div class="box-info">Responsable Commerciale<br>Matricule : EMP-2024-0042</div></div>
</div>
<div class="section-title">Détails de la demande</div>
<div class="info-grid">
  <div class="info-cell"><div class="info-cell-label">Type</div><div class="info-cell-val">${e.name}</div></div>
  <div class="info-cell"><div class="info-cell-label">Date de la demande</div><div class="info-cell-val">27/07/2026</div></div>
  <div class="info-cell"><div class="info-cell-label">Période concernée</div><div class="info-cell-val">01/08/2026 → 15/08/2026</div></div>
  <div class="info-cell"><div class="info-cell-label">Durée</div><div class="info-cell-val">15 jours ouvrables</div></div>
  <div class="info-cell"><div class="info-cell-label">Solde congés avant</div><div class="info-cell-val">28 jours</div></div>
  <div class="info-cell"><div class="info-cell-label">Solde après déduction</div><div class="info-cell-val">13 jours</div></div>
</div>
<div class="section-title">Motif</div>
<div class="text-block">Congé annuel — repos et raisons familiales.</div>
<div class="sigs">
  <div class="sig"><div class="sig-space"></div>Signature Employé</div>
  <div class="sig"><div class="sig-space"></div>✅ Approuvé par Direction<br><em>Date : ___________</em></div>
</div>`)}function N(e){return l(e.catColor,`
${o(e)}
<div class="info-grid">
  <div class="info-cell"><div class="info-cell-label">Partie A (Émetteur)</div><div class="info-cell-val">VOTRE SOCIÉTÉ SARL</div></div>
  <div class="info-cell"><div class="info-cell-label">Partie B (Destinataire)</div><div class="info-cell-val">CLIENT EXEMPLE & ASSOCIÉS</div></div>
  <div class="info-cell"><div class="info-cell-label">Date de signature</div><div class="info-cell-val">27/07/2026</div></div>
  <div class="info-cell"><div class="info-cell-label">Lieu de signature</div><div class="info-cell-val">Abidjan, Côte d'Ivoire</div></div>
  <div class="info-cell"><div class="info-cell-label">Référence</div><div class="info-cell-val">DOC-2026-ADM-0042</div></div>
  <div class="info-cell"><div class="info-cell-label">Durée / Validité</div><div class="info-cell-val">12 mois à compter de la signature</div></div>
</div>
<div class="section-title">Objet</div>
<div class="text-block">${e.desc}.<br><br>Les parties sus-nommées conviennent et s'engagent mutuellement à respecter les termes et conditions énoncés dans le présent document, conformément aux dispositions du droit OHADA et aux lois en vigueur en République de Côte d'Ivoire.</div>
<div class="section-title">Clauses principales</div>
<div class="text-block">
  <b>Article 1 — Objet :</b> Le présent ${e.name.toLowerCase()} a pour objet de définir les modalités de la relation entre les parties.<br><br>
  <b>Article 2 — Durée :</b> Il prend effet à compter de sa date de signature pour une durée de 12 mois, renouvelable par accord tacite.<br><br>
  <b>Article 3 — Obligations des parties :</b> Chaque partie s'engage à respecter ses obligations dans les délais convenus.
</div>
<div class="sigs">
  <div class="sig"><div class="sig-space"></div>Signature Partie A<br><em>Nom, Qualité & Cachet</em></div>
  <div class="sig"><div class="sig-space"></div>Signature Partie B<br><em>Nom, Qualité & Cachet</em></div>
</div>`)}function V(e){return l(e.catColor,`
${o(e)}
<div class="two-col">
  <div class="box"><div class="box-label">Prestataire</div><div class="box-name">VOTRE SOCIÉTÉ SARL</div><div class="box-info">Service Après-Vente · Abidjan</div></div>
  <div class="box"><div class="box-label">Client</div><div class="box-name">ENTREPRISE KABORÉ SAS</div><div class="box-info">Zone Industrielle, Abidjan<br>Contact technique : M. Bamba — 07 11 22 33</div></div>
</div>
<div class="info-grid">
  <div class="info-cell"><div class="info-cell-label">Équipement</div><div class="info-cell-val">Groupe électrogène 250 KVA</div></div>
  <div class="info-cell"><div class="info-cell-label">N° de série</div><div class="info-cell-val">GE-2021-AB-00931</div></div>
  <div class="info-cell"><div class="info-cell-label">Date d'intervention</div><div class="info-cell-val">27/07/2026 — 09h00 → 14h30</div></div>
  <div class="info-cell"><div class="info-cell-label">Type d'intervention</div><div class="info-cell-val">🔧 Curative — Panne moteur</div></div>
  <div class="info-cell"><div class="info-cell-label">Technicien</div><div class="info-cell-val">DIALLO Moussa — Tech. N3</div></div>
  <div class="info-cell"><div class="info-cell-label">Statut</div><div class="info-cell-val"><span class="status-badge" style="background:#dcfce7;color:#166534">✅ Résolu</span></div></div>
</div>
<div class="section-title">Diagnostic & travaux réalisés</div>
<div class="text-block">Remplacement du démarreur défectueux et nettoyage complet du circuit de carburant. Vérification et réétalonnage des capteurs de pression. Test de charge à 80 % pendant 2 h — résultat nominal.</div>
<div class="section-title">Pièces remplacées</div>
<table><thead><tr><th>Désignation</th><th>Qté</th><th>P.U.</th><th>Total</th></tr></thead>
<tbody>
<tr><td>Démarreur 24V — Réf. ST-24-093</td><td>1</td><td>85 000 XOF</td><td>85 000 XOF</td></tr>
<tr><td>Filtre à carburant double</td><td>2</td><td>12 500 XOF</td><td>25 000 XOF</td></tr>
<tr><td>Joint d'étanchéité</td><td>4</td><td>3 500 XOF</td><td>14 000 XOF</td></tr>
</tbody></table>
<div class="sigs">
  <div class="sig"><div class="sig-space"></div>Visa Technicien</div>
  <div class="sig"><div class="sig-space"></div>Bon pour accord Client<br><em>Date : ___________</em></div>
</div>`)}function z(e){return l(e.catColor,`
${o(e)}
<div class="two-col">
  <div class="box"><div class="box-label">Maître d'œuvre</div><div class="box-name">VOTRE SOCIÉTÉ SARL</div><div class="box-info">BTP & Génie Civil · Abidjan</div></div>
  <div class="box"><div class="box-label">Maître d'ouvrage</div><div class="box-name">RÉSIDENCE LES BOUGAINVILLIERS SCI</div><div class="box-info">Cocody Angré, Abidjan<br>M. ADJOUA Pierre — 05 04 03 02 01</div></div>
</div>
<div class="info-grid">
  <div class="info-cell"><div class="info-cell-label">Chantier</div><div class="info-cell-val">Construction R+3 — Lot A</div></div>
  <div class="info-cell"><div class="info-cell-label">Adresse chantier</div><div class="info-cell-val">Angré 8ème Tranche, Abidjan</div></div>
  <div class="info-cell"><div class="info-cell-label">Période concernée</div><div class="info-cell-val">01/07/2026 → 31/07/2026</div></div>
  <div class="info-cell"><div class="info-cell-label">Avancement global</div><div class="info-cell-val">▓▓▓▓▓▓░░░░ 62 %</div></div>
</div>
<div class="section-title">Travaux exécutés ce mois</div>
<table><thead><tr><th>Poste</th><th>Unité</th><th>Prévu</th><th>Réalisé</th><th>%</th><th>Montant</th></tr></thead>
<tbody>
<tr><td>Terrassement</td><td>m³</td><td>450</td><td>450</td><td>100%</td><td>2 250 000</td></tr>
<tr><td>Fondations béton armé</td><td>m³</td><td>120</td><td>98</td><td>82%</td><td>4 900 000</td></tr>
<tr><td>Maçonnerie parpaings</td><td>m²</td><td>600</td><td>370</td><td>62%</td><td>2 590 000</td></tr>
</tbody></table>
<div class="totals-wrap"><div class="totals">
  <div class="tot-row"><span style="color:#64748b">Situation ce mois</span><span>9 740 000 XOF</span></div>
  <div class="tot-row"><span style="color:#64748b">Situations précédentes</span><span>14 200 000 XOF</span></div>
  <div class="tot-final"><span>CUMUL FACTURABLE</span><span>23 940 000 XOF</span></div>
</div></div>
<div class="sigs">
  <div class="sig"><div class="sig-space"></div>Visa Maître d'Œuvre</div>
  <div class="sig"><div class="sig-space"></div>Visa Maître d'Ouvrage</div>
</div>`)}function q(e){return l(e.catColor,`
${o(e)}
<div class="info-grid">
  <div class="info-cell"><div class="info-cell-label">Entrepôt</div><div class="info-cell-val">Dépôt Principal — Yopougon</div></div>
  <div class="info-cell"><div class="info-cell-label">Date d'opération</div><div class="info-cell-val">27/07/2026 — 08h30</div></div>
  <div class="info-cell"><div class="info-cell-label">Responsable stock</div><div class="info-cell-val">COULIBALY Seydou</div></div>
  <div class="info-cell"><div class="info-cell-label">Type de mouvement</div><div class="info-cell-val">${e.icon} ${e.name}</div></div>
</div>
<table><thead><tr><th>Réf.</th><th>Désignation</th><th>Unité</th><th>Qté avant</th><th>Mouvement</th><th>Qté après</th></tr></thead>
<tbody>
<tr><td>ART-001</td><td>Ciment Portland CPA 42.5</td><td>Sac</td><td>248</td><td style="color:#16a34a;font-weight:700">+50</td><td>298</td></tr>
<tr><td>ART-002</td><td>Sable fin de rivière</td><td>m³</td><td>32</td><td style="color:#dc2626;font-weight:700">-8</td><td>24</td></tr>
<tr><td>ART-003</td><td>Fers à béton Ø12 — 6m</td><td>Barre</td><td>120</td><td style="color:#16a34a;font-weight:700">+30</td><td>150</td></tr>
<tr><td>ART-004</td><td>Parpaings creux 15×20×40</td><td>Unité</td><td>2400</td><td style="color:#dc2626;font-weight:700">-500</td><td>1900</td></tr>
</tbody></table>
<div class="section-title">Observations</div>
<div class="text-block">Mouvement validé suite à réception BL N° 2026-BL-0089 — Fournisseur : MATÉRIAUX DU GOLF SARL.</div>
<div class="sigs">
  <div class="sig"><div class="sig-space"></div>Signature Magasinier</div>
  <div class="sig"><div class="sig-space"></div>Signature Responsable<br><em>Visa & Date</em></div>
</div>`)}function U(e){return l(e.catColor,`
${o(e)}
<div class="two-col">
  <div class="box"><div class="box-label">Bailleur / Propriétaire</div><div class="box-name">IMMOBILIÈRE DU PLATEAU SA</div><div class="box-info">Plateau, Abidjan · Tél. +225 27 22 11 00 00</div></div>
  <div class="box"><div class="box-label">Locataire</div><div class="box-name">KONÉ Aminata</div><div class="box-info">CIN : CI0123456789<br>Contact : +225 07 11 22 33 44</div></div>
</div>
<div class="info-grid">
  <div class="info-cell"><div class="info-cell-label">Bien loué</div><div class="info-cell-val">Villa F4 — Cocody Riviera 3</div></div>
  <div class="info-cell"><div class="info-cell-label">Superficie</div><div class="info-cell-val">180 m² habitables</div></div>
  <div class="info-cell"><div class="info-cell-label">Durée du bail</div><div class="info-cell-val">24 mois — 01/08/2026 → 31/07/2028</div></div>
  <div class="info-cell"><div class="info-cell-label">Loyer mensuel</div><div class="info-cell-val"><b>450 000 XOF</b> / mois TTC</div></div>
  <div class="info-cell"><div class="info-cell-label">Charges incluses</div><div class="info-cell-val">Eau, gardiennage, entretien communs</div></div>
  <div class="info-cell"><div class="info-cell-label">Caution versée</div><div class="info-cell-val">900 000 XOF (2 mois)</div></div>
</div>
<div class="section-title">Conditions de paiement</div>
<div class="text-block">Loyer payable le 1er de chaque mois par virement bancaire ou chèque certifié. Tout retard de paiement entraîne une pénalité de 5 % par mois de retard.</div>
<div class="sigs">
  <div class="sig"><div class="sig-space"></div>Signature Bailleur<br><em>Cachet & Date</em></div>
  <div class="sig"><div class="sig-space"></div>Signature Locataire<br><em>Lu & approuvé</em></div>
</div>`)}function H(e){return l(e.catColor,`
${o(e)}
<div class="two-col">
  <div class="box"><div class="box-label">Exportateur</div><div class="box-name">VOTRE SOCIÉTÉ SARL</div><div class="box-info">Plateau, Abidjan · CI<br>RCCM CI-ABJ-2024-B-12345 · NIF 2405812A</div></div>
  <div class="box"><div class="box-label">Importateur / Destinataire</div><div class="box-name">SAHEL TRADING SA</div><div class="box-info">Bamako, Mali<br>REG. COM. : ML-BKO-2019-B-4521</div></div>
</div>
<div class="info-grid">
  <div class="info-cell"><div class="info-cell-label">Port / Aéroport d'embarquement</div><div class="info-cell-val">Port Autonome d'Abidjan</div></div>
  <div class="info-cell"><div class="info-cell-label">Port de destination</div><div class="info-cell-val">Bamako — Mali (voie terrestre)</div></div>
  <div class="info-cell"><div class="info-cell-label">Incoterm</div><div class="info-cell-val">CIF Bamako</div></div>
  <div class="info-cell"><div class="info-cell-label">N° déclaration douane</div><div class="info-cell-val">CI-EXP-2026-00891</div></div>
</div>
<table><thead><tr><th>Désignation</th><th>Quantité</th><th>Poids net</th><th>Valeur FOB</th><th>Pays d'origine</th></tr></thead>
<tbody>
<tr><td>Café robusta grade A</td><td>200 sacs</td><td>10 000 kg</td><td>8 500 000 XOF</td><td>🇨🇮 Côte d'Ivoire</td></tr>
<tr><td>Cacao en fèves brut</td><td>150 sacs</td><td>7 500 kg</td><td>11 250 000 XOF</td><td>🇨🇮 Côte d'Ivoire</td></tr>
</tbody></table>
<div class="totals-wrap"><div class="totals">
  <div class="tot-row"><span style="color:#64748b">Valeur FOB totale</span><span>19 750 000 XOF</span></div>
  <div class="tot-row"><span style="color:#64748b">Fret maritime</span><span>1 200 000 XOF</span></div>
  <div class="tot-final"><span>VALEUR CIF TOTAL</span><span>20 950 000 XOF</span></div>
</div></div>
<div class="sigs">
  <div class="sig"><div class="sig-space"></div>Signature Exportateur<br><em>Cachet officiel</em></div>
  <div class="sig"><div class="sig-space"></div>Visa Bureau des Douanes<br><em>Cachet & Réf.</em></div>
</div>`)}function G(e){return e.id==="ordo"?l(e.catColor,`
${o(e)}
<div class="two-col">
  <div class="box"><div class="box-label">Praticien</div><div class="box-name">Dr. KOUASSI Emmanuel</div><div class="box-info">Médecin généraliste — Ordre N° CI-MED-4521<br>Clinique Saint-Luc, Cocody, Abidjan</div></div>
  <div class="box"><div class="box-label">Patient</div><div class="box-name">DIALLO Mariama</div><div class="box-info">Née le : 12/03/1988 · F<br>Poids : 62 kg · Allergie : Pénicilline</div></div>
</div>
<div class="section-title">💊 Médicaments prescrits</div>
<table><thead><tr><th>Médicament</th><th>Dosage</th><th>Posologie</th><th>Durée</th></tr></thead>
<tbody>
<tr><td>Amoxicilline 500 mg</td><td>500 mg</td><td>1 cp × 3/jour</td><td>7 jours</td></tr>
<tr><td>Paracétamol 1000 mg</td><td>1000 mg</td><td>1 cp × 3/jour si douleur</td><td>5 jours</td></tr>
<tr><td>Ibuprofène 400 mg</td><td>400 mg</td><td>1 cp × 2/jour après repas</td><td>5 jours</td></tr>
</tbody></table>
<div class="text-block">⚠️ <b>Ne pas dépasser les doses prescrites.</b> En cas de réaction allergique, arrêter immédiatement et consulter en urgence.<br><br>Repos recommandé pendant 3 jours. Revoir dans 7 jours si pas d'amélioration.</div>
<div class="sigs">
  <div class="sig"><div class="sig-space"></div>Signature & Cachet Médecin<br>Dr. KOUASSI Emmanuel</div>
  <div class="sig"><div class="sig-space"></div>Date : 27/07/2026<br>Abidjan, Côte d'Ivoire</div>
</div>`):l(e.catColor,`
${o(e)}
<div class="two-col">
  <div class="box"><div class="box-label">Établissement</div><div class="box-name">CLINIQUE SAINT-LUC</div><div class="box-info">Cocody, Abidjan · Tél. +225 27 22 44 55 00<br>Agrément : MS-CI-2018-CL-0042</div></div>
  <div class="box"><div class="box-label">Patient</div><div class="box-name">DIALLO Mariama</div><div class="box-info">Née le : 12/03/1988<br>Dossier N° : PAT-2026-0781</div></div>
</div>
<div class="section-title">Actes et soins réalisés</div>
<table><thead><tr><th>Désignation</th><th>Qté</th><th>P.U.</th><th>Montant</th></tr></thead>
<tbody>
<tr><td>Consultation médecin généraliste</td><td>1</td><td>15 000 XOF</td><td>15 000 XOF</td></tr>
<tr><td>Prise de sang complète (NFS)</td><td>1</td><td>12 500 XOF</td><td>12 500 XOF</td></tr>
<tr><td>Radiographie thorax F+P</td><td>1</td><td>18 000 XOF</td><td>18 000 XOF</td></tr>
<tr><td>Perfusion + produits</td><td>2</td><td>8 500 XOF</td><td>17 000 XOF</td></tr>
</tbody></table>
<div class="totals-wrap"><div class="totals">
  <div class="tot-row"><span style="color:#64748b">Sous-total</span><span>62 500 XOF</span></div>
  <div class="tot-row"><span style="color:#64748b">Prise en charge assurance</span><span style="color:#16a34a">-37 500 XOF</span></div>
  <div class="tot-final"><span>RESTE À CHARGE</span><span>25 000 XOF</span></div>
</div></div>
<div class="sigs">
  <div class="sig"><div class="sig-space"></div>Signature Caissier</div>
  <div class="sig"><div class="sig-space"></div>Signature Patient<br><em>Reçu le ___________</em></div>
</div>`)}function Q(e){return l(e.catColor,`
${o(e)}
<div class="two-col">
  <div class="box"><div class="box-label">Établissement</div><div class="box-name">ÉCOLE SUPÉRIEURE DE COMMERCE D'ABIDJAN</div><div class="box-info">Cocody, Abidjan · Tél. +225 27 22 55 66 77<br>Agrément MEN N° 2015-0042</div></div>
  <div class="box"><div class="box-label">Apprenant / Élève</div><div class="box-name">TRAORÉ Ibrahim</div><div class="box-info">Matricule : ETU-2025-1842<br>Filière : BTS Commerce International — L2</div></div>
</div>
<div class="info-grid">
  <div class="info-cell"><div class="info-cell-label">Année scolaire</div><div class="info-cell-val">2025 / 2026</div></div>
  <div class="info-cell"><div class="info-cell-label">Trimestre / Semestre</div><div class="info-cell-val">3ème trimestre — T3</div></div>
  <div class="info-cell"><div class="info-cell-label">Frais de scolarité annuels</div><div class="info-cell-val">850 000 XOF</div></div>
  <div class="info-cell"><div class="info-cell-label">Mode de paiement</div><div class="info-cell-val">Tranche — 3 × 283 333 XOF</div></div>
</div>
<div class="section-title">Détail du règlement</div>
<table><thead><tr><th>Tranche</th><th>Échéance</th><th>Montant</th><th>Statut</th></tr></thead>
<tbody>
<tr><td>1ère tranche</td><td>01/10/2025</td><td>283 333 XOF</td><td><span class="status-badge" style="background:#dcfce7;color:#166534">✅ Payée</span></td></tr>
<tr><td>2ème tranche</td><td>10/01/2026</td><td>283 333 XOF</td><td><span class="status-badge" style="background:#dcfce7;color:#166534">✅ Payée</span></td></tr>
<tr><td>3ème tranche</td><td>10/04/2026</td><td>283 334 XOF</td><td><span class="status-badge" style="background:#fef9c3;color:#854d0e">⏳ En attente</span></td></tr>
</tbody></table>
<div class="sigs">
  <div class="sig"><div class="sig-space"></div>Signature Scolarité</div>
  <div class="sig"><div class="sig-space"></div>Signature Parent / Étudiant</div>
</div>`)}function K(e){return l(e.catColor,`
${o(e)}
<div class="two-col">
  <div class="box"><div class="box-label">Émetteur / Caisse</div><div class="box-name">VOTRE SOCIÉTÉ SARL</div><div class="box-info">Caisse principale · Plateau, Abidjan<br>Responsable caisse : BAMBA Adjoua</div></div>
  <div class="box"><div class="box-label">Bénéficiaire / Débiteur</div><div class="box-name">KONÉ Aminata</div><div class="box-info">Responsable Commerciale<br>Matricule : EMP-2024-0042</div></div>
</div>
<div class="info-grid">
  <div class="info-cell"><div class="info-cell-label">Type de mouvement</div><div class="info-cell-val">${e.name}</div></div>
  <div class="info-cell"><div class="info-cell-label">Date</div><div class="info-cell-val">27/07/2026 — 10h45</div></div>
  <div class="info-cell"><div class="info-cell-label">Motif</div><div class="info-cell-val">Avance frais mission Bouaké</div></div>
  <div class="info-cell"><div class="info-cell-label">Mode de paiement</div><div class="info-cell-val">💵 Espèces</div></div>
</div>
<div class="section-title">Montant de l'opération</div>
<div style="text-align:center;padding:20px;background:${e.catColor}10;border-radius:12px;margin-bottom:16px">
  <div style="font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:.08em;margin-bottom:6px">Montant</div>
  <div style="font-size:32px;font-weight:900;color:${e.catColor}">150 000 XOF</div>
  <div style="font-size:11px;color:#64748b;margin-top:4px">Cent cinquante mille francs CFA</div>
</div>
<div class="sigs">
  <div class="sig"><div class="sig-space"></div>Signature du Caissier<br><em>BAMBA Adjoua</em></div>
  <div class="sig"><div class="sig-space"></div>Signature du Bénéficiaire<br><em>Reçu le ___________</em></div>
</div>`)}function J(e){return l(e.catColor,`
${o(e)}
<div style="background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:14px 18px;margin-bottom:18px;display:flex;align-items:center;gap:12px">
  <div style="font-size:28px">↩️</div>
  <div>
    <div style="font-size:12px;font-weight:700;color:#991b1b">DOCUMENT D'AVOIR / CRÉDIT</div>
    <div style="font-size:11px;color:#b91c1c;margin-top:2px">En référence à la facture N° <b>FAC-2026-0038</b> du 15/07/2026 — Montant initial : 1 200 000 XOF</div>
  </div>
</div>
<div class="two-col">
  <div class="box"><div class="box-label">Émetteur</div><div class="box-name">VOTRE SOCIÉTÉ SARL</div><div class="box-info">Plateau, Abidjan · RCCM CI-ABJ-2024-B-12345</div></div>
  <div class="box"><div class="box-label">Bénéficiaire du crédit</div><div class="box-name">CLIENT EXEMPLE & ASSOCIÉS</div><div class="box-info">Cocody Riviera 3, Abidjan · +225 05 00 11 22 33</div></div>
</div>
<div class="section-title">Motif de l'avoir</div>
<div class="text-block">Retour partiel de marchandises défectueuses (lot N° LOT-2026-041). 3 unités sur 10 retournées après constat contradictoire du 20/07/2026.</div>
<table><thead><tr><th>Désignation</th><th>Qté retournée</th><th>P.U.</th><th>Montant crédit</th></tr></thead>
<tbody>
<tr><td>Climatiseur split 1,5 CV — Réf. AC-150</td><td>3</td><td>185 000 XOF</td><td style="color:#16a34a;font-weight:700">- 555 000 XOF</td></tr>
<tr><td>Frais de transport retour</td><td>1 fft</td><td>15 000 XOF</td><td style="color:#16a34a;font-weight:700">- 15 000 XOF</td></tr>
</tbody></table>
<div class="totals-wrap"><div class="totals">
  <div class="tot-row"><span style="color:#64748b">Sous-total HT</span><span style="color:#16a34a">- 570 000 XOF</span></div>
  <div class="tot-row"><span style="color:#64748b">TVA 18 %</span><span style="color:#16a34a">- 102 600 XOF</span></div>
  <div class="tot-final" style="background:#15803d"><span>TOTAL AVOIR TTC</span><span>- 672 600 XOF</span></div>
</div></div>
<p style="font-size:11px;color:#64748b;margin-bottom:16px">Ce montant sera déduit de votre prochaine facture ou remboursé par virement sous 5 jours ouvrés.</p>
<div class="sigs">
  <div class="sig"><div class="sig-space"></div>Visa Responsable Commercial</div>
  <div class="sig"><div class="sig-space"></div>Accusé de réception Client</div>
</div>`)}function Y(e){const t=e.catId==="achat";return l(e.catColor,`
${o(e)}
<div style="background:${e.catColor}10;border-left:4px solid ${e.catColor};border-radius:8px;padding:12px 16px;margin-bottom:18px">
  <div style="font-size:11px;font-weight:700;color:${e.catColor};text-transform:uppercase;letter-spacing:.06em">Commande officielle — Veuillez préparer et livrer les articles ci-dessous</div>
</div>
<div class="two-col">
  <div class="box"><div class="box-label">${t?"Acheteur":"Client"}</div><div class="box-name">VOTRE SOCIÉTÉ SARL</div><div class="box-info">Service Achats · Plateau, Abidjan<br>Contact : Mme BAMBA — 07 11 22 33 00</div></div>
  <div class="box"><div class="box-label">${t?"Fournisseur":"Nous"}</div><div class="box-name">${t?"MATÉRIAUX DU GOLF SARL":"CLIENT EXEMPLE & ASSOCIÉS"}</div><div class="box-info">${t?"Zone Industrielle, Abidjan · Tél. +225 27 22 88 99 00":"Cocody Riviera 3 · +225 05 00 11 22 33"}</div></div>
</div>
<div class="info-grid">
  <div class="info-cell"><div class="info-cell-label">N° bon de commande</div><div class="info-cell-val">BC-2026-0089</div></div>
  <div class="info-cell"><div class="info-cell-label">Date commande</div><div class="info-cell-val">27/07/2026</div></div>
  <div class="info-cell"><div class="info-cell-label">Livraison souhaitée</div><div class="info-cell-val">03/08/2026 avant 12h</div></div>
  <div class="info-cell"><div class="info-cell-label">Lieu de livraison</div><div class="info-cell-val">Entrepôt Yopougon — Porte B</div></div>
</div>
<table><thead><tr><th>#</th><th>Désignation</th><th>Qté</th><th>Unité</th><th>P.U. HT</th><th>Total HT</th></tr></thead>
<tbody>
<tr><td>01</td><td>Ciment Portland CPA 42.5</td><td>100</td><td>Sac 50 kg</td><td>9 500 XOF</td><td>950 000 XOF</td></tr>
<tr><td>02</td><td>Sable fin de rivière lavé</td><td>15</td><td>m³</td><td>22 000 XOF</td><td>330 000 XOF</td></tr>
<tr><td>03</td><td>Fers à béton Ø12 — 6 m</td><td>50</td><td>Barre</td><td>8 200 XOF</td><td>410 000 XOF</td></tr>
</tbody></table>
<div class="totals-wrap"><div class="totals">
  <div class="tot-row"><span style="color:#64748b">Sous-total HT</span><span>1 690 000 XOF</span></div>
  <div class="tot-row"><span style="color:#64748b">TVA 18 %</span><span>304 200 XOF</span></div>
  <div class="tot-final"><span>TOTAL TTC</span><span>1 994 200 XOF</span></div>
</div></div>
<div class="sigs">
  <div class="sig"><div class="sig-space"></div>Visa Responsable Achats<br><em>VOTRE SOCIÉTÉ SARL</em></div>
  <div class="sig"><div class="sig-space"></div>Confirmation Fournisseur<br><em>Accusé de réception</em></div>
</div>`)}function Z(e){return l(e.catColor,`
${o(e)}
<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:16px;margin-bottom:18px">
  <div style="font-size:11px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.08em;margin-bottom:10px">📋 Informations du demandeur</div>
  <div class="info-grid" style="margin-bottom:0">
    <div class="info-cell"><div class="info-cell-label">Nom complet</div><div class="info-cell-val">KONÉ Aminata</div></div>
    <div class="info-cell"><div class="info-cell-label">Département</div><div class="info-cell-val">Commercial & Marketing</div></div>
    <div class="info-cell"><div class="info-cell-label">Poste</div><div class="info-cell-val">Responsable Commerciale</div></div>
    <div class="info-cell"><div class="info-cell-label">Date de la demande</div><div class="info-cell-val">27/07/2026</div></div>
  </div>
</div>
<div class="section-title">Objet de la demande</div>
<div class="text-block">${e.name} — ${e.desc}.</div>
<div class="section-title">Détails et justification</div>
<div class="info-grid">
  <div class="info-cell"><div class="info-cell-label">Urgence</div><div class="info-cell-val">🟡 Normale</div></div>
  <div class="info-cell"><div class="info-cell-label">Délai souhaité</div><div class="info-cell-val">Avant le 31/07/2026</div></div>
  <div class="info-cell"><div class="info-cell-label">Budget estimé</div><div class="info-cell-val">450 000 XOF</div></div>
  <div class="info-cell"><div class="info-cell-label">Imputation</div><div class="info-cell-val">Centre coût : CC-COMM-01</div></div>
</div>
<div class="text-block">Achat nécessaire pour assurer la continuité des opérations commerciales. Sans cette acquisition, le projet CLIENT-2026-042 sera retardé de 2 semaines.</div>
<div class="section-title">Circuit de validation</div>
<table><thead><tr><th>Niveau</th><th>Validateur</th><th>Statut</th><th>Date</th></tr></thead>
<tbody>
<tr><td>N+1 — Manager</td><td>DIALLO Moussa</td><td><span class="status-badge" style="background:#dcfce7;color:#166534">✅ Approuvé</span></td><td>27/07/2026</td></tr>
<tr><td>N+2 — Direction</td><td>M. KOUASSI DG</td><td><span class="status-badge" style="background:#fef9c3;color:#854d0e">⏳ En attente</span></td><td>—</td></tr>
<tr><td>Finance</td><td>Mme BAMBA CFO</td><td><span class="status-badge" style="background:#f1f5f9;color:#64748b">⬜ Pending</span></td><td>—</td></tr>
</tbody></table>
<div class="sigs">
  <div class="sig"><div class="sig-space"></div>Signature Demandeur</div>
  <div class="sig"><div class="sig-space"></div>Visa Direction Générale</div>
</div>`)}function W(e){return l(e.catColor,`
${o(e)}
<div style="text-align:center;padding:24px 16px;border:2px solid ${e.catColor}30;border-radius:14px;margin-bottom:20px;background:${e.catColor}06">
  <div style="font-size:40px;margin-bottom:8px">✅</div>
  <div style="font-size:13px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px">Paiement reçu</div>
  <div style="font-size:36px;font-weight:900;color:${e.catColor};margin:8px 0">750 000 XOF</div>
  <div style="font-size:12px;color:#64748b">Sept cent cinquante mille francs CFA</div>
</div>
<div class="info-grid">
  <div class="info-cell"><div class="info-cell-label">Reçu de</div><div class="info-cell-val">CLIENT EXEMPLE & ASSOCIÉS</div></div>
  <div class="info-cell"><div class="info-cell-label">Date de paiement</div><div class="info-cell-val">27/07/2026 — 14h30</div></div>
  <div class="info-cell"><div class="info-cell-label">Mode de paiement</div><div class="info-cell-val">🏦 Virement bancaire</div></div>
  <div class="info-cell"><div class="info-cell-label">Référence virement</div><div class="info-cell-val">VIR-BICICI-20260727-004</div></div>
  <div class="info-cell"><div class="info-cell-label">En règlement de</div><div class="info-cell-val">Facture FAC-2026-0041</div></div>
  <div class="info-cell"><div class="info-cell-label">Solde restant dû</div><div class="info-cell-val" style="color:#16a34a;font-weight:700">0 XOF — Soldé ✅</div></div>
</div>
<div class="sigs">
  <div class="sig"><div class="sig-space"></div>Cachet & Signature<br><em>VOTRE SOCIÉTÉ SARL</em></div>
  <div class="sig"><div class="sig-space"></div>Signature du Payeur<br><em>CLIENT EXEMPLE</em></div>
</div>`)}function ee(e){const t=e.id==="mise_dem";return l(e.catColor,`
${o(e)}
<div class="two-col">
  <div class="box"><div class="box-label">Expéditeur</div><div class="box-name">VOTRE SOCIÉTÉ SARL</div><div class="box-info">Plateau, Abidjan 01<br>RCCM CI-ABJ-2024-B-12345</div></div>
  <div class="box"><div class="box-label">Destinataire</div><div class="box-name">M. TRAORÉ Issouf, Gérant</div><div class="box-info">TRAORÉ & FILS SARL<br>Adjamé, Abidjan</div></div>
</div>
<div style="margin-bottom:16px">
  <div style="font-size:11px;color:#64748b">Abidjan, le <b>27 juillet 2026</b></div>
  <div style="font-size:13px;font-weight:700;margin:10px 0 6px;color:${e.catColor}">Objet : ${e.name} — Facture N° FAC-2026-0021 du 01/06/2026</div>
  ${t?'<div style="display:inline-flex;align-items:center;gap:6px;background:#fef2f2;border:1px solid #fecaca;border-radius:6px;padding:4px 12px;font-size:11px;font-weight:700;color:#991b1b;margin-bottom:10px">⚠️ MISE EN DEMEURE — Recommandé avec AR</div>':""}
</div>
<div class="text-block">
Monsieur le Gérant,<br><br>
${t?`Par la présente, et faute d'avoir reçu le règlement de notre facture N° <b>FAC-2026-0021</b> d'un montant de <b>985 000 XOF TTC</b>, malgré nos relances des 15/06 et 30/06/2026, nous vous mettons formellement en demeure de vous acquitter de cette somme dans un délai de <b>8 jours calendaires</b> à compter de la réception de ce courrier.<br><br>
À défaut de règlement dans ce délai, nous nous réservons le droit d'engager toute procédure contentieuse à votre encontre, avec frais et dépens à votre charge.`:`Sauf erreur ou omission de notre part, nous constatons que notre facture N° <b>FAC-2026-0021</b> d'un montant de <b>985 000 XOF TTC</b>, arrivée à échéance le <b>01/07/2026</b>, n'a pas encore été réglée.<br><br>
Nous vous remercions de bien vouloir procéder au règlement de cette somme dans les meilleurs délais, ou de nous contacter si vous rencontrez une difficulté.`}
<br><br>
Veuillez agréer, Monsieur le Gérant, l'expression de nos salutations distinguées.
</div>
<div class="sigs">
  <div class="sig"><div class="sig-space"></div>Direction Générale<br><em>VOTRE SOCIÉTÉ SARL</em></div>
</div>`)}function ie(e){const t=["avoir","avoir_f","note_credit_f","fac_rect"],b=["bc_client","bc_f"],s=["dem_achat","dem_prix","consult_f","dem_conge","auto_abs","autoris"],d=["recu","ticket","quittance_l","attest_paie","recu_scol"],f=["relance","mise_dem","accuse"];if(t.includes(e.id))return J(e);if(b.includes(e.id))return Y(e);if(s.includes(e.id))return Z(e);if(d.includes(e.id))return W(e);if(f.includes(e.id))return ee(e);switch(e.catId){case"rh":return j(e);case"admin":return N(e);case"sav":return V(e);case"btp":return z(e);case"stock":return q(e);case"immobilier":return U(e);case"export":return H(e);case"sante":return G(e);case"education":return Q(e);case"finance":return K(e);case"logistique":return D(e);case"achat":return["br_f","retour_f","bon_emb"].includes(e.id)?D(e):I(e);default:return I(e)}}return(e,t)=>(n(),r(x,null,[w(C(te),{title:"Catalogue de documents — IBIG FactPro"}),w(ce,null,{header:O(()=>[i("div",pe,[i("div",null,[t[9]||(t[9]=i("h2",{class:"text-xl font-bold text-gray-800"},"Catalogue de documents",-1)),i("p",ue,a(C(y).length)+" modèles · "+a(g.length)+" catégories · Normes OHADA",1)]),w(C(ne),{href:e.route("documents.create"),class:"inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-bold text-white shadow-md transition hover:shadow-lg hover:brightness-110 shrink-0",style:{background:"linear-gradient(135deg,#1E3A5F 0%,#2563EB 100%)"}},{default:O(()=>[...t[10]||(t[10]=[i("svg",{class:"h-4 w-4",fill:"none",viewBox:"0 0 24 24",stroke:"currentColor","stroke-width":"2.5"},[i("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M12 4v16m8-8H4"})],-1),S(" Nouveau document ",-1)])]),_:1},8,["href"])])]),default:O(()=>{var b;return[i("div",fe,[t[18]||(t[18]=i("div",{class:"absolute -top-12 -right-12 h-48 w-48 rounded-full opacity-10",style:{background:"#fff"}},null,-1)),t[19]||(t[19]=i("div",{class:"absolute -bottom-8 -left-8 h-32 w-32 rounded-full opacity-10",style:{background:"#60a5fa"}},null,-1)),i("div",me,[i("div",be,[i("div",xe,[i("span",ge,a(C(y).length),1),t[11]||(t[11]=i("span",{class:"ml-1.5 text-sm font-medium text-blue-200"},"modèles",-1))]),t[13]||(t[13]=i("div",{class:"hidden h-8 w-px bg-white/20 sm:block"},null,-1)),i("div",he,[i("span",ye,a(g.length),1),t[12]||(t[12]=i("span",{class:"ml-1.5 text-sm font-medium text-blue-200"},"catégories",-1))]),t[14]||(t[14]=i("div",{class:"hidden h-8 w-px bg-white/20 sm:block"},null,-1)),t[15]||(t[15]=i("div",{class:"text-white"},[i("span",{class:"text-sm font-medium text-blue-200"},"✅ Conformes OHADA")],-1))]),i("div",Ce,[t[17]||(t[17]=i("svg",{class:"absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400 pointer-events-none",fill:"none",viewBox:"0 0 24 24",stroke:"currentColor","stroke-width":"2"},[i("circle",{cx:"11",cy:"11",r:"8"}),i("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"m21 21-4.35-4.35"})],-1)),se(i("input",{"onUpdate:modelValue":t[0]||(t[0]=s=>p.value=s),type:"text",placeholder:"Rechercher un document (facture, devis, contrat de bail, bulletin de paie…)",class:"w-full rounded-2xl border-0 bg-white/95 py-4 pl-12 pr-4 text-sm font-medium text-gray-700 shadow-xl backdrop-blur transition focus:bg-white focus:outline-none focus:ring-4 focus:ring-white/30"},null,512),[[ae,p.value]]),p.value?(n(),r("button",{key:0,class:"absolute right-3 top-1/2 -translate-y-1/2 rounded-full bg-gray-100 p-1.5 text-gray-400 hover:text-gray-600 transition",onClick:t[1]||(t[1]=s=>p.value="")},[...t[16]||(t[16]=[i("svg",{class:"h-4 w-4",fill:"none",viewBox:"0 0 24 24",stroke:"currentColor","stroke-width":"2.5"},[i("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M6 18L18 6M6 6l12 12"})],-1)])])):m("",!0)])])]),i("div",_e,[i("div",Te,[i("div",Ae,[i("button",{class:L(["shrink-0 rounded-full px-3.5 py-1.5 text-xs font-semibold transition-all",c.value?"bg-gray-100 text-gray-600 hover:bg-gray-200":"bg-blue-600 text-white shadow-md"]),onClick:t[2]||(t[2]=s=>E(null))}," 🗂️ Tous ("+a(C(y).length)+") ",3),(n(),r(x,null,_(g,s=>i("button",{key:s.id,class:L(["shrink-0 rounded-full px-3.5 py-1.5 text-xs font-semibold transition-all",c.value===s.id?"text-white shadow-md":"bg-gray-100 text-gray-600 hover:bg-gray-200"]),style:v(c.value===s.id?`background:${s.color}`:""),onClick:d=>E(s.id)},a(s.icon)+" "+a(s.label)+" ("+a(s.docs.length)+") ",15,we)),64))])])]),i("div",Oe,[!c.value&&!p.value?(n(),r(x,{key:0},[i("div",Se,[t[21]||(t[21]=i("div",{class:"mb-4 flex items-center justify-between"},[i("h3",{class:"text-base font-bold text-gray-800"},"⭐ Modèles populaires"),i("span",{class:"text-xs text-gray-400"},"Les plus utilisés")],-1)),i("div",Fe,[(n(!0),r(x,null,_(M.value,s=>(n(),r("div",{key:"pop_"+s.id,class:"catalog-card group flex cursor-pointer flex-col rounded-2xl bg-white p-4 shadow-sm transition-all duration-200",onClick:d=>h(s)},[i("div",Re,[i("div",{class:"flex h-11 w-11 items-center justify-center rounded-xl text-xl",style:v(`background:${s.catColor}15`)},a(s.icon),5),t[20]||(t[20]=i("span",{class:"rounded-full bg-amber-50 px-2 py-0.5 text-[9px] font-bold text-amber-600"},"⭐ TOP",-1))]),i("p",{class:"text-[9px] font-bold uppercase tracking-wider mb-1",style:v(`color:${s.catColor}`)},a(s.catLabel),5),i("p",Ee,a(s.name),1),i("div",Ie,[i("button",{class:"flex-none rounded-lg border border-gray-200 px-2.5 py-1.5 text-[10px] font-semibold text-gray-600 hover:bg-gray-50 transition",onClick:R(d=>k(s),["stop"])},"👁️",8,De),i("button",{class:"flex-1 rounded-lg py-1.5 text-[10px] font-bold text-white transition hover:brightness-110",style:v(`background:${s.catColor}`),onClick:R(d=>h(s),["stop"])},"Créer",12,Le)])],8,ke))),128))])]),t[22]||(t[22]=i("div",{class:"mb-6 flex items-center gap-3"},[i("div",{class:"h-px flex-1 bg-gray-200"}),i("span",{class:"text-xs font-bold text-gray-400 uppercase tracking-wider"},"Tous les modèles"),i("div",{class:"h-px flex-1 bg-gray-200"})],-1))],64)):m("",!0),i("div",Be,[i("p",Pe,[i("strong",Me,a(T.value.length),1),S(" modèle"+a(T.value.length>1?"s":"")+" ",1),c.value?(n(),r("span",Xe," · "+a((b=g.find(s=>s.id===c.value))==null?void 0:b.label),1)):m("",!0),p.value?(n(),r("span",$e," · « "+a(p.value)+" »",1)):m("",!0)]),c.value||p.value?(n(),r("button",{key:0,class:"text-xs font-semibold text-blue-600 hover:text-blue-800 transition",onClick:t[3]||(t[3]=s=>{c.value=null,p.value=""})}," ✕ Effacer filtres ")):m("",!0)]),!c.value&&!p.value?(n(),r(x,{key:1},_(g,s=>i("div",{key:s.id,class:"mb-10"},[i("div",je,[i("div",{class:"flex h-9 w-9 items-center justify-center rounded-xl text-lg shrink-0",style:v(`background:${s.color}18`)},a(s.icon),5),i("div",Ne,[i("h4",Ve,a(s.label),1),i("p",ze,a(s.docs.length)+" modèles",1)]),t[23]||(t[23]=i("div",{class:"h-px flex-1 bg-gray-200 hidden sm:block"},null,-1))]),i("div",qe,[(n(!0),r(x,null,_(s.docs,d=>(n(),r("div",{key:d.id,class:"catalog-card group flex flex-col rounded-2xl bg-white p-4 shadow-sm transition-all duration-200 cursor-default","data-color":s.color,onMouseenter:f=>{f.currentTarget.style.boxShadow=`0 8px 25px ${s.color}25`,f.currentTarget.style.transform="translateY(-2px)"},onMouseleave:t[4]||(t[4]=f=>{f.currentTarget.style.boxShadow="",f.currentTarget.style.transform=""})},[i("div",He,[i("div",{class:"flex h-11 w-11 items-center justify-center rounded-xl text-xl shrink-0",style:v(`background:${s.color}15`)},a(d.icon),5),d.pop?(n(),r("span",Ge,"⭐ Pop")):m("",!0)]),i("p",Qe,a(d.name),1),i("p",Ke,a(d.desc),1),i("div",Je,[i("button",{class:"flex-none rounded-xl border border-gray-200 px-3 py-1.5 text-[10px] font-semibold text-gray-600 transition hover:border-gray-300 hover:bg-gray-50",onClick:f=>k({...d,catId:s.id,catLabel:s.label,catColor:s.color,catIcon:s.icon})},"👁️ Aperçu",8,Ye),i("button",{class:"flex-1 rounded-xl py-1.5 text-[10px] font-bold text-white transition hover:brightness-110 hover:shadow-md",style:v(`background:${s.color}`),onClick:f=>h(d)},"Créer le document",12,Ze)])],40,Ue))),128))])])),64)):(n(),r("div",We,[(n(!0),r(x,null,_(T.value,s=>(n(),r("div",{key:s.catId+"__"+s.id,class:"catalog-card group flex flex-col rounded-2xl bg-white p-4 shadow-sm transition-all duration-200 cursor-default",onMouseenter:d=>{d.currentTarget.style.boxShadow=`0 8px 25px ${s.catColor}25`,d.currentTarget.style.transform="translateY(-2px)"},onMouseleave:t[5]||(t[5]=d=>{d.currentTarget.style.boxShadow="",d.currentTarget.style.transform=""})},[i("div",ii,[i("div",{class:"flex h-11 w-11 items-center justify-center rounded-xl text-xl shrink-0",style:v(`background:${s.catColor}15`)},a(s.icon),5),s.pop?(n(),r("span",ti,"⭐ Pop")):m("",!0)]),i("p",{class:"mb-0.5 text-[9px] font-bold uppercase tracking-wider",style:v(`color:${s.catColor}`)},a(s.catIcon)+" "+a(s.catLabel),5),i("p",si,a(s.name),1),i("p",ai,a(s.desc),1),i("div",di,[i("button",{class:"flex-none rounded-xl border border-gray-200 px-3 py-1.5 text-[10px] font-semibold text-gray-600 transition hover:bg-gray-50",onClick:d=>k(s)},"👁️ Aperçu",8,oi),i("button",{class:"flex-1 rounded-xl py-1.5 text-[10px] font-bold text-white transition hover:brightness-110 hover:shadow-md",style:v(`background:${s.catColor}`),onClick:d=>h(s)},"Créer le document",12,li)])],40,ei))),128)),T.value.length===0?(n(),r("div",ni,[t[25]||(t[25]=i("div",{class:"mb-4 text-6xl"},"🔍",-1)),t[26]||(t[26]=i("p",{class:"text-lg font-bold text-gray-600"},"Aucun modèle trouvé",-1)),i("p",ri,[t[24]||(t[24]=S("Essayez un autre mot-clé ou ",-1)),i("button",{class:"text-blue-600 font-semibold hover:underline",onClick:t[6]||(t[6]=s=>{p.value="",c.value=null})},"effacez les filtres")])])):m("",!0)]))]),(n(),de(oe,{to:"body"},[w(le,{name:"modal"},{default:O(()=>[u.value?(n(),r("div",{key:0,class:"fixed inset-0 z-50 flex items-end justify-center sm:items-center sm:p-4",style:{background:"rgba(15,35,86,.75)","backdrop-filter":"blur(4px)"},onClick:R(A,["self"])},[i("div",ci,[i("div",vi,[i("div",pi,[i("div",{class:"flex h-10 w-10 items-center justify-center rounded-xl text-xl",style:v(`background:${u.value.catColor}15`)},a(u.value.icon),5),i("div",null,[i("p",ui,a(u.value.name),1),i("p",fi,a(u.value.catLabel)+" · Données de démonstration",1)])]),i("div",mi,[i("button",{class:"hidden sm:inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-bold text-white shadow-md transition hover:brightness-110",style:v(`background:${u.value.catColor}`),onClick:t[7]||(t[7]=s=>{h(u.value),A()})},[...t[27]||(t[27]=[i("svg",{class:"h-4 w-4",fill:"none",viewBox:"0 0 24 24",stroke:"currentColor","stroke-width":"2.5"},[i("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M12 4v16m8-8H4"})],-1),S(" Créer ce document ",-1)])],4),i("button",{class:"rounded-xl bg-gray-100 p-2.5 text-gray-500 transition hover:bg-gray-200",onClick:A},[...t[28]||(t[28]=[i("svg",{class:"h-4 w-4",fill:"none",viewBox:"0 0 24 24",stroke:"currentColor","stroke-width":"2.5"},[i("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M6 18L18 6M6 6l12 12"})],-1)])])])]),i("div",bi,[i("iframe",{class:"h-full w-full border-none",style:{"min-height":"500px"},sandbox:"allow-same-origin",srcdoc:ie(u.value)},null,8,xi)]),i("div",gi,[i("button",{class:"w-full rounded-xl py-3 text-sm font-bold text-white shadow-md transition hover:brightness-110",style:v(`background:${u.value.catColor}`),onClick:t[8]||(t[8]=s=>{h(u.value),A()})}," ✏️ Créer ce document ",4)])])])):m("",!0)]),_:1})]))]}),_:1})],64))}},Oi=ve(hi,[["__scopeId","data-v-0f655091"]]);export{Oi as default};

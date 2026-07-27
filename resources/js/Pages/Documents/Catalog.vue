<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { Head, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const CATS = [
  { id: 'vente', label: 'Vente & Facturation', icon: '💰', color: '#2563EB', docs: [
    { id: 'devis',       factproType: 'quote',             name: 'Devis',                    icon: '📋', desc: 'Proposition de prix détaillée et professionnelle',          pop: true  },
    { id: 'offre',       factproType: 'quote',             name: 'Offre Commerciale',        icon: '🤝', desc: "Présentation d'offre percutante avec argumentaire",         pop: false },
    { id: 'proposition', factproType: 'quote',             name: 'Proposition Commerciale',  icon: '💼', desc: 'Proposition complète et structurée',                        pop: false },
    { id: 'proforma',    factproType: 'proforma',  name: 'Facture Proforma',         icon: '📄', desc: 'Facture prévisionnelle avant commande officielle',           pop: true  },
    { id: 'bc_client',   factproType: 'sales_order',       name: 'Bon de Commande Client',   icon: '🛒', desc: 'Confirmation officielle des commandes reçues',               pop: true  },
    { id: 'bon_resa',    factproType: 'quote',             name: 'Bon de Réservation',       icon: '📅', desc: 'Confirmation de réservation produit ou service',             pop: false },
    { id: 'bon_prep',    factproType: 'delivery_note',     name: 'Bon de Préparation',       icon: '📦', desc: 'Ordre interne de préparation de commande',                  pop: false },
    { id: 'bon_liv',     factproType: 'delivery_note',     name: 'Bon de Livraison',         icon: '🚚', desc: 'Attestation officielle de livraison marchandises',          pop: true  },
    { id: 'ordre_liv',   factproType: 'delivery_note',     name: 'Ordre de Livraison',       icon: '📮', desc: 'Autorisation de libération des marchandises',               pop: false },
    { id: 'facture',     factproType: 'invoice',           name: 'Facture',                  icon: '🧾', desc: 'Facture commerciale standard — normes OHADA',               pop: true  },
    { id: 'fac_simple',  factproType: 'invoice',           name: 'Facture Simplifiée',       icon: '🗒️', desc: 'Facture allégée pour petits montants',                      pop: false },
    { id: 'fac_export',  factproType: 'invoice',           name: 'Facture Export',           icon: '🌍', desc: 'Facture pour transactions internationales',                 pop: false },
    { id: 'fac_exo',     factproType: 'invoice',           name: 'Facture Exonérée TVA',     icon: '🔖', desc: 'Facture sans TVA pour entreprises exonérées',               pop: false },
    { id: 'fac_rect',    factproType: 'credit_note',       name: 'Facture Rectificative',    icon: '✏️', desc: "Correction d'une facture déjà émise",                       pop: false },
    { id: 'fac_acompte', factproType: 'deposit_invoice',   name: "Facture d'Acompte",        icon: '💸', desc: "Facturation d'acompte sur commande en cours",               pop: true  },
    { id: 'fac_solde',   factproType: 'balance_invoice',   name: 'Facture de Solde',         icon: '✔️', desc: "Solde de facturation après versement d'acompte",            pop: false },
    { id: 'avoir',       factproType: 'credit_note',       name: 'Avoir / Note de Crédit',   icon: '↩️', desc: 'Note de crédit pour remboursement client',                  pop: false },
    { id: 'recu',        factproType: 'payment_receipt',           name: 'Reçu de Paiement',         icon: '✅', desc: 'Confirmation officielle de réception de paiement',          pop: true  },
    { id: 'ticket',      factproType: 'payment_receipt',           name: 'Ticket de Caisse',         icon: '🖨️', desc: 'Reçu de vente au comptant simplifié',                       pop: false },
    { id: 'contrat_com', factproType: 'invoice',           name: 'Contrat Commercial',       icon: '📝', desc: 'Accord contractuel entre parties commerciales',             pop: false },
    { id: 'fac_abo', factproType: 'invoice', name: "Facture Abonnement", icon: '🔄', desc: "Facturation récurrente de services par abonnement", pop: true },
    { id: 'fac_period', factproType: 'invoice', name: "Facture Périodique", icon: '📅', desc: "Facture émise à intervalles réguliers définis", pop: true },
    { id: 'contrat_cadre', factproType: 'invoice', name: "Contrat-Cadre", icon: '📋', desc: "Accord commercial global encadrant les transactions futures", pop: false },
    { id: 'bon_enl', factproType: 'delivery_note', name: "Bon Enlèvement", icon: '🚛', desc: "Autorisation client pour retrait marchandises entrepôt", pop: true },
    { id: 'avis_exp', factproType: 'delivery_note', name: "Avis Expédition", icon: '📨', desc: "Notification envoi de marchandises vers le destinataire", pop: true },
    { id: 'cert_liv', factproType: 'delivery_note', name: "Certificat Livraison", icon: '✅', desc: "Attestation officielle confirmant la bonne réception livraison", pop: false },
    { id: 'bon_gar', factproType: 'site_report', name: "Bon de Garantie", icon: '🛡️', desc: "Document attestant la garantie produit accordée client", pop: false },
    { id: 'fac_com', factproType: 'invoice', name: "Facture Commission", icon: '💼', desc: "Facturation des commissions sur ventes ou services", pop: false },
    { id: 'note_honor', factproType: 'invoice', name: "Note Honoraires", icon: '⚖️', desc: "Facture de prestation intellectuelle ou libérale", pop: true },
    { id: 'fac_interco', factproType: 'invoice', name: "Facture Interco", icon: '🏢', desc: "Facture entre entités d'un même groupe d'entreprises", pop: false },
    { id: 'fac_loc', factproType: 'invoice', name: "Facture Location", icon: '🔑', desc: "Facturation de location matériel ou espace commercial", pop: true },
    { id: 'fac_instal', factproType: 'invoice', name: "Facture Installation", icon: '🔧', desc: "Facturation des frais d'installation équipements ou logiciels", pop: false },
    { id: 'fac_serv', factproType: 'invoice', name: "Facture Service", icon: '🛠️', desc: "Facture générique pour prestations de service diverses", pop: true },
    { id: 'recu_partiel', factproType: 'payment_receipt', name: "Reçu Partiel", icon: '🧾', desc: "Reçu confirmant paiement partiel d'une facture", pop: false },
    { id: 'bord_vente', factproType: 'invoice', name: "Bordereau Vente", icon: '📄', desc: "Récapitulatif journalier ou hebdomadaire des ventes réalisées", pop: false },
    { id: 'fac_presta', factproType: 'invoice', name: "Facture Prestation", icon: '👷', desc: "Facturation détaillée de prestations réalisées chez client", pop: true },
    { id: 'lettre_conf', factproType: 'invoice', name: "Lettre Confirmation", icon: '✉️', desc: "Confirmation écrite d'une commande ou d'un accord", pop: false },
    { id: 'offre_promo', factproType: 'quote', name: "Offre Promotionnelle", icon: '🎁', desc: "Proposition commerciale avec remises ou conditions spéciales", pop: false },
    { id: 'devis_modif', factproType: 'quote', name: "Devis Modificatif", icon: '✏️', desc: "Révision d'un devis existant suite à changement de périmètre", pop: false },
    { id: 'fac_regul', factproType: 'invoice', name: "Facture Régularisation", icon: '⚙️', desc: "Ajustement de facturation pour corriger écarts constatés", pop: false },
    { id: 'cert_prise', factproType: 'site_report', name: "Cert. Prise en Charge", icon: '📝', desc: "Certificat officiel de prise en charge d'un dossier client", pop: false },
  ]},
  { id: 'achat', label: 'Achats & Fournisseurs', icon: '🏪', color: '#7C3AED', docs: [
    { id: 'dem_achat',     factproType: 'purchase_order', name: "Demande d'Achat",             icon: '📨', desc: "Demande interne d'approvisionnement",             pop: false },
    { id: 'dem_prix',      factproType: 'quote',          name: 'Demande de Prix',              icon: '💬', desc: 'Consultation de prix auprès des fournisseurs',    pop: false },
    { id: 'consult_f',     factproType: 'quote',          name: 'Consultation Fournisseur',     icon: '📞', desc: "Appel d'offres fournisseur structuré",             pop: false },
    { id: 'bc_f',          factproType: 'purchase_order', name: 'Bon de Commande Fournisseur',  icon: '📮', desc: 'Commande officielle passée à un fournisseur',     pop: true  },
    { id: 'br_f',          factproType: 'goods_receipt',  name: 'Bon de Réception',             icon: '📥', desc: 'Réception de marchandises fournisseur',           pop: true  },
    { id: 'fac_f',         factproType: 'invoice',        name: 'Facture Fournisseur',          icon: '🧾', desc: "Enregistrement d'une facture fournisseur",         pop: false },
    { id: 'avoir_f',       factproType: 'credit_note',    name: 'Avoir Fournisseur',            icon: '↩️', desc: "Note de crédit reçue d'un fournisseur",            pop: false },
    { id: 'retour_f',      factproType: 'delivery_note',  name: 'Bon de Retour Fournisseur',    icon: '🔄', desc: 'Retour de marchandises au fournisseur',            pop: false },
    { id: 'note_debit',    factproType: 'invoice',        name: 'Note de Débit',                icon: '📊', desc: 'Débit complémentaire sur une facture existante',  pop: false },
    { id: 'note_credit_f', factproType: 'credit_note',    name: 'Note de Crédit Fournisseur',   icon: '📊', desc: 'Crédit accordé par le fournisseur',               pop: false },
    { id: 'appel_offres', factproType: 'purchase_order', name: "Appel d'Offres", icon: '📢', desc: "Sollicitation concurrentielle de propositions fournisseurs", pop: true },
    { id: 'cahier_charges', factproType: 'purchase_order', name: "Cahier des Charges", icon: '📋', desc: "Spécifications techniques pour achat fournisseur", pop: true },
    { id: 'grille_eval_f', factproType: 'site_report', name: "Grille Évaluation F.", icon: '📊', desc: "Critères de sélection et notation des fournisseurs", pop: false },
    { id: 'contrat_cadre', factproType: 'purchase_order', name: "Contrat Cadre Achat", icon: '📜', desc: "Accord général encadrant les commandes récurrentes", pop: true },
    { id: 'avenant_cmd', factproType: 'purchase_order', name: "Avenant Commande", icon: '✏️', desc: "Modification contractuelle d'une commande existante", pop: false },
    { id: 'accuse_recep', factproType: 'goods_receipt', name: "Accusé Réception", icon: '✅', desc: "Confirmation formelle de réception d'une commande", pop: true },
    { id: 'fiche_homolog', factproType: 'site_report', name: "Fiche Homologation", icon: '🏷️', desc: "Validation officielle d'un nouveau fournisseur référencé", pop: false },
    { id: 'rapport_qual_r', factproType: 'site_report', name: "Rapport Qualité Récep.", icon: '🔍', desc: "Contrôle qualité des marchandises à la réception", pop: false },
    { id: 'litige_fourn', factproType: 'credit_note', name: "Litige Fournisseur", icon: '⚠️', desc: "Déclaration de différend commercial avec un fournisseur", pop: false },
    { id: 'dem_garantie', factproType: 'site_report', name: "Demande de Garantie", icon: '🛡️', desc: "Réclamation de garantie auprès d'un fournisseur", pop: false },
    { id: 'contrat_soustr', factproType: 'purchase_order', name: "Contrat Sous-Traitance", icon: '🤝', desc: "Délégation partielle de prestation à un sous-traitant", pop: true },
    { id: 'bord_prix', factproType: 'purchase_order', name: "Bordereau de Prix", icon: '💰', desc: "Référentiel tarifaire négocié avec le fournisseur", pop: true },
    { id: 'attest_conf_f', factproType: 'site_report', name: "Attestation Conformité", icon: '🎖️', desc: "Certification de conformité produit par le fournisseur", pop: false },
    { id: 'ordre_rappel', factproType: 'purchase_order', name: "Ordre de Rappel", icon: '🔔', desc: "Instruction de retrait de produit non conforme", pop: false },
    { id: 'plan_appro', factproType: 'purchase_order', name: "Plan Approvisionnement", icon: '📅', desc: "Planification des achats et réapprovisionnements périodiques", pop: true },
  ]},
  { id: 'stock', label: 'Stocks & Inventaire', icon: '📦', color: '#D97706', docs: [
    { id: 'be',           factproType: 'goods_receipt',  name: "Bon d'Entrée de Stock",     icon: '⬆️', desc: "Enregistrement d'une entrée en stock",           pop: false },
    { id: 'bs',           factproType: 'delivery_note',  name: 'Bon de Sortie de Stock',    icon: '⬇️', desc: "Enregistrement d'une sortie de stock",           pop: false },
    { id: 'transfert',    factproType: 'delivery_note',  name: 'Bon de Transfert',          icon: '↔️', desc: 'Transfert de stock entre entrepôts',             pop: false },
    { id: 'consommation', factproType: 'delivery_note',  name: 'Bon de Consommation',       icon: '🔧', desc: 'Consommation interne de matières ou produits',   pop: false },
    { id: 'inventaire',   factproType: 'invoice',        name: "Bon d'Inventaire",          icon: '📊', desc: 'Fiche de comptage et valorisation du stock',     pop: true  },
    { id: 'ajustement',   factproType: 'invoice',        name: 'Ajustement de Stock',       icon: '⚖️', desc: "Correction des écarts constatés à l'inventaire", pop: false },
    { id: 'destruction',  factproType: 'invoice',        name: 'Bon de Destruction / Casse',icon: '🗑️', desc: 'Mise au rebut de stock détérioré ou obsolète',   pop: false },
    { id: 'of',           factproType: 'invoice',        name: 'Ordre de Fabrication',      icon: '🏭', desc: "Lancement d'un ordre de production",             pop: false },
    { id: 'transform',    factproType: 'invoice',        name: 'Bon de Transformation',     icon: '🔄', desc: 'Transformation de produits en stock',            pop: false },
    { id: 'fiche_art', factproType: 'goods_receipt', name: "Fiche Article Stock", icon: '🗂️', desc: "Fiche détaillée d'un article en stock", pop: true },
    { id: 'rapport_peremp', factproType: 'site_report', name: "Rapport Péremption", icon: '⏰', desc: "Suivi des articles proches de péremption", pop: false },
    { id: 'bilan_stock', factproType: 'site_report', name: "Bilan de Stock", icon: '📊', desc: "Synthèse globale des niveaux de stock", pop: true },
    { id: 'pv_casse', factproType: 'site_report', name: "PV de Casse", icon: '💥', desc: "Procès-verbal officiel de destruction marchandise", pop: false },
    { id: 'inv_tournant', factproType: 'goods_receipt', name: "Inventaire Tournant", icon: '🔄', desc: "Comptage partiel et cyclique du stock", pop: true },
    { id: 'etiq_stock', factproType: 'goods_receipt', name: "Étiquette Stock", icon: '🏷️', desc: "Étiquette d'identification et de localisation article", pop: false },
    { id: 'fiche_magasin', factproType: 'goods_receipt', name: "Fiche Magasin", icon: '🏪', desc: "Fiche de gestion par emplacement magasin", pop: false },
    { id: 'alerte_reap', factproType: 'site_report', name: "Alerte Réapprovisionnement", icon: '🔔', desc: "Notification de seuil minimum stock atteint", pop: true },
    { id: 'rapport_rotat', factproType: 'site_report', name: "Rapport de Rotation", icon: '📈', desc: "Analyse de la rotation des articles stock", pop: false },
    { id: 'bord_pesee', factproType: 'delivery_note', name: "Bordereau de Pesée", icon: '⚖️', desc: "Relevé de poids à la réception marchandise", pop: false },
    { id: 'plan_entrepot', factproType: 'site_report', name: "Plan Entrepôt", icon: '🗺️', desc: "Cartographie et plan de rangement entrepôt", pop: false },
  ]},
  { id: 'sav', label: 'SAV & Maintenance', icon: '🔧', color: '#0891B2', docs: [
    { id: 'rma',          factproType: 'site_report', name: 'Bon de Retour RMA',        icon: '📦', desc: 'Retour de produit en garantie client',               pop: false },
    { id: 'fiche_sav',    factproType: 'site_report', name: 'Fiche SAV',                icon: '🔧', desc: 'Dossier complet de service après-vente',            pop: true  },
    { id: 'bon_rep',      factproType: 'site_report', name: 'Bon de Réparation',        icon: '⚙️', desc: "Ordre de réparation d'équipement ou matériel",     pop: true  },
    { id: 'rapport_int',  factproType: 'site_report', name: "Rapport d'Intervention",   icon: '📋', desc: "Compte-rendu d'intervention technique",            pop: true  },
    { id: 'bon_maint',    factproType: 'site_report', name: 'Bon de Maintenance',       icon: '🛠️', desc: 'Ordre de maintenance préventive ou curative',       pop: false },
    { id: 'cert_gar',     factproType: 'invoice',        name: 'Certificat de Garantie',   icon: '🏅', desc: 'Attestation de garantie produit ou service',        pop: false },
    { id: 'contrat_maint',factproType: 'invoice',        name: 'Contrat de Maintenance',   icon: '📝', desc: 'Contrat de maintenance périodique',                 pop: false },
    { id: 'devis_rep', factproType: 'quote', name: "Devis Réparation", icon: '🔧', desc: "Estimation du coût d'une réparation client", pop: true },
    { id: 'fiche_diagno', factproType: 'site_report', name: "Fiche Diagnostic", icon: '🔍', desc: "Diagnostic technique d'un équipement défaillant", pop: true },
    { id: 'rapport_expert', factproType: 'site_report', name: "Rapport Expertise", icon: '🧪', desc: "Expertise technique détaillée d'un équipement", pop: false },
    { id: 'cert_remise', factproType: 'payment_receipt', name: "Cert. Remise en État", icon: '✅', desc: "Certificat attestant la remise en état produit", pop: false },
    { id: 'plan_maint_p', factproType: 'site_report', name: "Planning Maintenance", icon: '📅', desc: "Calendrier de maintenance préventive planifiée", pop: true },
    { id: 'contrat_supp', factproType: 'site_report', name: "Contrat de Support", icon: '🤝', desc: "Contrat de support technique et assistance client", pop: true },
    { id: 'ticket_inc', factproType: 'site_report', name: "Ticket Incident", icon: '🎫', desc: "Enregistrement et suivi d'un incident client", pop: true },
    { id: 'pv_recept_rep', factproType: 'goods_receipt', name: "PV Réception SAV", icon: '📋', desc: "Procès-verbal de réception après réparation terminée", pop: false },
    { id: 'fiche_tech_p', factproType: 'site_report', name: "Fiche Technique Produit", icon: '📄', desc: "Spécifications techniques complètes d'un produit", pop: false },
    { id: 'notice_util', factproType: 'site_report', name: "Notice Utilisation", icon: '📖', desc: "Guide d'utilisation destiné à l'utilisateur final", pop: false },
    { id: 'rapport_test', factproType: 'site_report', name: "Rapport de Test", icon: '🧾', desc: "Résultats des tests effectués sur un équipement", pop: false },
    { id: 'bon_pret', factproType: 'delivery_note', name: "Bon de Prêt", icon: '🔁', desc: "Bon de prêt temporaire d'équipement au client", pop: false },
    { id: 'attest_conf', factproType: 'payment_receipt', name: "Attest. Conformité", icon: '🏅', desc: "Attestation de mise en conformité réglementaire produit", pop: false },
  ]},
  { id: 'btp', label: 'BTP & Travaux', icon: '🏗️', color: '#DC2626', docs: [
    { id: 'bon_trav',   factproType: 'invoice',         name: 'Bon de Travaux',              icon: '🔨', desc: "Ordre d'exécution de travaux de construction",   pop: true  },
    { id: 'os',         factproType: 'invoice',         name: 'Ordre de Service',            icon: '📋', desc: 'Instruction officielle de démarrage de chantier',pop: true  },
    { id: 'situation',  factproType: 'deposit_invoice', name: 'Situation de Travaux',        icon: '📐', desc: "Facturation d'avancement de chantier",           pop: true  },
    { id: 'decompte_p', factproType: 'deposit_invoice', name: 'Décompte Provisoire',         icon: '🔢', desc: 'Décompte intermédiaire des travaux réalisés',    pop: false },
    { id: 'decompte_d', factproType: 'balance_invoice', name: 'Décompte Définitif',          icon: '✔️', desc: 'Décompte final en fin de chantier',              pop: false },
    { id: 'pv_prov',    factproType: 'invoice',         name: 'PV Réception Provisoire',     icon: '📋', desc: 'Réception provisoire des travaux achevés',       pop: true  },
    { id: 'pv_def',     factproType: 'invoice',         name: 'PV Réception Définitive',     icon: '🏆', desc: 'Réception définitive — fin de période de garantie',pop: true },
    { id: 'rapport_ch', factproType: 'site_report',  name: 'Rapport de Chantier',         icon: '📊', desc: 'Compte-rendu journalier ou hebdo de chantier',   pop: false },
    { id: 'cctp', factproType: 'site_report', name: "CCTP", icon: '📋', desc: "Cahier des clauses techniques et prescriptions", pop: true },
    { id: 'dpgf', factproType: 'quote', name: "DPGF", icon: '📊', desc: "Décomposition du prix global et forfaitaire", pop: true },
    { id: 'soumission', factproType: 'quote', name: "Soumission", icon: '📝', desc: "Offre formelle de prix pour un marché", pop: true },
    { id: 'attach_trav', factproType: 'invoice', name: "Attachement Travaux", icon: '📎', desc: "Relevé contradictoire des quantités exécutées", pop: true },
    { id: 'mem_tech', factproType: 'site_report', name: "Mémoire Technique", icon: '🗂️', desc: "Document technique justificatif de l offre", pop: false },
    { id: 'plan_charge', factproType: 'site_report', name: "Plan de Charge", icon: '📅', desc: "Répartition des ressources sur le chantier", pop: false },
    { id: 'fiche_pose', factproType: 'site_report', name: "Fiche de Pose", icon: '🔧', desc: "Suivi installation d un équipement posé", pop: false },
    { id: 'rap_secu_ch', factproType: 'site_report', name: "Rapport Sécurité", icon: '⛑️', desc: "Rapport de sécurité hebdomadaire du chantier", pop: false },
    { id: 'cert_conf_tr', factproType: 'payment_receipt', name: "Cert. Conformité", icon: '✅', desc: "Certificat de conformité des travaux réalisés", pop: false },
    { id: 'ord_modif', factproType: 'purchase_order', name: "Ordre Modification", icon: '🔄', desc: "Instruction de modification d un ouvrage existant", pop: false },
    { id: 'avenant_trav', factproType: 'invoice', name: "Avenant Marché", icon: '📄', desc: "Modification contractuelle d un marché travaux", pop: true },
    { id: 'gantt_ch', factproType: 'site_report', name: "Planning Gantt", icon: '📆', desc: "Planning prévisionnel Gantt du chantier", pop: false },
    { id: 'journal_ch', factproType: 'site_report', name: "Journal de Chantier", icon: '📓', desc: "Registre journalier des activités du chantier", pop: true },
    { id: 'fiche_nc', factproType: 'site_report', name: "Fiche Non-Conformité", icon: '🚫', desc: "Signalement et suivi d une non-conformité chantier", pop: false },
    { id: 'bon_regie', factproType: 'invoice', name: "Bon de Régie", icon: '🧾', desc: "Facturation de travaux en régie journalière", pop: false },
    { id: 'cert_achev', factproType: 'payment_receipt', name: "Cert. Achèvement", icon: '🏅', desc: "Certificat d achèvement des travaux contractuels", pop: true },
    { id: 'attest_fin_tr', factproType: 'payment_receipt', name: "Attest. Fin Travaux", icon: '🎖️', desc: "Attestation officielle de fin de travaux", pop: false },
  ]},
  { id: 'logistique', label: 'Logistique & Transport', icon: '🚛', color: '#059669', docs: [
    { id: 'bon_exp',     factproType: 'delivery_note', name: "Bon d'Expédition",       icon: '📤', desc: "Bon d'envoi officiel de marchandises",             pop: true  },
    { id: 'lettre_v',    factproType: 'delivery_note', name: 'Lettre de Voiture',      icon: '📜', desc: 'Document de transport routier officiel',           pop: true  },
    { id: 'packing',     factproType: 'delivery_note', name: 'Packing List',           icon: '📋', desc: 'Liste de colisage pour expédition internationale', pop: true  },
    { id: 'bord_ch',     factproType: 'delivery_note', name: 'Bordereau de Chargement',icon: '📊', desc: 'Récapitulatif détaillé du chargement véhicule',   pop: false },
    { id: 'transfert_d', factproType: 'delivery_note', name: 'Transfert Inter-Dépôts', icon: '↔️', desc: 'Transfert de stock entre dépôts et entrepôts',    pop: false },
    { id: 'manifeste',   factproType: 'delivery_note', name: 'Manifeste de Livraison', icon: '📊', desc: 'Récapitulatif de toutes les livraisons effectuées',pop: false },
    { id: 'feuille_route', factproType: 'delivery_note', name: "Feuille de Route", icon: '🗺️', desc: "Itinéraire et missions du chauffeur livreur", pop: true },
    { id: 'bon_tournee', factproType: 'delivery_note', name: "Bon de Tournée", icon: '🚚', desc: "Liste des arrêts et colis d une tournée", pop: true },
    { id: 'fiche_charg', factproType: 'delivery_note', name: "Fiche Chargement", icon: '📦', desc: "Détail du chargement d un véhicule de transport", pop: false },
    { id: 'suivi_liv', factproType: 'delivery_note', name: "Suivi de Livraison", icon: '📍', desc: "État en temps réel des livraisons en cours", pop: false },
    { id: 'bord_retour', factproType: 'credit_note', name: "Bordereau Retour", icon: '↩️', desc: "Récapitulatif des colis retournés non livrés", pop: false },
    { id: 'rap_transport', factproType: 'site_report', name: "Rapport Transport", icon: '📋', desc: "Compte rendu d une opération de transport", pop: false },
    { id: 'bon_ramassage', factproType: 'goods_receipt', name: "Bon de Ramassage", icon: '🗑️', desc: "Collecte de marchandises chez un client", pop: false },
    { id: 'planning_liv', factproType: 'site_report', name: "Planning Livraisons", icon: '🗓️', desc: "Calendrier prévisionnel des livraisons planifiées", pop: false },
    { id: 'fiche_vehic', factproType: 'site_report', name: "Fiche Véhicule", icon: '🚛', desc: "Fiche d entretien et suivi du véhicule", pop: false },
    { id: 'rap_accident', factproType: 'site_report', name: "Rapport Accident", icon: '⚠️', desc: "Déclaration d accident survenu lors du transport", pop: false },
    { id: 'attest_liv', factproType: 'payment_receipt', name: "Attest. Livraison", icon: '✔️', desc: "Attestation confirmant la livraison effectuée", pop: false },
    { id: 'fiche_decharg', factproType: 'goods_receipt', name: "Fiche Déchargement", icon: '🏭', desc: "Contrôle et enregistrement du déchargement reçu", pop: false },
    { id: 'bon_colisage', factproType: 'delivery_note', name: "Bon de Colisage", icon: '📬', desc: "Liste des colis et contenus d un envoi", pop: false },
    { id: 'fiche_exp_exp', factproType: 'delivery_note', name: "Expédition Express", icon: '⚡', desc: "Fiche d expédition rapide pour envoi urgent", pop: false },
  ]},
  { id: 'finance', label: 'Finance & Trésorerie', icon: '💳', color: '#0284C7', docs: [
    { id: 'bord_rem',     factproType: 'payment_receipt',        name: 'Bordereau de Remise',         icon: '📄', desc: 'Remise de chèques ou effets en banque',             pop: false },
    { id: 'note_frais',   factproType: 'expense_report', name: 'Note de Frais',               icon: '🧾', desc: 'Remboursement de frais professionnels engagés',     pop: true  },
    { id: 'bon_caisse',   factproType: 'cash_voucher',   name: 'Bon de Caisse',               icon: '💵', desc: "Mouvement d'entrée ou sortie de caisse",            pop: true  },
    { id: 'depot_banc',   factproType: 'payment_receipt',        name: 'Bordereau de Dépôt Bancaire', icon: '🏦', desc: 'Dépôt de fonds en agence bancaire',                pop: false },
    { id: 'retrait_banc', factproType: 'payment_receipt',        name: 'Bon de Retrait Bancaire',     icon: '🏧', desc: 'Retrait de fonds en agence bancaire',              pop: false },
    { id: 'effet',        factproType: 'invoice',        name: 'Effet de Commerce / Traite',  icon: '📄', desc: 'Lettre de change — instrument de paiement différé',pop: false },
    { id: 'billet_ordre', factproType: 'invoice',        name: 'Billet à Ordre',              icon: '📝', desc: 'Engagement de paiement à une date fixée',          pop: false },
    { id: 'plan_financement', factproType: 'expense_report', name: "Plan de financement", icon: '📊', desc: "Planification des sources et emplois de fonds", pop: true },
    { id: 'tableau_amort', factproType: 'expense_report', name: "Tableau amortissement", icon: '📉', desc: "Échéancier de remboursement d un prêt", pop: true },
    { id: 'rapproch_banc', factproType: 'expense_report', name: "Rapprochement bancaire", icon: '🏦', desc: "État de concordance solde caisse et banque", pop: true },
    { id: 'budget_prev', factproType: 'expense_report', name: "Budget prévisionnel", icon: '🎯', desc: "Projection des recettes et dépenses futures", pop: true },
    { id: 'rapport_treso', factproType: 'expense_report', name: "Rapport trésorerie", icon: '💰', desc: "Synthèse des flux de liquidités disponibles", pop: true },
    { id: 'solde_compte', factproType: 'cash_voucher', name: "Solde de compte", icon: '🔢', desc: "Relevé ponctuel du solde d un compte", pop: false },
    { id: 'virement_int', factproType: 'cash_voucher', name: "Virement interne", icon: '🔄', desc: "Transfert de fonds entre comptes internes", pop: false },
    { id: 'cheque_paie', factproType: 'cash_voucher', name: "Chèque de paiement", icon: '📝', desc: "Document de paiement par chèque émis", pop: false },
    { id: 'recu_virement', factproType: 'payment_receipt', name: "Reçu de virement", icon: '✅', desc: "Confirmation de réception d un virement bancaire", pop: false },
    { id: 'avis_debit', factproType: 'cash_voucher', name: "Avis de débit", icon: '📤', desc: "Notification de débit émis par la banque", pop: false },
    { id: 'avis_credit', factproType: 'cash_voucher', name: "Avis de crédit", icon: '📥', desc: "Notification de crédit reçu en banque", pop: false },
    { id: 'plan_rembours', factproType: 'expense_report', name: "Plan de remboursement", icon: '🗓️', desc: "Calendrier détaillé des remboursements dus", pop: false },
    { id: 'attest_solde', factproType: 'payment_receipt', name: "Attestation de solde", icon: '🏅', desc: "Certificat officiel du solde d un compte", pop: false },
    { id: 'releve_simple', factproType: 'expense_report', name: "Relevé de compte", icon: '📋', desc: "Historique simplifié des opérations du compte", pop: true },
    { id: 'caisse_journ', factproType: 'cash_voucher', name: "Caisse journalière", icon: '🗂️', desc: "Fiche de suivi des encaissements quotidiens", pop: true },
    { id: 'etat_impayes', factproType: 'expense_report', name: "État des impayés", icon: '⚠️', desc: "Liste des créances non réglées en souffrance", pop: true },
    { id: 'rapport_fin_m', factproType: 'expense_report', name: "Rapport financier mensuel", icon: '📈', desc: "Bilan mensuel des indicateurs financiers clés", pop: true },
    { id: 'dem_credit_int', factproType: 'deposit_invoice', name: "Demande crédit interne", icon: '🤝', desc: "Demande de financement interne à l entreprise", pop: false },
    { id: 'lettre_mise_p', factproType: 'site_report', name: "Lettre mise à pied", icon: '⛔', desc: "Notification de suspension temporaire du salarié", pop: false },
    { id: 'pv_discipline', factproType: 'site_report', name: "PV conseil discipline", icon: '⚖️', desc: "Procès-verbal de la séance disciplinaire tenue", pop: false },
    { id: 'fiche_accident', factproType: 'site_report', name: "Fiche accident travail", icon: '🚑', desc: "Déclaration d un accident survenu au travail", pop: false },
    { id: 'avenant_contrat', factproType: 'site_report', name: "Avenant contrat", icon: '✏️', desc: "Modification officielle d une clause du contrat", pop: false },
    { id: 'rapport_fin_st', factproType: 'site_report', name: "Rapport fin de stage", icon: '📝', desc: "Bilan écrit remis à la fin du stage effectué", pop: false },
    { id: 'lettre_recomm', factproType: 'site_report', name: "Lettre de recommandation", icon: '⭐', desc: "Témoignage positif officiel sur un ancien collaborateur", pop: false },
  ]},
  { id: 'rh', label: 'Ressources Humaines', icon: '👥', color: '#7C3AED', docs: [
    { id: 'ordre_miss',  factproType: 'site_report', name: 'Ordre de Mission',        icon: '✈️', desc: 'Autorisation officielle de déplacement professionnel',pop: true  },
    { id: 'dem_conge',   factproType: 'invoice',        name: 'Demande de Congé',        icon: '🏖️', desc: 'Formulaire de demande de congé payé',                pop: true  },
    { id: 'bulletin',    factproType: 'payslip',        name: 'Bulletin de Paie',        icon: '💰', desc: 'Fiche de salaire mensuelle détaillée',               pop: true  },
    { id: 'avance_sal',  factproType: 'deposit_invoice',name: 'Avance sur Salaire',      icon: '💵', desc: 'Demande d\'avance sur rémunération mensuelle',       pop: false },
    { id: 'note_serv',   factproType: 'invoice',        name: 'Note de Service',         icon: '📢', desc: 'Communication interne officielle de direction',      pop: true  },
    { id: 'auto_abs',    factproType: 'invoice',        name: "Autorisation d'Absence",  icon: '📝', desc: "Autorisation d'absence exceptionnelle motivée",      pop: false },
    { id: 'contrat_cdi', factproType: 'site_report', name: "Contrat CDI", icon: '📃', desc: "Contrat de travail à durée indéterminée", pop: true },
    { id: 'contrat_cdd', factproType: 'site_report', name: "Contrat CDD", icon: '📄', desc: "Contrat de travail à durée déterminée", pop: true },
    { id: 'contrat_stage', factproType: 'site_report', name: "Contrat de stage", icon: '🎓', desc: "Convention de stage entre stagiaire et entreprise", pop: true },
    { id: 'contrat_partiel', factproType: 'site_report', name: "Contrat temps partiel", icon: '⏱️', desc: "Contrat de travail à temps partiel signé", pop: false },
    { id: 'lettre_embauche', factproType: 'site_report', name: "Lettre d embauche", icon: '✉️', desc: "Offre officielle d emploi adressée au candidat", pop: true },
    { id: 'lettre_licenc', factproType: 'site_report', name: "Lettre de licenciement", icon: '📮', desc: "Notification formelle de rupture du contrat", pop: false },
    { id: 'lettre_demiss', factproType: 'site_report', name: "Lettre de démission", icon: '🚪', desc: "Déclaration de départ volontaire du salarié", pop: false },
    { id: 'solde_tt_cpt', factproType: 'payment_receipt', name: "Solde de tout compte", icon: '🔏', desc: "Reçu final de toutes sommes dues au salarié", pop: true },
    { id: 'attest_travail', factproType: 'site_report', name: "Attestation de travail", icon: '🗒️', desc: "Justificatif officiel d emploi en cours ou passé", pop: true },
    { id: 'attest_salaire', factproType: 'site_report', name: "Attestation de salaire", icon: '💼', desc: "Preuve de la rémunération perçue par le salarié", pop: true },
    { id: 'cert_travail', factproType: 'site_report', name: "Certificat de travail", icon: '🏆', desc: "Document remis à la fin du contrat de travail", pop: false },
    { id: 'fiche_poste', factproType: 'site_report', name: "Fiche de poste", icon: '📌', desc: "Description détaillée des missions et responsabilités", pop: false },
    { id: 'grille_sal', factproType: 'payslip', name: "Grille salariale", icon: '💹', desc: "Barème des salaires par catégorie et échelon", pop: false },
    { id: 'planning_conges', factproType: 'site_report', name: "Planning congés", icon: '🏖️', desc: "Calendrier annuel des absences autorisées planifiées", pop: true },
    { id: 'fiche_presence', factproType: 'site_report', name: "Fiche de présence", icon: '🕐', desc: "Relevé quotidien des heures de présence salariée", pop: true },
    { id: 'rapport_eval', factproType: 'site_report', name: "Rapport d évaluation", icon: '📊', desc: "Appréciation annuelle des performances du salarié", pop: false },
    { id: 'lettre_avert', factproType: 'site_report', name: "Lettre d avertissement", icon: '🔔', desc: "Sanction écrite pour manquement aux obligations professionnelles", pop: false },
    { id: 'accord_conf_rh', factproType: 'site_report', name: "Accord confidentialité RH", icon: '🔐', desc: "Engagement de non-divulgation signé par le salarié", pop: false },
    { id: 'decl_embauche', factproType: 'site_report', name: "Déclaration d embauche", icon: '📑', desc: "Déclaration préalable d embauche auprès des organismes", pop: false },
    { id: 'recu_materiel', factproType: 'payment_receipt', name: "Reçu remise matériel", icon: '📦', desc: "Accusé de réception des équipements remis au salarié", pop: false },
    { id: 'fiche_form_rh', factproType: 'site_report', name: "Fiche formation RH", icon: '📚', desc: "Suivi des formations suivies par le salarié", pop: false },
    { id: 'registre_pers', factproType: 'site_report', name: "Registre du personnel", icon: '📒', desc: "Fiche individuelle de chaque salarié de l entreprise", pop: false },
    { id: 'attest_stage', factproType: 'site_report', name: "Attestation de stage", icon: '🎖️', desc: "Certification officielle de la réalisation du stage", pop: true },
  ]},
  { id: 'admin', label: 'Administratif & Juridique', icon: '⚖️', color: '#4F46E5', docs: [
    { id: 'contrat',  factproType: 'invoice',        name: 'Contrat',             icon: '📝', desc: 'Contrat commercial ou de prestation de services',pop: true  },
    { id: 'pv_reun',  factproType: 'site_report', name: 'Procès-Verbal',       icon: '📋', desc: "PV de réunion, d'assemblée ou de décision",      pop: true  },
    { id: 'attest',   factproType: 'invoice',        name: 'Attestation',         icon: '🏅', desc: 'Attestation officielle toutes natures',           pop: true  },
    { id: 'certif',   factproType: 'invoice',        name: 'Certificat',          icon: '🎓', desc: 'Certificat professionnel ou de conformité',       pop: false },
    { id: 'relance',  factproType: 'invoice',        name: 'Lettre de Relance',   icon: '📩', desc: "Relance amiable d'un impayé ou d'un document",    pop: true  },
    { id: 'mise_dem', factproType: 'invoice',        name: 'Mise en Demeure',     icon: '⚠️', desc: 'Mise en demeure formelle avant contentieux',      pop: false },
    { id: 'accuse',   factproType: 'invoice',        name: 'Accusé de Réception', icon: '✅', desc: 'Confirmation officielle de réception de document',pop: false },
    { id: 'autoris',  factproType: 'invoice',        name: 'Autorisation',        icon: '🔓', desc: 'Autorisation administrative ou opérationnelle',   pop: false },
    { id: 'statuts_soc', factproType: 'site_report', name: "Statuts de Société", icon: '🏛️', desc: "Document fondateur de la personne morale", pop: true },
    { id: 'regl_int', factproType: 'site_report', name: "Règlement Intérieur", icon: '📋', desc: "Charte des règles internes de l'entreprise", pop: false },
    { id: 'deleg_pouv', factproType: 'site_report', name: "Délégation de Pouvoirs", icon: '🤝', desc: "Transfert autorité à un représentant désigné", pop: true },
    { id: 'procuration', factproType: 'site_report', name: "Procuration", icon: '✍️', desc: "Mandat donné à un tiers pour agir", pop: true },
    { id: 'conv_part', factproType: 'site_report', name: "Convention Partenariat", icon: '🔗', desc: "Accord formel entre deux entités partenaires", pop: true },
    { id: 'accord_conf', factproType: 'site_report', name: "Accord Confidentialité", icon: '🔒', desc: "Protection des informations sensibles partagées", pop: true },
    { id: 'charte_eth', factproType: 'site_report', name: "Charte Éthique", icon: '⚖️', desc: "Engagements déontologiques de organisation", pop: false },
    { id: 'rapport_ag', factproType: 'site_report', name: "Rapport AG", icon: '📊', desc: "Compte-rendu officiel des délibérations AG", pop: false },
    { id: 'resol_cons', factproType: 'site_report', name: "Résolution du Conseil", icon: '📜', desc: "Décision formelle du conseil d'administration", pop: false },
    { id: 'avis_conv_ag', factproType: 'site_report', name: "Avis Convocation AG", icon: '📣', desc: "Convocation officielle des actionnaires à l'assemblée", pop: false },
    { id: 'lettre_notif', factproType: 'site_report', name: "Lettre Notification", icon: '📨', desc: "Communication officielle à une partie concernée", pop: false },
    { id: 'dem_autor', factproType: 'site_report', name: "Demande Autorisation", icon: '📝', desc: "Requête formelle auprès d'une autorité", pop: false },
    { id: 'recep_depot', factproType: 'payment_receipt', name: "Récépissé de Dépôt", icon: '🗂️', desc: "Preuve de dépôt d'un document officiel", pop: false },
    { id: 'decis_dir', factproType: 'site_report', name: "Décision de Direction", icon: '📌', desc: "Acte unilatéral émanant de la direction", pop: false },
    { id: 'accord_trans', factproType: 'site_report', name: "Accord Transactionnel", icon: '🤜', desc: "Règlement amiable d'un différend entre parties", pop: false },
    { id: 'cdc_ao', factproType: 'site_report', name: "Cahier des Charges AO", icon: '📂', desc: "Spécifications pour un appel d'offres public", pop: true },
    { id: 'memoire_exp', factproType: 'site_report', name: "Mémoire Explicatif", icon: '📄', desc: "Note détaillée justifiant une demande ou projet", pop: false },
    { id: 'lettre_resil', factproType: 'site_report', name: "Lettre de Résiliation", icon: '❌', desc: "Notification formelle de fin de contrat", pop: true },
    { id: 'avis_reun', factproType: 'site_report', name: "Avis Convocation Réunion", icon: '📅', desc: "Invitation officielle à une réunion interne", pop: false },
    { id: 'cr_reun', factproType: 'site_report', name: "Compte-Rendu Réunion", icon: '📓', desc: "Synthèse des décisions prises en réunion", pop: true },
    { id: 'proto_accord', factproType: 'site_report', name: "Protocole Accord", icon: '🤲', desc: "Document préparatoire à un accord définitif", pop: false },
    { id: 'lettre_gar', factproType: 'site_report', name: "Lettre de Garantie", icon: '🛡️', desc: "Engagement de garantie émis par une entité", pop: true },
    { id: 'attest_stage', factproType: 'site_report', name: "Attestation Stage Étudiant", icon: '🏅', desc: "Certificat de réalisation d'un stage académique", pop: true },
  ]},
  { id: 'immobilier', label: 'Immobilier & Location', icon: '🏠', color: '#DB2777', docs: [
    { id: 'bail',        factproType: 'invoice', name: 'Contrat de Bail',           icon: '🔑', desc: 'Contrat de location immobilière résidentiel ou commercial',pop: true  },
    { id: 'edle',        factproType: 'invoice', name: "État des Lieux d'Entrée",   icon: '🏡', desc: "Constat d'état à l'entrée du locataire",                  pop: true  },
    { id: 'edls',        factproType: 'invoice', name: 'État des Lieux de Sortie',  icon: '🚪', desc: 'Constat d\'état au départ du locataire',                  pop: true  },
    { id: 'appel_l',     factproType: 'invoice', name: 'Appel de Loyer',            icon: '💳', desc: 'Avis mensuel de loyer et charges à régler',              pop: true  },
    { id: 'quittance_l', factproType: 'payment_receipt', name: 'Quittance de Loyer',        icon: '✅', desc: 'Reçu de paiement de loyer mensuel',                      pop: true  },
    { id: 'prom_vente', factproType: 'site_report', name: "Promesse de Vente", icon: '🏠', desc: "Engagement unilatéral de vendre un bien immobilier", pop: true },
    { id: 'compro_vente', factproType: 'site_report', name: "Compromis de Vente", icon: '🏡', desc: "Accord bilatéral avant acte de vente définitif", pop: true },
    { id: 'mand_vente', factproType: 'site_report', name: "Mandat de Vente", icon: '📋', desc: "Délégation à un agent pour vendre un bien", pop: true },
    { id: 'mand_loc', factproType: 'site_report', name: "Mandat de Location", icon: '🔑', desc: "Autorisation de louer un bien par un mandataire", pop: false },
    { id: 'fiche_bien', factproType: 'site_report', name: "Fiche Descriptive Bien", icon: '🏢', desc: "Description technique d'un bien immobilier", pop: false },
    { id: 'rapp_visite', factproType: 'site_report', name: "Rapport de Visite", icon: '👁️', desc: "Compte-rendu d'une visite d'un bien immobilier", pop: false },
    { id: 'offre_achat', factproType: 'invoice', name: "Offre Achat Immobilier", icon: '💰', desc: "Proposition financière pour acquérir un bien", pop: true },
    { id: 'acte_caution', factproType: 'site_report', name: "Acte de Caution", icon: '🤝', desc: "Engagement d'un garant pour un locataire", pop: false },
    { id: 'edl_contra', factproType: 'site_report', name: "État des Lieux", icon: '🏚️', desc: "Constat contradictoire de l'état du logement", pop: true },
    { id: 'notice_loc', factproType: 'site_report', name: "Notice Info Locataire", icon: 'ℹ️', desc: "Document d'information remis au nouveau locataire", pop: false },
    { id: 'revis_loyer', factproType: 'invoice', name: "Révision de Loyer", icon: '📈', desc: "Notification d'ajustement du montant du loyer", pop: false },
    { id: 'resil_bail', factproType: 'site_report', name: "Résiliation de Bail", icon: '🚪', desc: "Document officiel de fin de bail locatif", pop: true },
    { id: 'dem_depot_g', factproType: 'invoice', name: "Demande Dépôt Garantie", icon: '💵', desc: "Réclamation du dépôt de garantie au locataire", pop: false },
    { id: 'attest_dom', factproType: 'site_report', name: "Attestation de Domicile", icon: '🏠', desc: "Justificatif officiel du lieu de résidence", pop: true },
    { id: 'fiche_loc', factproType: 'site_report', name: "Fiche Rens. Locataire", icon: '📋', desc: "Dossier de candidature du locataire potentiel", pop: false },
  ]},
  { id: 'export', label: 'Export & Douane', icon: '🌍', color: '#B45309', docs: [
    { id: 'cert_orig', factproType: 'invoice',       name: "Certificat d'Origine",   icon: '📜', desc: "Attestation de l'origine des marchandises exportées",pop: true  },
    { id: 'decl_d',    factproType: 'invoice',       name: 'Déclaration Douanière',  icon: '🏛️', desc: 'Déclaration en douane import ou export',            pop: true  },
    { id: 'bon_emb',   factproType: 'delivery_note', name: "Bon d'Embarquement",     icon: '🚢', desc: "Autorisation d'embarquement marchandises",          pop: false },
    { id: 'fac_exp_d', factproType: 'invoice',       name: 'Facture Export (Douane)',icon: '🧾', desc: 'Facture conforme aux exigences douanières',          pop: true  },
    { id: 'connaiss_bl', factproType: 'delivery_note', name: "Connaissement B/L", icon: '🚢', desc: "Titre de transport maritime de marchandises", pop: true },
    { id: 'cert_phyto', factproType: 'site_report', name: "Certificat Phytosanitaire", icon: '🌿', desc: "Contrôle sanitaire des végétaux à l'export", pop: true },
    { id: 'cert_sanit', factproType: 'site_report', name: "Certificat Sanitaire", icon: '🩺', desc: "Attestation de conformité sanitaire produits animaux", pop: true },
    { id: 'carnet_ata', factproType: 'site_report', name: "Carnet ATA", icon: '🗃️', desc: "Admission temporaire internationale de marchandises", pop: false },
    { id: 'note_fr_dou', factproType: 'expense_report', name: "Note Frais Douaniers", icon: '🏛️', desc: "Récapitulatif des frais de dédouanement payés", pop: false },
    { id: 'cert_inspec', factproType: 'site_report', name: "Certificat Inspection", icon: '🔍', desc: "Attestation de conformité par un organisme tiers", pop: true },
    { id: 'letcred_doc', factproType: 'invoice', name: "Lettre Crédit Documentaire", icon: '📃', desc: "Instrument bancaire garantissant le paiement export", pop: true },
    { id: 'bon_del_dou', factproType: 'delivery_note', name: "Bon à Délivrer Douane", icon: '✅', desc: "Autorisation douanière de livraison des marchandises", pop: false },
    { id: 'decl_valeur', factproType: 'site_report', name: "Déclaration de Valeur", icon: '💲', desc: "Déclaration officielle de la valeur marchande exportée", pop: false },
    { id: 'liste_px_exp', factproType: 'invoice', name: "Liste Prix Export", icon: '📋', desc: "Tarifs spécifiques pour les clients à l'export", pop: false },
    { id: 'cert_ass_tr', factproType: 'site_report', name: "Certificat Assurance Transport", icon: '🛡️', desc: "Couverture assurance pour marchandises en transit", pop: false },
  ]},
  { id: 'sante', label: 'Santé & Médical', icon: '🏥', color: '#0891B2', docs: [
    { id: 'fac_med',   factproType: 'invoice', name: 'Facture Médicale',   icon: '🏥', desc: 'Facture de consultations et soins médicaux',  pop: true  },
    { id: 'ordo',      factproType: 'invoice', name: 'Ordonnance',         icon: '💊', desc: 'Prescription médicale du praticien',           pop: true  },
    { id: 'bon_labo',  factproType: 'invoice', name: 'Bon de Laboratoire', icon: '🔬', desc: "Prescription d'analyses biologiques",          pop: false },
    { id: 'feuille_s', factproType: 'invoice', name: 'Feuille de Soins',   icon: '📋', desc: 'Feuille de soins pour remboursement assurance',pop: false },
    { id: 'bon_hospit', factproType: 'invoice', name: "Bon Hospitalisation", icon: '🏥', desc: "Autorisation d'admission patient en structure médicale", pop: true },
    { id: 'cert_med', factproType: 'site_report', name: "Certificat Médical", icon: '🩺', desc: "Attestation médicale émise par un praticien", pop: true },
    { id: 'rapp_radio', factproType: 'site_report', name: "Rapport de Radiologie", icon: '🔬', desc: "Résultats d'un examen radiologique interprété", pop: false },
    { id: 'consent_pat', factproType: 'site_report', name: "Consentement Patient", icon: '✍️', desc: "Accord éclairé du patient avant un acte médical", pop: true },
    { id: 'fiche_pat', factproType: 'site_report', name: "Fiche Patient", icon: '📋', desc: "Dossier d'identification et antécédents patient", pop: true },
    { id: 'rapp_consult', factproType: 'site_report', name: "Rapport de Consultation", icon: '📝', desc: "Compte-rendu d'une consultation médicale effectuée", pop: false },
    { id: 'bon_tr_pat', factproType: 'delivery_note', name: "Bon Transport Patient", icon: '🚑', desc: "Autorisation de transport médicalisé d'un patient", pop: false },
    { id: 'prescr_kine', factproType: 'site_report', name: "Prescription Kiné", icon: '💪', desc: "Ordonnance pour séances de kinésithérapie prescrites", pop: false },
    { id: 'cert_aptit', factproType: 'site_report', name: "Certificat Aptitude Médicale", icon: '✅', desc: "Attestation d'aptitude physique pour une activité", pop: true },
    { id: 'fiche_vaccin', factproType: 'site_report', name: "Fiche de Vaccination", icon: '💉', desc: "Carnet de suivi des vaccinations reçues", pop: false },
    { id: 'bon_exam_sp', factproType: 'site_report', name: "Bon Examens Spécialisés", icon: '🔭', desc: "Prescription d'examens auprès de spécialistes", pop: false },
    { id: 'rapp_operat', factproType: 'site_report', name: "Rapport Opératoire", icon: '⚕️', desc: "Compte-rendu d'une intervention chirurgicale", pop: false },
    { id: 'doss_med_s', factproType: 'site_report', name: "Dossier Médical Simplifié", icon: '📁', desc: "Synthèse simplifiée du dossier médical patient", pop: false },
    { id: 'attest_med_a', factproType: 'site_report', name: "Attestation Médicale Assurance", icon: '📄', desc: "Document médical requis pour remboursement assurance", pop: true },
    { id: 'bon_med_urg', factproType: 'delivery_note', name: "Bon Médicaments Urgents", icon: '🚨', desc: "Bon de sortie médicaments pour une urgence", pop: false },
    { id: 'fac_clin', factproType: 'invoice', name: "Facture Clinique/Hôpital", icon: '🏥', desc: "Facturation des soins dispensés en établissement", pop: true },
  ]},
  { id: 'education', label: 'Éducation & Formation', icon: '🎓', color: '#7C3AED', docs: [
    { id: 'recu_scol',   factproType: 'payment_receipt', name: 'Reçu de Scolarité',       icon: '🏫', desc: 'Reçu de paiement de frais de scolarité',           pop: true  },
    { id: 'fac_form',    factproType: 'invoice', name: 'Facture de Formation',     icon: '📚', desc: 'Facture de prestation de formation professionnelle',pop: true  },
    { id: 'attest_paie', factproType: 'payment_receipt', name: 'Attestation de Paiement', icon: '✅', desc: 'Attestation de règlement de frais scolaires',       pop: true  },
    { id: 'bul_notes',   factproType: 'invoice', name: 'Bulletin de Notes',       icon: '📝', desc: 'Bulletin scolaire trimestriel ou semestriel',       pop: false },
    { id: 'contrat_form', factproType: 'site_report', name: "Contrat de Formation", icon: '📘', desc: "Accord de formation professionnelle entre parties", pop: true },
    { id: 'cert_scol', factproType: 'site_report', name: "Certificat de Scolarité", icon: '🎓', desc: "Attestation officielle d'inscription dans un établissement", pop: true },
    { id: 'prog_pedag', factproType: 'site_report', name: "Programme Pédagogique", icon: '📚', desc: "Plan détaillé des contenus et objectifs de formation", pop: false },
    { id: 'diplome_cert', factproType: 'site_report', name: "Diplôme / Certificat", icon: '🎖️', desc: "Titre officiel attestant une qualification obtenue", pop: true },
    { id: 'convoc_exam', factproType: 'site_report', name: "Convocation Examen", icon: '📅', desc: "Document officiel de convocation à un examen", pop: true },
    { id: 'releve_notes', factproType: 'site_report', name: "Relevé de Notes", icon: '📊', desc: "Récapitulatif des résultats académiques d'un élève", pop: true },
    { id: 'lettre_recom', factproType: 'site_report', name: "Lettre Recommandation", icon: '⭐', desc: "Recommandation écrite pour un étudiant méritant", pop: false },
    { id: 'fiche_inscr', factproType: 'site_report', name: "Fiche Inscription", icon: '📝', desc: "Formulaire d'enregistrement d'un apprenant", pop: true },
    { id: 'accord_alter', factproType: 'site_report', name: "Accord Alternance", icon: '🔄', desc: "Convention entre entreprise et établissement scolaire", pop: false },
    { id: 'planning_crs', factproType: 'site_report', name: "Planning de Cours", icon: '🗓️', desc: "Calendrier détaillé des séances de cours prévues", pop: false },
    { id: 'rapp_pedag', factproType: 'site_report', name: "Rapport Pédagogique", icon: '📋', desc: "Bilan des activités et résultats pédagogiques", pop: false },
    { id: 'bon_tr_scol', factproType: 'delivery_note', name: "Bon Transport Scolaire", icon: '🚌', desc: "Titre de transport pour élèves d'un établissement", pop: false },
    { id: 'cert_fin_for', factproType: 'site_report', name: "Certificat Fin Formation", icon: '🏆', desc: "Attestation de réussite et fin d'une formation", pop: true },
    { id: 'autor_par', factproType: 'site_report', name: "Autorisation Parentale", icon: '👨‍👧', desc: "Accord parental pour une sortie scolaire organisée", pop: false },
    { id: 'liste_fourni', factproType: 'site_report', name: "Liste de Fournitures", icon: '✏️', desc: "Liste officielle des fournitures scolaires requises", pop: false },
  ]},
  { id: 'resto',  label: 'Restauration & Hôtellerie', icon: '🍽️', color: '#EA580C', docs: [
    { id: 'note_table', factproType: 'payment_receipt', name: "Note de Table", icon: '🧾', desc: "Addition client en fin de repas au restaurant", pop: true },
    { id: 'fac_resto', factproType: 'invoice', name: "Facture Restaurant", icon: '🍽️', desc: "Facture officielle pour repas servis au restaurant", pop: true },
    { id: 'bon_table', factproType: 'cash_voucher', name: "Bon de Table", icon: '📋', desc: "Bon de commande interne par table client", pop: true },
    { id: 'menu_jour', factproType: 'quote', name: "Menu du Jour", icon: '📅', desc: "Proposition de plats du jour avec prix fixes", pop: false },
    { id: 'resa_table', factproType: 'payment_receipt', name: "Réservation Table", icon: '📞', desc: "Confirmation de réservation de table restaurant", pop: true },
    { id: 'fac_hotel', factproType: 'invoice', name: "Facture Hôtel", icon: '🏨', desc: "Facture de séjour avec prestations hôtelières détaillées", pop: true },
    { id: 'fiche_chambre', factproType: 'site_report', name: "Fiche de Chambre", icon: '🛏️', desc: "Fiche client et consommations par chambre occupée", pop: true },
    { id: 'etat_ch_hotel', factproType: 'site_report', name: "État Lieux Chambre", icon: '🔍', desc: "Constat état chambre à l'entrée et sortie client", pop: false },
    { id: 'bc_cuisine', factproType: 'purchase_order', name: "Bon Commande Cuisine", icon: '👨‍🍳', desc: "Commande interne des plats transmise en cuisine", pop: false },
    { id: 'fiche_prod_r', factproType: 'site_report', name: "Fiche de Production", icon: '📊', desc: "Suivi de production journalière en cuisine professionnelle", pop: false },
    { id: 'rapport_jr_r', factproType: 'site_report', name: "Rapport Journalier", icon: '📝', desc: "Bilan quotidien des activités et ventes restaurant", pop: false },
    { id: 'planning_res', factproType: 'site_report', name: "Planning Personnel", icon: '🗓️', desc: "Planning hebdomadaire du personnel de restauration", pop: false },
    { id: 'bl_cuisine', factproType: 'delivery_note', name: "Bon Livraison Cuisine", icon: '🚚', desc: "Bon de livraison des marchandises reçues en cuisine", pop: false },
    { id: 'fiche_stk_bar', factproType: 'site_report', name: "Fiche Stock Bar", icon: '🍺', desc: "État des stocks et mouvements au comptoir bar", pop: false },
    { id: 'fac_traiteur', factproType: 'invoice', name: "Facture Traiteur", icon: '🥘', desc: "Facture pour prestation traiteur lors d'un événement", pop: true },
    { id: 'devis_banquet', factproType: 'quote', name: "Devis Banquet", icon: '🥂', desc: "Devis pour banquet ou réception événementielle client", pop: true },
    { id: 'bs_cuisine', factproType: 'cash_voucher', name: "Bon Sortie Cuisine", icon: '🍱', desc: "Bon de sortie des denrées utilisées en cuisine", pop: false },
    { id: 'rpt_caisse_r', factproType: 'site_report', name: "Rapport Caisse Resto", icon: '💰', desc: "Rapport de caisse journalier du point de vente", pop: true },
    { id: 'fiche_entr_ch', factproType: 'site_report', name: "Fiche Entretien Chambre", icon: '🧹', desc: "Suivi du nettoyage et entretien des chambres hôtel", pop: false },
    { id: 'rapport_haccp', factproType: 'site_report', name: "Rapport HACCP", icon: '✅', desc: "Rapport de contrôle hygiène et sécurité alimentaire", pop: false },
    { id: 'bc_boissons', factproType: 'purchase_order', name: "Bon Commande Boissons", icon: '🍷', desc: "Commande de boissons et consommables pour le bar", pop: false },
    { id: 'fac_minibar', factproType: 'invoice', name: "Facture Mini-Bar", icon: '🧊', desc: "Facturation des consommations mini-bar par chambre", pop: false },
  ]},
  { id: 'garage', label: 'Auto & Garage',             icon: '🚗', color: '#1D4ED8', docs: [
    { id: 'ordre_rep_auto', factproType: 'site_report', name: "Ordre Réparation Auto", icon: '🔧', desc: "Autorisation officielle de réparation d'un véhicule client", pop: true },
    { id: 'fiche_veh', factproType: 'site_report', name: "Fiche Véhicule Client", icon: '🚘', desc: "Dossier complet du véhicule client en atelier", pop: true },
    { id: 'rapport_exp_v', factproType: 'site_report', name: "Rapport Expertise Véhicule", icon: '🔍', desc: "Évaluation technique détaillée d'un véhicule sinistré", pop: false },
    { id: 'devis_rep', factproType: 'quote', name: "Devis Réparation Auto", icon: '📋', desc: "Estimation chiffrée des travaux de réparation automobile", pop: true },
    { id: 'fac_garage', factproType: 'invoice', name: "Facture Garage", icon: '🧾', desc: "Facture de prestation mécanique ou carrosserie", pop: true },
    { id: 'bon_trav_auto', factproType: 'site_report', name: "Bon de Travaux Auto", icon: '🛠️', desc: "Liste des opérations effectuées sur le véhicule", pop: true },
    { id: 'cert_rev', factproType: 'payment_receipt', name: "Certificat de Révision", icon: '✅', desc: "Attestation officielle de révision périodique effectuée", pop: false },
    { id: 'fiche_ct', factproType: 'site_report', name: "Fiche Contrôle Technique", icon: '📝', desc: "Résultat complet du contrôle technique véhicule", pop: false },
    { id: 'bc_pieces', factproType: 'purchase_order', name: "Bon Commande Pièces", icon: '🔩', desc: "Commande de pièces détachées auprès du fournisseur", pop: true },
    { id: 'retour_pieces', factproType: 'credit_note', name: "Retour Pièces Défect.", icon: '↩️', desc: "Retour fournisseur de pièces non conformes", pop: false },
    { id: 'permis_circ', factproType: 'payment_receipt', name: "Permis Circuler Provisoire", icon: '🪪', desc: "Autorisation temporaire de circulation du véhicule", pop: false },
    { id: 'fiche_pret_v', factproType: 'delivery_note', name: "Fiche Prêt Véhicule", icon: '🔑', desc: "Document de remise d'un véhicule de courtoisie", pop: false },
    { id: 'contrat_loc_v', factproType: 'invoice', name: "Contrat Location Voiture", icon: '🚗', desc: "Contrat et tarification pour location de véhicule", pop: true },
    { id: 'fac_loc_v', factproType: 'invoice', name: "Facture Location Voiture", icon: '🧾', desc: "Facturation des jours de location de véhicule", pop: true },
    { id: 'rapport_acc', factproType: 'site_report', name: "Rapport Accident Véhicule", icon: '🚨', desc: "Constat et rapport détaillé suite à un accident", pop: false },
    { id: 'fiche_entr_p', factproType: 'site_report', name: "Fiche Entretien Préventif", icon: '📅', desc: "Suivi périodique des entretiens préventifs du véhicule", pop: true },
    { id: 'attest_rep', factproType: 'payment_receipt', name: "Attestation de Réparation", icon: '📄', desc: "Certificat confirmant la réparation effectuée en atelier", pop: false },
    { id: 'bon_gar_piece', factproType: 'payment_receipt', name: "Bon de Garantie Pièce", icon: '🛡️', desc: "Document de garantie sur une pièce installée", pop: false },
    { id: 'rapport_diag', factproType: 'site_report', name: "Rapport Diagnostic Électron.", icon: '💻', desc: "Rapport de diagnostic électronique du véhicule", pop: true },
    { id: 'fiche_carb', factproType: 'expense_report', name: "Fiche Carburant", icon: '⛽', desc: "Suivi des consommations et dépenses de carburant", pop: false },
  ]},
  { id: 'it',     label: 'Informatique & IT',         icon: '💻', color: '#6D28D9', docs: [
    { id: 'it_cdc', factproType: 'site_report', name: "Cahier des Charges IT", icon: '📘', desc: "Spécifications techniques et fonctionnelles d'un projet IT", pop: true },
    { id: 'it_bon_inst', factproType: 'delivery_note', name: "Bon Installation Matériel", icon: '🖥️', desc: "Confirmation d'installation de matériel informatique", pop: false },
    { id: 'it_contrat_m', factproType: 'site_report', name: "Contrat Maintenance Info", icon: '🔄', desc: "Contrat de maintenance et support informatique régulier", pop: true },
    { id: 'it_ticket', factproType: 'site_report', name: "Ticket Incident IT", icon: '🎫', desc: "Enregistrement et suivi d'un incident informatique", pop: true },
    { id: 'it_rapport_int', factproType: 'site_report', name: "Rapport Intervention IT", icon: '📊', desc: "Rapport d'intervention technique sur site ou à distance", pop: true },
    { id: 'it_devis', factproType: 'quote', name: "Devis Informatique", icon: '💰', desc: "Estimation de coût pour prestation ou fourniture IT", pop: true },
    { id: 'it_fac', factproType: 'invoice', name: "Facture Prestation IT", icon: '🧾', desc: "Facturation de services ou fournitures informatiques", pop: true },
    { id: 'it_bon_liv', factproType: 'delivery_note', name: "Bon Livraison Matériel IT", icon: '📦', desc: "Accusé de réception de matériel informatique livré", pop: false },
    { id: 'it_contrat_lic', factproType: 'site_report', name: "Contrat Licence Logiciel", icon: '📜', desc: "Accord de licence et conditions d'utilisation logiciel", pop: false },
    { id: 'it_audit_sec', factproType: 'site_report', name: "Rapport Audit Sécurité", icon: '🔐', desc: "Analyse des vulnérabilités et recommandations sécurité", pop: false },
    { id: 'it_pra', factproType: 'site_report', name: "Plan Reprise Activité", icon: '♻️', desc: "Plan de continuité et reprise après sinistre informatique", pop: false },
    { id: 'it_heberg', factproType: 'invoice', name: "Contrat Hébergement Web", icon: '🌐', desc: "Contrat de services d'hébergement web et DNS", pop: true },
    { id: 'it_fiche_conf', factproType: 'site_report', name: "Fiche de Configuration", icon: '⚙️', desc: "Paramétrage technique documenté d'un équipement", pop: false },
    { id: 'it_rapport_p', factproType: 'site_report', name: "Rapport de Projet IT", icon: '📈', desc: "Avancement et bilan d'un projet informatique", pop: false },
    { id: 'it_bon_sort', factproType: 'delivery_note', name: "Bon de Sortie Matériel", icon: '📤', desc: "Autorisation de sortie de matériel du parc informatique", pop: false },
    { id: 'it_contrat_dev', factproType: 'invoice', name: "Contrat de Développement", icon: '👨‍💻', desc: "Contrat de réalisation d'un développement logiciel", pop: true },
    { id: 'it_attest_f', factproType: 'payment_receipt', name: "Attest. Formation Inform.", icon: '🎓', desc: "Attestation de participation à une formation informatique", pop: false },
    { id: 'it_pv_recette', factproType: 'payment_receipt', name: "PV de Recette Logiciel", icon: '✔️', desc: "Procès-verbal de validation et recette d'un logiciel", pop: true },
    { id: 'it_rapport_bk', factproType: 'site_report', name: "Rapport de Sauvegarde", icon: '💾', desc: "Compte-rendu de l'état des sauvegardes effectuées", pop: false },
    { id: 'it_fiche_test', factproType: 'site_report', name: "Fiche de Test", icon: '🧪', desc: "Cas de test et résultats de validation fonctionnelle", pop: false },
  ]},
  { id: 'agri',   label: 'Agriculture & Élevage',     icon: '🌱', color: '#16A34A', docs: [
    { id: 'agri_bon_recolt', factproType: 'goods_receipt', name: "Bon de Récolte", icon: '🌾', desc: "Enregistrement quantitatif de la récolte par parcelle", pop: true },
    { id: 'agri_fiche_parc', factproType: 'site_report', name: "Fiche Parcelle", icon: '🗺️', desc: "Données techniques et historique d'une parcelle agricole", pop: true },
    { id: 'agri_bord_pesee', factproType: 'delivery_note', name: "Bordereau Pesée", icon: '⚖️', desc: "Pesée et réception officielle de la récolte", pop: true },
    { id: 'agri_tracab', factproType: 'site_report', name: "Traçabilité Produit", icon: '🔍', desc: "Suivi complet du produit agricole de champ à marché", pop: false },
    { id: 'agri_bon_intr', factproType: 'purchase_order', name: "Bon d'Intrant", icon: '🧪', desc: "Sortie d'intrants agricoles pour usage en parcelle", pop: true },
    { id: 'agri_fiche_troup', factproType: 'site_report', name: "Fiche Troupeau", icon: '🐄', desc: "Recensement et suivi du cheptel par espèce", pop: false },
    { id: 'agri_rapp_prod', factproType: 'site_report', name: "Rapport Production", icon: '📊', desc: "Bilan mensuel ou saisonnier de production agricole", pop: true },
    { id: 'agri_bon_vente', factproType: 'invoice', name: "Bon Vente Récolte", icon: '💰', desc: "Vente directe de produits agricoles au marché", pop: true },
    { id: 'agri_cert_phyto', factproType: 'site_report', name: "Cert. Phytosanitaire", icon: '🌿', desc: "Attestation interne de conformité sanitaire des cultures", pop: false },
    { id: 'agri_plan_cult', factproType: 'site_report', name: "Planning Cultural", icon: '📅', desc: "Calendrier des travaux agricoles par campagne", pop: false },
    { id: 'agri_fiche_elev', factproType: 'site_report', name: "Fiche Élevage", icon: '🐓', desc: "Suivi quotidien alimentation et croissance animale", pop: false },
    { id: 'agri_rapp_vet', factproType: 'site_report', name: "Rapport Vétérinaire", icon: '🩺', desc: "Compte rendu des soins et diagnostics vétérinaires", pop: false },
    { id: 'agri_bon_vacc', factproType: 'site_report', name: "Bon Vaccination", icon: '💉', desc: "Enregistrement des vaccinations par animal ou lot", pop: false },
    { id: 'agri_cont_meta', factproType: 'site_report', name: "Contrat Métayage", icon: '🤝', desc: "Accord de partage de récolte entre propriétaire et métayer", pop: false },
    { id: 'agri_attest_bio', factproType: 'site_report', name: "Attest. Production Bio", icon: '♻️', desc: "Attestation interne de conformité agriculture biologique", pop: false },
  ]},
  { id: 'enrg',   label: 'Énergie Solaire',           icon: '☀️', color: '#F59E0B', docs: [
    { id: 'enrg_etude_fais', factproType: 'quote', name: "Étude Faisabilité", icon: '☀️', desc: "Analyse technique et financière d'un projet solaire", pop: true },
    { id: 'enrg_devis_sol', factproType: 'quote', name: "Devis Solaire", icon: '🔆', desc: "Offre chiffrée pour installation de panneaux solaires", pop: true },
    { id: 'enrg_cont_inst', factproType: 'site_report', name: "Contrat Installation", icon: '📋', desc: "Accord contractuel pour travaux d'installation solaire", pop: true },
    { id: 'enrg_bon_pose', factproType: 'delivery_note', name: "Bon de Pose", icon: '🔧', desc: "Validation de la pose effective des panneaux solaires", pop: true },
    { id: 'enrg_pv_service', factproType: 'site_report', name: "PV Mise en Service", icon: '✅', desc: "Procès-verbal officiel de mise en service installation", pop: true },
    { id: 'enrg_rapp_prod', factproType: 'site_report', name: "Rapport Production", icon: '⚡', desc: "Relevé de la production d'énergie solaire générée", pop: false },
    { id: 'enrg_releve_cpt', factproType: 'site_report', name: "Relevé Compteur", icon: '🔌', desc: "Lecture périodique du compteur électrique du site", pop: false },
    { id: 'enrg_cont_maint', factproType: 'site_report', name: "Contrat Maintenance", icon: '🛠️', desc: "Accord de maintenance préventive et curative solaire", pop: false },
    { id: 'enrg_fac_enrg', factproType: 'invoice', name: "Facture Énergie", icon: '🧾', desc: "Facturation de la consommation d'énergie solaire produite", pop: true },
    { id: 'enrg_attest_elec', factproType: 'site_report', name: "Attest. Conformité", icon: '🏅', desc: "Attestation de conformité électrique de l'installation", pop: false },
    { id: 'enrg_rapp_ctrl', factproType: 'site_report', name: "Rapport Contrôle", icon: '📝', desc: "Rapport de contrôle périodique de l'installation solaire", pop: false },
    { id: 'enrg_fiche_tech', factproType: 'site_report', name: "Fiche Technique", icon: '📄', desc: "Spécifications techniques du panneau ou équipement solaire", pop: false },
    { id: 'enrg_gar_mater', factproType: 'site_report', name: "Garantie Matériel", icon: '🛡️', desc: "Document de garantie fabricant pour matériel solaire", pop: false },
    { id: 'enrg_audit_enrg', factproType: 'site_report', name: "Rapport Audit Énergie", icon: '🔎', desc: "Analyse des consommations et potentiel d'économie énergétique", pop: false },
    { id: 'enrg_cont_rach', factproType: 'site_report', name: "Contrat Rachat Énergie", icon: '🔄', desc: "Accord de rachat de surplus d'énergie solaire produite", pop: false },
  ]},
  { id: 'banq',   label: 'Banque & Microfinance',     icon: '🏦', color: '#1E40AF', docs: [
    { id: 'banq_dem_credit', factproType: 'site_report', name: "Demande de Crédit", icon: '📩', desc: "Formulaire de demande de prêt par un client", pop: true },
    { id: 'banq_anal_cred', factproType: 'site_report', name: "Fiche Analyse Crédit", icon: '🔬', desc: "Évaluation financière et risque d'un dossier de crédit", pop: true },
    { id: 'banq_tab_amort', factproType: 'site_report', name: "Tableau Amortissement", icon: '📉', desc: "Échéancier détaillé des remboursements d'un prêt accordé", pop: true },
    { id: 'banq_cont_pret', factproType: 'site_report', name: "Contrat de Prêt", icon: '📜', desc: "Contrat officiel liant l'institution et l'emprunteur", pop: true },
    { id: 'banq_act_nant', factproType: 'site_report', name: "Acte Nantissement", icon: '🏦', desc: "Mise en garantie d'un bien mobilier pour sûreté crédit", pop: false },
    { id: 'banq_cont_caut', factproType: 'site_report', name: "Contrat de Caution", icon: '🤝', desc: "Engagement d'un tiers à garantir le remboursement du prêt", pop: false },
    { id: 'banq_avis_decais', factproType: 'cash_voucher', name: "Avis de Décaissement", icon: '💸', desc: "Notification officielle de mise à disposition des fonds", pop: true },
    { id: 'banq_fiche_remb', factproType: 'payment_receipt', name: "Fiche Remboursement", icon: '🔁', desc: "Enregistrement de chaque remboursement effectué par client", pop: true },
    { id: 'banq_attest_sold', factproType: 'site_report', name: "Attest. Crédit Soldé", icon: '✔️', desc: "Attestation confirmant le remboursement total du crédit", pop: true },
    { id: 'banq_dem_eparg', factproType: 'site_report', name: "Demande d'Épargne", icon: '🏧', desc: "Ouverture ou modification d'un compte épargne client", pop: false },
    { id: 'banq_releve_epg', factproType: 'site_report', name: "Relevé Épargne", icon: '📒', desc: "Historique des mouvements sur compte épargne client", pop: false },
    { id: 'banq_avis_prel', factproType: 'site_report', name: "Avis de Prélèvement", icon: '📤', desc: "Notification de prélèvement automatique sur compte client", pop: false },
    { id: 'banq_attest_vir', factproType: 'site_report', name: "Attest. Virement", icon: '➡️', desc: "Confirmation d'exécution d'un virement bancaire effectué", pop: false },
    { id: 'banq_cont_leas', factproType: 'site_report', name: "Contrat de Leasing", icon: '🚜', desc: "Accord de location-financement d'un équipement professionnel", pop: false },
    { id: 'banq_fiche_kyc', factproType: 'site_report', name: "Fiche KYC Client", icon: '🪪', desc: "Connaissance client et vérification d'identité réglementaire", pop: true },
    { id: 'banq_decl_pat', factproType: 'site_report', name: "Décl. Patrimoine", icon: '🏡', desc: "Déclaration des actifs et passifs du demandeur de crédit", pop: false },
    { id: 'banq_rapp_visit', factproType: 'site_report', name: "Rapport Visite Crédit", icon: '🚶', desc: "Compte rendu de la visite terrain pour instruction crédit", pop: false },
    { id: 'banq_fiche_gar', factproType: 'site_report', name: "Fiche de Garantie", icon: '🔐', desc: "Détail et valorisation des garanties apportées par l'emprunteur", pop: false },
  ]},
  { id: 'ong',    label: 'ONG & Associations',        icon: '🌍', color: '#0369A1', docs: [
    { id: 'ong_rapp_act', factproType: 'site_report', name: "Rapport d'activité", icon: '📋', desc: "Bilan des activités réalisées par l'ONG", pop: true },
    { id: 'ong_dem_fin', factproType: 'purchase_order', name: "Demande financement", icon: '💰', desc: "Formulaire de demande de financement projet ONG", pop: true },
    { id: 'ong_conv_part', factproType: 'site_report', name: "Convention partenariat", icon: '🤝', desc: "Accord de collaboration entre organisations partenaires", pop: true },
    { id: 'ong_rapp_bail', factproType: 'site_report', name: "Rapport bailleur", icon: '📊', desc: "Rapport financier destiné aux bailleurs de fonds", pop: true },
    { id: 'ong_benevolat', factproType: 'site_report', name: "Formulaire bénévolat", icon: '🙋', desc: "Inscription et engagement d'un bénévole associatif", pop: false },
    { id: 'ong_benefic', factproType: 'site_report', name: "Fiche bénéficiaire", icon: '👤', desc: "Profil et suivi d'un bénéficiaire de projet", pop: false },
    { id: 'ong_volontariat', factproType: 'site_report', name: "Contrat volontariat", icon: '📝', desc: "Engagement contractuel d'un volontaire associatif", pop: false },
    { id: 'ong_cr_mission', factproType: 'site_report', name: "CR mission terrain", icon: '🗺️', desc: "Compte-rendu de mission effectuée sur le terrain", pop: true },
    { id: 'ong_suivi_proj', factproType: 'site_report', name: "Rapport suivi projet", icon: '📈', desc: "Suivi périodique de l'avancement d'un projet", pop: true },
    { id: 'ong_pv_ag', factproType: 'site_report', name: "PV assemblée générale", icon: '🏛️', desc: "Procès-verbal de l'assemblée générale associative", pop: false },
    { id: 'ong_bilan_ann', factproType: 'site_report', name: "Bilan annuel asso", icon: '📅', desc: "Bilan annuel financier et moral de l'association", pop: false },
    { id: 'ong_plan_act', factproType: 'site_report', name: "Plan action projet", icon: '🎯', desc: "Planification des activités et jalons du projet", pop: true },
    { id: 'ong_solli_don', factproType: 'site_report', name: "Lettre sollicitation don", icon: '✉️', desc: "Courrier de demande de don ou de subvention", pop: false },
    { id: 'ong_attest_don', factproType: 'payment_receipt', name: "Attestation de don", icon: '🧾', desc: "Justificatif officiel de réception d'un don", pop: true },
    { id: 'ong_protocole', factproType: 'site_report', name: "Protocole d'accord", icon: '📜', desc: "Protocole d'accord entre ONG et partenaires terrain", pop: false },
  ]},
  { id: 'cons',   label: 'Audit & Conseil',           icon: '♟️', color: '#374151', docs: [
    { id: 'cons_lett_miss', factproType: 'site_report', name: "Lettre de mission", icon: '📄', desc: "Lettre définissant le périmètre de la mission audit", pop: true },
    { id: 'cons_rapp_aud', factproType: 'site_report', name: "Rapport audit financier", icon: '🔍', desc: "Rapport complet d'audit des comptes financiers", pop: true },
    { id: 'cons_due_dilig', factproType: 'site_report', name: "Rapport due diligence", icon: '🧐', desc: "Analyse approfondie de risques avant investissement", pop: true },
    { id: 'cons_plan_corr', factproType: 'site_report', name: "Plan action correctif", icon: '⚙️', desc: "Actions correctives suite aux conclusions d'audit", pop: false },
    { id: 'cons_rapp_strat', factproType: 'site_report', name: "Rapport conseil strat.", icon: '♟️', desc: "Rapport de conseil en stratégie d'entreprise", pop: true },
    { id: 'cons_fiche_miss', factproType: 'site_report', name: "Fiche de mission", icon: '🗒️', desc: "Fiche descriptive d'une mission de consultant", pop: false },
    { id: 'cons_diagnostic', factproType: 'site_report', name: "Rapport diagnostic", icon: '🏢', desc: "Diagnostic global de la situation d'une entreprise", pop: true },
    { id: 'cons_livrables', factproType: 'site_report', name: "Livrables projet", icon: '📦', desc: "Document récapitulatif des livrables remis au client", pop: false },
    { id: 'cons_cr_miss', factproType: 'site_report', name: "CR de mission", icon: '📋', desc: "Compte-rendu synthétique d'une mission de conseil", pop: false },
    { id: 'cons_prop_com', factproType: 'quote', name: "Proposition commerciale", icon: '💼', desc: "Offre de services de conseil personnalisée client", pop: true },
    { id: 'cons_contrat', factproType: 'site_report', name: "Contrat de conseil", icon: '✍️', desc: "Contrat encadrant une prestation de conseil", pop: true },
    { id: 'cons_rapp_int', factproType: 'site_report', name: "Rapport intermédiaire", icon: '🔄', desc: "Point d'étape intermédiaire d'un projet de conseil", pop: false },
    { id: 'cons_note_syn', factproType: 'site_report', name: "Note de synthèse audit", icon: '📝', desc: "Synthèse concise des conclusions de l'audit", pop: false },
    { id: 'cons_conformite', factproType: 'site_report', name: "Rapport de conformité", icon: '✅', desc: "Évaluation de la conformité réglementaire et interne", pop: false },
    { id: 'cons_attest_miss', factproType: 'payment_receipt', name: "Attestation de mission", icon: '🎖️', desc: "Attestation officielle de réalisation d'une mission", pop: false },
  ]},
  { id: 'tour',   label: 'Tourisme & Voyages',        icon: '✈️', color: '#0EA5E9', docs: [
    { id: 'tour_bon_voy', factproType: 'cash_voucher', name: "Bon de voyage", icon: '✈️', desc: "Bon autorisant un voyage pour un client", pop: true },
    { id: 'tour_prog_tour', factproType: 'site_report', name: "Programme touristique", icon: '🗺️', desc: "Itinéraire détaillé d'un circuit touristique", pop: true },
    { id: 'tour_voucher_h', factproType: 'payment_receipt', name: "Voucher hôtel", icon: '🏨', desc: "Bon de confirmation de réservation hôtelière", pop: true },
    { id: 'tour_recu_visa', factproType: 'payment_receipt', name: "Reçu de visa", icon: '🛂', desc: "Reçu de paiement des frais de visa voyageur", pop: false },
    { id: 'tour_fac_agc', factproType: 'invoice', name: "Facture agence voyage", icon: '🧾', desc: "Facture émise par une agence de voyage", pop: true },
    { id: 'tour_bon_trans', factproType: 'cash_voucher', name: "Bon transport tourist.", icon: '🚌', desc: "Bon de transport dédié aux groupes touristiques", pop: false },
    { id: 'tour_contrat_to', factproType: 'site_report', name: "Contrat tour opérateur", icon: '🤝', desc: "Contrat entre agence et tour opérateur partenaire", pop: false },
    { id: 'tour_fiche_exc', factproType: 'site_report', name: "Fiche excursion", icon: '🏞️', desc: "Description et détails d'une excursion touristique", pop: false },
    { id: 'tour_passagers', factproType: 'site_report', name: "Liste passagers", icon: '👥', desc: "Manifeste des passagers d'un groupe touristique", pop: false },
    { id: 'tour_assurance', factproType: 'site_report', name: "Assurance voyage", icon: '🛡️', desc: "Document d'assurance voyage pour touriste", pop: true },
    { id: 'tour_bon_vol', factproType: 'cash_voucher', name: "Bon réservation vol", icon: '🎫', desc: "Bon de réservation de billet d'avion client", pop: true },
    { id: 'tour_fac_circ', factproType: 'invoice', name: "Facture circuit tourist.", icon: '💳', desc: "Facture d'un circuit touristique complet", pop: true },
    { id: 'tour_fiche_guide', factproType: 'site_report', name: "Fiche guide touristique", icon: '🧭', desc: "Fiche d'information du guide touristique affecté", pop: false },
    { id: 'tour_satisfact', factproType: 'site_report', name: "Rapport satisfaction client", icon: '⭐', desc: "Rapport d'évaluation satisfaction client post-voyage", pop: false },
    { id: 'tour_devis_voy', factproType: 'quote', name: "Devis voyage sur mesure", icon: '📐', desc: "Devis personnalisé pour un voyage sur mesure", pop: true },
  ]},
  { id: 'pharm',  label: 'Pharmacie',                 icon: '💊', color: '#BE185D', docs: [
    { id: 'pharm_bc_medic', factproType: 'purchase_order', name: "BC médicaments", icon: '💊', desc: "Bon de commande de médicaments auprès du grossiste", pop: true },
    { id: 'pharm_stock', factproType: 'site_report', name: "Fiche stock pharmacie", icon: '📦', desc: "État du stock de produits pharmaceutiques", pop: true },
    { id: 'pharm_perempti', factproType: 'site_report', name: "Rapport péremption", icon: '⚠️', desc: "Liste des médicaments en fin de date de péremption", pop: true },
    { id: 'pharm_bl', factproType: 'delivery_note', name: "BL pharmacie", icon: '🚚', desc: "Bon de livraison de produits pharmaceutiques", pop: false },
    { id: 'pharm_fac_gros', factproType: 'invoice', name: "Facture grossiste", icon: '🧾', desc: "Facture d'achat auprès du grossiste pharmaceutique", pop: true },
    { id: 'pharm_dispens', factproType: 'site_report', name: "Fiche dispensation", icon: '🏥', desc: "Fiche de remise de médicaments au patient", pop: false },
    { id: 'pharm_inventair', factproType: 'site_report', name: "Rapport inventaire pharma", icon: '📋', desc: "Inventaire complet du stock de la pharmacie", pop: false },
    { id: 'pharm_retour', factproType: 'credit_note', name: "Bon retour médicament", icon: '↩️', desc: "Retour de médicaments défectueux ou périmés", pop: false },
    { id: 'pharm_tracab', factproType: 'site_report', name: "Traçabilité lot", icon: '🔎', desc: "Fiche de traçabilité d'un lot de médicaments", pop: false },
    { id: 'pharm_cert_ana', factproType: 'site_report', name: "Certificat d'analyse", icon: '🔬', desc: "Certificat d'analyse de qualité d'un produit", pop: false },
    { id: 'pharm_amm', factproType: 'site_report', name: "Autorisation mise marché", icon: '📜', desc: "Autorisation officielle de mise sur le marché", pop: false },
    { id: 'pharm_inspect', factproType: 'site_report', name: "Rapport inspection pharma", icon: '🏛️', desc: "Rapport d'inspection de l'officine pharmaceutique", pop: false },
    { id: 'pharm_destruct', factproType: 'site_report', name: "Bon destruction médic.", icon: '🗑️', desc: "Bon de destruction de médicaments non conformes", pop: false },
    { id: 'pharm_fiche_pat', factproType: 'site_report', name: "Fiche patient pharmacie", icon: '👤', desc: "Dossier patient suivi par la pharmacie", pop: true },
    { id: 'pharm_fac_offic', factproType: 'invoice', name: "Facture officine", icon: '💊', desc: "Facture de vente émise par l'officine au client", pop: true },
  ]},
  { id: 'mine',   label: 'Mines & Carrières',         icon: '⛏️', color: '#78350F', docs: [
    { id: 'mine_permis', factproType: 'site_report', name: "Permis d'exploitation", icon: '⛏️', desc: "Permis officiel d'exploitation minière ou carrière", pop: true },
    { id: 'mine_rapp_geo', factproType: 'site_report', name: "Rapport géologique", icon: '🪨', desc: "Rapport d'étude géologique du site minier", pop: true },
    { id: 'mine_bord_min', factproType: 'delivery_note', name: "Bordereau minerai", icon: '📋', desc: "Bordereau de pesée et de livraison de minerai", pop: true },
    { id: 'mine_bon_sort', factproType: 'cash_voucher', name: "Bon sortie minerai", icon: '🚛', desc: "Bon de sortie de minerai du site d'exploitation", pop: true },
    { id: 'mine_rapp_prod', factproType: 'site_report', name: "Rapport production mines", icon: '📈', desc: "Rapport mensuel de production minière", pop: true },
    { id: 'mine_fiche_secu', factproType: 'site_report', name: "Fiche sécurité mine", icon: '⛑️', desc: "Fiche de consignes de sécurité du site minier", pop: false },
    { id: 'mine_rapp_env', factproType: 'site_report', name: "Rapport environnemental", icon: '🌿', desc: "Rapport d'impact environnemental de l'exploitation", pop: true },
    { id: 'mine_cession', factproType: 'invoice', name: "Contrat cession minerai", icon: '🤝', desc: "Contrat de vente et cession de minerais extraits", pop: false },
    { id: 'mine_attest_exp', factproType: 'payment_receipt', name: "Attestation exploitation", icon: '📜', desc: "Attestation officielle du droit d'exploiter le site", pop: false },
    { id: 'mine_fiche_tech', factproType: 'site_report', name: "Fiche technique minerai", icon: '🔩', desc: "Caractéristiques techniques d'un minerai extrait", pop: false },
    { id: 'mine_ctrl_carr', factproType: 'site_report', name: "Rapport contrôle carrière", icon: '🏗️', desc: "Rapport de contrôle technique d'une carrière", pop: false },
    { id: 'mine_cert_conf', factproType: 'payment_receipt', name: "Certificat conformité mines", icon: '✅', desc: "Certification de conformité des opérations minières", pop: false },
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
// Mapping catId → { key, name } du vrai template PDF Blade recommandé
const CAT_TEMPLATE = {
  vente:      { key: 'corporate-01', name: 'Corporate Marine' },
  achat:      { key: 'corporate-02', name: 'Acier' },
  btp:        { key: 'btp-01',       name: 'Entreprise BTP' },
  garage:     { key: 'auto-01',      name: 'Garage Auto' },
  sante:      { key: 'medical-01',   name: 'Clinique' },
  pharm:      { key: 'medical-02',   name: 'Pharmacie' },
  it:         { key: 'tech-saas-01', name: 'Startup SaaS' },
  agri:       { key: 'agri-01',      name: 'Exploitation Agricole' },
  transport:  { key: 'transport-01', name: 'Transporteur Routier' },
  rh:         { key: 'corporate-03', name: 'Slate Premium' },
  finance:    { key: 'finance-01',   name: 'Banque Classique' },
  banq:       { key: 'finance-02',   name: 'Fintech' },
  immobilier: { key: 'immo-01',      name: 'Agence Prestige' },
  resto:      { key: 'resto-01',     name: 'Gastronome' },
  export:     { key: 'africa-01',    name: 'Panafricain' },
  admin:      { key: 'legal-01',     name: "Cabinet d'Avocats" },
  sav:        { key: 'corporate-04', name: 'Bordeaux Executive' },
  stock:      { key: 'corporate-05', name: 'Vert Institution' },
  education:  { key: 'education-01', name: 'École Privée' },
  tour:       { key: 'hotel-01',     name: 'Hôtel Luxe' },
  ong:        { key: 'ong-01',       name: 'Association Caritative' },
  cons:       { key: 'corporate-03', name: 'Slate Premium' },
  mine:       { key: 'africa-02',    name: 'Sahel Terre' },
  enrg:       { key: 'corporate-05', name: 'Vert Institution' },
}

function recommendedTemplate(doc) {
  return CAT_TEMPLATE[doc.catId] || { key: 'corporate-01', name: 'Corporate Marine' }
}

function createDoc(doc) {
  const tpl = recommendedTemplate(doc)
  router.visit(route('documents.create', {
    type: doc.factproType,
    template: tpl.key,
  }))
}
function selectCat(id)    { selCat.value = id; sidebarOpen.value = false }

// ─── Aperçus — CSS commun minimal + helpers ─────────────────────
const RESET = `*{box-sizing:border-box;margin:0;padding:0}body{font-family:'Segoe UI',system-ui,Arial,sans-serif;color:#1e293b;background:#fff;font-size:13px}`
const FTR = `<div style="border-top:1px solid #e2e8f0;margin-top:18px;padding:10px 0;text-align:center;font-size:9.5px;color:#94a3b8">Document généré par <b style="color:#1E3A5F">IBIG FactPro</b> · ibigsoft.com · Conforme OHADA &nbsp;·&nbsp; Aperçu de démonstration</div>`
function wrap(css, body) { return `<!DOCTYPE html><html><head><meta charset="UTF-8"><style>${RESET}${css}</style></head><body>${body}${FTR}</body></html>` }

// ── TEMPLATE 1 : Facture / Devis — contenu adaptatif par secteur
function previewInvoice(doc) {
  const c = doc.catColor || '#2563EB'
  const isDevis = doc.factproType === 'quote' || doc.factproType === 'proforma_invoice' || ['devis','offre','proposition','proforma','bon_resa','devis_modif','offre_promo'].includes(doc.id)

  // Lignes de facturation adaptees au secteur
  const sectorRows = {
    vente:      [['Produits electroniques — ref. LOT-2026-088','50 u.','25 000','1 250 000'],['Textiles et confection (tailles S/M/L)','30 pcs','12 500','375 000'],['Accessoires et emballages','1 lot','18 000','18 000'],['Frais de port et manutention','1 fft','8 500','8 500']],
    achat:      [['Matieres premieres — commande mensuelle','200 kg','3 500','700 000'],['Fournitures de bureau et consommables','5 rlx','8 500','42 500'],['Emballages et conditionnement','500 u.','150','75 000'],['Frais de douane et transit','1 fft','45 000','45 000']],
    btp:        [['Gros oeuvre — fondations et murs porteurs','1 fft','3 500 000','3 500 000'],['Charpente metallique et couverture','1 fft','1 200 000','1 200 000'],['Electricite HTA/BT et plomberie','1 fft','800 000','800 000'],['Finitions, peintures et revetements','1 fft','450 000','450 000']],
    logistique: [['Fret routier Abidjan - Bamako (5 tonnes)','1 trajet','450 000','450 000'],['Transit douanier et dedouanement','1 fft','85 000','85 000'],['Manutention et stockage (3 jours)','3 j','30 000','90 000'],['Assurance transport tout risque','1 fft','22 000','22 000']],
    immobilier: [['Loyer mensuel — bureaux 120 m²','1 mois','350 000','350 000'],['Charges locatives et copropriete','1 mois','45 000','45 000'],['Gardiennage et securite 24h/24','1 mois','30 000','30 000'],['Internet fibres optiques et telephonie','1 mois','25 000','25 000']],
    sante:      [['Consultation medicale specialisee','1 acte','25 000','25 000'],['Analyses biologiques — bilan complet','1 panel','35 000','35 000'],['Medicaments prescrits et consommables','1 ord.','18 500','18 500'],['Frais de dossier et administratifs','1 fft','2 000','2 000']],
    education:  [['Frais de scolarite — 3e trimestre 2025-2026','1 trim.','150 000','150 000'],['Manuels scolaires et fournitures','1 lot','25 000','25 000'],['Activites parascolaires et sorties','1 fft','15 000','15 000'],['Assurance scolaire annuelle','1 an','8 000','8 000']],
    resto:      [['Repas — buffet gala 120 couverts','120 couv.','12 500','1 500 000'],['Boissons et cocktails de bienvenue','120 pers.','4 500','540 000'],['Service traiteur VIP et personnalise','1 fft','85 000','85 000'],['Location salle et decoration florale','1 eve.','150 000','150 000']],
    garage:     [['Main oeuvre — revision complete 50 000km','4 h','18 000','72 000'],['Pieces detachees certifiees OEM','1 lot','95 000','95 000'],['Vidange huile 5W40 et filtres','5 L','4 200','21 000'],['Nettoyage et controle technique CT','1 fft','12 000','12 000']],
    it:         [['Developpement application mobile iOS/Android','40 j','95 000','3 800 000'],['Hebergement cloud haute disponibilite (12 mois)','1 an','85 000','85 000'],['Licences logicielles — pack PME (10 postes)','10 u.','25 000','250 000'],['Support technique mensuel prioritaire','1 fft','65 000','65 000']],
    agri:       [['Semences certifiees mais hybride DK-8031','50 kg','8 500','425 000'],['Engrais NPK 20-10-10 sacs 50kg','200 sacs','18 000','3 600 000'],['Pesticides homologues (traitement preventif)','20 L','12 000','240 000'],['Materiel et outillage agricole','1 lot','85 000','85 000']],
    enrg:       [['Consommation electrique juillet 2026','2 450 kWh','120','294 000'],['Location compteur intelligent AMR','1 mois','8 500','8 500'],['Frais de raccordement reseau electrique','1 fft','45 000','45 000'],['Prime fixe abonnement BT mensuel','1 mois','12 000','12 000']],
    banq:       [['Commission gestion compte professionnel','1 mois','15 000','15 000'],['Frais virements internationaux SWIFT','3 op.','8 500','25 500'],['Location coffre-fort securise annuel','1 an','45 000','45 000'],['Primes assurance compte et CBE','12 mois','5 000','60 000']],
    pharm:      [['Medicaments prescription — lot B (DCI)','500 u.','2 500','1 250 000'],['Materiels medicaux et consommables','1 lot','85 000','85 000'],['Frais de livraison temperature controlee','1 fft','15 000','15 000'],['Controle qualite et certification ISO','1 cert.','25 000','25 000']],
    mine:       [['Extraction minerai de fer calibre 0-10mm','150 T','45 000','6 750 000'],['Transport vers port en vrac','150 T','8 500','1 275 000'],['Traitement, purification et criblage','150 T','12 000','1 800 000'],['Certification, analyses et conformite','1 cert.','85 000','85 000']],
    ong:        [['Programme aide alimentaire — beneficiaires','1 fft','2 500 000','2 500 000'],['Frais logistiques et distribution terrain','1 mois','180 000','180 000'],['Sensibilisation et formations communautes','5 sess.','45 000','225 000'],['Frais administratifs et gestion (10%)','1 fft','272 500','272 500']],
    cons:       [['Audit organisationnel et diagnostic','10 j','250 000','2 500 000'],['Elaboration plan strategique 3 ans','5 j','250 000','1 250 000'],['Ateliers et formations dirigeants','3 j','300 000','900 000'],['Rapport final, livrables et suivi','1 fft','150 000','150 000']],
    tour:       [['Circuit touristique 7 jours / 6 nuits','2 pers.','550 000','1 100 000'],['Hebergement hotel 4 etoiles petit-dejeuner inclus','6 nuits','120 000','720 000'],['Transferts aeroport et transports inclus','1 fft','85 000','85 000'],['Guide local, excursions et entrees sites','1 fft','75 000','75 000']],
    finance:    [['Commission gestion portefeuille (1,5%)','1 trim.','125 000','125 000'],['Frais transactions boursieres et courtage','15 op.','3 500','52 500'],['Droits de garde titres annuels','1 an','45 000','45 000'],['Abonnement reporting et analyse financiere','12 mois','8 500','102 000']],
    sav:        [['Diagnostic et expertise technique approfondie','1 acte','15 000','15 000'],['Reparation — main oeuvre specialisee','3 h','25 000','75 000'],['Pieces de rechange certifiees constructeur','1 lot','65 000','65 000'],['Essais finaux et recette client','1 acte','12 000','12 000']],
    export:     [['Marchandises export — lot 2026-EXP-044','1 conte.','1 850 000','1 850 000'],['Assurance maritime tous risques','1 fft','65 000','65 000'],['Fret maritime CIF Le Havre','1 fft','280 000','280 000'],['Certificat origine et documents export','1 lot','45 000','45 000']],
  }
  const rows = sectorRows[doc.catId] || sectorRows.vente
  const subHT = rows.reduce((s,r) => s + parseInt(r[3].replace(/ /g,'')), 0)
  const tva = Math.round(subHT * 0.18)
  const ttc = subHT + tva
  const fmt = n => n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ')

  const css = `
.page{max-width:680px;margin:0 auto;font-family:Arial,sans-serif;background:#fff}
.top{background:linear-gradient(135deg,#0f1f4b 0%,${c} 100%);color:#fff;padding:22px 28px;display:flex;justify-content:space-between;align-items:flex-start}
.logo-area{display:flex;align-items:center;gap:12px}
.logo-sq{width:44px;height:44px;background:rgba(255,255,255,.2);border-radius:10px;display:grid;place-items:center;font-size:20px;flex-shrink:0}
.co{font-size:14px;font-weight:800;letter-spacing:.01em}
.co-sub{font-size:9.5px;opacity:.8;margin-top:3px;line-height:1.8}
.doc-r{text-align:right}
.dtype{font-size:18px;font-weight:900;text-transform:uppercase;letter-spacing:.04em;line-height:1.2}
.dsector{font-size:9px;opacity:.75;font-weight:600;letter-spacing:.1em;text-transform:uppercase;margin-bottom:4px}
.dref{font-size:10px;opacity:.85;margin-top:5px;line-height:1.9}
.vbar{background:#FFF8E1;border-left:4px solid #F59E0B;padding:8px 22px;font-size:10.5px;font-weight:700;color:#78350F;display:flex;align-items:center;gap:8px}
.body{padding:18px 28px}
.parties{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:16px}
.party{padding:12px 14px;border-radius:10px;background:#F8FAFC}
.party.em{border-top:3px solid ${c}}
.party.dest{border-top:3px solid #10B981}
.plbl{font-size:8px;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:#94A3B8;margin-bottom:4px}
.pname{font-size:13px;font-weight:800;color:#0F172A;margin-bottom:3px}
.pinfo{font-size:10.5px;color:#64748B;line-height:1.65}
table{width:100%;border-collapse:collapse;margin-bottom:12px}
thead tr{background:#0F1F4B}
th{color:#fff;padding:8px 10px;text-align:left;font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.06em}
th:last-child,td:last-child{text-align:right}
td{padding:8px 10px;border-bottom:1px solid #F1F5F9;font-size:11.5px;color:#374151}
tr:nth-child(even) td{background:#F8FAFC}
.tw{display:flex;justify-content:flex-end;margin-bottom:12px}
.tb{width:270px}
.tl{display:flex;justify-content:space-between;padding:6px 12px;font-size:11.5px;border-bottom:1px solid #F1F5F9;color:#475569}
.tf{display:flex;justify-content:space-between;padding:12px;font-size:14px;font-weight:900;background:${c};color:#fff;border-radius:0 0 8px 8px}
.paybar{background:#EFF6FF;border-radius:8px;padding:10px 14px;font-size:10.5px;color:#1E40AF;line-height:1.7;margin-bottom:12px}
.sigs{display:flex;gap:12px;margin-top:6px}
.sig{flex:1;border:1.5px dashed #CBD5E1;border-radius:8px;padding:10px;text-align:center;font-size:10px;color:#94A3B8}
.ss{height:38px}`
  return wrap(css, `<div class="page">
<div class="top">
  <div class="logo-area"><div class="logo-sq">${doc.icon}</div><div><div class="co">VOTRE SOCIETE SARL</div><div class="co-sub">Plateau, Abidjan 01 · Cote d'Ivoire<br>+225 27 22 33 44 55 · RCCM CI-ABJ-2024-B-12345</div></div></div>
  <div class="doc-r"><div class="dsector">${doc.catLabel || ''}</div><div class="dtype">${doc.name}</div><div class="dref">N° <b>2026-0042</b> · 27/07/2026<br>${isDevis ? 'Valable jusqu\'au <b>26/08/2026</b>' : 'Echeance : <b>27/08/2026</b>'}</div></div>
</div>
${isDevis ? '<div class="vbar">&#9203; Devis valable 30 jours — La signature vaut acceptation ferme</div>' : ''}
<div class="body">
<div class="parties">
  <div class="party em"><div class="plbl">Emetteur</div><div class="pname">VOTRE SOCIETE SARL</div><div class="pinfo">Plateau, Abidjan 01<br>NIF 2405812 A · CNPS 123-456-789</div></div>
  <div class="party dest"><div class="plbl">Client / Destinataire</div><div class="pname">CLIENT EXEMPLE &amp; ASSOCIES</div><div class="pinfo">Cocody Riviera 3, Abidjan<br>+225 05 00 11 22 33 · RCCM CI-ABJ-2020-B-44521</div></div>
</div>
<table>
<thead><tr><th>#</th><th>Designation</th><th>Qte</th><th>P.U. HT</th><th>Total HT</th></tr></thead>
<tbody>
${rows.map((r,i) => `<tr><td>0${i+1}</td><td>${r[0]}</td><td>${r[1]}</td><td>${r[2]} XOF</td><td>${r[3]} XOF</td></tr>`).join('')}
</tbody>
</table>
<div class="tw"><div class="tb">
  <div class="tl"><span>Sous-total HT</span><span>${fmt(subHT)} XOF</span></div>
  <div class="tl" style="background:#FFFBEB"><span>TVA 18 %</span><span>${fmt(tva)} XOF</span></div>
  <div class="tf"><span>TOTAL TTC</span><span>${fmt(ttc)} XOF</span></div>
</div></div>
<div class="paybar">&#127974; Virement BICICI · IBAN CI61 0123 4567 8901 2345 6789 00 · Delai : 30 jours nets</div>
<div class="sigs">
  <div class="sig"><div class="ss"></div>Signature Client — Bon pour accord</div>
  <div class="sig"><div class="ss"></div>Cachet &amp; Signature Emetteur</div>
</div>
</div></div>`)
}

// ── TEMPLATE 2 : Bon de livraison — style logistique avec route visuelle
function previewDelivery(doc) {
  const c = doc.catColor || '#059669'
  const css = `
.page{max-width:680px;margin:0 auto}
.top{background:#0F172A;color:#fff;padding:0}
.topbar{display:flex;justify-content:space-between;align-items:center;padding:16px 24px;border-bottom:1px solid rgba(255,255,255,.1)}
.tlogo{display:flex;align-items:center;gap:10px}
.ticon{width:36px;height:36px;background:${c};border-radius:8px;display:grid;place-items:center;font-size:16px}
.tco{font-size:13px;font-weight:800;color:#fff}
.tsub{font-size:9.5px;color:#94A3B8;margin-top:1px}
.tdoc{text-align:right}
.ttype{font-size:16px;font-weight:900;text-transform:uppercase;color:${c};letter-spacing:.04em}
.tref{font-size:10px;color:#94A3B8;margin-top:4px;line-height:1.7}
.route{display:flex;align-items:center;padding:14px 24px;gap:0}
.rbox{flex:1;padding:10px 14px;background:rgba(255,255,255,.07);border-radius:8px}
.rlbl{font-size:8.5px;text-transform:uppercase;letter-spacing:.1em;color:#64748B;margin-bottom:3px}
.rval{font-size:12px;font-weight:700;color:#E2E8F0}
.rsub{font-size:10px;color:#94A3B8;margin-top:2px}
.rarrow{flex:0 0 60px;text-align:center;font-size:22px;color:${c}}
.status-strip{background:${c};color:#fff;padding:8px 24px;display:flex;gap:16px;align-items:center}
.ss-item{font-size:10px;font-weight:600;display:flex;align-items:center;gap:5px}
.body{padding:18px 24px}
table{width:100%;border-collapse:collapse;margin-bottom:14px}
thead tr{background:#1E293B}
th{color:#94A3B8;padding:8px 10px;text-align:left;font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.07em}
td{padding:9px 10px;border-bottom:1px solid #F1F5F9;font-size:11.5px}
tr:nth-child(even) td{background:#F8FAFC}
.conform{display:inline-block;padding:2px 8px;border-radius:20px;font-size:9.5px;font-weight:700}
.ok{background:#DCFCE7;color:#166534}
.warn{background:#FEF9C3;color:#854D0E}
.obs{background:#FFF7ED;border-left:3px solid #F59E0B;padding:10px 14px;border-radius:6px;font-size:11px;color:#92400E;margin-bottom:14px}
.sigs{display:flex;gap:12px}
.sig{flex:1;border:1.5px dashed #CBD5E1;border-radius:8px;padding:10px;text-align:center;font-size:10px;color:#94A3B8}
.ss2{height:38px}`
  return wrap(css, `<div class="page">
<div class="top">
  <div class="topbar">
    <div class="tlogo"><div class="ticon">🚚</div><div><div class="tco">VOTRE SOCIÉTÉ SARL</div><div class="tsub">Plateau, Abidjan · RCCM CI-ABJ-2024-B-12345</div></div></div>
    <div class="tdoc"><div class="ttype">${doc.name}</div><div class="tref">N° <b>BL-2026-0089</b><br>Réf. BC : BC-2026-0018<br>Date : 27/07/2026</div></div>
  </div>
  <div class="route">
    <div class="rbox"><div class="rlbl">🏭 Expéditeur</div><div class="rval">VOTRE SOCIÉTÉ SARL</div><div class="rsub">Entrepôt Yopougon — Porte B</div></div>
    <div class="rarrow">→</div>
    <div class="rbox"><div class="rlbl">📍 Destinataire</div><div class="rval">CLIENT EXEMPLE & ASSOCIÉS</div><div class="rsub">Cocody Riviera 3 · M. Kouassi : 07 11 22 33</div></div>
  </div>
  <div class="status-strip">
    <div class="ss-item">🚚 LOGIS EXPRESS CI</div>
    <div class="ss-item">📅 27/07/2026 — 09h30</div>
    <div class="ss-item">❄️ Transport réfrigéré</div>
    <div class="ss-item" style="margin-left:auto;background:rgba(255,255,255,.15);padding:3px 10px;border-radius:12px">EN COURS DE LIVRAISON</div>
  </div>
</div>
<div class="body">
<table>
<thead><tr><th>#</th><th>Désignation article</th><th>Qté commandée</th><th>Qté livrée</th><th>Unité</th><th>Conformité</th></tr></thead>
<tbody>
<tr><td>01</td><td>Ciment Portland CPA 42.5</td><td>50</td><td>50</td><td>Sac 50 kg</td><td><span class="conform ok">✅ Conforme</span></td></tr>
<tr><td>02</td><td>Fers à béton Ø12 — 6 m</td><td>20</td><td>18</td><td>Barre</td><td><span class="conform warn">⚠️ 2 manquants</span></td></tr>
<tr><td>03</td><td>Parpaings creux 15×20×40 cm</td><td>500</td><td>500</td><td>Unité</td><td><span class="conform ok">✅ Conforme</span></td></tr>
<tr><td>04</td><td>Sable fin de rivière lavé</td><td>8</td><td>8</td><td>m³</td><td><span class="conform ok">✅ Conforme</span></td></tr>
</tbody>
</table>
<div class="obs">⚠️ <b>Observations :</b> 2 barres de fer Ø12 manquantes — livraison complémentaire confirmée pour le 30/07/2026. Bon partiel accepté par le client.</div>
<div class="sigs">
  <div class="sig"><div class="ss2"></div>Signature & Tampon Livreur<br>LOGIS EXPRESS CI</div>
  <div class="sig"><div class="ss2"></div>Signature Réceptionnaire<br><em>Bon pour réception — Date :</em></div>
</div>
</div></div>`)
}

// ── TEMPLATE 3 : RH — bulletin de paie avec colonnes gains/retenues
function previewHR(doc) {
  const c = doc.catColor || '#7C3AED'
  if (doc.id === 'bulletin') {
    const css = `
.page{max-width:680px;margin:0 auto}
.hdr{background:linear-gradient(to right,#1E1B4B,${c});color:#fff;padding:18px 24px;display:flex;justify-content:space-between;align-items:center}
.hco{font-size:14px;font-weight:800}.hsub{font-size:9.5px;opacity:.75;margin-top:2px;line-height:1.6}
.htitle{text-align:right;font-size:13px;font-weight:900;text-transform:uppercase;letter-spacing:.05em}
.hperiod{font-size:10px;opacity:.8;margin-top:4px}
.emp{background:#F5F3FF;border-bottom:2px solid ${c};padding:12px 24px;display:flex;gap:20px;align-items:center}
.empav{width:44px;height:44px;background:${c};border-radius:50%;display:grid;place-items:center;color:#fff;font-size:16px;font-weight:900;flex-shrink:0}
.empname{font-size:14px;font-weight:800;color:#1E1B4B}.empsub{font-size:10.5px;color:#64748B;margin-top:2px;line-height:1.6}
.body{padding:16px 24px}
.paycols{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px}
.paycol{border-radius:10px;overflow:hidden}
.pchead{padding:10px 14px;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.08em}
.gains .pchead{background:#DCFCE7;color:#166534}
.retenues .pchead{background:#FEE2E2;color:#991B1B}
.pcrow{display:flex;justify-content:space-between;padding:7px 14px;font-size:11.5px;border-bottom:1px solid #F1F5F9}
.pcrow:last-child{border-bottom:none}
.gains .pcrow{color:#166534}.retenues .pcrow{color:#991B1B}
.pctot{display:flex;justify-content:space-between;padding:9px 14px;font-size:12px;font-weight:700}
.gains .pctot{background:#F0FDF4;color:#15803D}.retenues .pctot{background:#FFF1F2;color:#BE123C}
.netbox{background:${c};border-radius:12px;color:#fff;text-align:center;padding:16px;margin-bottom:14px}
.netlbl{font-size:10px;text-transform:uppercase;letter-spacing:.1em;opacity:.8;margin-bottom:6px}
.netamt{font-size:30px;font-weight:900;letter-spacing:.02em}
.netsub{font-size:10.5px;opacity:.75;margin-top:4px}
.sigs{display:flex;gap:12px}
.sig{flex:1;border:1.5px dashed #C4B5FD;border-radius:8px;padding:10px;text-align:center;font-size:10px;color:#7C3AED}
.ss{height:38px}`
    return wrap(css, `<div class="page">
<div class="hdr">
  <div><div class="hco">VOTRE SOCIÉTÉ SARL</div><div class="hsub">Plateau, Abidjan · CNPS Employeur : 123456789<br>NIF : 2405812 A · Effectif : 24 agents</div></div>
  <div><div class="htitle">Bulletin de Paie</div><div class="hperiod">Juillet 2026 — Mois 07/2026</div></div>
</div>
<div class="emp">
  <div class="empav">KA</div>
  <div><div class="empname">KONÉ Aminata</div><div class="empsub">Responsable Commerciale · Catégorie 3B<br>Mat. : EMP-2024-0042 · Embauche : 01/03/2022 · CNPS : 987-654-321</div></div>
</div>
<div class="body">
<div class="paycols">
  <div class="paycol gains">
    <div class="pchead">💚 Éléments de gains</div>
    <div class="pcrow"><span>Salaire de base</span><span>350 000</span></div>
    <div class="pcrow"><span>Indemnité de transport</span><span>30 000</span></div>
    <div class="pcrow"><span>Prime de rendement (10%)</span><span>35 000</span></div>
    <div class="pcrow"><span>Heures supplémentaires ×2</span><span>14 000</span></div>
    <div class="pcrow"><span>Prime d'ancienneté</span><span>17 500</span></div>
    <div class="pctot"><span>TOTAL BRUT</span><span>446 500 XOF</span></div>
  </div>
  <div class="paycol retenues">
    <div class="pchead">❤️ Retenues obligatoires</div>
    <div class="pcrow"><span>CNPS salarié (3.2%)</span><span>-14 288</span></div>
    <div class="pcrow"><span>Impôt/Salaire ITS</span><span>-22 400</span></div>
    <div class="pcrow"><span>Avance sur salaire</span><span>-20 000</span></div>
    <div class="pcrow"><span>Mutuelle santé</span><span>-5 000</span></div>
    <div class="pcrow" style="opacity:0">&nbsp;</div>
    <div class="pctot"><span>TOTAL RETENUES</span><span>-61 688 XOF</span></div>
  </div>
</div>
<div class="netbox">
  <div class="netlbl">Net à payer au salarié</div>
  <div class="netamt">384 812 XOF</div>
  <div class="netsub">Trois cent quatre-vingt-quatre mille huit cent douze francs CFA</div>
</div>
<div class="sigs">
  <div class="sig"><div class="ss"></div>Signature Employé — Reçu le ___________</div>
  <div class="sig"><div class="ss"></div>Cachet & Signature DRH</div>
</div>
</div></div>`)
  }
  if (doc.id === 'ordre_miss') {
    const css = `
.page{max-width:680px;margin:0 auto;border:2px solid ${c};border-radius:12px;overflow:hidden}
.hdr{background:${c};color:#fff;padding:16px 22px;display:flex;justify-content:space-between;align-items:center}
.ht{font-size:17px;font-weight:900;text-transform:uppercase;letter-spacing:.06em}
.hsub{font-size:10px;opacity:.8;margin-top:3px}
.hnum{text-align:right;font-size:11px;opacity:.9;line-height:1.8}
.banner{background:#F5F3FF;border-bottom:1px solid #DDD6FE;padding:12px 22px;display:flex;gap:24px}
.bstat{display:flex;align-items:center;gap:6px;font-size:11px;font-weight:600;color:${c}}
.body{padding:18px 22px}
.grid4{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:16px}
.cell{background:#F8FAFC;border-radius:8px;padding:11px;border-left:3px solid ${c}}
.clbl{font-size:8.5px;text-transform:uppercase;letter-spacing:.1em;color:#94A3B8;font-weight:700;margin-bottom:3px}
.cval{font-size:12px;font-weight:700;color:#1E293B}
.stitle{font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:${c};margin:14px 0 8px}
table{width:100%;border-collapse:collapse;margin-bottom:14px}
th{background:${c};color:#fff;padding:8px 12px;text-align:left;font-size:9.5px;font-weight:700;text-transform:uppercase}
th:last-child,td:last-child{text-align:right}
td{padding:8px 12px;border-bottom:1px solid #EDE9FE;font-size:11.5px}
.tot{background:#5B21B6;color:#fff;display:flex;justify-content:space-between;padding:10px 12px;font-size:13px;font-weight:800;border-radius:0 0 8px 8px}
.sigs{display:flex;gap:12px;margin-top:6px}
.sig{flex:1;border:1.5px dashed #C4B5FD;border-radius:8px;padding:10px;text-align:center;font-size:10px;color:#7C3AED}
.ss{height:38px}`
    return wrap(css, `<div class="page">
<div class="hdr">
  <div><div class="ht">Ordre de Mission</div><div class="hsub">VOTRE SOCIÉTÉ SARL — Direction Générale</div></div>
  <div class="hnum">OM-2026-0021<br>Émis le : 25/07/2026</div>
</div>
<div class="banner">
  <div class="bstat">✈️ Mission : Bouaké, Côte d'Ivoire</div>
  <div class="bstat">📅 28/07 → 30/07/2026</div>
  <div class="bstat">⏱️ 3 jours ouvrés</div>
</div>
<div class="body">
<div class="grid4">
  <div class="cell"><div class="clbl">Agent en mission</div><div class="cval">KONÉ Aminata</div></div>
  <div class="cell"><div class="clbl">Fonction</div><div class="cval">Responsable Commerciale</div></div>
  <div class="cell"><div class="clbl">Objet de la mission</div><div class="cval">Prospection commerciale — Région Centre</div></div>
  <div class="cell"><div class="clbl">Moyen de transport</div><div class="cval">🚗 Véhicule société</div></div>
</div>
<div class="stitle">Frais prévisionnels autorisés</div>
<table>
<thead><tr><th>Poste de dépense</th><th>Calcul</th><th>Montant</th></tr></thead>
<tbody>
<tr><td>Hébergement (hôtel Bouaké)</td><td>2 nuits × 40 000</td><td>80 000 XOF</td></tr>
<tr><td>Perdiem repas journalier</td><td>3 jours × 15 000</td><td>45 000 XOF</td></tr>
<tr><td>Transport local sur place</td><td>forfait</td><td>25 000 XOF</td></tr>
</tbody>
</table>
<div class="tot"><span>AVANCE DE MISSION</span><span>150 000 XOF</span></div>
<div class="sigs" style="margin-top:14px">
  <div class="sig"><div class="ss"></div>Signature Agent — KONÉ Aminata</div>
  <div class="sig"><div class="ss"></div>Visa Direction Générale</div>
</div>
</div></div>`)
  }
  // Autres docs RH (congé, note de service, etc.)
  const css = `
.page{max-width:680px;margin:0 auto}
.hdr{display:flex;justify-content:space-between;align-items:flex-start;padding:18px 22px;background:#F5F3FF;border-bottom:3px solid ${c}}
.hco{font-size:14px;font-weight:800;color:#1E1B4B}.hsub{font-size:10px;color:#6D28D9;margin-top:2px;line-height:1.6}
.hdoc{text-align:right}
.dtype{font-size:16px;font-weight:900;color:${c};text-transform:uppercase}
.dref{font-size:10px;color:#64748B;margin-top:4px;line-height:1.7}
.body{padding:18px 22px}
.empcard{display:flex;align-items:center;gap:14px;background:#EDE9FE;border-radius:10px;padding:12px 16px;margin-bottom:16px}
.av{width:42px;height:42px;background:${c};border-radius:50%;display:grid;place-items:center;color:#fff;font-size:15px;font-weight:900;flex-shrink:0}
.ename{font-size:14px;font-weight:800;color:#1E1B4B}
.esub{font-size:10.5px;color:#6D28D9;margin-top:2px}
.grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:16px}
.cell{background:#F8FAFC;border-radius:8px;padding:11px;border-left:3px solid ${c}}
.clbl{font-size:8.5px;text-transform:uppercase;letter-spacing:.1em;color:#94A3B8;font-weight:700;margin-bottom:3px}
.cval{font-size:12px;font-weight:700;color:#1E293B}
.motive{background:#F5F3FF;border-left:3px solid ${c};border-radius:6px;padding:12px 14px;font-size:11.5px;color:#374151;line-height:1.75;margin-bottom:16px}
.balance{display:flex;gap:10px;margin-bottom:16px}
.bcard{flex:1;text-align:center;padding:12px;border-radius:10px}
.bval{font-size:20px;font-weight:900}
.blbl{font-size:9.5px;color:#64748B;margin-top:3px}
.sigs{display:flex;gap:12px}
.sig{flex:1;border:1.5px dashed #C4B5FD;border-radius:8px;padding:10px;text-align:center;font-size:10px;color:#7C3AED}
.ss{height:38px}`
  return wrap(css, `<div class="page">
<div class="hdr">
  <div><div class="hco">VOTRE SOCIÉTÉ SARL</div><div class="hsub">Département Ressources Humaines<br>Plateau, Abidjan · RCCM CI-ABJ-2024-B-12345</div></div>
  <div class="hdoc"><div class="dtype">${doc.name}</div><div class="dref">N° : RH-2026-0053<br>Date : 27/07/2026<br>Urgence : 🟡 Normale</div></div>
</div>
<div class="body">
<div class="empcard">
  <div class="av">KA</div>
  <div><div class="ename">KONÉ Aminata</div><div class="esub">Responsable Commerciale · Mat. EMP-2024-0042 · Embauche : 01/03/2022</div></div>
</div>
<div class="grid">
  <div class="cell"><div class="clbl">Type de demande</div><div class="cval">${doc.name}</div></div>
  <div class="cell"><div class="clbl">Date de la demande</div><div class="cval">27/07/2026</div></div>
  <div class="cell"><div class="clbl">Période concernée</div><div class="cval">01/08/2026 → 15/08/2026</div></div>
  <div class="cell"><div class="clbl">Durée</div><div class="cval">15 jours ouvrables</div></div>
</div>
<div class="balance">
  <div class="bcard" style="background:#F0FDF4"><div class="bval" style="color:#15803D">28 j</div><div class="blbl">Solde avant</div></div>
  <div class="bcard" style="background:#FFF7ED"><div class="bval" style="color:#C2410C">-15 j</div><div class="blbl">Déduction</div></div>
  <div class="bcard" style="background:#EFF6FF"><div class="bval" style="color:#1D4ED8">13 j</div><div class="blbl">Solde restant</div></div>
</div>
<div class="motive"><b>Motif :</b> Congé annuel — repos et raisons familiales. L'employé confirme avoir planifié le transfert de ses dossiers à Mme BAMBA Adjoua pour la période concernée.</div>
<div class="sigs">
  <div class="sig"><div class="ss"></div>Signature Employé</div>
  <div class="sig"><div class="ss"></div>✅ Visa Manager N+1</div>
  <div class="sig"><div class="ss"></div>Visa Direction RH</div>
</div>
</div></div>`)
}

// ── TEMPLATE 4 : Administratif/Juridique — document légal avec articles
function previewAdmin(doc) {
  const c = doc.catColor || '#4F46E5'
  const isPV = doc.id === 'pv_reun'
  const css = `
.page{max-width:680px;margin:0 auto;border:1px solid #E2E8F0;border-radius:4px}
.seal-top{display:flex;justify-content:space-between;align-items:flex-start;padding:20px 28px;border-bottom:2px solid #1E293B}
.co-block{display:flex;align-items:flex-start;gap:10px}
.co-logo{width:44px;height:44px;background:#1E293B;border-radius:6px;display:grid;place-items:center;color:#fff;font-size:14px;font-weight:900;flex-shrink:0}
.co-name{font-size:13px;font-weight:800;color:#1E293B}
.co-info{font-size:10px;color:#64748B;margin-top:3px;line-height:1.65}
.doc-ref-block{text-align:right}
.doc-num{font-size:11px;font-weight:700;color:#64748B;margin-bottom:4px}
.doc-stamp{display:inline-block;background:${c};color:#fff;padding:5px 14px;border-radius:20px;font-size:9.5px;font-weight:800;text-transform:uppercase;letter-spacing:.06em}
.title-block{text-align:center;padding:22px 28px;border-bottom:1px solid #E2E8F0}
.doc-title{font-size:18px;font-weight:900;color:#1E293B;text-transform:uppercase;letter-spacing:.04em;margin-bottom:6px}
.doc-sub{font-size:11px;color:#64748B}
.line-ornament{width:60px;height:3px;background:${c};margin:10px auto 0}
.parties{display:grid;grid-template-columns:1fr 1fr;gap:0;border-bottom:1px solid #E2E8F0}
.party{padding:14px 28px}
.party:first-child{border-right:1px solid #E2E8F0}
.plbl{font-size:8.5px;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:#94A3B8;margin-bottom:5px}
.pname{font-size:13px;font-weight:800;color:#1E293B}
.pinfo{font-size:10.5px;color:#64748B;line-height:1.65;margin-top:3px}
.articles{padding:18px 28px}
.art{margin-bottom:14px;padding-bottom:14px;border-bottom:1px solid #F1F5F9}
.art:last-child{border-bottom:none;margin-bottom:0}
.artnum{font-size:10.5px;font-weight:800;text-transform:uppercase;color:${c};margin-bottom:5px}
.artbody{font-size:11.5px;color:#374151;line-height:1.8}
.meta{display:flex;gap:20px;padding:10px 28px;background:#F8FAFC;border-top:1px solid #E2E8F0;font-size:10px;color:#64748B}
.sigs{display:flex;gap:0;border-top:2px solid #1E293B}
.sig{flex:1;padding:14px 28px;text-align:center;font-size:10px;color:#94A3B8}
.sig:first-child{border-right:1px dashed #CBD5E1}
.ss{height:40px}`
  const pvContent = `
<div class="artnum">Présents à la réunion</div>
<div class="artbody">M. KOUASSI Emmanuel — Directeur Général (Président de séance)<br>Mme BAMBA Adjoua — Directrice Financière<br>M. DIALLO Moussa — Directeur Commercial<br>Mme KONÉ Aminata — Responsable Commerciale</div>
<div class="artnum" style="margin-top:12px">Ordre du jour</div>
<div class="artbody">1. Bilan commercial du 1er semestre 2026<br>2. Objectifs et plan d'action 2ème semestre<br>3. Validation du budget marketing Q3<br>4. Questions diverses</div>
<div class="artnum" style="margin-top:12px">Résolutions adoptées</div>
<div class="artbody">• Objectif CA : +15% sur S2 2026 — adopté à l'unanimité<br>• Budget marketing Q3 : 2 500 000 XOF — approuvé<br>• Recrutement de 2 commerciaux terrain — en cours<br>Prochaine réunion : 31/10/2026</div>`
  const contratContent = `
<div class="art"><div class="artnum">Article 1 — Objet</div><div class="artbody">Le présent ${doc.name.toLowerCase()} a pour objet de définir les modalités et conditions de la relation entre les parties, notamment : ${doc.desc}. Il est régi par les dispositions du droit OHADA en vigueur en République de Côte d'Ivoire.</div></div>
<div class="art"><div class="artnum">Article 2 — Durée & Entrée en vigueur</div><div class="artbody">Le présent document prend effet à compter de sa date de signature pour une durée de <b>douze (12) mois</b>, renouvelable par accord exprès des parties. En cas de non-renouvellement, un préavis de 30 jours est requis.</div></div>
<div class="art"><div class="artnum">Article 3 — Obligations des parties</div><div class="artbody">Chaque partie s'engage à respecter ses obligations dans les délais convenus et à informer l'autre partie de tout événement pouvant affecter l'exécution du présent accord. Toute modification devra faire l'objet d'un avenant écrit et signé.</div></div>
<div class="art"><div class="artnum">Article 4 — Règlement des litiges</div><div class="artbody">En cas de litige, les parties s'engagent à rechercher une solution amiable. À défaut, tout différend sera soumis au Tribunal de Commerce d'Abidjan, seul compétent.</div></div>`
  return wrap(css, `<div class="page">
<div class="seal-top">
  <div class="co-block"><div class="co-logo">⚖️</div><div><div class="co-name">VOTRE SOCIÉTÉ SARL</div><div class="co-info">Plateau, Abidjan 01 · Côte d'Ivoire<br>RCCM CI-ABJ-2024-B-12345 · NIF 2405812 A<br>📞 +225 27 22 33 44 55</div></div></div>
  <div class="doc-ref-block"><div class="doc-num">Réf. : ADM-2026-0042 · ${isPV ? 'Abidjan, 27/07/2026' : '27 juillet 2026'}</div><div class="doc-stamp">${doc.name}</div></div>
</div>
<div class="title-block">
  <div class="doc-title">${doc.name}</div>
  <div class="doc-sub">${isPV ? 'Procès-verbal de réunion du Comité de Direction — 27 juillet 2026 · 14h00 · Siège social' : 'Entre VOTRE SOCIÉTÉ SARL, ci-après «&nbsp;Partie A&nbsp;» et CLIENT EXEMPLE & ASSOCIÉS, ci-après «&nbsp;Partie B&nbsp;»'}</div>
  <div class="line-ornament"></div>
</div>
${isPV ? '' : `<div class="parties"><div class="party"><div class="plbl">Partie A — Prestataire</div><div class="pname">VOTRE SOCIÉTÉ SARL</div><div class="pinfo">Plateau, Abidjan 01<br>RCCM CI-ABJ-2024-B-12345<br>Représentée par M. KOUASSI Emmanuel, DG</div></div><div class="party"><div class="plbl">Partie B — Client</div><div class="pname">CLIENT EXEMPLE & ASSOCIÉS</div><div class="pinfo">Cocody Riviera 3, Abidjan<br>RCCM CI-ABJ-2020-B-44521<br>Représentée par M. TRAORÉ Issouf, Gérant</div></div></div>`}
<div class="articles">${isPV ? pvContent : contratContent}</div>
<div class="meta"><span>📍 Fait à Abidjan</span><span>📅 Le 27 juillet 2026</span><span>📄 En deux (2) exemplaires originaux</span><span>⚖️ Droit applicable : OHADA</span></div>
<div class="sigs"><div class="sig"><div class="ss"></div>${isPV ? 'Signature du Président de séance<br>M. KOUASSI Emmanuel' : 'Signature Partie A<br>Nom, Qualité & Cachet'}</div><div class="sig"><div class="ss"></div>${isPV ? 'Signature Secrétaire de séance<br>Mme BAMBA Adjoua' : 'Signature Partie B<br>Nom, Qualité & Cachet'}</div></div>
</div>`)
}

// ── TEMPLATE 5 : SAV — rapport technique avec sidebar équipement
function previewSAV(doc) {
  const c = doc.catColor || '#0891B2'
  const css = `
.page{max-width:680px;margin:0 auto;display:flex;flex-direction:column}
.top{background:linear-gradient(to right,#0C4A6E,${c});color:#fff;padding:16px 22px;display:flex;justify-content:space-between;align-items:center}
.ttype{font-size:16px;font-weight:900;text-transform:uppercase;letter-spacing:.05em}
.tsub{font-size:9.5px;opacity:.8;margin-top:3px}
.tref{text-align:right;font-size:10px;opacity:.85;line-height:1.8}
.layout{display:flex;flex:1}
.sidebar{width:200px;background:#0C4A6E;color:#fff;padding:16px;flex-shrink:0}
.sblbl{font-size:8px;text-transform:uppercase;letter-spacing:.1em;color:#67E8F9;font-weight:700;margin-bottom:4px;margin-top:12px}
.sblbl:first-child{margin-top:0}
.sbval{font-size:11px;font-weight:600;line-height:1.55;color:#E0F2FE}
.status-box{margin-top:14px;background:rgba(255,255,255,.1);border-radius:8px;padding:10px;text-align:center}
.sicon{font-size:22px}
.stxt{font-size:9.5px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;margin-top:4px;color:#6EE7B7}
.main{flex:1;padding:16px 20px}
.stitle{font-size:9.5px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:${c};margin:14px 0 7px;border-bottom:1px solid #E0F2FE;padding-bottom:4px}
.stitle:first-child{margin-top:0}
.steps{display:flex;flex-direction:column;gap:6px;margin-bottom:14px}
.step{display:flex;gap:8px;align-items:flex-start;font-size:11.5px;color:#374151}
.snum{width:20px;height:20px;background:${c};color:#fff;border-radius:50%;display:grid;place-items:center;font-size:9px;font-weight:800;flex-shrink:0;margin-top:1px}
table{width:100%;border-collapse:collapse;margin-bottom:10px}
thead tr{background:${c}}
th{color:#fff;padding:7px 10px;text-align:left;font-size:9px;font-weight:700;text-transform:uppercase}
th:last-child,td:last-child{text-align:right}
td{padding:7px 10px;border-bottom:1px solid #E0F2FE;font-size:11px}
.tot{display:flex;justify-content:flex-end;margin-bottom:10px}
.totb{background:${c};color:#fff;padding:8px 14px;border-radius:6px;font-size:12px;font-weight:800}
.sigs{display:flex;gap:10px;margin-top:8px}
.sig{flex:1;border:1.5px dashed #BAE6FD;border-radius:8px;padding:10px;text-align:center;font-size:10px;color:#0891B2}
.ss{height:36px}`
  return wrap(css, `<div class="page">
<div class="top">
  <div><div class="ttype">${doc.name}</div><div class="tsub">VOTRE SOCIÉTÉ SARL — Service Après-Vente</div></div>
  <div class="tref">N° INT-2026-0144<br>Client : KABORÉ SAS<br>27/07/2026 · 09h00→14h30</div>
</div>
<div class="layout">
<div class="sidebar">
  <div class="sblbl">Équipement</div><div class="sbval">Groupe électrogène<br>250 KVA — Perkins</div>
  <div class="sblbl">N° de série</div><div class="sbval">GE-2021-AB-00931</div>
  <div class="sblbl">Localisation</div><div class="sbval">Zone Industrielle<br>Yopougon, Abidjan</div>
  <div class="sblbl">Type</div><div class="sbval">🔧 Curative<br>Panne moteur</div>
  <div class="sblbl">Technicien</div><div class="sbval">DIALLO Moussa<br>Technicien N3</div>
  <div class="status-box"><div class="sicon">✅</div><div class="stxt">Résolu</div></div>
</div>
<div class="main">
  <div class="stitle">Diagnostic réalisé</div>
  <div class="steps">
    <div class="step"><div class="snum">1</div><div>Inspection visuelle complète de l'équipement — identification de la panne moteur au niveau du démarreur</div></div>
    <div class="step"><div class="snum">2</div><div>Démontage et vérification du démarreur 24V — bobinage brûlé confirmé</div></div>
    <div class="step"><div class="snum">3</div><div>Nettoyage circuit carburant + remplacement double filtre — encrassement avancé détecté</div></div>
    <div class="step"><div class="snum">4</div><div>Réétalonnage capteurs pression huile et température — hors normes (+12%)</div></div>
    <div class="step"><div class="snum">5</div><div>Test de charge à 80% pendant 2h — résultat nominal — équipement opérationnel</div></div>
  </div>
  <div class="stitle">Pièces de rechange utilisées</div>
  <table>
  <thead><tr><th>Désignation</th><th>Réf.</th><th>Qté</th><th>P.U.</th><th>Total</th></tr></thead>
  <tbody>
  <tr><td>Démarreur 24V</td><td>ST-24-093</td><td>1</td><td>85 000</td><td>85 000 XOF</td></tr>
  <tr><td>Filtre carburant double</td><td>FC-D-441</td><td>2</td><td>12 500</td><td>25 000 XOF</td></tr>
  <tr><td>Joint d'étanchéité</td><td>JE-012</td><td>4</td><td>3 500</td><td>14 000 XOF</td></tr>
  </tbody>
  </table>
  <div class="tot"><div class="totb">Total pièces : 124 000 XOF</div></div>
  <div class="sigs">
    <div class="sig"><div class="ss"></div>Visa Technicien — DIALLO Moussa</div>
    <div class="sig"><div class="ss"></div>Bon pour accord Client<br><em>Date :</em></div>
  </div>
</div>
</div></div>`)
}

// ── TEMPLATE 6 : BTP — dashboard chantier avec barres de progression
function previewBTP(doc) {
  const c = doc.catColor || '#DC2626'
  const bar = (pct, color = c) => `<div style="background:#E2E8F0;border-radius:4px;height:8px;width:100%;margin-top:4px"><div style="background:${color};height:8px;border-radius:4px;width:${pct}%"></div></div>`
  const css = `
.page{max-width:680px;margin:0 auto}
.top{background:#1C1917;color:#fff;padding:16px 22px}
.toprow{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px}
.tco{font-size:13px;font-weight:800}
.tsub{font-size:9.5px;color:#A8A29E;margin-top:2px}
.tdoc{text-align:right}
.ttype{font-size:15px;font-weight:900;text-transform:uppercase;color:${c};letter-spacing:.04em}
.tref{font-size:10px;color:#A8A29E;margin-top:4px;line-height:1.7}
.chantier-bar{display:flex;gap:14px;background:rgba(255,255,255,.07);border-radius:8px;padding:10px 14px}
.cb{flex:1;font-size:10px}
.cblbl{color:#78716C;font-size:8.5px;text-transform:uppercase;letter-spacing:.08em;margin-bottom:2px}
.cbval{font-weight:700;color:#F5F5F4;font-size:11.5px}
.avancement{background:#FEF2F2;border-left:4px solid ${c};padding:10px 18px;display:flex;align-items:center;justify-content:space-between}
.av-label{font-size:11px;font-weight:700;color:#7F1D1D}
.av-pct{font-size:24px;font-weight:900;color:${c}}
.av-bar{background:#FCA5A5;border-radius:4px;height:6px;width:120px;margin-top:4px}
.av-fill{background:${c};height:6px;border-radius:4px;width:62%}
.body{padding:16px 22px}
.parties{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:14px}
.party{background:#F8FAFC;border-radius:8px;padding:11px;border-left:3px solid ${c}}
.plbl{font-size:8.5px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#94A3B8;margin-bottom:3px}
.pname{font-size:12.5px;font-weight:800;color:#1C1917}
.pinfo{font-size:10.5px;color:#64748B;margin-top:2px;line-height:1.55}
.stitle{font-size:9.5px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:${c};margin:14px 0 8px}
.posts{display:flex;flex-direction:column;gap:10px;margin-bottom:14px}
.post{background:#F8FAFC;border-radius:8px;padding:10px 14px}
.post-top{display:flex;justify-content:space-between;align-items:baseline;margin-bottom:2px}
.post-name{font-size:11.5px;font-weight:700;color:#1C1917}
.post-nums{font-size:10px;color:#64748B}
.post-pct{font-size:13px;font-weight:900;color:${c}}
.tw{display:flex;justify-content:flex-end;margin-bottom:12px}
.tb{width:280px}
.tl{display:flex;justify-content:space-between;padding:6px 12px;font-size:11.5px;border-bottom:1px solid #F1F5F9;color:#475569}
.tf{display:flex;justify-content:space-between;padding:10px 12px;font-size:14px;font-weight:900;background:${c};color:#fff;border-radius:0 0 8px 8px}
.sigs{display:flex;gap:12px}
.sig{flex:1;border:1.5px dashed #FCA5A5;border-radius:8px;padding:10px;text-align:center;font-size:10px;color:${c}}
.ss{height:38px}`
  return wrap(css, `<div class="page">
<div class="top">
  <div class="toprow">
    <div><div class="tco">VOTRE SOCIÉTÉ SARL — BTP & Génie Civil</div><div class="tsub">Plateau, Abidjan · RCCM CI-ABJ-2024-B-12345</div></div>
    <div class="tdoc"><div class="ttype">${doc.name}</div><div class="tref">N° SIT-2026-0007<br>Période : Juillet 2026</div></div>
  </div>
  <div class="chantier-bar">
    <div class="cb"><div class="cblbl">🏗️ Chantier</div><div class="cbval">Résidence Les Bougainvilliers — R+3</div></div>
    <div class="cb"><div class="cblbl">📍 Adresse</div><div class="cbval">Angré 8ème Tranche, Abidjan</div></div>
    <div class="cb"><div class="cblbl">📅 Délai contractuel</div><div class="cbval">18 mois — fin : 31/01/2028</div></div>
  </div>
</div>
<div class="avancement">
  <div><div class="av-label">🏗️ Avancement global du chantier</div><div class="av-bar"><div class="av-fill"></div></div></div>
  <div class="av-pct">62 %</div>
</div>
<div class="body">
<div class="parties">
  <div class="party"><div class="plbl">Maître d'œuvre</div><div class="pname">VOTRE SOCIÉTÉ SARL</div><div class="pinfo">Gérant chantier : DIALLO Moussa<br>Chef de travaux : KOFFI Yao Emmanuel</div></div>
  <div class="party"><div class="plbl">Maître d'ouvrage</div><div class="pname">SCI LES BOUGAINVILLIERS</div><div class="pinfo">M. ADJOUA Pierre — 05 04 03 02 01<br>Architecte : Cabinet DESIGN CI</div></div>
</div>
<div class="stitle">Avancement par poste — Juillet 2026</div>
<div class="posts">
  <div class="post"><div class="post-top"><span class="post-name">Terrassement & VRD</span><span class="post-nums">450 / 450 m³</span><span class="post-pct">100%</span></div>${bar(100,'#16A34A')}</div>
  <div class="post"><div class="post-top"><span class="post-name">Fondations béton armé</span><span class="post-nums">98 / 120 m³</span><span class="post-pct">82%</span></div>${bar(82,'#2563EB')}</div>
  <div class="post"><div class="post-top"><span class="post-name">Maçonnerie parpaings</span><span class="post-nums">370 / 600 m²</span><span class="post-pct">62%</span></div>${bar(62,c)}</div>
  <div class="post"><div class="post-top"><span class="post-name">Charpente & toiture</span><span class="post-nums">0 / 280 m²</span><span class="post-pct">0%</span></div>${bar(0,'#94A3B8')}</div>
</div>
<div class="tw"><div class="tb">
  <div class="tl"><span>Situation mois courant</span><span>9 740 000 XOF</span></div>
  <div class="tl"><span>Situations cumulées antérieures</span><span>14 200 000 XOF</span></div>
  <div class="tf"><span>CUMUL FACTURABLE TTC</span><span>23 940 000 XOF</span></div>
</div></div>
<div class="sigs">
  <div class="sig"><div class="ss"></div>Visa Maître d'Œuvre</div>
  <div class="sig"><div class="ss"></div>Visa Maître d'Ouvrage</div>
  <div class="sig"><div class="ss"></div>Visa Bureau de Contrôle</div>
</div>
</div></div>`)
}

// ── TEMPLATE 7 : Stock — avant/après avec indicateurs colorés
function previewStock(doc) {
  const c = doc.catColor || '#D97706'
  const isEntree = ['be','br_f','bon_emb'].includes(doc.id)
  const isSortie = ['bs','consommation','destruction'].includes(doc.id)
  const mvColor = isSortie ? '#DC2626' : '#16A34A'
  const mvLabel = isSortie ? '⬇️ SORTIE DE STOCK' : '⬆️ ENTRÉE EN STOCK'
  const css = `
.page{max-width:680px;margin:0 auto}
.top{background:#1C1917;color:#fff;padding:14px 22px;display:flex;justify-content:space-between;align-items:center}
.tco{font-size:13px;font-weight:800;color:#F5F5F4}
.tsub{font-size:9.5px;color:#A8A29E;margin-top:2px}
.tdoc{text-align:right}
.ttype{font-size:14px;font-weight:900;text-transform:uppercase;color:${c};letter-spacing:.04em}
.tref{font-size:10px;color:#A8A29E;margin-top:4px;line-height:1.7}
.mvbanner{display:flex;align-items:center;justify-content:space-between;padding:11px 22px;background:${mvColor};color:#fff}
.mvlbl{font-size:13px;font-weight:900;text-transform:uppercase;letter-spacing:.04em}
.mvmeta{font-size:10px;opacity:.9;line-height:1.7}
.body{padding:16px 22px}
.infos{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-bottom:14px}
.icard{background:#FFF7ED;border-radius:8px;padding:10px;border-top:3px solid ${c};text-align:center}
.ilbl{font-size:8.5px;text-transform:uppercase;letter-spacing:.08em;color:#92400E;font-weight:700;margin-bottom:3px}
.ival{font-size:11.5px;font-weight:800;color:#1C1917}
.stitle{font-size:9.5px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:${c};margin:14px 0 8px}
table{width:100%;border-collapse:collapse;margin-bottom:12px}
thead tr{background:#1C1917}
th{color:#D6D3D1;padding:8px 10px;text-align:left;font-size:9px;font-weight:700;text-transform:uppercase}
th.num{text-align:right}
td{padding:8px 10px;border-bottom:1px solid #FEF3C7;font-size:11.5px}
td.num{text-align:right}
td.avant{color:#6B7280;text-align:right}
td.mouv{font-weight:800;text-align:right;color:${mvColor}}
td.apres{font-weight:800;text-align:right;color:#1C1917}
.obs{background:#FFFBEB;border-left:3px solid ${c};padding:10px 14px;border-radius:6px;font-size:11px;color:#92400E;margin-bottom:12px}
.sigs{display:flex;gap:12px}
.sig{flex:1;border:1.5px dashed #FCD34D;border-radius:8px;padding:10px;text-align:center;font-size:10px;color:${c}}
.ss{height:36px}`
  return wrap(css, `<div class="page">
<div class="top">
  <div><div class="tco">VOTRE SOCIÉTÉ SARL</div><div class="tsub">Gestion des Stocks — Entrepôt Principal Yopougon</div></div>
  <div class="tdoc"><div class="ttype">${doc.name}</div><div class="tref">N° STK-2026-0231<br>27/07/2026 — 08h30</div></div>
</div>
<div class="mvbanner">
  <div class="mvlbl">${mvLabel}</div>
  <div class="mvmeta">Responsable : COULIBALY Seydou<br>Ref. document : BL-2026-BL-0089</div>
</div>
<div class="body">
<div class="infos">
  <div class="icard"><div class="ilbl">Dépôt</div><div class="ival">Principal<br>Yopougon</div></div>
  <div class="icard"><div class="ilbl">Date</div><div class="ival">27/07<br>2026</div></div>
  <div class="icard"><div class="ilbl">Articles</div><div class="ival" style="font-size:20px;color:${c}">4</div></div>
  <div class="icard"><div class="ilbl">Statut</div><div class="ival" style="color:#16A34A">✅ Validé</div></div>
</div>
<div class="stitle">Détail des mouvements de stock</div>
<table>
<thead><tr><th>Réf.</th><th>Désignation de l'article</th><th>Unité</th><th class="num">Stock avant</th><th class="num">Mouvement</th><th class="num">Stock après</th></tr></thead>
<tbody>
<tr><td>ART-001</td><td>Ciment Portland CPA 42.5</td><td>Sac 50 kg</td><td class="avant">248</td><td class="mouv">+50</td><td class="apres">298</td></tr>
<tr><td>ART-002</td><td>Sable fin de rivière lavé</td><td>m³</td><td class="avant">32</td><td class="mouv" style="color:#DC2626">-8</td><td class="apres">24</td></tr>
<tr><td>ART-003</td><td>Fers à béton Ø12 — 6 m</td><td>Barre</td><td class="avant">120</td><td class="mouv">+30</td><td class="apres">150</td></tr>
<tr><td>ART-004</td><td>Parpaings creux 15×20×40 cm</td><td>Unité</td><td class="avant">2 400</td><td class="mouv" style="color:#DC2626">-500</td><td class="apres">1 900</td></tr>
</tbody>
</table>
<div class="obs">📋 <b>Observations :</b> Mouvement validé suite à réception BL N° 2026-BL-0089 — Fournisseur : MATÉRIAUX DU GOLF SARL. Colis vérifiés et conformes. Stockage zone A3.</div>
<div class="sigs">
  <div class="sig"><div class="ss"></div>Signature Magasinier<br>COULIBALY Seydou</div>
  <div class="sig"><div class="ss"></div>Visa Responsable Stock<br><em>Date : ___________</em></div>
</div>
</div></div>`)
}

// ── TEMPLATE 8 : Immobilier — fiche bien + conditions bail
function previewImmobilier(doc) {
  const c = doc.catColor || '#DB2777'
  const isEtat = ['edle','edls'].includes(doc.id)
  const isQuittance = doc.id === 'quittance_l'
  const css = `
.page{max-width:680px;margin:0 auto}
.top{background:linear-gradient(135deg,#831843 0%,${c} 100%);color:#fff;padding:18px 24px;display:flex;justify-content:space-between;align-items:center}
.tco{font-size:13px;font-weight:800}
.tsub{font-size:9.5px;opacity:.8;margin-top:2px;line-height:1.6}
.tdoc{text-align:right}
.ttype{font-size:15px;font-weight:900;text-transform:uppercase;letter-spacing:.04em}
.tref{font-size:10px;opacity:.85;margin-top:4px;line-height:1.8}
.property-card{background:#FFF1F2;border-left:4px solid ${c};padding:14px 22px;display:flex;justify-content:space-between;align-items:center}
.pico{font-size:36px}
.pdet{}
.pname{font-size:15px;font-weight:900;color:#881337}
.psub{font-size:11px;color:#BE123C;margin-top:3px}
.pspecs{display:flex;gap:12px;margin-top:6px;flex-wrap:wrap}
.spec{background:#fff;border-radius:6px;padding:5px 10px;font-size:10.5px;font-weight:700;color:${c};border:1px solid #FECDD3}
.body{padding:16px 24px}
.grid3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-bottom:14px}
.grid2{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:14px}
.card{background:#FFF1F2;border-radius:9px;padding:12px;text-align:center;border-top:3px solid ${c}}
.clbl{font-size:8.5px;text-transform:uppercase;letter-spacing:.1em;color:#BE123C;font-weight:700;margin-bottom:4px}
.cval{font-size:13px;font-weight:900;color:#881337}
.csub{font-size:9.5px;color:#64748B;margin-top:2px}
.parties{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:14px}
.party{background:#F8FAFC;border-radius:8px;padding:11px;border-left:3px solid ${c}}
.plbl{font-size:8.5px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#94A3B8;margin-bottom:3px}
.pname2{font-size:12.5px;font-weight:800;color:#1E293B}
.pinfo{font-size:10.5px;color:#64748B;margin-top:2px;line-height:1.55}
.clause-title{font-size:9.5px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:${c};margin:12px 0 6px}
.clauses{font-size:11.5px;color:#374151;line-height:1.8;background:#FFF1F2;border-radius:8px;padding:12px 14px;margin-bottom:12px}
.sigs{display:flex;gap:12px}
.sig{flex:1;border:1.5px dashed #FECDD3;border-radius:8px;padding:10px;text-align:center;font-size:10px;color:${c}}
.ss{height:38px}`
  return wrap(css, `<div class="page">
<div class="top">
  <div><div class="tco">IMMOBILIÈRE DU PLATEAU SA</div><div class="tsub">Plateau, Abidjan · Agrément MH N° 2018-IMM-0042<br>+225 27 22 11 00 00 · immoplat@gmail.com</div></div>
  <div class="tdoc"><div class="ttype">${doc.name}</div><div class="tref">N° IMM-2026-0042<br>Date : 27/07/2026</div></div>
</div>
<div class="property-card">
  <div class="pico">🏡</div>
  <div class="pdet">
    <div class="pname">Villa F4 — Cocody Riviera 3</div>
    <div class="psub">Résidence Les Flamboyants — Bâtiment C, Porte 08</div>
    <div class="pspecs"><span class="spec">180 m²</span><span class="spec">4 chambres</span><span class="spec">2 SDB</span><span class="spec">Garage 2 voitures</span><span class="spec">Eau & élec.</span></div>
  </div>
</div>
<div class="body">
<div class="grid3">
  <div class="card"><div class="clbl">💰 Loyer mensuel</div><div class="cval">450 000</div><div class="csub">XOF / mois TTC</div></div>
  <div class="card"><div class="clbl">📅 Durée du bail</div><div class="cval">24 mois</div><div class="csub">01/08/2026 → 31/07/2028</div></div>
  <div class="card"><div class="clbl">🔒 Caution versée</div><div class="cval">900 000</div><div class="csub">XOF — 2 mois de loyer</div></div>
</div>
<div class="parties">
  <div class="party"><div class="plbl">Bailleur / Propriétaire</div><div class="pname2">IMMOBILIÈRE DU PLATEAU SA</div><div class="pinfo">Plateau, Abidjan · NIF 1234567 B<br>Représentée par M. ADJOUA Pierre, PDG</div></div>
  <div class="party"><div class="plbl">Locataire</div><div class="pname2">KONÉ Aminata</div><div class="pinfo">CIN : CI0123456789<br>+225 07 11 22 33 44 · koneaminata@email.ci</div></div>
</div>
<div class="clause-title">Conditions générales du bail</div>
<div class="clauses">
• <b>Paiement :</b> Loyer payable le 1er de chaque mois par virement BICICI ou chèque certifié<br>
• <b>Retard :</b> Pénalité de 5 % par mois de retard appliquée après 5 jours de grâce<br>
• <b>Charges incluses :</b> Eau, gardiennage 24h, entretien des parties communes<br>
• <b>Charges exclues :</b> Électricité, internet, déchets ménagers<br>
• <b>Sous-location :</b> Interdite sans accord écrit préalable du bailleur<br>
• <b>Renouvellement :</b> Tacite sauf résiliation avec préavis de 60 jours
</div>
<div class="sigs">
  <div class="sig"><div class="ss"></div>Signature & Cachet Bailleur</div>
  <div class="sig"><div class="ss"></div>Signature Locataire — Lu & approuvé</div>
</div>
</div></div>`)
}

// ── TEMPLATE 9 : Export/Douane — document international avec route
function previewExport(doc) {
  const c = doc.catColor || '#B45309'
  const css = `
.page{max-width:680px;margin:0 auto}
.top{background:#1C1917;padding:0}
.topbar{display:flex;justify-content:space-between;align-items:center;padding:14px 22px;border-bottom:1px solid #44403C}
.tco{color:#F5F5F4;font-size:13px;font-weight:800}
.tsub{color:#A8A29E;font-size:9.5px;margin-top:2px;line-height:1.6}
.tdoc{text-align:right}
.ttype{color:${c};font-size:15px;font-weight:900;text-transform:uppercase;letter-spacing:.04em}
.tref{color:#A8A29E;font-size:10px;margin-top:4px;line-height:1.7}
.route-bar{display:flex;align-items:center;padding:12px 22px;gap:8px;background:#292524}
.rside{flex:1;background:rgba(255,255,255,.06);border-radius:8px;padding:10px 14px}
.rfl{font-size:20px}
.rlbl{font-size:8px;text-transform:uppercase;letter-spacing:.1em;color:#78716C;margin-top:2px}
.rval{font-size:11.5px;font-weight:700;color:#E7E5E4;margin-top:1px}
.rarr{color:${c};font-size:20px;flex-shrink:0}
.meta-strip{display:flex;gap:14px;padding:10px 22px;background:${c};color:#fff}
.ms{font-size:9.5px;font-weight:700;display:flex;align-items:center;gap:4px}
.body{padding:16px 22px}
.decl{background:#FEF3C7;border:2px solid ${c};border-radius:8px;padding:12px 16px;margin-bottom:14px;display:flex;justify-content:space-between;align-items:center}
.dnum{font-size:14px;font-weight:900;color:#92400E}
.dlbl{font-size:9px;text-transform:uppercase;letter-spacing:.08em;color:#B45309;margin-bottom:3px}
.dsub{font-size:11px;color:#78350F}
.parties{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:14px}
.party{background:#F8FAFC;border-radius:8px;padding:11px;border-left:3px solid ${c}}
.plbl{font-size:8.5px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#94A3B8;margin-bottom:3px}
.pname{font-size:12.5px;font-weight:800;color:#1C1917}
.pinfo{font-size:10.5px;color:#64748B;margin-top:2px;line-height:1.55}
table{width:100%;border-collapse:collapse;margin-bottom:12px}
thead tr{background:#1C1917}
th{color:#D6D3D1;padding:8px 10px;text-align:left;font-size:9px;font-weight:700;text-transform:uppercase}
th:last-child,td:last-child{text-align:right}
td{padding:8px 10px;border-bottom:1px solid #FEF3C7;font-size:11.5px}
.tw{display:flex;justify-content:flex-end;margin-bottom:12px}
.tb{width:272px}
.tl{display:flex;justify-content:space-between;padding:6px 12px;font-size:11.5px;border-bottom:1px solid #FEF3C7;color:#475569}
.tf{display:flex;justify-content:space-between;padding:10px 12px;font-size:14px;font-weight:900;background:${c};color:#fff;border-radius:0 0 8px 8px}
.sigs{display:flex;gap:12px}
.sig{flex:1;border:1.5px dashed #FCD34D;border-radius:8px;padding:10px;text-align:center;font-size:10px;color:${c}}
.ss{height:38px}`
  return wrap(css, `<div class="page">
<div class="top">
  <div class="topbar">
    <div><div class="tco">VOTRE SOCIÉTÉ SARL</div><div class="tsub">Export & Commerce International · Abidjan, Côte d'Ivoire<br>RCCM CI-ABJ-2024-B-12345 · NIF 2405812 A</div></div>
    <div class="tdoc"><div class="ttype">${doc.name}</div><div class="tref">N° EXP-2026-00891<br>Date : 27/07/2026</div></div>
  </div>
  <div class="route-bar">
    <div class="rside"><div class="rfl">🇨🇮</div><div class="rlbl">Port d'embarquement</div><div class="rval">Port Autonome d'Abidjan</div></div>
    <div class="rarr">⟶</div>
    <div class="rside"><div class="rfl">🇲🇱</div><div class="rlbl">Destination finale</div><div class="rval">Bamako — Mali (voie terrestre)</div></div>
  </div>
  <div class="meta-strip">
    <div class="ms">📦 INCOTERM : CIF Bamako</div>
    <div class="ms">🚚 Transport : Routier TIR</div>
    <div class="ms">📋 Régime douanier : Export définitif</div>
  </div>
</div>
<div class="body">
<div class="decl">
  <div><div class="dlbl">N° Déclaration douanière</div><div class="dnum">CI-EXP-2026-00891</div></div>
  <div><div class="dlbl">Transitaire agréé</div><div class="dsub">TRANSIT EXPRESS CI · Agrément 0042</div></div>
</div>
<div class="parties">
  <div class="party"><div class="plbl">🇨🇮 Exportateur</div><div class="pname">VOTRE SOCIÉTÉ SARL</div><div class="pinfo">Plateau, Abidjan 01 · CI<br>NIF 2405812 A · RCCM CI-ABJ-2024-B-12345</div></div>
  <div class="party"><div class="plbl">🇲🇱 Importateur destinataire</div><div class="pname">SAHEL TRADING SA</div><div class="pinfo">Bamako, Mali<br>REG. COM. : ML-BKO-2019-B-4521</div></div>
</div>
<table>
<thead><tr><th>Désignation marchandise</th><th>Code SH</th><th>Quantité</th><th>Poids net</th><th>Valeur FOB</th></tr></thead>
<tbody>
<tr><td>Café robusta grade A — Récolte 2026</td><td>0901.11.00</td><td>200 sacs</td><td>10 000 kg</td><td>8 500 000 XOF</td></tr>
<tr><td>Cacao en fèves brut — Qualité export</td><td>1801.00.00</td><td>150 sacs</td><td>7 500 kg</td><td>11 250 000 XOF</td></tr>
</tbody>
</table>
<div class="tw"><div class="tb">
  <div class="tl"><span>Valeur FOB totale</span><span>19 750 000 XOF</span></div>
  <div class="tl"><span>Fret & assurance maritime</span><span>1 200 000 XOF</span></div>
  <div class="tf"><span>VALEUR CIF TOTALE</span><span>20 950 000 XOF</span></div>
</div></div>
<div class="sigs">
  <div class="sig"><div class="ss"></div>Signature Exportateur<br>Cachet officiel</div>
  <div class="sig"><div class="ss"></div>Visa Bureau des Douanes<br>Cachet & Réf.</div>
</div>
</div></div>`)
}

// ── TEMPLATE 10 : Santé — ordonnance / facture médicale
function previewSante(doc) {
  const c = doc.catColor || '#0891B2'
  if (doc.id === 'ordo' || doc.id === 'bon_labo') {
    const isLabo = doc.id === 'bon_labo'
    const css = `
.page{max-width:640px;margin:0 auto;border:2px solid ${c};border-radius:8px;overflow:hidden}
.top{background:${c};color:#fff;padding:14px 20px;display:flex;justify-content:space-between;align-items:flex-start}
.tco{font-size:13px;font-weight:800}.tsub{font-size:9.5px;opacity:.8;margin-top:2px;line-height:1.6}
.tdoc{text-align:right;font-size:10px;opacity:.9;line-height:1.8}
.patient-bar{background:#E0F2FE;padding:10px 20px;display:flex;gap:12px;align-items:center;border-bottom:1px solid rgba(8,145,178,.2)}
.pav{width:38px;height:38px;background:${c};border-radius:50%;display:grid;place-items:center;color:#fff;font-size:14px;font-weight:900;flex-shrink:0}
.pname{font-size:13px;font-weight:800;color:#0C4A6E}
.pinfo{font-size:10px;color:#0369A1;line-height:1.6;margin-top:1px}
.palert{display:inline-block;background:#FEF2F2;border:1px solid #FECACA;border-radius:4px;padding:2px 8px;font-size:9.5px;font-weight:700;color:#991B1B;margin-top:3px}
.body{padding:16px 20px;position:relative}
.rxbig{font-size:72px;font-weight:900;color:rgba(8,145,178,.1);position:absolute;right:14px;top:4px;line-height:1;letter-spacing:-2px}
.stitle{font-size:9.5px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:${c};margin:12px 0 8px;border-bottom:1px solid #E0F2FE;padding-bottom:4px}
.drugs{display:flex;flex-direction:column;gap:8px;margin-bottom:14px}
.drug{background:#F0F9FF;border-left:3px solid ${c};border-radius:6px;padding:10px 14px}
.dname{font-size:12.5px;font-weight:800;color:#0C4A6E}
.ddose{font-size:11px;color:#0369A1;margin-top:3px;line-height:1.55}
.dpos{font-size:10.5px;color:#475569;margin-top:2px}
.warn{background:#FFF7ED;border:1px solid #FED7AA;border-radius:8px;padding:10px 14px;font-size:11px;color:#92400E;margin-bottom:12px;line-height:1.7}
.sigs{display:flex;gap:12px}
.sig{flex:1;border:1.5px dashed #BAE6FD;border-radius:8px;padding:10px;text-align:center;font-size:10px;color:${c}}
.ss{height:38px}`
    const content = isLabo ? `
<div class="drug"><div class="dname">🔬 Numération Formule Sanguine (NFS)</div><div class="ddose">Hémogramme complet + différentielle des leucocytes</div><div class="dpos">Tube EDTA violet — pas de jeûne requis</div></div>
<div class="drug"><div class="dname">🧪 Glycémie à jeun</div><div class="ddose">Dosage du glucose plasmatique à jeun</div><div class="dpos">Tube fluorure rouge — jeûne strict 8h minimum</div></div>
<div class="drug"><div class="dname">🧫 CRP quantitative</div><div class="ddose">Protéine C-réactive — marqueur inflammation</div><div class="dpos">Tube sec rouge — résultat sous 4h</div></div>
<div class="drug"><div class="dname">🩺 ECBU — Examen Cyto-Bactériologique des Urines</div><div class="ddose">Urine du milieu de jet — 1er matin</div><div class="dpos">Flacon stérile fourni par le laboratoire</div></div>` : `
<div class="drug"><div class="dname">💊 Amoxicilline 500 mg</div><div class="ddose">1 comprimé × 3 fois/jour — toutes les 8 heures</div><div class="dpos">7 jours · De préférence au repas, avec de l'eau</div></div>
<div class="drug"><div class="dname">💊 Paracétamol 1000 mg</div><div class="ddose">1 comprimé × 3 fois/jour en cas de douleur ou fièvre</div><div class="dpos">5 jours · Max 3 g/jour — intervalle minimum 6h</div></div>
<div class="drug"><div class="dname">💊 Ibuprofène 400 mg</div><div class="ddose">1 comprimé × 2 fois/jour après les repas</div><div class="dpos">5 jours · Éviter à jeun — contre-indiqué si gastrite</div></div>`
    return wrap(css, `<div class="page">
<div class="top">
  <div><div class="tco">Dr. KOUASSI Emmanuel</div><div class="tsub">Médecin Généraliste · Ordre N° CI-MED-4521<br>Clinique Saint-Luc, Cocody, Abidjan</div></div>
  <div class="tdoc">${doc.name}<br>N° 2026-ORD-0781<br>27/07/2026</div>
</div>
<div class="patient-bar">
  <div class="pav">DM</div>
  <div><div class="pname">DIALLO Mariama</div><div class="pinfo">Née : 12/03/1988 · Féminin · Poids : 62 kg · Groupe : A+</div><div class="palert">⚠️ Allergie connue : Pénicilline</div></div>
</div>
<div class="body">
  <div class="rxbig">Rx</div>
  <div class="stitle">${isLabo ? '🔬 Analyses prescrites' : '💊 Médicaments prescrits'}</div>
  <div class="drugs">${content}</div>
  <div class="warn">⚠️ ${isLabo ? '<b>Instructions :</b> Résultats à remettre directement au médecin prescripteur sous 24h. Signaler tout traitement en cours au biologiste.' : '<b>Ne pas dépasser les doses prescrites.</b> En cas de réaction allergique, arrêter immédiatement et consulter aux urgences. Repos 3 jours. Contrôle dans 7 jours si pas d\'amélioration.'}</div>
  <div class="sigs">
    <div class="sig"><div class="ss"></div>Signature & Cachet Médecin<br>Dr. KOUASSI Emmanuel</div>
    <div class="sig" style="background:#F0F9FF;border-color:${c};border-style:solid"><div style="margin:8px 0;font-size:10px;color:${c}">Abidjan, le 27/07/2026<br>Valable 3 mois</div></div>
  </div>
</div></div>`)
  }
  // Facture médicale / feuille de soins
  const css = `
.page{max-width:680px;margin:0 auto}
.top{background:linear-gradient(to right,#164E63,${c});color:#fff;padding:16px 22px;display:flex;justify-content:space-between;align-items:center}
.tco{font-size:13px;font-weight:800}.tsub{font-size:9.5px;opacity:.8;margin-top:2px;line-height:1.6}
.tdoc{text-align:right;font-size:10px;opacity:.9;line-height:1.8}
.patient-card{display:flex;gap:12px;padding:12px 22px;background:#E0F2FE;border-bottom:2px solid ${c};align-items:center}
.pav{width:40px;height:40px;background:${c};border-radius:50%;display:grid;place-items:center;color:#fff;font-size:14px;font-weight:900;flex-shrink:0}
.pname{font-size:13px;font-weight:800;color:#164E63}
.pinfo{font-size:10.5px;color:#0369A1;margin-top:2px;line-height:1.6}
.body{padding:16px 22px}
table{width:100%;border-collapse:collapse;margin-bottom:12px}
thead tr{background:#164E63}
th{color:#fff;padding:8px 10px;text-align:left;font-size:9.5px;font-weight:700;text-transform:uppercase}
th:last-child,td:last-child{text-align:right}
td{padding:8px 10px;border-bottom:1px solid #E0F2FE;font-size:11.5px}
tr:nth-child(even) td{background:#F0F9FF}
.tw{display:flex;justify-content:flex-end;margin-bottom:12px}
.tb{width:260px}
.tl{display:flex;justify-content:space-between;padding:6px 12px;font-size:11.5px;border-bottom:1px solid #E0F2FE;color:#475569}
.tg{display:flex;justify-content:space-between;padding:6px 12px;font-size:11.5px;border-bottom:1px solid #E0F2FE;color:#166534}
.tf{display:flex;justify-content:space-between;padding:10px 12px;font-size:14px;font-weight:900;background:${c};color:#fff;border-radius:0 0 8px 8px}
.sigs{display:flex;gap:12px}
.sig{flex:1;border:1.5px dashed #BAE6FD;border-radius:8px;padding:10px;text-align:center;font-size:10px;color:${c}}
.ss{height:38px}`
  return wrap(css, `<div class="page">
<div class="top">
  <div><div class="tco">CLINIQUE SAINT-LUC</div><div class="tsub">Cocody, Abidjan · Agrément MS-CI-2018-CL-0042<br>+225 27 22 44 55 00 · clinique.saintluc@email.ci</div></div>
  <div class="tdoc">${doc.name}<br>N° FAC-MED-2026-0781<br>27/07/2026</div>
</div>
<div class="patient-card">
  <div class="pav">DM</div>
  <div><div class="pname">DIALLO Mariama</div><div class="pinfo">Née : 12/03/1988 · Dossier N° PAT-2026-0781 · Dr. KOUASSI<br>Assurance NSIA Santé · Contrat N° NSI-2024-07891</div></div>
</div>
<div class="body">
<table>
<thead><tr><th>Acte / Désignation</th><th>Qté</th><th>P.U.</th><th>Montant</th></tr></thead>
<tbody>
<tr><td>Consultation médecin généraliste</td><td>1</td><td>15 000</td><td>15 000 XOF</td></tr>
<tr><td>NFS — Numération Formule Sanguine</td><td>1</td><td>12 500</td><td>12 500 XOF</td></tr>
<tr><td>Radiographie thorax face + profil</td><td>1</td><td>18 000</td><td>18 000 XOF</td></tr>
<tr><td>Perfusion + soluté glucosé 500 ml</td><td>2</td><td>8 500</td><td>17 000 XOF</td></tr>
</tbody>
</table>
<div class="tw"><div class="tb">
  <div class="tl"><span>Sous-total actes</span><span>62 500 XOF</span></div>
  <div class="tg"><span>Prise en charge NSIA (60%)</span><span>-37 500 XOF</span></div>
  <div class="tf"><span>RESTE À CHARGE PATIENT</span><span>25 000 XOF</span></div>
</div></div>
<div class="sigs">
  <div class="sig"><div class="ss"></div>Signature Caissier</div>
  <div class="sig"><div class="ss"></div>Signature Patient — Reçu le ___________</div>
</div>
</div></div>`)
}

// ── TEMPLATE 11 : Éducation — fiche scolarité avec progression paiement
function previewEducation(doc) {
  const c = doc.catColor || '#7C3AED'
  const css = `
.page{max-width:680px;margin:0 auto}
.top{background:linear-gradient(135deg,#1E1B4B 0%,${c} 100%);color:#fff;padding:16px 22px}
.toprow{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:10px}
.tco{font-size:13px;font-weight:800}.tsub{font-size:9.5px;opacity:.8;margin-top:2px;line-height:1.6}
.tdoc{text-align:right;font-size:10px;opacity:.9;line-height:1.8}
.stu-banner{background:rgba(255,255,255,.12);border-radius:8px;padding:10px 14px;display:flex;gap:12px;align-items:center}
.stav{width:38px;height:38px;background:rgba(255,255,255,.2);border-radius:50%;display:grid;place-items:center;font-size:14px;font-weight:900;flex-shrink:0}
.stname{font-size:13px;font-weight:800}
.stinfo{font-size:9.5px;opacity:.8;margin-top:2px;line-height:1.6}
.body{padding:16px 22px}
.infogrid{display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-bottom:16px}
.icard{background:#F5F3FF;border-radius:8px;padding:11px;text-align:center;border-top:3px solid ${c}}
.ilbl{font-size:8.5px;text-transform:uppercase;letter-spacing:.1em;color:#6D28D9;font-weight:700;margin-bottom:3px}
.ival{font-size:13px;font-weight:900;color:#1E1B4B}
.isub{font-size:9.5px;color:#64748B;margin-top:2px}
.progress-container{background:#EDE9FE;border-radius:8px;padding:12px 14px;margin-bottom:16px}
.prog-top{display:flex;justify-content:space-between;align-items:center;margin-bottom:8px}
.prog-lbl{font-size:10px;font-weight:700;color:#4C1D95}
.prog-pct{font-size:16px;font-weight:900;color:${c}}
.prog-bar{background:#C4B5FD;border-radius:6px;height:10px}
.prog-fill{background:${c};height:10px;border-radius:6px;width:67%}
.stitle{font-size:9.5px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:${c};margin:14px 0 8px}
table{width:100%;border-collapse:collapse;margin-bottom:12px}
thead tr{background:#1E1B4B}
th{color:#fff;padding:8px 10px;text-align:left;font-size:9.5px;font-weight:700;text-transform:uppercase}
th:last-child,td:last-child{text-align:right}
td{padding:9px 10px;border-bottom:1px solid #EDE9FE;font-size:11.5px}
.badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:9.5px;font-weight:700}
.paid{background:#DCFCE7;color:#166534}
.due{background:#FEF9C3;color:#854D0E}
.overdue{background:#FEE2E2;color:#991B1B}
.sigs{display:flex;gap:12px}
.sig{flex:1;border:1.5px dashed #C4B5FD;border-radius:8px;padding:10px;text-align:center;font-size:10px;color:${c}}
.ss{height:38px}`
  return wrap(css, `<div class="page">
<div class="top">
  <div class="toprow">
    <div><div class="tco">ÉCOLE SUPÉRIEURE DE COMMERCE D'ABIDJAN</div><div class="tsub">Cocody, Abidjan · Agrément MEN N° 2015-0042<br>+225 27 22 55 66 77 · esca@education.ci</div></div>
    <div class="tdoc">${doc.name}<br>N° SCO-2026-1842<br>27/07/2026</div>
  </div>
  <div class="stu-banner">
    <div class="stav">TI</div>
    <div><div class="stname">TRAORÉ Ibrahim</div><div class="stinfo">Matricule : ETU-2025-1842 · BTS Commerce International — Niveau L2<br>Année scolaire : 2025 / 2026 · 3ème trimestre</div></div>
  </div>
</div>
<div class="body">
<div class="infogrid">
  <div class="icard"><div class="ilbl">Frais annuels</div><div class="ival">850 000</div><div class="isub">XOF / an</div></div>
  <div class="icard"><div class="ilbl">Versé à ce jour</div><div class="ival">566 666</div><div class="isub">XOF (2/3)</div></div>
  <div class="icard"><div class="ilbl">Solde restant</div><div class="ival" style="color:#C2410C">283 334</div><div class="isub">XOF dû</div></div>
</div>
<div class="progress-container">
  <div class="prog-top"><span class="prog-lbl">🎓 Progression du paiement — Année 2025/2026</span><span class="prog-pct">67%</span></div>
  <div class="prog-bar"><div class="prog-fill"></div></div>
</div>
<div class="stitle">Détail du règlement par tranche</div>
<table>
<thead><tr><th>Tranche</th><th>Échéance</th><th>Montant</th><th>Mode paiement</th><th>Statut</th></tr></thead>
<tbody>
<tr><td>1ère tranche</td><td>01/10/2025</td><td>283 333 XOF</td><td>Virement bancaire</td><td><span class="badge paid">✅ Payée</span></td></tr>
<tr><td>2ème tranche</td><td>10/01/2026</td><td>283 333 XOF</td><td>Orange Money</td><td><span class="badge paid">✅ Payée</span></td></tr>
<tr><td>3ème tranche</td><td>10/04/2026</td><td>283 334 XOF</td><td>—</td><td><span class="badge due">⏳ En attente</span></td></tr>
</tbody>
</table>
<div class="sigs">
  <div class="sig"><div class="ss"></div>Signature Service Scolarité</div>
  <div class="sig"><div class="ss"></div>Signature Parent / Étudiant</div>
</div>
</div></div>`)
}

// ── TEMPLATE 12 : Finance/Caisse — bon centré avec montant prominent
function previewFinance(doc) {
  const c = doc.catColor || '#0284C7'
  const isEntree = ['bord_rem','depot_banc','note_frais'].includes(doc.id)
  const css = `
.page{max-width:600px;margin:0 auto;border:2px solid #E2E8F0;border-radius:12px;overflow:hidden}
.top{background:linear-gradient(to right,#0C4A6E,${c});color:#fff;padding:14px 22px;display:flex;justify-content:space-between;align-items:center}
.tco{font-size:12.5px;font-weight:800}.tsub{font-size:9.5px;opacity:.8;margin-top:2px}
.tdoc{text-align:right;font-size:10px;opacity:.9;line-height:1.8}
.parties{display:flex;gap:0;border-bottom:1px solid #E2E8F0}
.party{flex:1;padding:12px 18px}
.party:first-child{border-right:1px solid #E2E8F0}
.plbl{font-size:8.5px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#94A3B8;margin-bottom:4px}
.pname{font-size:12.5px;font-weight:800;color:#0C172A}
.pinfo{font-size:10.5px;color:#64748B;margin-top:2px;line-height:1.55}
.amount-box{text-align:center;padding:24px 22px;background:linear-gradient(135deg,${c}0D,${c}1A);border-bottom:1px solid ${c}30}
.adir{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:${c};margin-bottom:8px}
.aval{font-size:38px;font-weight:900;color:${c};letter-spacing:.01em}
.awords{font-size:11px;color:#64748B;margin-top:6px;font-style:italic}
.amode{display:inline-flex;align-items:center;gap:5px;margin-top:8px;background:#fff;border:1.5px solid ${c}40;border-radius:20px;padding:4px 14px;font-size:10.5px;font-weight:700;color:${c}}
.details{padding:14px 22px;border-bottom:1px solid #F1F5F9}
.drow{display:flex;justify-content:space-between;padding:5px 0;border-bottom:1px solid #F8FAFC;font-size:11.5px}
.dlbl{color:#94A3B8}
.dval{font-weight:600;color:#0F172A}
.sigs{display:flex;gap:0}
.sig{flex:1;padding:14px 18px;text-align:center;font-size:10px;color:#94A3B8}
.sig:first-child{border-right:1px dashed #E2E8F0}
.ss{height:36px}
.solde{background:#F0FDF4;border-top:1px solid #BBF7D0;padding:10px 22px;display:flex;justify-content:space-between;align-items:center}
.soldel{font-size:10px;color:#166534;font-weight:700}
.soldev{font-size:14px;font-weight:900;color:#15803D}`
  return wrap(css, `<div class="page">
<div class="top">
  <div><div class="tco">VOTRE SOCIÉTÉ SARL</div><div class="tsub">Service Finances & Trésorerie — Plateau, Abidjan</div></div>
  <div class="tdoc">${doc.name}<br>N° CAI-2026-0847<br>27/07/2026 — 10h45</div>
</div>
<div class="parties">
  <div class="party"><div class="plbl">💼 Caissier / Émetteur</div><div class="pname">BAMBA Adjoua</div><div class="pinfo">Responsable Caisse Principale<br>VOTRE SOCIÉTÉ SARL</div></div>
  <div class="party"><div class="plbl">👤 Bénéficiaire</div><div class="pname">KONÉ Aminata</div><div class="pinfo">Responsable Commerciale<br>Mat. EMP-2024-0042</div></div>
</div>
<div class="amount-box">
  <div class="adir">${isEntree ? '⬆️ Entrée caisse' : '⬇️ Sortie caisse'} — ${doc.name}</div>
  <div class="aval">150 000 XOF</div>
  <div class="awords">Cent cinquante mille francs CFA exactement</div>
  <div class="amode">💵 Espèces</div>
</div>
<div class="details">
  <div class="drow"><span class="dlbl">Motif du mouvement</span><span class="dval">Avance sur frais de mission — Bouaké</span></div>
  <div class="drow"><span class="dlbl">Imputation comptable</span><span class="dval">CC-COMM-01 / Compte 627</span></div>
  <div class="drow"><span class="dlbl">Réf. ordre de mission</span><span class="dval">OM-2026-0021</span></div>
  <div class="drow"><span class="dlbl">Pièce justificative</span><span class="dval">Ordre de mission signé DG</span></div>
</div>
<div class="solde"><span class="soldel">Solde caisse avant opération</span><span class="soldev">1 240 000 XOF → après : 1 090 000 XOF</span></div>
<div class="sigs">
  <div class="sig"><div class="ss"></div>Signature Caissier<br>BAMBA Adjoua</div>
  <div class="sig"><div class="ss"></div>Signature Bénéficiaire<br>Reçu le ___________</div>
</div>
</div>`)
}

// ── TEMPLATE 13 : Avoir / Note de crédit — fond rouge/vert distinct
function previewAvoir(doc) {
  const c = '#DC2626'
  const css = `
.page{max-width:680px;margin:0 auto}
.top{background:linear-gradient(to right,#7F1D1D,#DC2626);color:#fff;padding:16px 22px;display:flex;justify-content:space-between;align-items:center}
.tco{font-size:13px;font-weight:800}.tsub{font-size:9.5px;opacity:.8;margin-top:2px;line-height:1.6}
.tdoc{text-align:right;font-size:10px;opacity:.9;line-height:1.8}
.ref-bar{background:#FEF2F2;border-left:4px solid #DC2626;padding:11px 22px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #FCA5A5}
.rblbl{font-size:9px;text-transform:uppercase;letter-spacing:.08em;color:#991B1B;font-weight:700;margin-bottom:3px}
.rbval{font-size:12px;font-weight:800;color:#7F1D1D}
.parties{display:grid;grid-template-columns:1fr 1fr;gap:10px;padding:14px 22px;border-bottom:1px solid #FEE2E2}
.party{background:#FFF1F2;border-radius:8px;padding:11px;border-left:3px solid #DC2626}
.plbl{font-size:8.5px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#94A3B8;margin-bottom:3px}
.pname{font-size:12.5px;font-weight:800;color:#1E293B}
.pinfo{font-size:10.5px;color:#64748B;margin-top:2px;line-height:1.55}
.body{padding:14px 22px}
.motif{background:#FFF1F2;border-left:3px solid #DC2626;border-radius:6px;padding:10px 14px;font-size:11.5px;color:#7F1D1D;line-height:1.75;margin-bottom:14px}
table{width:100%;border-collapse:collapse;margin-bottom:12px}
thead tr{background:#7F1D1D}
th{color:#FCA5A5;padding:8px 10px;text-align:left;font-size:9px;font-weight:700;text-transform:uppercase}
th:last-child,td:last-child{text-align:right}
td{padding:8px 10px;border-bottom:1px solid #FEE2E2;font-size:11.5px}
td.credit{color:#166534;font-weight:800;text-align:right}
.tw{display:flex;justify-content:flex-end;margin-bottom:10px}
.tb{width:270px}
.tl{display:flex;justify-content:space-between;padding:6px 12px;font-size:11.5px;border-bottom:1px solid #FEE2E2;color:#475569}
.tg{display:flex;justify-content:space-between;padding:6px 12px;font-size:11.5px;border-bottom:1px solid #FEE2E2;color:#166534}
.tf{display:flex;justify-content:space-between;padding:10px 12px;font-size:14px;font-weight:900;background:#15803D;color:#fff;border-radius:0 0 8px 8px}
.note{background:#F0FDF4;border-left:3px solid #16A34A;border-radius:6px;padding:10px 14px;font-size:11px;color:#166534;margin-bottom:12px}
.sigs{display:flex;gap:12px}
.sig{flex:1;border:1.5px dashed #FCA5A5;border-radius:8px;padding:10px;text-align:center;font-size:10px;color:#DC2626}
.ss{height:38px}`
  return wrap(css, `<div class="page">
<div class="top">
  <div><div class="tco">VOTRE SOCIÉTÉ SARL</div><div class="tsub">Service Commercial · Plateau, Abidjan<br>RCCM CI-ABJ-2024-B-12345 · NIF 2405812 A</div></div>
  <div class="tdoc">↩️ ${doc.name}<br>N° AV-2026-0012<br>27/07/2026</div>
</div>
<div class="ref-bar">
  <div><div class="rblbl">Document d'avoir — en référence à :</div><div class="rbval">Facture N° FAC-2026-0038 du 15/07/2026 — Montant initial TTC : 1 200 000 XOF</div></div>
  <div style="font-size:26px">↩️</div>
</div>
<div class="parties">
  <div class="party"><div class="plbl">Émetteur de l'avoir</div><div class="pname">VOTRE SOCIÉTÉ SARL</div><div class="pinfo">Plateau, Abidjan<br>RCCM CI-ABJ-2024-B-12345</div></div>
  <div class="party"><div class="plbl">Bénéficiaire du crédit</div><div class="pname">CLIENT EXEMPLE & ASSOCIÉS</div><div class="pinfo">Cocody Riviera 3, Abidjan<br>+225 05 00 11 22 33</div></div>
</div>
<div class="body">
<div class="motif">📋 <b>Motif :</b> Retour partiel de marchandises défectueuses — lot N° LOT-2026-041. 3 unités sur 10 retournées après constat contradictoire en date du 20/07/2026. Défaut : condensateur hors-norme.</div>
<table>
<thead><tr><th>Désignation article retourné</th><th>Réf.</th><th>Qté retournée</th><th>P.U. HT</th><th>Crédit HT</th></tr></thead>
<tbody>
<tr><td>Climatiseur split 1,5 CV</td><td>AC-150</td><td>3</td><td>185 000</td><td class="credit">- 555 000 XOF</td></tr>
<tr><td>Frais de transport retour</td><td>—</td><td>1 fft</td><td>15 000</td><td class="credit">- 15 000 XOF</td></tr>
</tbody>
</table>
<div class="tw"><div class="tb">
  <div class="tl"><span>Sous-total HT</span><span class="tg" style="color:#166534;padding:0">- 570 000 XOF</span></div>
  <div class="tl"><span>TVA 18 %</span><span style="color:#166534">- 102 600 XOF</span></div>
  <div class="tf"><span>TOTAL AVOIR TTC</span><span>- 672 600 XOF</span></div>
</div></div>
<div class="note">✅ Ce montant de 672 600 XOF sera déduit de votre prochaine facture ou remboursé par virement bancaire sous 5 jours ouvrés.</div>
<div class="sigs">
  <div class="sig"><div class="ss"></div>Visa Responsable Commercial</div>
  <div class="sig"><div class="ss"></div>Accusé de réception Client</div>
</div>
</div></div>`)
}

// ── TEMPLATE 14 : Bon de Commande — avec dates livraison et statut
function previewCommande(doc) {
  const c = doc.catColor || '#7C3AED'
  const isFourn = doc.catId === 'achat'
  const css = `
.page{max-width:680px;margin:0 auto}
.top{background:#1E293B;color:#fff;padding:0}
.toprow{display:flex;justify-content:space-between;align-items:center;padding:14px 22px;border-bottom:1px solid #334155}
.tco{font-size:13px;font-weight:800;color:#F1F5F9}
.tsub{font-size:9.5px;color:#94A3B8;margin-top:2px;line-height:1.6}
.tdoc{text-align:right}
.ttype{font-size:15px;font-weight:900;text-transform:uppercase;color:${c};letter-spacing:.04em}
.tref{font-size:10px;color:#94A3B8;margin-top:4px;line-height:1.8}
.official-bar{background:${c};color:#fff;padding:8px 22px;font-size:10.5px;font-weight:700;letter-spacing:.04em;text-transform:uppercase}
.body{padding:14px 22px}
.parties{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:14px}
.party{background:#F8FAFC;border-radius:8px;padding:11px;border-top:3px solid ${c}}
.plbl{font-size:8.5px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#94A3B8;margin-bottom:3px}
.pname{font-size:12.5px;font-weight:800;color:#0F172A}
.pinfo{font-size:10.5px;color:#64748B;margin-top:2px;line-height:1.55}
.meta{display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:8px;margin-bottom:14px}
.mc{background:#F8FAFC;border-radius:8px;padding:9px;text-align:center}
.mlbl{font-size:8px;text-transform:uppercase;letter-spacing:.08em;color:#94A3B8;font-weight:700;margin-bottom:2px}
.mval{font-size:11px;font-weight:700;color:#0F172A}
table{width:100%;border-collapse:collapse;margin-bottom:12px}
thead tr{background:#1E293B}
th{color:#94A3B8;padding:8px 10px;text-align:left;font-size:9px;font-weight:700;text-transform:uppercase}
th:last-child,td:last-child{text-align:right}
td{padding:8px 10px;border-bottom:1px solid #F1F5F9;font-size:11.5px}
tr:nth-child(even) td{background:#F8FAFC}
.tw{display:flex;justify-content:flex-end;margin-bottom:12px}
.tb{width:264px}
.tl{display:flex;justify-content:space-between;padding:6px 12px;font-size:11.5px;border-bottom:1px solid #F1F5F9;color:#475569}
.tf{display:flex;justify-content:space-between;padding:10px 12px;font-size:14px;font-weight:900;background:${c};color:#fff;border-radius:0 0 8px 8px}
.cond{background:#F5F3FF;border-left:3px solid ${c};border-radius:6px;padding:10px 14px;font-size:11px;color:#4C1D95;margin-bottom:12px;line-height:1.7}
.sigs{display:flex;gap:12px}
.sig{flex:1;border:1.5px dashed #DDD6FE;border-radius:8px;padding:10px;text-align:center;font-size:10px;color:${c}}
.ss{height:38px}`
  return wrap(css, `<div class="page">
<div class="top">
  <div class="toprow">
    <div><div class="tco">VOTRE SOCIÉTÉ SARL</div><div class="tsub">Service Achats · Plateau, Abidjan · +225 27 22 33 44 55</div></div>
    <div class="tdoc"><div class="ttype">${doc.name}</div><div class="tref">N° BC-2026-0089<br>Date : 27/07/2026</div></div>
  </div>
  <div class="official-bar">📋 Commande officielle — Veuillez préparer et livrer les articles ci-dessous selon les conditions indiquées</div>
</div>
<div class="body">
<div class="parties">
  <div class="party"><div class="plbl">🏢 ${isFourn ? 'Acheteur' : 'Client'}</div><div class="pname">VOTRE SOCIÉTÉ SARL</div><div class="pinfo">Service Achats · Plateau, Abidjan<br>Contact : Mme BAMBA — 07 11 22 33 00</div></div>
  <div class="party"><div class="plbl">🏪 ${isFourn ? 'Fournisseur' : 'Prestataire'}</div><div class="pname">${isFourn ? 'MATÉRIAUX DU GOLF SARL' : 'CLIENT EXEMPLE & ASSOCIÉS'}</div><div class="pinfo">${isFourn ? 'Zone Industrielle, Abidjan<br>+225 27 22 88 99 00 · RCCM CI-ABJ-2019-B-8821' : 'Cocody Riviera 3 · +225 05 00 11 22 33'}</div></div>
</div>
<div class="meta">
  <div class="mc"><div class="mlbl">N° BC</div><div class="mval">BC-2026-0089</div></div>
  <div class="mc"><div class="mlbl">Date commande</div><div class="mval">27/07/2026</div></div>
  <div class="mc"><div class="mlbl">Livraison souhaitée</div><div class="mval">03/08/2026 12h</div></div>
  <div class="mc"><div class="mlbl">Lieu livraison</div><div class="mval">Entrepôt Yopougon</div></div>
</div>
<table>
<thead><tr><th>#</th><th>Désignation article</th><th>Qté</th><th>Unité</th><th>P.U. HT</th><th>Total HT</th></tr></thead>
<tbody>
<tr><td>01</td><td>Ciment Portland CPA 42.5</td><td>100</td><td>Sac 50 kg</td><td>9 500</td><td>950 000 XOF</td></tr>
<tr><td>02</td><td>Sable fin de rivière lavé</td><td>15</td><td>m³</td><td>22 000</td><td>330 000 XOF</td></tr>
<tr><td>03</td><td>Fers à béton Ø12 — 6 m</td><td>50</td><td>Barre</td><td>8 200</td><td>410 000 XOF</td></tr>
</tbody>
</table>
<div class="tw"><div class="tb">
  <div class="tl"><span>Sous-total HT</span><span>1 690 000 XOF</span></div>
  <div class="tl" style="background:#F5F3FF"><span>TVA 18 %</span><span>304 200 XOF</span></div>
  <div class="tf"><span>TOTAL TTC</span><span>1 994 200 XOF</span></div>
</div></div>
<div class="cond">📦 <b>Conditions :</b> Livraison franco entrepôt Yopougon · Bon de livraison exigé · Facture en 2 exemplaires · Paiement à 30 jours</div>
<div class="sigs">
  <div class="sig"><div class="ss"></div>Visa Responsable Achats<br>VOTRE SOCIÉTÉ SARL</div>
  <div class="sig"><div class="ss"></div>Confirmation Fournisseur<br>Accusé de réception daté</div>
</div>
</div></div>`)
}

// ── TEMPLATE 15 : Demande — formulaire avec circuit visuel d'approbation
function previewDemande(doc) {
  const c = doc.catColor || '#0284C7'
  const css = `
.page{max-width:680px;margin:0 auto}
.top{background:#0F172A;color:#fff;padding:14px 22px;display:flex;justify-content:space-between;align-items:center}
.tco{font-size:13px;font-weight:800;color:#F1F5F9}
.tsub{font-size:9.5px;color:#64748B;margin-top:2px}
.tdoc{text-align:right}
.ttype{font-size:14px;font-weight:900;text-transform:uppercase;color:${c};letter-spacing:.04em}
.tref{font-size:10px;color:#94A3B8;margin-top:4px;line-height:1.8}
.form-body{padding:16px 22px}
.req-card{display:flex;gap:12px;background:#F0F9FF;border:1.5px solid ${c}50;border-radius:10px;padding:12px 16px;margin-bottom:16px;align-items:center}
.reav{width:38px;height:38px;background:${c};border-radius:50%;display:grid;place-items:center;color:#fff;font-size:14px;font-weight:900;flex-shrink:0}
.rname{font-size:13px;font-weight:800;color:#0C4A6E}
.rinfo{font-size:10.5px;color:#0369A1;margin-top:2px;line-height:1.6}
.grid4{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:14px}
.field{background:#F8FAFC;border-radius:8px;padding:10px 12px;border-left:3px solid ${c}60}
.flbl{font-size:8.5px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#94A3B8;margin-bottom:3px}
.fval{font-size:12px;font-weight:600;color:#0F172A}
.objet{background:#EFF6FF;border-left:3px solid ${c};border-radius:8px;padding:12px 14px;margin-bottom:14px}
.obtitle{font-size:9.5px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:${c};margin-bottom:5px}
.obtext{font-size:11.5px;color:#1E40AF;line-height:1.75}
.justif{background:#F8FAFC;border-radius:8px;padding:12px 14px;margin-bottom:14px;font-size:11.5px;color:#374151;line-height:1.75}
.circuit-title{font-size:9.5px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:${c};margin:14px 0 10px}
.circuit{display:flex;align-items:flex-start;gap:0;margin-bottom:14px}
.step-wrap{flex:1;text-align:center}
.step-dot{width:32px;height:32px;border-radius:50%;display:grid;place-items:center;font-size:12px;font-weight:900;margin:0 auto 6px}
.ok{background:#DCFCE7;color:#166534;border:2px solid #16A34A}
.wait{background:#FEF9C3;color:#854D0E;border:2px solid #CA8A04}
.pend{background:#F1F5F9;color:#64748B;border:2px solid #CBD5E1}
.step-name{font-size:10px;font-weight:700;color:#0F172A}
.step-who{font-size:9.5px;color:#64748B;margin-top:1px}
.step-date{font-size:9px;color:#94A3B8;margin-top:1px}
.connector{flex:0 0 28px;height:2px;background:#E2E8F0;margin-top:16px}
.sigs{display:flex;gap:12px}
.sig{flex:1;border:1.5px dashed #BAE6FD;border-radius:8px;padding:10px;text-align:center;font-size:10px;color:${c}}
.ss{height:38px}`
  return wrap(css, `<div class="page">
<div class="top">
  <div><div class="tco">VOTRE SOCIÉTÉ SARL</div><div class="tsub">Processus interne — Formulaire de demande</div></div>
  <div class="tdoc"><div class="ttype">${doc.name}</div><div class="tref">N° DEM-2026-0153<br>27/07/2026 — Urgence : 🟡 Normale</div></div>
</div>
<div class="form-body">
<div class="req-card">
  <div class="reav">KA</div>
  <div><div class="rname">KONÉ Aminata</div><div class="rinfo">Responsable Commerciale · Dép. Commercial & Marketing · Mat. EMP-2024-0042</div></div>
</div>
<div class="grid4">
  <div class="field"><div class="flbl">Type de demande</div><div class="fval">${doc.name}</div></div>
  <div class="field"><div class="flbl">Date demande</div><div class="fval">27/07/2026</div></div>
  <div class="field"><div class="flbl">Budget estimé</div><div class="fval">450 000 XOF</div></div>
  <div class="field"><div class="flbl">Centre de coût</div><div class="fval">CC-COMM-01</div></div>
</div>
<div class="objet"><div class="obtitle">📋 Objet de la demande</div><div class="obtext">${doc.name} — ${doc.desc}. Délai souhaité : avant le 31/07/2026.</div></div>
<div class="justif"><b>Justification :</b> Acquisition nécessaire pour assurer la continuité des opérations commerciales du département. Sans cette action, le projet CLIENT-2026-042 sera retardé de 2 semaines avec risque de pénalité contractuelle de 150 000 XOF.</div>
<div class="circuit-title">🔄 Circuit d'approbation</div>
<div class="circuit">
  <div class="step-wrap"><div class="step-dot ok">✓</div><div class="step-name">N+1 Manager</div><div class="step-who">DIALLO Moussa</div><div class="step-date">✅ 27/07/2026</div></div>
  <div class="connector"></div>
  <div class="step-wrap"><div class="step-dot wait">⏳</div><div class="step-name">Direction Générale</div><div class="step-who">M. KOUASSI DG</div><div class="step-date">En attente</div></div>
  <div class="connector"></div>
  <div class="step-wrap"><div class="step-dot pend">○</div><div class="step-name">Service Finance</div><div class="step-who">Mme BAMBA CFO</div><div class="step-date">Pending</div></div>
</div>
<div class="sigs">
  <div class="sig"><div class="ss"></div>Signature Demandeur<br>KONÉ Aminata</div>
  <div class="sig"><div class="ss"></div>Visa Direction Générale</div>
</div>
</div></div>`)
}

// ── TEMPLATE 16 : Reçu — ticket centré avec montant large
function previewRecu(doc) {
  const c = doc.catColor || '#2563EB'
  const css = `
.page{max-width:480px;margin:0 auto;border:2px solid #E2E8F0;border-radius:12px;overflow:hidden}
.top{background:linear-gradient(135deg,#0F172A,${c});color:#fff;padding:14px 20px;text-align:center}
.tco{font-size:13px;font-weight:800}
.tsub{font-size:9.5px;opacity:.8;margin-top:2px;line-height:1.6}
.tdoc{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;margin-top:6px;color:${c === '#2563EB' ? '#93C5FD' : '#fff'};opacity:.9}
.center-block{padding:22px 20px;text-align:center;background:#FAFAFA;border-bottom:2px dashed #E2E8F0}
.bigcheck{font-size:44px;margin-bottom:8px}
.plbl{font-size:9.5px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#94A3B8;margin-bottom:8px}
.bigamt{font-size:40px;font-weight:900;color:${c};letter-spacing:.01em;margin:6px 0}
.amtwords{font-size:10.5px;color:#64748B;font-style:italic}
.mode{display:inline-flex;align-items:center;gap:5px;margin-top:10px;background:#fff;border:1.5px solid ${c}40;border-radius:20px;padding:5px 14px;font-size:10.5px;font-weight:700;color:${c}}
.details{padding:14px 20px;border-bottom:1px solid #F1F5F9}
.drow{display:flex;justify-content:space-between;padding:5px 0;border-bottom:1px solid #F8FAFC;font-size:11px}
.dlbl{color:#94A3B8}
.dval{font-weight:600;color:#0F172A;text-align:right;max-width:55%}
.solde{background:#F0FDF4;padding:10px 20px;display:flex;align-items:center;justify-content:center;gap:8px;font-size:12px;font-weight:800;color:#15803D;border-bottom:1px solid #BBF7D0}
.sig-area{padding:14px 20px;text-align:center;font-size:10px;color:#94A3B8}
.sig-line{border-top:1px dashed #CBD5E1;margin:8px auto;width:60%;padding-top:6px}`
  return wrap(css, `<div class="page">
<div class="top">
  <div class="tco">VOTRE SOCIÉTÉ SARL</div>
  <div class="tsub">Plateau, Abidjan · RCCM CI-ABJ-2024-B-12345</div>
  <div class="tdoc">${doc.name}</div>
</div>
<div class="center-block">
  <div class="bigcheck">✅</div>
  <div class="plbl">Paiement reçu avec succès</div>
  <div class="bigamt">750 000 XOF</div>
  <div class="amtwords">Sept cent cinquante mille francs CFA exactement</div>
  <div class="mode">🏦 Virement bancaire BICICI</div>
</div>
<div class="details">
  <div class="drow"><span class="dlbl">Reçu de</span><span class="dval">CLIENT EXEMPLE & ASSOCIÉS</span></div>
  <div class="drow"><span class="dlbl">Date de paiement</span><span class="dval">27/07/2026 — 14h30</span></div>
  <div class="drow"><span class="dlbl">Réf. virement</span><span class="dval">VIR-BICICI-20260727-004</span></div>
  <div class="drow"><span class="dlbl">En règlement de</span><span class="dval">Facture FAC-2026-0041</span></div>
  <div class="drow"><span class="dlbl">N° reçu</span><span class="dval">REC-2026-0142</span></div>
</div>
<div class="solde">✅ SOLDE FACTURE : 0 XOF — COMPTE SOLDÉ</div>
<div class="sig-area">
  <div class="sig-line">Cachet & Signature VOTRE SOCIÉTÉ SARL</div>
</div>
</div>`)
}

// ── TEMPLATE 17 : Lettre formelle / relance / mise en demeure
function previewLettre(doc) {
  const c = doc.catColor || '#4F46E5'
  const isMed = doc.id === 'mise_dem'
  const css = `
.page{max-width:680px;margin:0 auto;border:1px solid #E2E8F0}
.letterhead{display:flex;justify-content:space-between;align-items:flex-start;padding:20px 28px;border-bottom:3px solid #0F172A}
.lco{font-size:13.5px;font-weight:900;color:#0F172A}
.lsub{font-size:10px;color:#64748B;margin-top:3px;line-height:1.65}
.lref{text-align:right;font-size:10px;color:#64748B;line-height:1.8}
.parties{display:flex;justify-content:space-between;padding:14px 28px;border-bottom:1px solid #F1F5F9}
.from-block{font-size:11px;color:#475569;line-height:1.7}
.to-block{background:#F8FAFC;border-radius:8px;padding:12px 14px;font-size:11px;line-height:1.7;max-width:220px}
.to-lbl{font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#94A3B8;margin-bottom:5px}
.to-name{font-size:12.5px;font-weight:800;color:#0F172A}
.to-info{color:#64748B;margin-top:3px}
${isMed ? `.med-badge{background:#FEF2F2;border:2px solid #DC2626;border-radius:6px;padding:8px 18px;margin:12px 28px;display:flex;align-items:center;gap:8px;font-size:11px;font-weight:800;color:#991B1B}` : ''}
.object-line{padding:12px 28px;background:#F8FAFC;border-bottom:1px solid #E2E8F0}
.obj-lbl{font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#94A3B8;margin-bottom:3px}
.obj-val{font-size:12.5px;font-weight:800;color:${isMed ? '#DC2626' : c}}
.letter-body{padding:18px 28px;font-size:12px;color:#374151;line-height:1.85;border-bottom:1px solid #F1F5F9}
.amount-highlight{display:inline;background:#FEF3C7;border-radius:3px;padding:1px 4px;font-weight:800;color:#92400E}
.deadline{display:inline-flex;align-items:center;gap:4px;background:#FEF2F2;border-radius:4px;padding:2px 8px;font-weight:800;color:#DC2626}
.sig-area{padding:18px 28px;display:flex;justify-content:flex-end}
.sig-block{text-align:center;min-width:200px}
.ss{height:50px}
.sig-name{font-size:11px;font-weight:700;color:#0F172A}
.sig-title{font-size:10px;color:#64748B;margin-top:2px}`
  const bodyContent = isMed
    ? `Monsieur le Gérant,<br><br>Nous avons l'honneur de vous informer que, malgré nos relances des <b>15 juin</b> et <b>30 juin 2026</b>, votre facture N° <b>FAC-2026-0021</b> d'un montant de <span class="amount-highlight">985 000 XOF TTC</span>, arrivée à échéance le <b>01/07/2026</b>, n'a toujours pas été réglée à ce jour.<br><br>En conséquence, et conformément aux dispositions légales en vigueur en République de Côte d'Ivoire, nous vous mettons formellement en demeure de procéder au règlement intégral de cette somme dans un délai de <span class="deadline">⏰ 8 jours calendaires</span> à compter de la réception du présent courrier, envoyé par recommandé avec accusé de réception.<br><br>À défaut de règlement dans ce délai, nous nous réservons expressément le droit d'engager toute procédure judiciaire à votre encontre, notamment devant le Tribunal de Commerce d'Abidjan, les frais et dépens restant entièrement à votre charge.<br><br>Nous espérons que cette démarche ne sera pas nécessaire et vous invitons à régulariser votre situation dans les meilleurs délais.<br><br>Veuillez agréer, Monsieur le Gérant, l'expression de nos salutations distinguées.`
    : `Monsieur le Gérant,<br><br>Sauf erreur ou omission de notre part, nous constatons que notre facture N° <b>FAC-2026-0021</b> d'un montant de <span class="amount-highlight">985 000 XOF TTC</span>, arrivée à échéance le <b>01/07/2026</b>, demeure impayée à ce jour.<br><br>Nous vous rappelons que tout retard de paiement au-delà des délais contractuels peut entraîner des intérêts moratoires au taux légal en vigueur.<br><br>Nous vous serions reconnaissants de bien vouloir procéder au règlement de cette somme dans les meilleurs délais, ou de nous contacter si vous rencontrez une difficulté particulière, afin que nous puissions trouver ensemble une solution amiable.<br><br>Veuillez agréer, Monsieur le Gérant, l'expression de nos salutations distinguées.`
  return wrap(css, `<div class="page">
<div class="letterhead">
  <div><div class="lco">VOTRE SOCIÉTÉ SARL</div><div class="lsub">Plateau, Abidjan 01 · Côte d'Ivoire<br>RCCM CI-ABJ-2024-B-12345 · NIF 2405812 A<br>+225 27 22 33 44 55 · contact@votresociete.ci</div></div>
  <div class="lref">Abidjan, le 27 juillet 2026<br>Réf. : COURR-2026-0042<br>${isMed ? 'Envoi : Recommandé + AR' : 'Envoi : Ordinaire'}</div>
</div>
${isMed ? '<div class="med-badge">⚠️ MISE EN DEMEURE — Recommandé avec Accusé de Réception</div>' : ''}
<div class="parties">
  <div class="from-block"><b>De :</b> VOTRE SOCIÉTÉ SARL<br>Direction Commerciale<br>M. KOUASSI Emmanuel, DG</div>
  <div class="to-block"><div class="to-lbl">À l'attention de :</div><div class="to-name">M. TRAORÉ Issouf</div><div class="to-info">Gérant — TRAORÉ & FILS SARL<br>Adjamé, Abidjan · CI<br>RCCM CI-ABJ-2018-B-6621</div></div>
</div>
<div class="object-line">
  <div class="obj-lbl">Objet</div>
  <div class="obj-val">${doc.name} — Facture N° FAC-2026-0021 du 01/06/2026</div>
</div>
<div class="letter-body">${bodyContent}</div>
<div class="sig-area">
  <div class="sig-block">
    <div class="ss"></div>
    <div class="sig-name">M. KOUASSI Emmanuel</div>
    <div class="sig-title">Directeur Général — VOTRE SOCIÉTÉ SARL</div>
  </div>
</div>
</div>`)
}

// ── TEMPLATE : Rapport / Document officiel — adaptatif par secteur (249 docs)
function previewServiceReport(doc) {
  const c = doc.catColor || '#7C3AED'

  // Contenu specifique par secteur
  const sectorContent = {
    garage:  { who: 'Chef mecanicien — KONE Brice', objet: 'Vehicule Toyota Hilux · Immat. 4587 AB 01', champs: [['Kilometrage','87 420 km'],['Prochaine revision','100 000 km'],['Etat general','Bon — Reserve freins']], checks: [['ok','Vidange et filtres effectues'],['ok','Freins avant remplaces'],['warn','Pneus arriere a surveiller'],['info','Prochain CT dans 3 mois']], obs: 'Vehicule en bon etat general apres intervention. Surveillance pneus arriere recommandee dans 5 000 km.' },
    it:      { who: 'Ingenieur systeme — ASSI Franck', objet: 'Parc informatique SOCIETE EXEMPLE SA', champs: [['Nb. postes',  '24 stations'],['Serveur','Dell PowerEdge R740'],['OS','Windows Server 2022']], checks: [['ok','Mises a jour securite appliquees'],['ok','Sauvegarde verifiee (99,9%)'],['warn','Antivirus — 2 licences a renouveler'],['info','Migration cloud planifiee Q4 2026']], obs: 'Infrastructure stable. Renouvellement de 2 licences antivirus a prevoir avant fin septembre 2026.' },
    agri:    { who: 'Agronome — Dr. COULIBALY Mamadou', objet: 'Parcelle P-042 · Secteur Yamoussoukro', champs: [['Superficie','12,5 ha'],['Culture','Mais hybride DK-8031'],['Pluviometrie','1 240 mm/an']], checks: [['ok','Sol fertilise — NPK applique'],['ok','Semis effectue (densite 65 000 pl/ha)'],['warn','Risque chenille legionnaire — surveillance'],['info','Recolte estimee : octobre 2026']], obs: 'Parcelle en bon etat vegetatif. Traitement preventif contre la chenille legionnaire recommande dans 15 jours.' },
    enrg:    { who: 'Technicien reseau — TRAORE Oumar', objet: 'Site IBIG-POSTE-CI-0094 · Plateau', champs: [['Puissance','125 kVA'],['Tension','BT 380V / 220V'],['Indice Q','0,92 — Conforme']], checks: [['ok','Compteur AMR operationnel'],['ok','Protections differentielles OK'],['warn','Condensateur C3 — vieillissement detecte'],['info','Audit quinquennal prevu 2027']], obs: 'Installation conforme aux normes CI-CIGRE. Remplacement du condensateur C3 recommande avant fin 2026.' },
    banq:    { who: 'Charge de clientele — Mme BAMBA Aida', objet: 'Compte Pro N° 01234-567890-CI · SARL', champs: [['Solde moyen','4 850 000 XOF'],['Mouvements','127 op/mois'],['Notation','A+ — Excellent']], checks: [['ok','KYC a jour — Documents conformes'],['ok','Aucun incident de paiement'],['ok','Plafonds adaptes au profil'],['info','Offre Premium disponible — voir conseiller']], obs: 'Compte en bonne standing. Eligibilite au credit professionnel confirmee. Rendez-vous conseiller recommande.' },
    ong:     { who: 'Coordinateur terrain — DIALLO Ibrahim', objet: 'Programme FEED-CI 2026 · Region Savane', champs: [['Beneficiaires','1 842 personnes'],['Zones ciblees','8 villages'],['Budget execute','94,2 %']], checks: [['ok','Distribution alimentaire effectuee'],['ok','Formation hygiene et sante (450 pers.)'],['ok','Puits rehabilites (3 sur 3)'],['info','Rapport bailleur a soumettre avant 15/08']], obs: 'Programme en avance sur les objectifs initiaux. Impact positif mesure sur la securite alimentaire de la zone. ' },
    cons:    { who: 'Consultant Senior — Dr. KOFFI Jean-Marc', objet: 'Mission strategie 2026-2028 · Client X', champs: [['Phase','Phase 2 / 3 — Recommandations'],['Livrables','12 sur 14 fournis'],['Satisfaction','4,7 / 5']], checks: [['ok','Diagnostic organisationnel finalise'],['ok','Plan strategique valide en CODIR'],['warn','Formation dirigeants — 1 session restante'],['info','Rapport final a livrer le 15/08/2026']], obs: 'Mission en bonne voie. Derniere session de formation dirigeants a planifier. Rapport final en cours de redaction.' },
    pharm:   { who: 'Pharmacien responsable — Dr. OSEI Grace', objet: 'Lot de medicaments N° LOT-2026-0447', champs: [['DCI','Amoxicilline 500mg'],['Peremption','08/2028'],['Conformite','ISO 9001:2015']], checks: [['ok','Test dissolution conforme Ph. Eur.'],['ok','Conditionnement hermetique verifie'],['ok','Etiquetage reglementaire conforme'],['info','Conserver a temperature 15-25 C']], obs: 'Lot certifie conforme aux specifications de la pharmacopee europeenne. Stockage en zone temperee obligatoire.' },
    tour:    { who: 'Responsable sejour — ADOU Sandrine', objet: 'Circuit "Forets du Benin" · Ref. VG-2026-088', champs: [['Voyageurs','2 adultes + 1 enfant'],['Duree','8 jours / 7 nuits'],['Categorie','Luxe 4 etoiles']], checks: [['ok','Hotels confirmes et pre-payes'],['ok','Visas et assurances OK'],['ok','Guide certifie bilingue reserve'],['info','Vol retour — escale Accra 2h45']], obs: 'Sejour entierement confirme. Dossier de voyage transmis par email. Contact urgence: +225 07 88 99 00.' },
    mine:    { who: 'Geologue senior — M. ANOUMA Roger', objet: 'Concession MINE-CI-2026-044 · Zone Nord', champs: [['Minerai','Minerai de fer — Fe 62%'],['Tonnage estime','450 000 T'],['Profondeur','0-45 metres']], checks: [['ok','Permis exploitation en vigueur'],['ok','Etude impact environnemental validee'],['warn','Zone de securite perimetrale a renforcer'],['info','Audit environmental annuel prevu nov. 2026']], obs: 'Gisement en phase de production optimale. Renforcement securite perimetre recommande avant augmentation cadences.' },
  }

  const sc = sectorContent[doc.catId] || {
    who: 'Responsable service — M. KOUASSI Emmanuel',
    objet: `${doc.catLabel || 'Document officiel'} — SOCIETE EXEMPLE SA`,
    champs: [['Reference','RPT-2026-0089'],['Service',doc.catLabel || 'Direction Generale'],['Statut','En vigueur']],
    checks: [['ok','Conformite aux normes applicables'],['ok','Validation responsable habilite'],['info','Archivage requis — Conservation 5 ans'],['warn','Diffusion restreinte — Usage interne']],
    obs: 'Document etabli en bonne et due forme. Toute modification ulterieure devra faire l\'objet d\'un avenant signe.'
  }

  const css = `
.page{max-width:680px;margin:0 auto;font-family:Arial,sans-serif;background:#fff}
.rpt-top{background:linear-gradient(135deg,#1E1B4B 0%,${c} 100%);padding:22px 28px;color:#fff}
.rpt-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:16px}
.rpt-logo{display:flex;align-items:center;gap:10px}
.rpt-icon{width:42px;height:42px;background:rgba(255,255,255,.2);border-radius:10px;display:grid;place-items:center;font-size:18px;flex-shrink:0}
.rpt-co{font-size:14px;font-weight:800}
.rpt-co-sub{font-size:9px;opacity:.75;margin-top:2px}
.rpt-ref{text-align:right}
.rpt-badge{font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.12em;background:rgba(255,255,255,.2);border-radius:20px;padding:3px 10px;display:inline-block;margin-bottom:5px}
.rpt-title{font-size:18px;font-weight:900;line-height:1.2}
.rpt-num{font-size:9px;opacity:.8;margin-top:4px}
.rpt-pills{display:flex;gap:6px;flex-wrap:wrap}
.rpt-pill{background:rgba(255,255,255,.15);border-radius:20px;padding:4px 10px;font-size:9.5px}
.rpt-pill strong{font-weight:800}
.rpt-body{padding:18px 28px}
.rpt-objet{background:${c}10;border-left:4px solid ${c};border-radius:0 8px 8px 0;padding:10px 14px;margin-bottom:14px}
.rpt-objet-lbl{font-size:8px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:${c};margin-bottom:3px}
.rpt-objet-val{font-size:13px;font-weight:700;color:#1E293B}
.rpt-who{font-size:10.5px;color:#64748B;margin-top:2px}
.rpt-meta-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-bottom:14px}
.rpt-meta-box{background:#F8FAFC;border-radius:8px;padding:10px 12px;border-top:2px solid ${c}}
.rpt-meta-lbl{font-size:8px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#94A3B8;margin-bottom:3px}
.rpt-meta-val{font-size:12px;font-weight:700;color:#1E293B}
.rpt-checks{margin-bottom:14px}
.rpt-check-title{font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#64748B;margin-bottom:8px}
.rpt-check-item{display:flex;align-items:center;gap:8px;padding:6px 0;border-bottom:1px solid #F8FAFC;font-size:11.5px;color:#374151}
.rpt-check-item:last-child{border:none}
.rpt-chk{width:18px;height:18px;border-radius:5px;display:grid;place-items:center;font-size:10px;font-weight:800;flex-shrink:0}
.rpt-chk.ok{background:#DCFCE7;color:#16A34A}
.rpt-chk.warn{background:#FEF3C7;color:#D97706}
.rpt-chk.info{background:#DBEAFE;color:#2563EB}
.rpt-obs{background:#FFFBEB;border:1px solid #FDE68A;border-radius:8px;padding:12px 14px;font-size:11px;color:#78350F;line-height:1.7}
.rpt-obs-title{font-size:8.5px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#92400E;margin-bottom:5px}
.rpt-footer{background:#F1F5F9;padding:10px 28px;display:flex;justify-content:space-between;align-items:center;font-size:9.5px;color:#64748B;margin-top:14px}
.rpt-sigs{display:flex;gap:12px}
.rpt-sig{border:1.5px dashed #CBD5E1;border-radius:8px;padding:8px 14px;text-align:center;font-size:9px;color:#94A3B8;min-width:100px}
.rpt-sig-line{height:26px}`
  return wrap(css, `<div class="page">
<div class="rpt-top">
  <div class="rpt-header">
    <div class="rpt-logo"><div class="rpt-icon">${doc.icon}</div><div><div class="rpt-co">VOTRE SOCIETE SARL</div><div class="rpt-co-sub">Plateau, Abidjan 01 · NIF 2405812 A · RCCM CI-ABJ-2024-B-12345</div></div></div>
    <div class="rpt-ref"><div class="rpt-badge">${doc.catLabel || 'Document'}</div><div class="rpt-title">${doc.name}</div><div class="rpt-num">Ref: RPT-2026-0089 · 27/07/2026</div></div>
  </div>
  <div class="rpt-pills">
    <div class="rpt-pill">Emis le <strong>27/07/2026</strong></div>
    <div class="rpt-pill">Par: <strong>${sc.who.split(' — ')[0]}</strong></div>
    <div class="rpt-pill">Statut: <strong>Valide</strong></div>
    <div class="rpt-pill">Version: <strong>v1.0 Final</strong></div>
  </div>
</div>
<div class="rpt-body">
  <div class="rpt-objet">
    <div class="rpt-objet-lbl">Objet du document</div>
    <div class="rpt-objet-val">${sc.objet}</div>
    <div class="rpt-who">Redige par: ${sc.who}</div>
  </div>
  <div class="rpt-meta-grid">
    ${sc.champs.map(([l,v]) => `<div class="rpt-meta-box"><div class="rpt-meta-lbl">${l}</div><div class="rpt-meta-val">${v}</div></div>`).join('')}
  </div>
  <div class="rpt-checks">
    <div class="rpt-check-title">Points de controle &amp; constatations</div>
    ${sc.checks.map(([t,txt]) => `<div class="rpt-check-item"><div class="rpt-chk ${t}">${t==='ok'?'&#10003;':t==='warn'?'!':'i'}</div>${txt}</div>`).join('')}
  </div>
  <div class="rpt-obs">
    <div class="rpt-obs-title">Observations &amp; Recommandations</div>
    ${sc.obs}
  </div>
</div>
<div class="rpt-footer">
  <span>Document genere le 27/07/2026 via IBIG FactPro · Conservation 5 ans</span>
  <div class="rpt-sigs">
    <div class="rpt-sig"><div class="rpt-sig-line"></div>Redacteur</div>
    <div class="rpt-sig"><div class="rpt-sig-line"></div>Approbateur</div>
  </div>
</div>
</div>`)
}

// ── TEMPLATE : Bon de caisse / Voucher (cash_voucher — 14 docs)
function previewVoucher(doc) {
  const c = doc.catColor || '#0891B2'
  const css = `
.page{max-width:520px;margin:0 auto;font-family:Arial,sans-serif}
.voucher{background:#fff;border:2px solid ${c};border-radius:12px;overflow:hidden;box-shadow:0 8px 30px rgba(0,0,0,.12)}
.v-header{background:${c};color:#fff;padding:16px 20px;display:flex;justify-content:space-between;align-items:center}
.v-brand{font-size:14px;font-weight:900}
.v-brand-sub{font-size:9px;opacity:.8;margin-top:2px}
.v-badge{background:rgba(255,255,255,.25);border-radius:8px;padding:6px 12px;text-align:right}
.v-badge-type{font-size:11px;font-weight:900;text-transform:uppercase;letter-spacing:.06em}
.v-badge-num{font-size:9px;opacity:.85;margin-top:2px}
.v-perforation{height:0;border-top:2px dashed rgba(0,0,0,.15);margin:0 20px;position:relative}
.v-perforation::before,.v-perforation::after{content:"";position:absolute;top:-8px;width:16px;height:16px;background:#F3F4F6;border-radius:50%;border:2px solid ${c}}
.v-perforation::before{left:-28px}
.v-perforation::after{right:-28px}
.v-body{padding:20px}
.v-amount-section{text-align:center;padding:16px 0;margin-bottom:16px}
.v-amount-label{font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:#94A3B8;margin-bottom:6px}
.v-amount{font-size:32px;font-weight:900;color:${c}}
.v-amount-words{font-size:10.5px;color:#64748B;margin-top:4px;font-style:italic}
.v-info-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:14px}
.v-info-item{background:#F8FAFC;border-radius:8px;padding:10px 12px}
.v-info-label{font-size:8px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#94A3B8;margin-bottom:3px}
.v-info-value{font-size:12px;font-weight:700;color:#1E293B}
.v-purpose{background:#F0F9FF;border-left:3px solid ${c};border-radius:0 8px 8px 0;padding:10px 14px;margin-bottom:14px}
.v-purpose-label{font-size:8.5px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#0369A1;margin-bottom:4px}
.v-purpose-text{font-size:12px;color:#0F172A;font-weight:600}
.v-sigs{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:8px}
.v-sig{border:1.5px dashed #CBD5E1;border-radius:8px;padding:10px;text-align:center}
.v-sig-line{height:32px}
.v-sig-label{font-size:9px;color:#94A3B8;margin-top:4px}
.v-footer{background:#F8FAFC;padding:10px 20px;text-align:center;font-size:9px;color:#94A3B8;border-top:1px dashed #E2E8F0}`
  return wrap(css, `<div class="page"><div class="voucher">
<div class="v-header">
  <div><div class="v-brand">${doc.icon} VOTRE SOCIETE SARL</div><div class="v-brand-sub">Plateau, Abidjan 01 · +225 27 22 33 44 55</div></div>
  <div class="v-badge"><div class="v-badge-type">${doc.name}</div><div class="v-badge-num">N° BC-2026-0094</div></div>
</div>
<div class="v-perforation"></div>
<div class="v-body">
  <div class="v-amount-section">
    <div class="v-amount-label">Montant du bon</div>
    <div class="v-amount">85 000 XOF</div>
    <div class="v-amount-words">Quatre-vingt-cinq mille francs CFA</div>
  </div>
  <div class="v-info-grid">
    <div class="v-info-item"><div class="v-info-label">Beneficiaire</div><div class="v-info-value">KONE Albert</div></div>
    <div class="v-info-item"><div class="v-info-label">Date</div><div class="v-info-value">27/07/2026</div></div>
    <div class="v-info-item"><div class="v-info-label">Reference</div><div class="v-info-value">BC-2026-0094</div></div>
    <div class="v-info-item"><div class="v-info-label">Mode paiement</div><div class="v-info-value">Mobile Money</div></div>
  </div>
  <div class="v-purpose">
    <div class="v-purpose-label">Motif / Objet</div>
    <div class="v-purpose-text">${doc.desc ? doc.desc.substring(0,80) : 'Reglement selon accord commercial en vigueur'}</div>
  </div>
  <div class="v-sigs">
    <div class="v-sig"><div class="v-sig-line"></div><div class="v-sig-label">Signature Emetteur</div></div>
    <div class="v-sig"><div class="v-sig-line"></div><div class="v-sig-label">Signature Beneficiaire</div></div>
  </div>
</div>
<div class="v-footer">Bon valable 30 jours · IBIG FactPro · Document officiel</div>
</div></div>`)
}

// ── TEMPLATE : Note de frais (expense_report — 12 docs)
function previewExpenseReport(doc) {
  const c = doc.catColor || '#0D9488'
  const css = `
.page{max-width:680px;margin:0 auto;font-family:Arial,sans-serif;background:#fff}
.exp-top{background:#0F172A;padding:20px 28px;display:flex;justify-content:space-between;align-items:center}
.exp-brand{display:flex;align-items:center;gap:10px}
.exp-icon{width:40px;height:40px;background:${c};border-radius:8px;display:grid;place-items:center;font-size:18px;color:#fff}
.exp-co{font-size:14px;font-weight:800;color:#fff}
.exp-co-sub{font-size:9px;color:#94A3B8;margin-top:2px}
.exp-doc{text-align:right}
.exp-title{font-size:14px;font-weight:900;color:${c};text-transform:uppercase;letter-spacing:.04em}
.exp-num{font-size:9.5px;color:#94A3B8;margin-top:3px}
.exp-employee{background:#F0FDFA;border-bottom:2px solid ${c};padding:12px 28px;display:grid;grid-template-columns:repeat(4,1fr);gap:8px}
.emp-item label{font-size:8px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#0D9488;display:block;margin-bottom:3px}
.emp-item span{font-size:12px;font-weight:700;color:#0F172A}
.exp-body{padding:16px 28px}
table{width:100%;border-collapse:collapse;margin-bottom:14px;font-size:11px}
thead{background:#0F172A}
th{color:#fff;padding:8px 10px;text-align:left;font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.06em}
th:last-child,td:last-child{text-align:right}
td{padding:8px 10px;border-bottom:1px solid #F1F5F9;color:#374151}
tr:nth-child(even) td{background:#F8FAFC}
.cat-chip{display:inline-block;border-radius:12px;padding:2px 8px;font-size:9px;font-weight:700}
.cat-transport{background:#EFF6FF;color:#2563EB}
.cat-repas{background:#FFF7ED;color:#C2410C}
.cat-hebergement{background:#F0FDF4;color:#15803D}
.cat-divers{background:#F5F3FF;color:#7C3AED}
.exp-summary{display:flex;justify-content:flex-end;margin-bottom:14px}
.exp-totals{width:260px}
.exp-tl{display:flex;justify-content:space-between;padding:5px 12px;font-size:11px;border-bottom:1px solid #F1F5F9;color:#475569}
.exp-tf{display:flex;justify-content:space-between;padding:10px 12px;font-size:14px;font-weight:900;background:${c};color:#fff;border-radius:0 0 8px 8px}
.exp-justif{background:#FFF7ED;border:1px solid #FCD34D;border-radius:8px;padding:10px 14px;font-size:10.5px;color:#78350F;margin-bottom:12px}
.exp-sigs{display:grid;grid-template-columns:repeat(3,1fr);gap:10px}
.sig{border:1.5px dashed #CBD5E1;border-radius:8px;padding:10px;text-align:center;font-size:9.5px;color:#94A3B8}
.sig-line{height:28px}`
  return wrap(css, `<div class="page">
<div class="exp-top">
  <div class="exp-brand">
    <div class="exp-icon">&#128176;</div>
    <div><div class="exp-co">VOTRE SOCIETE SARL</div><div class="exp-co-sub">Plateau, Abidjan 01 · NIF 2405812 A</div></div>
  </div>
  <div class="exp-doc"><div class="exp-title">${doc.name}</div><div class="exp-num">N° NDF-2026-0047 · Mois: Juillet 2026</div></div>
</div>
<div class="exp-employee">
  <div class="emp-item"><label>Employe</label><span>DIALLO Fatou</span></div>
  <div class="emp-item"><label>Service</label><span>Commercial</span></div>
  <div class="emp-item"><label>Periode</label><span>Juil. 2026</span></div>
  <div class="emp-item"><label>Mission</label><span>Prospection Nord</span></div>
</div>
<div class="exp-body">
<table>
  <thead><tr><th>Date</th><th>Nature</th><th>Categorie</th><th>Justificatif</th><th>Montant</th></tr></thead>
  <tbody>
    <tr><td>03/07</td><td>Taxi aeroport A-B</td><td><span class="cat-chip cat-transport">Transport</span></td><td>Ticket taxi</td><td>12 000</td></tr>
    <tr><td>03/07</td><td>Dejeuner client KONE SA</td><td><span class="cat-chip cat-repas">Repas</span></td><td>Facture hotel</td><td>28 500</td></tr>
    <tr><td>04/07</td><td>Hotel Sofitel 2 nuits</td><td><span class="cat-chip cat-hebergement">Hebergement</span></td><td>Facture hotel</td><td>95 000</td></tr>
    <tr><td>05/07</td><td>Location vehicule</td><td><span class="cat-chip cat-transport">Transport</span></td><td>Contrat loc.</td><td>45 000</td></tr>
    <tr><td>06/07</td><td>Fournitures reunion</td><td><span class="cat-chip cat-divers">Divers</span></td><td>Facture</td><td>8 200</td></tr>
  </tbody>
</table>
<div class="exp-summary"><div class="exp-totals">
  <div class="exp-tl"><span>Transport</span><span>57 000 XOF</span></div>
  <div class="exp-tl"><span>Repas</span><span>28 500 XOF</span></div>
  <div class="exp-tl"><span>Hebergement</span><span>95 000 XOF</span></div>
  <div class="exp-tl"><span>Divers</span><span>8 200 XOF</span></div>
  <div class="exp-tf"><span>TOTAL A REMBOURSER</span><span>188 700 XOF</span></div>
</div></div>
<div class="exp-justif">&#128204; Tous les justificatifs originaux sont joints au present etat de frais. Conformite avec la politique voyages en vigueur.</div>
<div class="exp-sigs">
  <div class="sig"><div class="sig-line"></div>Employe</div>
  <div class="sig"><div class="sig-line"></div>Responsable N+1</div>
  <div class="sig"><div class="sig-line"></div>Direction / DAF</div>
</div>
</div></div>`)
}

// ── TEMPLATE : Bon de reception / Entree marchandises (goods_receipt — 11 docs)
function previewGoodsReceipt(doc) {
  const c = doc.catColor || '#B45309'
  const css = `
.page{max-width:680px;margin:0 auto;font-family:Arial,sans-serif;background:#fff}
.gr-top{background:linear-gradient(90deg,#1C1917 0%,#292524 100%);padding:18px 24px;display:flex;justify-content:space-between;align-items:center}
.gr-brand{display:flex;align-items:center;gap:10px}
.gr-icon{width:42px;height:42px;background:${c};border-radius:8px;display:grid;place-items:center;font-size:18px}
.gr-co{font-size:14px;font-weight:800;color:#fff}
.gr-co-sub{font-size:9px;color:#A8A29E;margin-top:2px}
.gr-doc{text-align:right}
.gr-doctype{font-size:14px;font-weight:900;color:${c};text-transform:uppercase;letter-spacing:.04em}
.gr-docnum{font-size:9px;color:#A8A29E;margin-top:3px}
.gr-info{display:grid;grid-template-columns:repeat(4,1fr);gap:0;background:#FAFAF9;border-bottom:2px solid ${c}}
.gr-info-item{padding:10px 14px;border-right:1px solid #E7E5E4}
.gr-info-item:last-child{border:none}
.gr-info-label{font-size:8px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#A8A29E;margin-bottom:3px}
.gr-info-value{font-size:11.5px;font-weight:700;color:#1C1917}
.gr-body{padding:16px 24px}
.gr-parties{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:14px}
.gr-party{background:#FAFAF9;border-radius:8px;padding:10px 14px;border-top:3px solid ${c}}
.gr-party-lbl{font-size:8px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#A8A29E;margin-bottom:4px}
.gr-party-name{font-size:13px;font-weight:800;color:#1C1917;margin-bottom:2px}
.gr-party-info{font-size:10.5px;color:#78716C;line-height:1.6}
table{width:100%;border-collapse:collapse;margin-bottom:12px}
thead{background:#1C1917}
th{color:#fff;padding:8px 10px;font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;text-align:left}
th:last-child,td:last-child{text-align:right}
td{padding:8px 10px;border-bottom:1px solid #F5F5F4;font-size:11px;color:#374151}
tr:nth-child(even) td{background:#FAFAF9}
.status-badge{display:inline-block;padding:2px 8px;border-radius:12px;font-size:9px;font-weight:700}
.s-ok{background:#DCFCE7;color:#15803D}
.s-diff{background:#FFFBEB;color:#B45309}
.s-ko{background:#FEE2E2;color:#DC2626}
.gr-diff-box{background:#FFFBEB;border:1px solid ${c};border-radius:8px;padding:10px 14px;margin-bottom:12px;font-size:11px;color:#78350F}
.gr-diff-title{font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#92400E;margin-bottom:6px}
.gr-sig-bar{background:#1C1917;border-radius:8px;padding:12px 16px;display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px}
.gr-sig{text-align:center}
.gr-sig-line{height:28px;border-bottom:1px solid rgba(255,255,255,.2);margin-bottom:6px}
.gr-sig-lbl{font-size:9px;color:#A8A29E}`
  return wrap(css, `<div class="page">
<div class="gr-top">
  <div class="gr-brand">
    <div class="gr-icon">&#128230;</div>
    <div><div class="gr-co">VOTRE SOCIETE SARL</div><div class="gr-co-sub">Zone Industrielle, Abidjan · RCCM CI-ABJ-2024-B-12345</div></div>
  </div>
  <div class="gr-doc"><div class="gr-doctype">${doc.name}</div><div class="gr-docnum">N° REC-2026-0215 · 27/07/2026</div></div>
</div>
<div class="gr-info">
  <div class="gr-info-item"><div class="gr-info-label">Bon de Commande ref.</div><div class="gr-info-value">BC-2026-0198</div></div>
  <div class="gr-info-item"><div class="gr-info-label">Date reception</div><div class="gr-info-value">27/07/2026 09:30</div></div>
  <div class="gr-info-item"><div class="gr-info-label">Quai / Zone</div><div class="gr-info-value">Quai 3 — Entrepot A</div></div>
  <div class="gr-info-item"><div class="gr-info-label">Statut</div><div class="gr-info-value" style="color:#D97706">&#9888; Ecart detecte</div></div>
</div>
<div class="gr-body">
<div class="gr-parties">
  <div class="gr-party"><div class="gr-party-lbl">Fournisseur</div><div class="gr-party-name">DISTRIBUTEUR CENTRAL SA</div><div class="gr-party-info">Zone Ind. Port-Bouet · +225 27 21 00 11 22<br>Ref fournisseur: FAC-DC-2026-4412</div></div>
  <div class="gr-party"><div class="gr-party-lbl">Receptionnaire</div><div class="gr-party-name">TRAORE Moussa</div><div class="gr-party-info">Responsable Magasin<br>Badge: MGS-0042 · Tel: +225 05 77 88 99</div></div>
</div>
<table>
  <thead><tr><th>Ref.</th><th>Designation</th><th>Qte commandee</th><th>Qte recue</th><th>Ecart</th><th>Etat</th></tr></thead>
  <tbody>
    <tr><td>REF-001</td><td>Ciment Portland 50kg</td><td>200 sacs</td><td>200 sacs</td><td>0</td><td><span class="status-badge s-ok">Conforme</span></td></tr>
    <tr><td>REF-002</td><td>Fer a beton 10mm</td><td>50 barres</td><td>48 barres</td><td>-2</td><td><span class="status-badge s-diff">Ecart</span></td></tr>
    <tr><td>REF-003</td><td>Cable electrique 2.5mm</td><td>10 rlx</td><td>10 rlx</td><td>0</td><td><span class="status-badge s-ok">Conforme</span></td></tr>
    <tr><td>REF-004</td><td>Peinture ext. blanc 20L</td><td>30 bidons</td><td>0</td><td>-30</td><td><span class="status-badge s-ko">Manquant</span></td></tr>
  </tbody>
</table>
<div class="gr-diff-box">
  <div class="gr-diff-title">&#9888; Reserves emises</div>
  2 barres de fer manquantes (REF-002) · 30 bidons peinture non livres (REF-004). Litige ouvert aupres du fournisseur. Reference: LIT-2026-0089.
</div>
<div class="gr-sig-bar">
  <div class="gr-sig"><div class="gr-sig-line"></div><div class="gr-sig-lbl">Chauffeur / Livreur</div></div>
  <div class="gr-sig"><div class="gr-sig-line"></div><div class="gr-sig-lbl">Magasinier</div></div>
  <div class="gr-sig"><div class="gr-sig-line"></div><div class="gr-sig-lbl">Responsable Magasin</div></div>
</div>
</div></div>`)
}

// ── TEMPLATE : Bon de commande fournisseur (supplier_order — 6 docs)
function previewSupplierOrder(doc) {
  const c = doc.catColor || '#0369A1'
  const css = `
.page{max-width:680px;margin:0 auto;font-family:Arial,sans-serif;background:#fff}
.so-top{background:linear-gradient(135deg,#0C4A6E 0%,${c} 100%);padding:22px 28px;display:flex;justify-content:space-between;align-items:flex-start;color:#fff}
.so-left .co{font-size:15px;font-weight:900}
.so-left .co-sub{font-size:9.5px;opacity:.75;margin-top:3px}
.so-right{text-align:right}
.so-doctype{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;background:rgba(255,255,255,.2);border-radius:16px;padding:3px 12px;display:inline-block;margin-bottom:6px}
.so-num{font-size:22px;font-weight:900}
.so-date{font-size:9.5px;opacity:.8;margin-top:4px}
.so-urgency{background:#FFFBEB;border-left:4px solid #F59E0B;padding:8px 20px;font-size:10.5px;font-weight:700;color:#78350F}
.so-body{padding:18px 28px}
.so-parties{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px}
.so-party{border:1px solid #E2E8F0;border-radius:10px;padding:12px 14px}
.so-party.buyer{border-top:3px solid ${c}}
.so-party.seller{border-top:3px solid #10B981}
.so-plbl{font-size:8.5px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#94A3B8;margin-bottom:5px}
.so-pname{font-size:13px;font-weight:800;color:#0F172A;margin-bottom:3px}
.so-pinfo{font-size:10.5px;color:#64748B;line-height:1.65}
table{width:100%;border-collapse:collapse;margin-bottom:14px}
thead{background:#0C4A6E}
th{color:#fff;padding:8px 10px;font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;text-align:left}
th:last-child,td:last-child{text-align:right}
td{padding:8px 10px;border-bottom:1px solid #F1F5F9;font-size:11px;color:#374151}
tr:nth-child(even) td{background:#F8FAFC}
.so-totals{display:flex;justify-content:flex-end;margin-bottom:14px}
.so-tb{width:270px}
.so-tl{display:flex;justify-content:space-between;padding:6px 12px;font-size:11.5px;border-bottom:1px solid #F1F5F9;color:#475569}
.so-tf{display:flex;justify-content:space-between;padding:11px 12px;font-size:14px;font-weight:900;background:${c};color:#fff;border-radius:0 0 8px 8px}
.so-terms{background:#F0F9FF;border-radius:8px;padding:10px 14px;font-size:10.5px;color:#0369A1;margin-bottom:12px;line-height:1.7}
.so-sigs{display:flex;gap:12px}
.so-sig{flex:1;border:1.5px dashed #CBD5E1;border-radius:8px;padding:10px;text-align:center;font-size:10px;color:#94A3B8}
.so-sig-line{height:36px}`
  return wrap(css, `<div class="page">
<div class="so-top">
  <div class="so-left"><div class="co">VOTRE SOCIETE SARL</div><div class="co-sub">Plateau, Abidjan 01 · NIF 2405812 A · RCCM CI-ABJ-2024-B-12345<br>Acheteur agree · Compte BICICI: CI61 0123 4567 8901 2345 6789 00</div></div>
  <div class="so-right">
    <div class="so-doctype">${doc.name}</div>
    <div class="so-num">BCF-2026-0094</div>
    <div class="so-date">Emis le 27/07/2026 · Livraison souhaitee: 10/08/2026</div>
  </div>
</div>
<div class="so-urgency">&#128343; Livraison souhaitee avant le <strong>10/08/2026</strong> — Priorite NORMALE</div>
<div class="so-body">
<div class="so-parties">
  <div class="so-party buyer"><div class="so-plbl">Acheteur</div><div class="so-pname">VOTRE SOCIETE SARL</div><div class="so-pinfo">Plateau, Abidjan 01<br>NIF 2405812 A · CNPS 123-456<br>service.achat@votresociete.ci</div></div>
  <div class="so-party seller"><div class="so-plbl">Fournisseur</div><div class="so-pname">GROSSISTE NATIONAL SA</div><div class="so-pinfo">Zone Portuaire, Abidjan<br>+225 27 21 55 66 77<br>commandes@grossiste.ci</div></div>
</div>
<table>
  <thead><tr><th>#</th><th>Ref. article</th><th>Designation</th><th>Qte</th><th>P.U. HT</th><th>Total HT</th></tr></thead>
  <tbody>
    <tr><td>01</td><td>ART-0042</td><td>Fournitures bureau — lot standard</td><td>10 boites</td><td>25 000</td><td>250 000</td></tr>
    <tr><td>02</td><td>MAT-0118</td><td>Materiel informatique (accessoires)</td><td>5 u.</td><td>85 000</td><td>425 000</td></tr>
    <tr><td>03</td><td>CON-0024</td><td>Consommables impression (toners)</td><td>12 u.</td><td>18 500</td><td>222 000</td></tr>
  </tbody>
</table>
<div class="so-totals"><div class="so-tb">
  <div class="so-tl"><span>Sous-total HT</span><span>897 000 XOF</span></div>
  <div class="so-tl"><span>TVA 18%</span><span>161 460 XOF</span></div>
  <div class="so-tf"><span>TOTAL TTC</span><span>1 058 460 XOF</span></div>
</div></div>
<div class="so-terms">&#128221; Conditions: Paiement 30 jours net · Livraison franco de port · En cas de rupture, informer sous 48h · Tout article non conforme sera retourne aux frais du fournisseur.</div>
<div class="so-sigs">
  <div class="so-sig"><div class="so-sig-line"></div>Service Achats</div>
  <div class="so-sig"><div class="so-sig-line"></div>Direction</div>
  <div class="so-sig"><div class="so-sig-line"></div>Fournisseur — Bon pour accord</div>
</div>
</div></div>`)
}

// ── TEMPLATE : Restauration / Ticket caisse
function previewResto(doc) {
  const c = doc.catColor || '#F97316'
  const css = `
body{background:#f5f0eb;font-family:'Courier New',monospace}
.page{max-width:340px;margin:0 auto}
.receipt{background:#fff;border-radius:4px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.12)}
.header{background:${c};color:#fff;text-align:center;padding:18px 16px}
.rest-name{font-size:18px;font-weight:900;letter-spacing:.05em}
.rest-sub{font-size:9px;opacity:.85;margin-top:3px;letter-spacing:.08em}
.doc-badge{background:rgba(255,255,255,.2);border-radius:4px;padding:4px 10px;font-size:10px;font-weight:700;display:inline-block;margin-top:8px}
.ticket-info{display:flex;justify-content:space-between;background:#FFF7ED;padding:8px 14px;font-size:9.5px;color:#92400E;border-bottom:2px dashed #FCD34D}
.items{padding:12px 14px}
.item{display:flex;justify-content:space-between;align-items:baseline;padding:5px 0;border-bottom:1px dotted #E5E7EB;font-size:11.5px}
.item:last-child{border:none}
.iname{flex:1;color:#111}
.iqty{color:#6B7280;font-size:10px;margin:0 8px}
.iprice{font-weight:700;color:#111}
.divider{border:none;border-top:2px dashed #D1D5DB;margin:4px 14px}
.total-section{padding:8px 14px 4px}
.subtotal{display:flex;justify-content:space-between;font-size:11px;color:#6B7280;padding:3px 0}
.total-line{display:flex;justify-content:space-between;font-size:15px;font-weight:900;color:#111;padding:7px 0;border-top:2px solid #111}
.payment-badge{background:${c};color:#fff;text-align:center;padding:8px;font-size:10px;font-weight:700;letter-spacing:.05em}
.merci{text-align:center;padding:12px;font-size:10px;color:#9CA3AF;letter-spacing:.03em}
.table-info{display:flex;gap:6px;flex-wrap:wrap;padding:0 14px 10px}
.tinfo-chip{background:#FFF7ED;border:1px solid #FCD34D;border-radius:12px;padding:3px 9px;font-size:9.5px;color:#92400E;font-weight:600}`
  return wrap(css, `<div class="page"><div class="receipt">
<div class="header">
  <div class="rest-name">${doc.icon} RESTAURANT EXCELLENCE</div>
  <div class="rest-sub">Avenue du Commerce, Abidjan · +225 27 22 44 55 66</div>
  <div class="doc-badge">${doc.name.toUpperCase()}</div>
</div>
<div class="ticket-info">
  <span>N° T-2026-0847</span><span>Table 05 · Couverts: 4</span><span>27/07/2026 13:42</span>
</div>
<div class="table-info">
  <span class="tinfo-chip">Serveur: Ali</span>
  <span class="tinfo-chip">Duree: 1h15</span>
  <span class="tinfo-chip">Salle VIP</span>
</div>
<div class="items">
  <div class="item"><span class="iname">Thiebu Royal</span><span class="iqty">x2</span><span class="iprice">14 000</span></div>
  <div class="item"><span class="iname">Poulet Yassa Grille</span><span class="iqty">x1</span><span class="iprice">8 500</span></div>
  <div class="item"><span class="iname">Jus Bissap</span><span class="iqty">x4</span><span class="iprice">6 000</span></div>
  <div class="item"><span class="iname">Salade Fraicheur</span><span class="iqty">x2</span><span class="iprice">3 200</span></div>
  <div class="item"><span class="iname">Dessert Maison</span><span class="iqty">x4</span><span class="iprice">7 600</span></div>
</div>
<hr class="divider">
<div class="total-section">
  <div class="subtotal"><span>Sous-total HT</span><span>33 390 XOF</span></div>
  <div class="subtotal"><span>TVA 18%</span><span>6 010 XOF</span></div>
  <div class="subtotal"><span>Service (5%)</span><span>1 670 XOF</span></div>
  <div class="total-line"><span>TOTAL</span><span>39 300 XOF</span></div>
</div>
<div class="payment-badge">PAYE PAR MOBILE MONEY</div>
<div class="merci">Merci de votre visite · A tres bientot !<br>Conservez ce recu pour tout remboursement</div>
</div></div>`)
}

// ── TEMPLATE : Garage / Atelier automobile
function previewGarage(doc) {
  const c = doc.catColor || '#6366F1'
  const css = `
.page{max-width:680px;margin:0 auto;font-family:Arial,sans-serif}
.topbar{background:#1C1C2E;color:#fff;padding:16px 24px;display:flex;justify-content:space-between;align-items:center}
.garage-brand{display:flex;align-items:center;gap:12px}
.garage-icon{width:44px;height:44px;background:${c};border-radius:8px;display:grid;place-items:center;font-size:20px}
.garage-name{font-size:15px;font-weight:900;color:#fff}
.garage-sub{font-size:9.5px;color:#94A3B8;margin-top:2px}
.doc-ref{text-align:right}
.doc-type{font-size:14px;font-weight:900;color:${c};text-transform:uppercase;letter-spacing:.05em}
.doc-num{font-size:10px;color:#94A3B8;margin-top:3px}
.vehicle-card{background:#F8FAFC;border-left:4px solid ${c};margin:16px;padding:12px 16px;border-radius:0 8px 8px 0;display:grid;grid-template-columns:repeat(4,1fr);gap:8px}
.vc-item label{font-size:8px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#94A3B8;display:block;margin-bottom:3px}
.vc-item span{font-size:12px;font-weight:700;color:#1C1C2E}
.section-title{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#94A3B8;margin:0 16px 8px;padding-top:8px}
table{width:calc(100% - 32px);margin:0 16px 16px;border-collapse:collapse}
th{background:#1C1C2E;color:#fff;padding:8px 10px;text-align:left;font-size:9px;font-weight:700;text-transform:uppercase}
th:last-child,td:last-child{text-align:right}
td{padding:8px 10px;border-bottom:1px solid #F1F5F9;font-size:11px;color:#374151}
tr:nth-child(even) td{background:#F8FAFC}
.diag-box{margin:0 16px 12px;background:#FFFBEB;border:1px solid #FCD34D;border-radius:8px;padding:10px 14px}
.diag-title{font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#92400E;margin-bottom:6px}
.diag-items{display:flex;flex-wrap:wrap;gap:6px}
.diag-chip{background:#fff;border:1px solid #FCD34D;border-radius:12px;padding:3px 8px;font-size:9.5px;color:#78350F}
.total-bar{background:#1C1C2E;margin:0 16px 16px;border-radius:8px;padding:10px 16px;display:flex;justify-content:space-between;align-items:center}
.total-label{font-size:11px;color:#94A3B8}
.total-amount{font-size:18px;font-weight:900;color:#fff}
.total-sub{font-size:9px;color:${c};text-align:right;margin-top:2px}`
  return wrap(css, `<div class="page">
<div class="topbar">
  <div class="garage-brand">
    <div class="garage-icon">&#128295;</div>
    <div><div class="garage-name">GARAGE AUTO EXCELLENCE</div><div class="garage-sub">Zone Industrielle, Abidjan · Agree constructeur · RCC CI-ABJ-2018-B-9412</div></div>
  </div>
  <div class="doc-ref"><div class="doc-type">${doc.name}</div><div class="doc-num">N° OS-2026-00312<br>27/07/2026</div></div>
</div>
<div class="vehicle-card">
  <div class="vc-item"><label>Marque/Modele</label><span>Toyota Hilux</span></div>
  <div class="vc-item"><label>Immatriculation</label><span>4587 AB 01</span></div>
  <div class="vc-item"><label>Kilometrage</label><span>87 420 km</span></div>
  <div class="vc-item"><label>Client</label><span>KONE Marcel</span></div>
</div>
<div class="diag-box">
  <div class="diag-title">Diagnostic effectue</div>
  <div class="diag-items">
    <span class="diag-chip">OK Vidange huile moteur</span>
    <span class="diag-chip">! Plaquettes de frein usees</span>
    <span class="diag-chip">OK Filtres air/huile/carburant</span>
    <span class="diag-chip">!! Courroie distribution</span>
  </div>
</div>
<div class="section-title">Prestations &amp; Pieces</div>
<table>
  <thead><tr><th>Designation</th><th>Ref.</th><th>Qte</th><th>P.U.</th><th>Total</th></tr></thead>
  <tbody>
    <tr><td>Main d oeuvre vidange complete</td><td>MO-001</td><td>1h</td><td>15 000</td><td>15 000</td></tr>
    <tr><td>Huile moteur 5W40 (5L)</td><td>HM-5W40</td><td>5</td><td>4 500</td><td>22 500</td></tr>
    <tr><td>Kit filtres complet</td><td>KF-TOY-H</td><td>1</td><td>18 000</td><td>18 000</td></tr>
    <tr><td>Plaquettes frein avant (jeu)</td><td>PF-TOY-H</td><td>1</td><td>35 000</td><td>35 000</td></tr>
    <tr><td>Main d oeuvre pose freins</td><td>MO-FRN</td><td>2h</td><td>15 000</td><td>30 000</td></tr>
  </tbody>
</table>
<div class="total-bar">
  <div><div class="total-label">Total HT · TVA 18% incl.</div><div style="font-size:9px;color:#6B7280;margin-top:2px">Garantie pieces : 6 mois · Main oeuvre : 3 mois</div></div>
  <div style="text-align:right"><div class="total-amount">141 800 XOF</div><div class="total-sub">Paiement: OM / CM / Especes</div></div>
</div>
</div>`)
}

// ── TEMPLATE : IT / Informatique & Telecoms
function previewIT(doc) {
  const c = doc.catColor || '#0EA5E9'
  const css = `
.page{max-width:680px;margin:0 auto;font-family:'Segoe UI',Arial,sans-serif;background:#fff}
.topbar{background:linear-gradient(135deg,#0F172A 0%,#1E3A5F 100%);padding:20px 28px;display:flex;justify-content:space-between;align-items:center}
.it-brand{display:flex;align-items:center;gap:12px}
.it-logo{width:44px;height:44px;background:${c};border-radius:10px;display:grid;place-items:center;font-size:20px}
.it-name{font-size:15px;font-weight:800;color:#fff}
.it-sub{font-size:9px;color:#94A3B8;margin-top:2px;font-family:monospace}
.doc-box{text-align:right}
.doc-type{font-size:13px;font-weight:900;color:${c};text-transform:uppercase;letter-spacing:.06em}
.doc-meta{font-size:9px;color:#94A3B8;margin-top:4px;line-height:1.7}
.contract-banner{background:#F0F9FF;border-bottom:3px solid ${c};padding:10px 28px;display:flex;gap:24px;align-items:center}
.cb-item{display:flex;align-items:center;gap:6px;font-size:10.5px;color:#0369A1}
.cb-icon{width:24px;height:24px;background:${c};border-radius:6px;display:grid;place-items:center;font-size:11px;color:#fff}
.body{padding:18px 28px}
.client-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:18px}
.cg-box{border:1px solid #E2E8F0;border-radius:10px;padding:12px 14px;background:#F8FAFC}
.cg-label{font-size:8.5px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#94A3B8;margin-bottom:4px}
.cg-name{font-size:13px;font-weight:700;color:#0F172A;margin-bottom:2px}
.cg-info{font-size:10px;color:#64748B;line-height:1.6;font-family:monospace}
.scope-title{font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#94A3B8;margin-bottom:8px}
table{width:100%;border-collapse:collapse;margin-bottom:16px}
thead{background:#0F172A}
th{color:#fff;padding:8px 10px;font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;text-align:left}
th:last-child,td:last-child{text-align:right}
td{padding:9px 10px;border-bottom:1px solid #F1F5F9;font-size:11px;color:#374151}
tr:nth-child(even) td{background:#F8FAFC}
.tag{display:inline-block;background:#EFF6FF;color:#3B82F6;border-radius:4px;padding:1px 6px;font-size:9px;font-weight:600;margin-left:4px;font-family:monospace}
.sla-box{background:#F0FDF4;border:1px solid #86EFAC;border-radius:8px;padding:10px 14px;margin-bottom:14px;display:grid;grid-template-columns:repeat(3,1fr);gap:8px;text-align:center}
.sla-val{font-size:15px;font-weight:900;color:#15803D}
.sla-lbl{font-size:8.5px;color:#16A34A;margin-top:2px}
.total-bar{background:#0F172A;border-radius:10px;padding:12px 18px;display:flex;justify-content:space-between;align-items:center}
.tl-label{font-size:10.5px;color:#94A3B8}
.tl-amount{font-size:17px;font-weight:900;color:#fff}
.tl-period{font-size:9px;color:${c};margin-top:2px;text-align:right}`
  return wrap(css, `<div class="page">
<div class="topbar">
  <div class="it-brand">
    <div class="it-logo">&#128187;</div>
    <div><div class="it-name">IBIG TECH SOLUTIONS SARL</div><div class="it-sub">www.ibigtech.ci · RCCM CI-ABJ-2020-B-5541 · NIF 2104567B</div></div>
  </div>
  <div class="doc-box"><div class="doc-type">${doc.name}</div><div class="doc-meta">Ref: IT-2026-0094<br>Date: 27/07/2026<br>Validite: 30 jours</div></div>
</div>
<div class="contract-banner">
  <div class="cb-item"><div class="cb-icon">S</div>SLA Garanti</div>
  <div class="cb-item"><div class="cb-icon">!</div>Support 24/7</div>
  <div class="cb-item"><div class="cb-icon">C</div>Cloud Herberge</div>
  <div class="cb-item"><div class="cb-icon">K</div>ISO 27001</div>
</div>
<div class="body">
<div class="client-grid">
  <div class="cg-box"><div class="cg-label">Prestataire</div><div class="cg-name">IBIG Tech Solutions</div><div class="cg-info">Plateau, Abidjan 01<br>+225 27 22 11 55 00<br>contact@ibigtech.ci</div></div>
  <div class="cg-box"><div class="cg-label">Client</div><div class="cg-name">SOCIETE COMMERCIALE SA</div><div class="cg-info">Cocody, Abidjan<br>+225 07 55 66 77 88<br>dsi@socom.ci</div></div>
</div>
<div class="sla-box">
  <div><div class="sla-val">99.9%</div><div class="sla-lbl">Disponibilite</div></div>
  <div><div class="sla-val">&lt;4h</div><div class="sla-lbl">Tps reponse</div></div>
  <div><div class="sla-val">12 mois</div><div class="sla-lbl">Duree contrat</div></div>
</div>
<div class="scope-title">Prestations &amp; Licences</div>
<table>
  <thead><tr><th>Designation</th><th>Type</th><th>Qte</th><th>P.U./mois</th><th>Total/an</th></tr></thead>
  <tbody>
    <tr><td>Infogerance serveurs <span class="tag">Cloud</span></td><td>Service</td><td>3</td><td>85 000</td><td>3 060 000</td></tr>
    <tr><td>Licences Microsoft 365 <span class="tag">SaaS</span></td><td>Licence</td><td>25</td><td>12 000</td><td>3 600 000</td></tr>
    <tr><td>Securite EDR/SOC <span class="tag">Cybersec</span></td><td>Service</td><td>1</td><td>120 000</td><td>1 440 000</td></tr>
    <tr><td>Support N1/N2/N3 illimite <span class="tag">Helpdesk</span></td><td>Forfait</td><td>1</td><td>65 000</td><td>780 000</td></tr>
  </tbody>
</table>
<div class="total-bar">
  <div><div class="tl-label">Total annuel TTC (TVA 18%)</div><div style="font-size:9px;color:#475569;margin-top:2px">Facturation trimestrielle · Prelevement automatique</div></div>
  <div><div class="tl-amount">10 449 000 XOF</div><div class="tl-period">874 250 XOF / mois</div></div>
</div>
</div></div>`)
}

// ── Routeur principal — factproType en priorite, catId en fallback pour invoices
function previewHTML(doc) {
  // Lettres/relances — override total
  const lettreIds = ['relance', 'mise_dem', 'accuse']
  if (lettreIds.includes(doc.id)) return previewLettre(doc)

  // Routage par factproType (type metier du document)
  switch (doc.factproType) {

    // Rapports, fiches, etudes, attestations, certificats (249 docs)
    case 'service_report':
      // Exceptions : categories ayant leurs propres templates specialises
      switch (doc.catId) {
        case 'rh':        return previewHR(doc)
        case 'admin':     return previewAdmin(doc)
        case 'sav':       return previewSAV(doc)
        case 'btp':       return previewBTP(doc)
        case 'stock':     return previewStock(doc)
        case 'immobilier':return previewImmobilier(doc)
        case 'export':    return previewExport(doc)
        case 'sante':     return previewSante(doc)
        case 'education': return previewEducation(doc)
        default:          return previewServiceReport(doc)
      }

    // Bons de caisse, vouchers, bons divers
    case 'cash_voucher':
      return previewVoucher(doc)

    // Notes de frais, etats de depenses
    case 'expense_report':
      return previewExpenseReport(doc)

    // Reception de marchandises, entree stock
    case 'goods_receipt':
      return previewGoodsReceipt(doc)

    // Bons de commande fournisseur
    case 'supplier_order':
      return previewSupplierOrder(doc)

    // Bons de commande client
    case 'purchase_order':
      return previewCommande(doc)

    // Bons de livraison, expeditions, transferts
    case 'delivery_note':
      return previewDelivery(doc)

    // Reçus, tickets, quittances
    case 'receipt':
      if (doc.catId === 'resto') return previewResto(doc)
      return previewRecu(doc)

    // Avoirs, notes de credit, retours
    case 'credit_note':
      return previewAvoir(doc)

    // Bulletins de paie
    case 'payslip':
      return previewHR(doc)

    // Devis, offres, propositions, proformas
    case 'quote':
    case 'proforma_invoice':
      return previewInvoice(doc)  // isDevis=true logique interne

    // Factures (invoice, advance_invoice, balance_invoice)
    case 'invoice':
    case 'advance_invoice':
    case 'balance_invoice':
      switch (doc.catId) {
        case 'rh':         return previewHR(doc)
        case 'admin':      return previewAdmin(doc)
        case 'finance':    return previewFinance(doc)
        case 'banq':       return previewFinance(doc)
        case 'enrg':       return previewFinance(doc)
        case 'sav':        return previewSAV(doc)
        case 'btp':        return previewBTP(doc)
        case 'immobilier': return previewImmobilier(doc)
        case 'resto':      return previewResto(doc)
        case 'garage':     return previewGarage(doc)
        case 'it':         return previewIT(doc)
        case 'sante':      return previewSante(doc)
        case 'education':  return previewEducation(doc)
        default:           return previewInvoice(doc)
      }

    default:
      // Fallback par catId
      switch (doc.catId) {
        case 'rh':         return previewHR(doc)
        case 'admin':      return previewAdmin(doc)
        case 'sav':        return previewSAV(doc)
        case 'btp':        return previewBTP(doc)
        case 'stock':      return previewStock(doc)
        case 'immobilier': return previewImmobilier(doc)
        case 'export':     return previewExport(doc)
        case 'sante':      return previewSante(doc)
        case 'education':  return previewEducation(doc)
        case 'finance':    return previewFinance(doc)
        case 'logistique': return previewDelivery(doc)
        case 'resto':      return previewResto(doc)
        case 'garage':     return previewGarage(doc)
        case 'it':         return previewIT(doc)
        case 'agri':       return previewServiceReport(doc)
        case 'enrg':       return previewFinance(doc)
        case 'banq':       return previewFinance(doc)
        case 'ong':        return previewServiceReport(doc)
        case 'cons':       return previewServiceReport(doc)
        case 'tour':       return previewVoucher(doc)
        case 'pharm':      return previewSante(doc)
        case 'mine':       return previewExport(doc)
        default:           return previewInvoice(doc)
      }
  }
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
                  @click="openPreview({...doc, catId: cat.id, catLabel: cat.label, catColor: cat.color, catIcon: cat.icon})"
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

            <!-- Bandeau aperçu + modèle recommandé -->
            <div class="flex flex-col sm:flex-row sm:items-center gap-2 bg-amber-50 border-y border-amber-200 px-4 py-2.5">
              <div class="flex items-center gap-2 flex-1">
                <svg class="h-4 w-4 flex-shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-xs text-amber-700 font-medium">
                  Aperçu indicatif — le document réel sera personnalisé avec vos données.
                </p>
              </div>
              <div class="flex items-center gap-1.5 bg-white border border-amber-200 rounded-lg px-2.5 py-1 shrink-0">
                <svg class="h-3.5 w-3.5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span class="text-xs text-gray-500">Modèle PDF :</span>
                <span class="text-xs font-bold text-blue-700">{{ recommendedTemplate(preview).name }}</span>
              </div>
            </div>

            <!-- Iframe -->
            <div class="flex-1 overflow-hidden">
              <iframe
                class="h-full w-full border-none"
                style="min-height:480px"
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
<?php

namespace App\Services;

use App\Models\Document;

class DocumentEngine
{
    /**
     * Map des types de documents vers leur configuration moteur.
     */
    private array $map = [
        // Factures et dérivés
        'invoice'             => 'invoice',
        'credit_note'         => 'invoice',
        'proforma'            => 'invoice',
        'advance_invoice'     => 'invoice',
        'deposit_invoice'     => 'invoice',
        'recurring_invoice'   => 'invoice',
        'final_invoice'       => 'invoice',
        'corrective_invoice'  => 'invoice',
        'tax_invoice'         => 'invoice',
        'commercial_invoice'  => 'invoice',

        // Devis
        'quote'               => 'quote',
        'price_offer'         => 'quote',
        'service_quote'       => 'quote',
        'work_quote'          => 'quote',
        'repair_estimate'     => 'quote',

        // Livraison (sans prix)
        'delivery_note'       => 'delivery',
        'packing_list'        => 'delivery',
        'shipping_order'      => 'delivery',
        'picking_list'        => 'delivery',
        'transfer_note'       => 'delivery',
        'goods_receipt'       => 'delivery',
        'return_note'         => 'delivery',
        'goods_return'        => 'delivery',

        // Bons de commande
        'purchase_order'      => 'purchase_order',
        'supplier_order'      => 'purchase_order',
        'rfq'                 => 'purchase_order',

        // Reçus de paiement
        'payment_receipt'     => 'receipt',
        'cash_receipt'        => 'receipt',
        'petty_cash_receipt'  => 'receipt',
        'advance_receipt'     => 'receipt',
        'refund_receipt'      => 'receipt',

        // Contrats
        'contract'            => 'contract',
        'service_contract'    => 'contract',
        'lease_agreement'     => 'contract',
        'maintenance_contract'=> 'contract',
        'partnership_agreement' => 'contract',
        'nda'                 => 'contract',
        'framework_agreement' => 'contract',
        'subcontracting_contract' => 'contract',

        // PV / Compte-rendu
        'meeting_minutes'     => 'minutes',
        'pv_reception'        => 'minutes',
        'pv_handover'         => 'minutes',
        'acceptance_report'   => 'minutes',
        'conflict_pv'         => 'minutes',
        'general_assembly_pv' => 'minutes',

        // Ordres de mission
        'mission_order'       => 'mission_order',
        'travel_request'      => 'mission_order',
        'expense_report'      => 'mission_order',

        // Bulletins de paie
        'payslip'             => 'payslip',

        // Ordonnances
        'prescription'        => 'prescription',
        'medical_certificate' => 'prescription',
        'medical_report'      => 'prescription',

        // Bulletins de notes
        'report_card'         => 'report_card',
        'school_certificate'  => 'report_card',
        'grade_sheet'         => 'report_card',

        // Rapports de chantier
        'site_report'         => 'site_report',
        'inspection_report'   => 'site_report',
        'progress_report'     => 'site_report',
        'daily_report'        => 'site_report',

        // États des lieux
        'rental_inventory'    => 'inventory_check',
        'inventory_check'     => 'inventory_check',
        'property_inspection' => 'inventory_check',

        // Demandes de congé
        'leave_request'       => 'leave_request',
        'leave_balance'       => 'leave_request',
        'hr_certificate'      => 'leave_request',
        'employment_certificate' => 'leave_request',
    ];

    /**
     * Configuration par template structurel.
     */
    private array $configs = [
        'invoice' => [
            'template'      => 'pdf.engine.invoice',
            'format'        => 'a4',
            'orientation'   => 'portrait',
            'primary_color' => '#1e3a5f',
            'signature_labels' => ['Émetteur', 'Destinataire'],
        ],
        'quote' => [
            'template'      => 'pdf.engine.quote',
            'format'        => 'a4',
            'orientation'   => 'portrait',
            'primary_color' => '#166534',
            'signature_labels' => ['Émetteur', 'Bon pour accord'],
        ],
        'delivery' => [
            'template'      => 'pdf.engine.delivery',
            'format'        => 'a4',
            'orientation'   => 'portrait',
            'primary_color' => '#c2410c',
            'signature_labels' => ['Signature Livreur', 'Signature Destinataire'],
        ],
        'purchase_order' => [
            'template'      => 'pdf.engine.purchase_order',
            'format'        => 'a4',
            'orientation'   => 'portrait',
            'primary_color' => '#7c3aed',
            'signature_labels' => ['Commandeur', 'Bon pour réception'],
        ],
        'receipt' => [
            'template'      => 'pdf.engine.receipt',
            'format'        => 'a4',
            'orientation'   => 'portrait',
            'primary_color' => '#0f766e',
            'signature_labels' => ['Signature Émetteur'],
        ],
        'contract' => [
            'template'      => 'pdf.engine.contract',
            'format'        => 'a4',
            'orientation'   => 'portrait',
            'primary_color' => '#1e293b',
            'signature_labels' => ['Partie A', 'Partie B'],
        ],
        'minutes' => [
            'template'      => 'pdf.engine.minutes',
            'format'        => 'a4',
            'orientation'   => 'portrait',
            'primary_color' => '#1e293b',
            'signature_labels' => ['Président de séance', 'Secrétaire'],
        ],
        'mission_order' => [
            'template'      => 'pdf.engine.mission_order',
            'format'        => 'a4',
            'orientation'   => 'portrait',
            'primary_color' => '#1e3a5f',
            'signature_labels' => ['RH', 'Directeur', 'DG'],
        ],
        'payslip' => [
            'template'      => 'pdf.engine.document-payslip',
            'format'        => 'a4',
            'orientation'   => 'portrait',
            'primary_color' => '#1a56db',
            'signature_labels' => ['Employeur', 'Employé'],
        ],
        'prescription' => [
            'template'      => 'pdf.engine.prescription',
            'format'        => 'a5',
            'orientation'   => 'portrait',
            'primary_color' => '#0369a1',
            'signature_labels' => ['Médecin'],
        ],
        'report_card' => [
            'template'      => 'pdf.engine.report_card',
            'format'        => 'a4',
            'orientation'   => 'landscape',
            'primary_color' => '#7c3aed',
            'signature_labels' => ['Prof. Principal', 'Directeur', 'Parent'],
        ],
        'site_report' => [
            'template'      => 'pdf.engine.site_report',
            'format'        => 'a4',
            'orientation'   => 'portrait',
            'primary_color' => '#c2410c',
            'signature_labels' => ['Chef de chantier', 'Conducteur de travaux'],
        ],
        'inventory_check' => [
            'template'      => 'pdf.engine.inventory_check',
            'format'        => 'a4',
            'orientation'   => 'portrait',
            'primary_color' => '#0f766e',
            'signature_labels' => ['Bailleur', 'Locataire'],
        ],
        'leave_request' => [
            'template'      => 'pdf.engine.leave_request',
            'format'        => 'a4',
            'orientation'   => 'portrait',
            'primary_color' => '#0284c7',
            'signature_labels' => ['N+1', 'RH', 'DG'],
        ],
    ];

    /**
     * Résout la configuration du moteur documentaire pour un document donné.
     *
     * @return array{template: string, format: string, orientation: string, primary_color: string, signature_labels: array}
     */
    public function resolve(Document $document): array
    {
        $type = $document->type ?? 'invoice';
        $engineKey = $this->map[$type] ?? 'invoice';
        return $this->configs[$engineKey];
    }
}

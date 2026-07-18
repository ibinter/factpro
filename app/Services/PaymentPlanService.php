<?php

namespace App\Services;

use App\Models\Document;
use App\Models\PaymentPlan;
use App\Models\PaymentPlanInstallment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Acomptes & plans de paiement échelonnés (cahier IBIG §12).
 *
 * S'appuie exclusivement sur DocumentService pour matérialiser les factures
 * d'acompte / de solde — aucune écriture directe sur les documents.
 */
class PaymentPlanService
{
    /** Tolérance d'arrondi acceptée entre la somme des échéances et le total. */
    private const TOLERANCE = 0.05;

    public function __construct(private DocumentService $documents)
    {
    }

    /**
     * Crée un plan de paiement depuis un document source (devis ou facture).
     *
     * @param  array<int, array{label: string, due_date: string, amount?: float|string, percentage?: float|string}>  $installments
     */
    public function createFromDocument(Document $source, array $installments, User $user): PaymentPlan
    {
        if (count($installments) < 1) {
            throw new RuntimeException('Un plan doit comporter au moins une échéance.');
        }

        $total = round((float) $source->total, 2);

        // Résolution des montants (depuis un pourcentage du total si fourni)
        $resolved = [];
        $sum = 0.0;
        foreach (array_values($installments) as $index => $row) {
            $percentage = isset($row['percentage']) && $row['percentage'] !== null && $row['percentage'] !== ''
                ? (float) $row['percentage']
                : null;

            $amount = $percentage !== null
                ? round($total * $percentage / 100, 2)
                : round((float) ($row['amount'] ?? 0), 2);

            $resolved[] = [
                'label' => $row['label'],
                'due_date' => $row['due_date'],
                'amount' => $amount,
                'percentage' => $percentage,
                'sort_order' => $index,
            ];
            $sum += $amount;
        }

        if (abs(round($sum, 2) - $total) > self::TOLERANCE) {
            throw new RuntimeException('La somme des échéances doit égaler le total.');
        }

        return DB::transaction(function () use ($source, $resolved, $user, $total) {
            $plan = PaymentPlan::create([
                'company_id' => $source->company_id,
                'customer_id' => $source->customer_id,
                'source_document_id' => $source->id,
                'name' => 'Plan '.$source->number,
                'total_amount' => $total,
                'currency' => $source->currency,
                'status' => 'active',
                'created_by' => $user->id,
            ]);

            foreach ($resolved as $row) {
                $plan->installments()->create([
                    'label' => $row['label'],
                    'due_date' => $row['due_date'],
                    'amount' => $row['amount'],
                    'percentage' => $row['percentage'],
                    'status' => 'pending',
                    'sort_order' => $row['sort_order'],
                ]);
            }

            return $plan->fresh(['installments', 'customer', 'sourceDocument']);
        });
    }

    /**
     * Matérialise une échéance en facture d'acompte (ou de solde pour la dernière).
     *
     * Choix comptable : l'acompte porte sur un montant TTC déjà taxé au niveau du
     * document source ; la ligne générée a donc tax_rate 0 pour ne pas re-taxer.
     */
    public function generateInstallmentInvoice(PaymentPlanInstallment $installment, ?User $user = null): Document
    {
        if ($installment->document_id !== null) {
            throw new RuntimeException('Une facture a déjà été générée pour cette échéance.');
        }

        $plan = $installment->plan()->with(['installments', 'company', 'sourceDocument'])->firstOrFail();
        $company = $plan->company;
        $user ??= $plan->creator ?? $company->owner;

        // Dernière échéance (sort_order max) → facture de solde, sinon acompte.
        $lastOrder = (int) $plan->installments->max('sort_order');
        $isBalance = (int) $installment->sort_order === $lastOrder;
        $type = $isBalance ? 'balance_invoice' : 'deposit_invoice';

        $sourceNumber = $plan->sourceDocument?->number;
        $prefix = $isBalance ? 'Solde' : 'Acompte';
        $description = $prefix.' '.$installment->label
            .($sourceNumber ? ' — réf '.$sourceNumber : '');

        return DB::transaction(function () use ($installment, $plan, $company, $user, $type, $description) {
            $document = $this->documents->create($company, $user, [
                'type' => $type,
                'customer_id' => $plan->customer_id,
                'parent_id' => $plan->source_document_id,
                'reference' => 'PLAN-'.$plan->id,
                'issue_date' => now()->toDateString(),
                'due_date' => $installment->due_date->toDateString(),
                'currency' => $plan->currency,
            ], [[
                'description' => $description,
                'quantity' => 1,
                'unit' => 'unité',
                'unit_price' => (float) $installment->amount,
                'discount_percent' => 0,
                'tax_rate' => 0,
            ]]);

            $this->documents->finalize($document);

            $installment->update([
                'status' => 'invoiced',
                'document_id' => $document->id,
            ]);

            return $document;
        });
    }

    /** Marque une échéance payée puis met à jour le statut du plan. */
    public function markInstallmentPaid(PaymentPlanInstallment $installment): PaymentPlan
    {
        $installment->update(['status' => 'paid']);

        return $this->refreshPlanStatus($installment->plan);
    }

    /** Passe le plan à « completed » quand toutes les échéances sont payées. */
    public function refreshPlanStatus(PaymentPlan $plan): PaymentPlan
    {
        $plan->loadMissing('installments');

        if ($plan->status === 'cancelled') {
            return $plan;
        }

        $allPaid = $plan->installments->isNotEmpty()
            && $plan->installments->every(fn (PaymentPlanInstallment $i) => $i->status === 'paid');

        $plan->update(['status' => $allPaid ? 'completed' : 'active']);

        return $plan->fresh(['installments']);
    }
}

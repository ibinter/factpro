<?php

namespace App\Services;

use App\Models\AutoReorderRule;
use App\Models\Company;
use App\Models\Document;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoReorderService
{
    public function __construct(
        private DocumentService $documentService,
        private DocumentNumberService $numbers,
    ) {}

    /**
     * Vérifie tous les produits d'une company et déclenche les BOC nécessaires.
     *
     * @return array{triggered: int, skipped: int, errors: int}
     */
    public function checkAndTrigger(int $companyId): array
    {
        $rules = AutoReorderRule::where('company_id', $companyId)
            ->where('is_active', true)
            ->with(['product', 'supplier', 'company.owner'])
            ->get();

        $triggered = 0;
        $skipped   = 0;
        $errors    = 0;

        foreach ($rules as $rule) {
            try {
                $product = $rule->product;

                if ((float) $product->stock_quantity > $rule->trigger_threshold) {
                    $skipped++;
                    continue;
                }

                if ($rule->isInCooldown()) {
                    $skipped++;
                    continue;
                }

                $this->createPurchaseOrder($rule);
                $triggered++;
            } catch (\Throwable $e) {
                Log::error('AutoReorder: erreur sur règle #'.$rule->id, ['error' => $e->getMessage()]);
                $errors++;
            }
        }

        return compact('triggered', 'skipped', 'errors');
    }

    /**
     * Crée un BOC (purchase_order Document) pour une règle de réapprovisionnement.
     */
    public function createPurchaseOrder(AutoReorderRule $rule): Document
    {
        return DB::transaction(function () use ($rule) {
            $company = $rule->company;
            $product = $rule->product;
            $supplier = $rule->supplier;

            // On utilise le propriétaire de la company comme créateur
            $user = $company->owner;

            $notes = 'BOC automatique — Seuil: '.$rule->trigger_threshold
                .' | Stock actuel: '.(float) $product->stock_quantity;

            if ($supplier) {
                $notes .= ' | Fournisseur: '.$supplier->name;
            }

            if ($rule->notes) {
                $notes .= "\n".$rule->notes;
            }

            $document = $this->documentService->create(
                $company,
                $user,
                [
                    'type'       => 'purchase_order',
                    'issue_date' => now()->toDateString(),
                    'currency'   => $company->currency ?? 'XOF',
                    'notes'      => $notes,
                    'status'     => 'draft',
                ],
                [
                    [
                        'product_id'  => $product->id,
                        'description' => $product->name,
                        'quantity'    => $rule->order_quantity,
                        'unit_price'  => (float) $product->cost,
                        'unit'        => $product->unit ?? 'unité',
                        'tax_rate'    => 0,
                    ],
                ]
            );

            if ($rule->auto_approve) {
                $this->documentService->finalize($document);
                $document->refresh();
            }

            $rule->update([
                'last_triggered_at' => now(),
                'last_document_id'  => $document->id,
            ]);

            return $document;
        });
    }

    /**
     * Retourne les produits sous leur seuil de réapprovisionnement pour une company.
     */
    public function getLowStockProducts(int $companyId): Collection
    {
        return AutoReorderRule::where('company_id', $companyId)
            ->where('is_active', true)
            ->with(['product', 'supplier'])
            ->get()
            ->filter(fn ($rule) => (float) $rule->product->stock_quantity <= $rule->trigger_threshold)
            ->values();
    }

    /**
     * Simule le déclenchement sans créer de BOC (preview).
     *
     * @return array{would_trigger: int, products: array}
     */
    public function simulate(int $companyId): array
    {
        $rules = AutoReorderRule::where('company_id', $companyId)
            ->where('is_active', true)
            ->with(['product', 'supplier'])
            ->get();

        $products = [];

        foreach ($rules as $rule) {
            $product = $rule->product;
            $wouldTrigger = (float) $product->stock_quantity <= $rule->trigger_threshold;

            $products[] = [
                'rule_id'           => $rule->id,
                'product_id'        => $product->id,
                'product_name'      => $product->name,
                'stock_quantity'    => (float) $product->stock_quantity,
                'trigger_threshold' => $rule->trigger_threshold,
                'order_quantity'    => $rule->order_quantity,
                'supplier_name'     => $rule->supplier?->name,
                'in_cooldown'       => $rule->isInCooldown(),
                'would_trigger'     => $wouldTrigger && ! $rule->isInCooldown(),
            ];
        }

        $wouldTriggerCount = collect($products)->where('would_trigger', true)->count();

        return [
            'would_trigger' => $wouldTriggerCount,
            'products'      => $products,
        ];
    }
}

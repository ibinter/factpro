<?php

namespace App\Services;

use App\Models\Document;
use App\Models\Product;
use App\Models\StockMovement;
use App\Notifications\LowStockAlert;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Gestion des stocks (cahier des charges §8).
 * Contrat partagé : POS et documents s'appuient sur ce service.
 */
class StockService
{
    /**
     * Enregistre un mouvement et met à jour le stock du produit, de façon atomique.
     * $type : in | out | adjustment | inventory. $quantity toujours positive
     * (pour adjustment/inventory, $attrs['target'] donne le stock cible).
     */
    public function record(Product $product, string $type, float $quantity, array $attrs = []): StockMovement
    {
        return DB::transaction(function () use ($product, $type, $quantity, $attrs) {
            $product = Product::whereKey($product->id)->lockForUpdate()->first();
            $before = (float) $product->stock_quantity;

            $after = match ($type) {
                'in' => $before + $quantity,
                'out' => $before - $quantity,
                'adjustment', 'inventory' => (float) ($attrs['target'] ?? $before),
                default => throw new \InvalidArgumentException("Type de mouvement inconnu : {$type}"),
            };

            // CMUP : recalcul du coût moyen pondéré sur les entrées valorisées
            if ($type === 'in' && isset($attrs['unit_cost']) && ($before + $quantity) > 0) {
                $newCost = (($before * (float) $product->cost) + ($quantity * (float) $attrs['unit_cost']))
                    / ($before + $quantity);
                $product->cost = round(max($newCost, 0), 2);
            }

            $product->stock_quantity = $after;
            $product->save();

            return StockMovement::create([
                'company_id' => $product->company_id,
                'product_id' => $product->id,
                'document_id' => $attrs['document_id'] ?? null,
                'type' => $type,
                'quantity' => in_array($type, ['adjustment', 'inventory']) ? abs($after - $before) : $quantity,
                'stock_before' => $before,
                'stock_after' => $after,
                'unit_cost' => $attrs['unit_cost'] ?? null,
                'reason' => $attrs['reason'] ?? null,
                'created_by' => $attrs['created_by'] ?? auth()->id(),
            ]);
        });
    }

    /** Débite le stock pour un document (facture / ticket POS). Idempotent par document. */
    public function debitForDocument(Document $document): void
    {
        if (StockMovement::where('document_id', $document->id)->exists()) {
            return; // déjà traité
        }

        $document->loadMissing('lines.product');

        foreach ($document->lines as $line) {
            if ($line->product && $line->product->track_stock) {
                $this->record($line->product, 'out', (float) $line->quantity, [
                    'document_id' => $document->id,
                    'reason' => $document->type_label.' '.$document->number,
                ]);
            }
        }
    }

    /**
     * Notifie le propriétaire de la société pour chaque produit du document
     * passé sous son seuil d'alerte. Dédupliqué : 1 notification / produit / jour.
     */
    public function notifyLowStockFor(Document $document): void
    {
        $document->loadMissing('lines', 'company.owner');

        $owner = $document->company?->owner;
        if (! $owner) {
            return;
        }

        foreach ($document->lines as $line) {
            if (! $line->product_id) {
                continue;
            }

            $product = Product::find($line->product_id); // état frais, post-débit
            if (! $product
                || ! $product->track_stock
                || $product->stock_alert_threshold === null
                || (float) $product->stock_quantity > (float) $product->stock_alert_threshold) {
                continue;
            }

            Cache::remember("lowstock:{$product->id}", now()->addDay(), function () use ($owner, $product) {
                $owner->notify(new LowStockAlert($product));

                return now()->toIso8601String();
            });
        }
    }

    /** Crédite le stock pour un avoir / retour. Idempotent par document. */
    public function creditForDocument(Document $document): void
    {
        if (StockMovement::where('document_id', $document->id)->exists()) {
            return;
        }

        $document->loadMissing('lines.product');

        foreach ($document->lines as $line) {
            if ($line->product && $line->product->track_stock) {
                $this->record($line->product, 'in', (float) $line->quantity, [
                    'document_id' => $document->id,
                    'reason' => $document->type_label.' '.$document->number,
                ]);
            }
        }
    }
}

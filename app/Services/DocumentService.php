<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Document;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DocumentService
{
    public function __construct(
        private DocumentNumberService $numbers,
        private DocumentIntegrityService $integrity,
        private LicenseService $licenses,
    ) {
    }

    /** Crée un document avec ses lignes et calcule les totaux. */
    public function create(Company $company, User $user, array $data, array $lines): Document
    {
        return DB::transaction(function () use ($company, $user, $data, $lines) {
            $document = Document::create([
                ...$data,
                'company_id' => $company->id,
                'number' => $this->numbers->next($company, $data['type']),
                'created_by' => $user->id,
                'trial_watermark' => $this->licenses->needsTrialWatermark($user),
            ]);

            $this->syncLines($document, $lines);
            $this->recalculate($document);

            return $document->fresh(['lines', 'customer']);
        });
    }

    /** Met à jour un document non finalisé. */
    public function update(Document $document, array $data, array $lines): Document
    {
        return DB::transaction(function () use ($document, $data, $lines) {
            $document->update($data);
            $document->lines()->delete();
            $this->syncLines($document, $lines);
            $this->recalculate($document);

            return $document->fresh(['lines', 'customer']);
        });
    }

    private function syncLines(Document $document, array $lines): void
    {
        foreach (array_values($lines) as $index => $line) {
            $quantity = (float) ($line['quantity'] ?? 1);
            $unitPrice = (float) ($line['unit_price'] ?? 0);
            $discount = (float) ($line['discount_percent'] ?? 0);
            $lineTotal = round($quantity * $unitPrice * (1 - $discount / 100), 2);

            $document->lines()->create([
                'product_id' => $line['product_id'] ?? null,
                'description' => $line['description'] ?? '',
                'quantity' => $quantity,
                'unit' => $line['unit'] ?? 'unité',
                'unit_price' => $unitPrice,
                'discount_percent' => $discount,
                'tax_rate' => (float) ($line['tax_rate'] ?? 0),
                'line_total' => $lineTotal,
                'sort_order' => $index,
            ]);
        }
    }

    /** Recalcule sous-total, remise globale, TVA et total. */
    public function recalculate(Document $document): void
    {
        $document->loadMissing('lines');

        $subtotal = $document->lines->sum(fn ($l) => (float) $l->line_total);

        $discountAmount = match ($document->discount_type) {
            'percent' => round($subtotal * ((float) $document->discount_value) / 100, 2),
            'fixed' => min((float) $document->discount_value, $subtotal),
            default => 0.0,
        };

        $baseAfterDiscount = $subtotal - $discountAmount;

        // TVA ligne par ligne, au prorata de la remise globale
        $taxAmount = 0.0;
        if ($subtotal > 0) {
            foreach ($document->lines as $line) {
                $share = (float) $line->line_total / $subtotal;
                $taxable = $baseAfterDiscount * $share;
                $taxAmount += $taxable * ((float) $line->tax_rate) / 100;
            }
        }
        $taxAmount = round($taxAmount, 2);

        $document->forceFill([
            'subtotal' => round($subtotal, 2),
            'discount_amount' => $discountAmount,
            'tax_amount' => $taxAmount,
            'total' => round($baseAfterDiscount + $taxAmount, 2),
        ])->save();
    }

    /** Convertit un document en un autre type (devis→facture, etc. — cahier §4). */
    public function convert(Document $source, string $targetType, User $user): Document
    {
        return DB::transaction(function () use ($source, $targetType, $user) {
            $copy = $source->replicate([
                'uuid', 'number', 'status', 'integrity_hash', 'finalized_at',
                'sent_at', 'amount_paid',
            ]);
            $copy->type = $targetType;
            $copy->status = 'draft';
            $copy->parent_id = $source->id;
            $copy->number = $this->numbers->next($source->company, $targetType);
            $copy->issue_date = now();
            $copy->created_by = $user->id;
            $copy->trial_watermark = $this->licenses->needsTrialWatermark($user);
            $copy->save();

            foreach ($source->lines as $line) {
                $copy->lines()->create($line->only([
                    'product_id', 'description', 'quantity', 'unit', 'unit_price',
                    'discount_percent', 'tax_rate', 'line_total', 'sort_order',
                ]));
            }

            if ($source->type === 'quote') {
                $source->update(['status' => 'converted']);
            }

            $this->recalculate($copy);

            return $copy->fresh(['lines', 'customer']);
        });
    }

    /** Finalise et scelle un document (hash SHA-256 + horodatage). */
    public function finalize(Document $document): Document
    {
        if (! $document->isFinalized()) {
            $this->integrity->seal($document);
        }

        // Gestion des stocks (cahier §8) — débit/crédit idempotent par document
        if (in_array($document->type, ['invoice', 'pos_ticket'], true)) {
            $stock = app(StockService::class);
            $stock->debitForDocument($document);
            $stock->notifyLowStockFor($document);
        } elseif ($document->type === 'credit_note') {
            app(StockService::class)->creditForDocument($document);
        }

        return $document;
    }

    /** Enregistre un paiement et met à jour le statut. */
    public function registerPayment(Document $document, array $data, User $user): void
    {
        DB::transaction(function () use ($document, $data, $user) {
            $document->payments()->create([
                ...$data,
                'company_id' => $document->company_id,
                'created_by' => $user->id,
            ]);

            $paid = $document->payments()->sum('amount');
            $document->update([
                'amount_paid' => $paid,
                'status' => $paid >= (float) $document->total ? 'paid' : 'partial',
            ]);
        });
    }
}

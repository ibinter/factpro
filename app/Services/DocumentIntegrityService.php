<?php

namespace App\Services;

use App\Models\Document;

/**
 * Anti-falsification (cahier des charges §5) :
 * hash SHA-256 du contenu complet du document, enregistré à la finalisation.
 * Toute modification ultérieure du contenu rend le hash invalide → détection.
 */
class DocumentIntegrityService
{
    /** Représentation canonique du contenu à sceller. */
    public function canonicalPayload(Document $document): array
    {
        $document->loadMissing(['lines', 'customer', 'company']);

        return [
            'company' => $document->company?->name,
            'type' => $document->type,
            'number' => $document->number,
            'issue_date' => $document->issue_date?->toDateString(),
            'due_date' => $document->due_date?->toDateString(),
            'customer' => $document->customer?->name,
            'currency' => $document->currency,
            'subtotal' => (string) $document->subtotal,
            'discount_amount' => (string) $document->discount_amount,
            'tax_amount' => (string) $document->tax_amount,
            'total' => (string) $document->total,
            'lines' => $document->lines->map(fn ($line) => [
                'description' => $line->description,
                'quantity' => (string) $line->quantity,
                'unit_price' => (string) $line->unit_price,
                'discount_percent' => (string) $line->discount_percent,
                'tax_rate' => (string) $line->tax_rate,
                'line_total' => (string) $line->line_total,
            ])->values()->all(),
        ];
    }

    public function computeHash(Document $document): string
    {
        return hash('sha256', json_encode(
            $this->canonicalPayload($document),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        ));
    }

    /** Scelle le document : hash + horodatage. À appeler à l'émission (envoi / finalisation). */
    public function seal(Document $document): Document
    {
        $document->forceFill([
            'integrity_hash' => $this->computeHash($document),
            'finalized_at' => $document->finalized_at ?? now(),
        ])->save();

        return $document;
    }

    /** Vérifie l'intégrité : le contenu actuel correspond-il au hash scellé ? */
    public function verify(Document $document): bool
    {
        if (! $document->integrity_hash) {
            return false;
        }

        return hash_equals($document->integrity_hash, $this->computeHash($document));
    }
}

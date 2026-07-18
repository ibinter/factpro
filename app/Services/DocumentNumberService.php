<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Document;
use App\Models\DocumentSequence;
use Illuminate\Support\Facades\DB;

class DocumentNumberService
{
    /**
     * Génère le prochain numéro pour un type de document, de façon atomique.
     * Format : {PREFIX}-{YYYY}-{NNNN} — ex. FAC-2026-0001
     */
    public function next(Company $company, string $type): string
    {
        $year = now()->year;
        $prefix = Document::TYPES[$type]['prefix'] ?? strtoupper(substr($type, 0, 3));

        return DB::transaction(function () use ($company, $type, $year, $prefix) {
            $sequence = DocumentSequence::where('company_id', $company->id)
                ->where('document_type', $type)
                ->where('year', $year)
                ->lockForUpdate()
                ->first();

            if (! $sequence) {
                $sequence = DocumentSequence::create([
                    'company_id' => $company->id,
                    'document_type' => $type,
                    'prefix' => $prefix,
                    'next_number' => 1,
                    'padding' => 4,
                    'year' => $year,
                ]);
            }

            $number = $sequence->next_number;
            $sequence->increment('next_number');

            return sprintf(
                '%s-%d-%s',
                $sequence->prefix,
                $year,
                str_pad((string) $number, $sequence->padding, '0', STR_PAD_LEFT)
            );
        });
    }
}

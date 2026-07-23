<?php

namespace App\Services;

use App\Models\Company;
use App\Models\VaultDocument;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class VaultService
{
    /**
     * Archive a document into the vault.
     *
     * @param Company     $company
     * @param string      $sourcePath     Relative path in Storage (e.g. returned by Storage::path())
     * @param string      $documentType   invoice | contract | payslip | ged
     * @param array       $metadata
     * @param int         $retentionYears
     * @param Model|null  $source         Eloquent source model (polymorphic)
     */
    public function archive(
        Company $company,
        string $sourcePath,
        string $documentType,
        array $metadata = [],
        int $retentionYears = 10,
        ?Model $source = null
    ): VaultDocument {
        $absoluteSource = Storage::path($sourcePath);

        // 1. SHA-256 du fichier source
        $fileHash = hash_file('sha256', $absoluteSource);

        // 2. Destination : storage/vault/{company_id}/{year}/{hash_prefix}_{filename}
        $year        = now()->year;
        $fileName    = basename($sourcePath);
        $destination = "vault/{$company->id}/{$year}/{$fileHash}_{$fileName}";
        Storage::copy($sourcePath, $destination);

        // 3. archive_hash = SHA-256(file_hash + json(metadata) + timestamp)
        $timestamp   = now()->toISOString();
        $archiveHash = hash('sha256', $fileHash . json_encode($metadata) . $timestamp);

        // 4. Création VaultDocument
        $doc = VaultDocument::create([
            'company_id'      => $company->id,
            'document_type'   => $documentType,
            'source_id'       => $source?->getKey(),
            'source_type'     => $source ? get_class($source) : null,
            'title'           => $metadata['title'] ?? $fileName,
            'file_path'       => $destination,
            'file_hash'       => $fileHash,
            'archive_hash'    => $archiveHash,
            'file_size'       => filesize($absoluteSource),
            'mime_type'       => $metadata['mime_type'] ?? 'application/pdf',
            'archived_at'     => now(),
            'retention_until' => now()->addYears($retentionYears)->toDateString(),
            'retention_years' => $retentionYears,
            'is_sealed'       => true,
            'metadata'        => $metadata,
            'seal_certificate'=> hash('sha256', $archiveHash . $company->id . $timestamp),
        ]);

        return $doc;
    }

    /**
     * Verify the integrity of a single vault document.
     */
    public function verify(VaultDocument $doc): array
    {
        $absolutePath = Storage::path($doc->file_path);
        $fileExists   = file_exists($absolutePath);
        $hashMatch    = false;

        if ($fileExists) {
            $currentHash = hash_file('sha256', $absolutePath);
            $hashMatch   = $currentHash === $doc->file_hash;
        }

        return [
            'valid'       => $fileExists && $hashMatch,
            'file_exists' => $fileExists,
            'hash_match'  => $hashMatch,
            'checked_at'  => now()->toISOString(),
        ];
    }

    /**
     * Generate a full integrity report for all documents of a company.
     */
    public function generateIntegrityReport(Company $company): array
    {
        $docs    = VaultDocument::forCompany($company->id)->get();
        $total   = $docs->count();
        $valid   = 0;
        $tampered = 0;
        $missing  = 0;

        foreach ($docs as $doc) {
            $result = $this->verify($doc);
            if (! $result['file_exists']) {
                $missing++;
            } elseif (! $result['hash_match']) {
                $tampered++;
            } else {
                $valid++;
            }
        }

        return [
            'company_id' => $company->id,
            'total'      => $total,
            'valid'      => $valid,
            'tampered'   => $tampered,
            'missing'    => $missing,
            'generated_at' => now()->toISOString(),
        ];
    }
}

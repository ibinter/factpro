<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentArchive;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class ArchiveService
{
    /**
     * Archive un document finalisé :
     * 1. Génère le PDF final via DomPDF
     * 2. Calcule le hash SHA-256 du PDF
     * 3. Signe le hash avec la clé RSA privée
     * 4. Copie le PDF dans storage/app/archives/{company_id}/{year}/{document_number}.pdf
     * 5. Crée l'enregistrement DocumentArchive
     *
     * @throws \Exception si le document n'est pas finalisé ou déjà archivé
     */
    public function archive(Document $document): DocumentArchive
    {
        if (! $document->isFinalized()) {
            throw new \Exception('Seuls les documents finalisés peuvent être archivés.');
        }

        if ($document->archive()->exists()) {
            return $document->archive;
        }

        $document->loadMissing(['lines', 'customer', 'company']);

        // Génère le PDF (passe les variables optionnelles requises par le template)
        $pdf = Pdf::loadView('pdf.document', [
            'document'   => $document,
            'company'    => $document->company,
            'qrDataUri'  => null,
            'watermark'  => null,
        ]);
        $pdfContent = $pdf->output();

        // SHA-256 du PDF
        $hash = hash('sha256', $pdfContent);

        // Signature RSA
        $privateKeyPath = storage_path('app/keys/archive_private.pem');
        if (! file_exists($privateKeyPath)) {
            throw new \Exception('Clé privée d\'archivage introuvable. Exécutez: php artisan archive:generate-keys');
        }

        $privateKey = file_get_contents($privateKeyPath);
        openssl_sign($hash, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        $signatureB64 = base64_encode($signature);

        // Stockage
        $year = now()->format('Y');
        $path = "archives/{$document->company_id}/{$year}/{$document->number}.pdf";
        Storage::put($path, $pdfContent);

        // Fingerprint clé publique
        $publicKeyPath = storage_path('app/keys/archive_public.pem');
        $publicKey = file_get_contents($publicKeyPath);
        $fingerprint = hash('sha256', $publicKey);

        return DocumentArchive::create([
            'document_id'            => $document->id,
            'company_id'             => $document->company_id,
            'archived_at'            => now(),
            'document_hash'          => $hash,
            'pdf_path'               => $path,
            'signature'              => $signatureB64,
            'public_key_fingerprint' => $fingerprint,
            'pdf_size'               => strlen($pdfContent),
        ]);
    }

    /**
     * Vérifie l'intégrité d'un document archivé.
     *
     * @return array{valid: bool, hash_match: bool, signature_valid: bool, error?: string}
     */
    public function verify(DocumentArchive $archive): array
    {
        try {
            $pdfContent = Storage::get($archive->pdf_path);
            if (! $pdfContent) {
                return ['valid' => false, 'hash_match' => false, 'signature_valid' => false, 'error' => 'Fichier introuvable.'];
            }

            $currentHash = hash('sha256', $pdfContent);
            $hashMatch = $currentHash === $archive->document_hash;

            $publicKeyPath = storage_path('app/keys/archive_public.pem');
            $publicKey = file_get_contents($publicKeyPath);
            $signatureValid = openssl_verify(
                $archive->document_hash,
                base64_decode($archive->signature),
                $publicKey,
                OPENSSL_ALGO_SHA256
            ) === 1;

            $archive->update([
                'is_verified'     => $hashMatch && $signatureValid,
                'last_verified_at' => now(),
            ]);

            return [
                'valid'            => $hashMatch && $signatureValid,
                'hash_match'       => $hashMatch,
                'signature_valid'  => $signatureValid,
            ];
        } catch (\Throwable $e) {
            return ['valid' => false, 'hash_match' => false, 'signature_valid' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Export en masse : ZIP de tous les PDF archivés d'une company pour une année.
     */
    public function exportZip(int $companyId, int $year): string
    {
        $archives = DocumentArchive::where('company_id', $companyId)
            ->whereYear('archived_at', $year)
            ->with('document')
            ->get();

        $tempDir = storage_path('app/temp');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $zipPath = $tempDir . "/archive_{$companyId}_{$year}.zip";
        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        foreach ($archives as $archive) {
            $content = Storage::get($archive->pdf_path);
            if ($content) {
                $zip->addFromString(basename($archive->pdf_path), $content);
            }
        }

        // Manifest JSON avec hashes
        $manifest = $archives->map(fn ($a) => [
            'document'    => $a->document->number ?? '',
            'hash'        => $a->document_hash,
            'signature'   => $a->signature,
            'archived_at' => $a->archived_at,
        ]);
        $zip->addFromString('manifest.json', $manifest->toJson(JSON_PRETTY_PRINT));
        $zip->close();

        return $zipPath;
    }

    /**
     * Journal d'audit enrichi pour un document.
     */
    public function getAuditTrail(Document $document): array
    {
        $logs = \App\Models\DocumentAuditLog::where('document_id', $document->id)
            ->with('user:id,name,email')
            ->orderBy('created_at')
            ->get()
            ->map(fn ($log) => [
                'type'       => 'audit',
                'action'     => $log->action,
                'user'       => $log->user ? ['name' => $log->user->name, 'email' => $log->user->email] : null,
                'metadata'   => $log->metadata,
                'occurred_at' => $log->created_at,
            ]);

        $archive = $document->archive;
        $archiveEntry = $archive ? [[
            'type'        => 'archive',
            'action'      => 'archived',
            'hash'        => $archive->document_hash,
            'signature'   => substr($archive->signature, 0, 20) . '...',
            'is_verified' => $archive->is_verified,
            'occurred_at' => $archive->archived_at,
        ]] : [];

        return collect($logs)->concat($archiveEntry)
            ->sortBy('occurred_at')
            ->values()
            ->all();
    }
}

<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Document;
use App\Services\VaultService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class AutoArchiveDocuments extends Command
{
    protected $signature   = 'vault:auto-archive {--company= : ID de la company (toutes si absent)}';
    protected $description = 'Archive automatiquement les documents finalisés des dernières 24h dans le coffre numérique';

    public function __construct(private VaultService $vault)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $companyId = $this->option('company');

        $companies = $companyId
            ? Company::where('id', $companyId)->get()
            : Company::all();

        foreach ($companies as $company) {
            $this->archiveForCompany($company);
        }

        $this->info('Auto-archivage terminé.');
        return self::SUCCESS;
    }

    private function archiveForCompany(Company $company): void
    {
        $documents = Document::where('company_id', $company->id)
            ->whereIn('status', ['paid', 'sent'])
            ->where('updated_at', '>=', now()->subHours(24))
            ->whereDoesntHave('vaultDocuments') // évite les doublons si relation disponible
            ->get();

        if ($documents->isEmpty()) {
            $this->line("  Company #{$company->id}: aucun document à archiver.");
            return;
        }

        $this->info("  Company #{$company->id}: {$documents->count()} document(s) à archiver...");

        foreach ($documents as $document) {
            try {
                // Chemin PDF du document — adapte selon ton implémentation PDF
                $pdfPath = "documents/{$company->id}/{$document->id}.pdf";

                if (! Storage::exists($pdfPath)) {
                    $this->warn("    Document #{$document->id}: fichier PDF introuvable ({$pdfPath}), ignoré.");
                    continue;
                }

                $this->vault->archive(
                    company:        $company,
                    sourcePath:     $pdfPath,
                    documentType:   'invoice',
                    metadata:       [
                        'title'           => $document->number ?? "Document #{$document->id}",
                        'document_number' => $document->number ?? null,
                        'amount'          => $document->total ?? null,
                        'status'          => $document->status,
                        'document_id'     => $document->id,
                    ],
                    retentionYears: 10,
                    source:         $document
                );

                $this->line("    Document #{$document->id} archivé.");
            } catch (\Throwable $e) {
                $this->error("    Erreur pour document #{$document->id}: {$e->getMessage()}");
            }
        }
    }
}

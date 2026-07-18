<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Document;
use App\Services\FacturXService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ExportFacturX extends Command
{
    protected $signature = 'invoices:export-facturx
                            {--company= : ID de la société}
                            {--month=   : Mois au format YYYY-MM (défaut : mois en cours)}
                            {--output=storage/facturx/ : Dossier de sortie}';

    protected $description = 'Exporte les XMLs Factur-X du mois pour une société (ENTERPRISE).';

    public function __construct(private FacturXService $facturX)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $companyId = $this->option('company');
        $month     = $this->option('month') ?? now()->format('Y-m');
        $outputDir = rtrim($this->option('output'), '/\\');

        if (! $companyId) {
            $this->error('L\'option --company est obligatoire.');
            return self::FAILURE;
        }

        $company = Company::find($companyId);
        if (! $company) {
            $this->error("Société #{$companyId} introuvable.");
            return self::FAILURE;
        }

        // Créer le dossier de sortie si besoin
        if (! is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        [$year, $mon] = explode('-', $month);
        $start = \Carbon\Carbon::create((int) $year, (int) $mon, 1)->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        $documents = Document::where('company_id', $company->id)
            ->whereIn('type', ['invoice', 'credit_note'])
            ->whereNotNull('finalized_at')
            ->whereBetween('issue_date', [$start->toDateString(), $end->toDateString()])
            ->get();

        if ($documents->isEmpty()) {
            $this->warn("Aucun document trouvé pour {$month} / société #{$companyId}.");
            return self::SUCCESS;
        }

        $count = 0;
        foreach ($documents as $document) {
            $xml      = $this->facturX->generateXml($document);
            $filename = $outputDir.'/'.$document->number.'-factur-x.xml';
            file_put_contents($filename, $xml);
            $this->line("  ✓ {$document->number} → {$filename}");
            $count++;
        }

        $this->info("{$count} fichier(s) Factur-X exporté(s) dans {$outputDir}/");

        return self::SUCCESS;
    }
}

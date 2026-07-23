<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Employee;
use App\Services\EncryptionService;
use Illuminate\Console\Command;

class EncryptSensitiveData extends Command
{
    protected $signature   = 'security:encrypt-data {--model=all}';
    protected $description = 'Chiffre les données sensibles existantes (AES-256)';

    public function __construct(private EncryptionService $encryption)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $model = $this->option('model');

        if (in_array($model, ['all', 'customers'])) {
            $this->encryptModel(Customer::class, ['email', 'phone', 'address', 'tax_number'], 'Customer');
        }

        if (in_array($model, ['all', 'employees'])) {
            $this->encryptModel(Employee::class, ['cnss_number', 'bank_account', 'national_id'], 'Employee');
        }

        $this->info('Chiffrement terminé.');
        return self::SUCCESS;
    }

    private function encryptModel(string $class, array $fields, string $label): void
    {
        $query = $class::query();
        $total = $query->count();

        if ($total === 0) {
            $this->line("  {$label}: aucun enregistrement.");
            return;
        }

        $this->info("Chiffrement {$label} ({$total} enregistrements)...");
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $query->chunk(200, function ($records) use ($fields, &$bar) {
            foreach ($records as $record) {
                $changed = false;
                foreach ($fields as $field) {
                    $raw = $record->getRawOriginal($field) ?? '';
                    if (!empty($raw) && !$this->encryption->isEncrypted($raw)) {
                        // Force set on raw column to bypass Encryptable trait double-encrypt
                        $record->$field = $raw;
                        $changed        = true;
                    }
                }
                if ($changed) {
                    $record->save();
                }
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();
    }
}

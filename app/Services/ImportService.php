<?php

namespace App\Services;

use League\Csv\Reader;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Company;

class ImportService
{
    private const MAX_ROWS = 1000;

    /**
     * Parse un fichier CSV et retourne les données avec les erreurs de validation.
     *
     * @return array{headers: string[], rows: array[], errors: array[]}
     */
    public function parseCsv(string $filePath): array
    {
        $csv = Reader::createFromPath($filePath, 'r');
        $csv->setHeaderOffset(0);

        $headers = $csv->getHeader();
        $rows    = [];
        $errors  = [];
        $i       = 1;

        foreach ($csv->getRecords() as $record) {
            if ($i > self::MAX_ROWS) {
                $errors[] = "Limite de " . self::MAX_ROWS . " lignes dépassée — les lignes suivantes ont été ignorées.";
                break;
            }
            $rows[] = array_values($record);
            $i++;
        }

        return [
            'headers' => $headers,
            'rows'    => $rows,
            'errors'  => $errors,
        ];
    }

    /**
     * Importe des clients depuis les données parsées.
     *
     * @param  array<string, int>  $columnMap  ['name' => 0, 'email' => 2, ...]
     * @return array{imported: int, skipped: int, errors: array[]}
     */
    public function importCustomers(Company $company, array $rows, array $columnMap): array
    {
        $imported = 0;
        $skipped  = 0;
        $errors   = [];

        foreach ($rows as $lineIndex => $row) {
            $line = $lineIndex + 2; // 1-based + header

            $name = $this->col($row, $columnMap, 'name');
            if ($name === null || $name === '') {
                $errors[] = "Ligne {$line} : le champ Nom est obligatoire.";
                $skipped++;
                continue;
            }

            $email = $this->col($row, $columnMap, 'email');

            // Doublon : même email dans la même company
            if ($email !== null && $email !== '') {
                $exists = Customer::where('company_id', $company->id)
                    ->where('email', $email)
                    ->exists();

                if ($exists) {
                    $errors[] = "Ligne {$line} : email \"{$email}\" déjà présent — ligne ignorée.";
                    $skipped++;
                    continue;
                }
            }

            Customer::create([
                'company_id'   => $company->id,
                'name'         => $name,
                'email'        => $email,
                'phone'        => $this->col($row, $columnMap, 'phone'),
                'address'      => $this->col($row, $columnMap, 'address'),
                'city'         => $this->col($row, $columnMap, 'city'),
                'country'      => $this->colDefault($row, $columnMap, 'country', 'CI'),
                'tax_id'       => $this->col($row, $columnMap, 'tax_id'),
                'currency'     => 'XOF',
            ]);

            $imported++;
        }

        return compact('imported', 'skipped', 'errors');
    }

    /**
     * Importe des produits depuis les données parsées (upsert par référence).
     *
     * @param  array<string, int>  $columnMap
     * @return array{imported: int, skipped: int, errors: array[]}
     */
    public function importProducts(Company $company, array $rows, array $columnMap): array
    {
        $imported = 0;
        $skipped  = 0;
        $errors   = [];

        foreach ($rows as $lineIndex => $row) {
            $line = $lineIndex + 2;

            $name = $this->col($row, $columnMap, 'name');
            if ($name === null || $name === '') {
                $errors[] = "Ligne {$line} : le champ Nom est obligatoire.";
                $skipped++;
                continue;
            }

            $sku    = $this->col($row, $columnMap, 'sku');
            $price  = $this->toFloat($this->col($row, $columnMap, 'price'));
            $taxRaw = $this->col($row, $columnMap, 'tax_rate');
            $taxRate = $taxRaw !== null ? min(100, max(0, $this->toFloat($taxRaw))) : 18.00;

            $data = [
                'company_id'  => $company->id,
                'name'        => $name,
                'sku'         => $sku,
                'description' => $this->col($row, $columnMap, 'description'),
                'price'       => $price ?? 0,
                'unit'        => $this->colDefault($row, $columnMap, 'unit', 'unité'),
                'tax_rate'    => $taxRate,
            ];

            $stockRaw = $this->col($row, $columnMap, 'stock_quantity');
            if ($stockRaw !== null && $stockRaw !== '') {
                $data['stock_quantity'] = $this->toFloat($stockRaw) ?? 0;
                $data['track_stock']    = true;
            }

            // Upsert par SKU si fourni
            if ($sku !== null && $sku !== '') {
                $existing = Product::where('company_id', $company->id)
                    ->where('sku', $sku)
                    ->first();

                if ($existing) {
                    $existing->update($data);
                    $imported++;
                    continue;
                }
            }

            Product::create($data);
            $imported++;
        }

        return compact('imported', 'skipped', 'errors');
    }

    /** Génère un template CSV pour les clients */
    public function customerCsvTemplate(): string
    {
        $headers = ['Nom', 'Email', 'Téléphone', 'Adresse', 'Ville', 'Pays', 'SIRET/RCCM', 'TVA N°'];
        $example = ['Dupont SARL', 'contact@dupont.ci', '+225 07 00 00 00', '12 rue de la Paix', 'Abidjan', 'CI', 'CI-ABJ-2024-001', 'CI-TVA-123456'];

        return implode(',', $headers) . "\n" . implode(',', $example) . "\n";
    }

    /** Génère un template CSV pour les produits */
    public function productCsvTemplate(): string
    {
        $headers = ['Nom', 'Référence', 'Description', 'Prix HT', 'Unité', 'TVA %', 'Stock initial'];
        $example = ['Stylo BIC', 'STY-001', 'Stylo à bille bleu', '500', 'unité', '18', '100'];

        return implode(',', $headers) . "\n" . implode(',', $example) . "\n";
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function col(array $row, array $map, string $field): ?string
    {
        if (! isset($map[$field])) {
            return null;
        }

        $value = $row[$map[$field]] ?? null;

        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

    private function colDefault(array $row, array $map, string $field, string $default): string
    {
        return $this->col($row, $map, $field) ?? $default;
    }

    private function toFloat(?string $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        // Remplacer virgules par points
        $value = str_replace(',', '.', $value);

        // Supprimer espaces et caractères non numériques sauf . et -
        $value = preg_replace('/[^\d.\-]/', '', $value);

        return is_numeric($value) ? (float) $value : null;
    }
}

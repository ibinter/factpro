<?php

namespace App\Services;

class OcrService
{
    /**
     * Extrait le texte d'un PDF ou image via:
     * 1. Google Cloud Vision API si GOOGLE_VISION_KEY configuré
     * 2. Fallback: pdftotext via exec() si disponible
     * 3. Fallback: retourne message d'indisponibilité
     */
    public function extractText(string $filePath): string
    {
        // Priorité 1: Google Vision
        if (config('services.google_vision.key')) {
            return $this->extractViaGoogleVision($filePath);
        }

        // Priorité 2: pdftotext (Linux/XAMPP)
        if (PHP_OS_FAMILY !== 'Windows' && function_exists('shell_exec')) {
            $output = shell_exec('pdftotext '.escapeshellarg($filePath).' -');
            if ($output) {
                return $output;
            }
        }

        // Fallback Windows / sans outil externe
        return '';
    }

    /**
     * Parse le texte brut pour extraire les données structurées.
     * Retourne un tableau avec les clés trouvées (peut être partiel).
     */
    public function parseInvoiceData(string $rawText): array
    {
        return [
            'supplier_name'  => $this->extractSupplierName($rawText),
            'invoice_number' => $this->extractInvoiceNumber($rawText),
            'invoice_date'   => $this->extractDate($rawText),
            'total_amount'   => $this->extractTotalAmount($rawText),
            'tax_amount'     => $this->extractTaxAmount($rawText),
            'line_items'     => $this->extractLineItems($rawText),
        ];
    }

    // -------------------------------------------------------------------------
    // Méthodes privées de parsing
    // -------------------------------------------------------------------------

    private function extractSupplierName(string $text): ?string
    {
        // Cherche "Fournisseur:", "De:", "De la société:" etc.
        if (preg_match('/(?:Fournisseur|De la soci[eé]t[eé]|Vendeur)\s*:\s*(.+)/i', $text, $m)) {
            return trim($m[1]);
        }
        if (preg_match('/^De\s*:\s*(.+)/im', $text, $m)) {
            return trim($m[1]);
        }
        // Première ligne non vide
        foreach (explode("\n", $text) as $line) {
            $line = trim($line);
            if ($line !== '') {
                return $line;
            }
        }

        return null;
    }

    private function extractInvoiceNumber(string $text): ?string
    {
        if (preg_match('/(?:N[°o]\s*(?:de\s*)?[Ff]acture|[Ff]acture\s*N[°o]|[Ii]nvoice\s*#|N[°o]\s*:)\s*:?\s*([A-Z0-9\-\/]+)/iu', $text, $m)) {
            return trim($m[1]);
        }

        return null;
    }

    private function extractDate(string $text): ?string
    {
        // DD/MM/YYYY
        if (preg_match('/(\d{2})\/(\d{2})\/(\d{4})/', $text, $m)) {
            return $m[3].'-'.$m[2].'-'.$m[1];
        }
        // YYYY-MM-DD
        if (preg_match('/(\d{4})-(\d{2})-(\d{2})/', $text, $m)) {
            return $m[1].'-'.$m[2].'-'.$m[3];
        }
        // DD-MM-YYYY
        if (preg_match('/(\d{2})-(\d{2})-(\d{4})/', $text, $m)) {
            return $m[3].'-'.$m[2].'-'.$m[1];
        }

        return null;
    }

    private function extractTotalAmount(string $text): ?float
    {
        if (preg_match('/(?:Total\s+TTC|TOTAL|Montant\s+total)\s*:?\s*([\d\s]+(?:[.,]\d+)?)/i', $text, $m)) {
            return (float) str_replace([' ', ','], ['', '.'], $m[1]);
        }

        return null;
    }

    private function extractTaxAmount(string $text): ?float
    {
        if (preg_match('/(?:TVA|VAT|Tax)\s*:?\s*([\d\s]+(?:[.,]\d+)?)/i', $text, $m)) {
            return (float) str_replace([' ', ','], ['', '.'], $m[1]);
        }

        return null;
    }

    private function extractLineItems(string $text): array
    {
        $items = [];
        // Heuristique: lignes contenant quantité × prix
        foreach (explode("\n", $text) as $line) {
            if (preg_match('/(\d+)\s*[x×]\s*([\d\s]+(?:[.,]\d+)?)/i', $line, $m)) {
                $items[] = [
                    'description' => trim(preg_replace('/\d+\s*[x×].+/', '', $line)),
                    'quantity'    => (int) $m[1],
                    'unit_price'  => (float) str_replace([' ', ','], ['', '.'], $m[2]),
                ];
            }
        }

        return $items;
    }

    private function extractViaGoogleVision(string $filePath): string
    {
        $key = config('services.google_vision.key');
        $content = base64_encode(file_get_contents($filePath));

        $payload = json_encode([
            'requests' => [[
                'image'    => ['content' => $content],
                'features' => [['type' => 'TEXT_DETECTION']],
            ]],
        ]);

        $ch = curl_init('https://vision.googleapis.com/v1/images:annotate?key='.$key);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);

        return $data['responses'][0]['fullTextAnnotation']['text'] ?? '';
    }
}

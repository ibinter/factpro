<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiAssistService
{
    private string $apiKey;
    private string $model = 'claude-haiku-4-5-20251001';
    private string $baseUrl = 'https://api.anthropic.com/v1/messages';

    public function __construct()
    {
        $this->apiKey = config('services.anthropic.api_key', '');
    }

    /** Vérifie si l'API est configurée */
    public function isAvailable(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Suggère une description pour un produit/service basé sur son nom.
     * Cache 24h par nom.
     */
    public function suggestProductDescription(string $productName, string $category = ''): string
    {
        if (!$this->isAvailable()) return '';

        $cacheKey = 'ai_product_' . md5($productName . $category);
        return Cache::remember($cacheKey, 86400, function () use ($productName, $category) {
            $prompt = "Génère une description commerciale courte (max 2 phrases) pour le produit/service : '{$productName}'"
                    . ($category ? " dans la catégorie '{$category}'" : "")
                    . ". Réponse directement la description, sans introduction.";
            return $this->callClaude($prompt, 150);
        });
    }

    /**
     * Détecte des doublons potentiels dans une liste de clients.
     * Retourne les indices des paires similaires.
     */
    public function detectCustomerDuplicates(array $customerNames): array
    {
        if (!$this->isAvailable() || count($customerNames) < 2) return [];

        $list = implode("\n", array_map(fn ($i, $n) => "$i. $n", array_keys($customerNames), $customerNames));
        $prompt = "Analyse cette liste de noms de clients et identifie les doublons potentiels (même entreprise avec orthographes différentes). Retourne UNIQUEMENT un JSON array de paires d'indices : [[0,3],[1,5]]. Si aucun doublon, retourne [].\n\n$list";

        $response = $this->callClaude($prompt, 200);
        preg_match('/\[.*\]/s', $response, $matches);
        return $matches ? json_decode($matches[0], true) ?? [] : [];
    }

    /**
     * Génère un résumé commercial d'un document.
     */
    public function summarizeDocument(array $documentData): string
    {
        if (!$this->isAvailable()) return '';

        $items = collect($documentData['items'] ?? [])->map(fn ($i) => "- {$i['description']} × {$i['quantity']} = {$i['total']}")->join("\n");
        $prompt = "Génère un résumé professionnel en 1-2 phrases de cette facture/devis :\nClient: {$documentData['customer_name']}\nTotal: {$documentData['total']} {$documentData['currency']}\nLignes:\n{$items}\n\nRésumé:";

        return $this->callClaude($prompt, 100);
    }

    /**
     * Suggère un prix pour un produit basé sur son nom et contexte.
     */
    public function suggestPrice(string $productName, string $currency = 'XOF'): ?float
    {
        if (!$this->isAvailable()) return null;

        $prompt = "Pour le produit/service '{$productName}' facturé en {$currency} en Afrique de l'Ouest, quel serait un prix unitaire raisonnable ? Réponds UNIQUEMENT par un nombre entier (ex: 15000). Pas de texte.";
        $response = $this->callClaude($prompt, 20);
        preg_match('/\d+/', $response, $matches);
        return $matches ? (float) $matches[0] : null;
    }

    private function callClaude(string $prompt, int $maxTokens = 200): string
    {
        try {
            $response = Http::withHeaders([
                'x-api-key'         => $this->apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type'      => 'application/json',
            ])->timeout(10)->post($this->baseUrl, [
                'model'      => $this->model,
                'max_tokens' => $maxTokens,
                'messages'   => [['role' => 'user', 'content' => $prompt]],
            ]);

            if ($response->successful()) {
                return $response->json('content.0.text', '');
            }
        } catch (\Exception $e) {
            Log::warning('AI assist error: ' . $e->getMessage());
        }
        return '';
    }
}

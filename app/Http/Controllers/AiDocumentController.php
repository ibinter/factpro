<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiDocumentController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'company_name'   => 'required|string|max:255',
            'client_name'    => 'required|string|max:255',
            'doc_type'       => 'required|string|max:100',
            'items'          => 'nullable|array',
            'items.*.description' => 'nullable|string',
            'items.*.quantity'    => 'nullable|numeric|min:0',
            'items.*.unit_price'  => 'nullable|numeric|min:0',
            'currency'       => 'nullable|string|max:10',
            'tva_rate'       => 'nullable|numeric|min:0|max:100',
            'doc_number'     => 'nullable|string|max:100',
            'doc_date'       => 'nullable|string|max:50',
            'due_date'       => 'nullable|string|max:50',
            'object'         => 'nullable|string|max:500',
            'notes'          => 'nullable|string|max:2000',
            'company_address' => 'nullable|string|max:500',
            'company_phone'   => 'nullable|string|max:50',
            'company_email'   => 'nullable|string|max:255',
            'company_rccm'    => 'nullable|string|max:100',
            'company_cc'      => 'nullable|string|max:100',
            'company_bank'    => 'nullable|string|max:255',
            'company_iban'    => 'nullable|string|max:100',
            'client_address'  => 'nullable|string|max:500',
            'client_phone'    => 'nullable|string|max:50',
            'client_email'    => 'nullable|string|max:255',
            'client_rccm'     => 'nullable|string|max:100',
        ]);

        $currency  = $request->input('currency', 'FCFA');
        $tvaRate   = (float) $request->input('tva_rate', 18);
        $items     = $request->input('items', []);

        // Calcul sous-total HT, TVA, TTC
        $subtotalHT = 0;
        $formattedLines = [];

        foreach ($items as $item) {
            $qty        = (float) ($item['quantity']   ?? 0);
            $unitPrice  = (float) ($item['unit_price'] ?? 0);
            $lineTotal  = $qty * $unitPrice;
            $subtotalHT += $lineTotal;

            $formattedLines[] = sprintf(
                '| %s | %s | %s %s | %s %s |',
                $item['description'] ?? '',
                $qty,
                number_format($unitPrice, 0, ',', ' '),
                $currency,
                number_format($lineTotal, 0, ',', ' '),
                $currency
            );
        }

        $tvaAmount = $subtotalHT * $tvaRate / 100;
        $totalTTC  = $subtotalHT + $tvaAmount;

        $linesText = implode("\n", $formattedLines);

        $docType     = $request->input('doc_type');
        $docNumber   = $request->input('doc_number', 'N/A');
        $docDate     = $request->input('doc_date', date('d/m/Y'));
        $dueDate     = $request->input('due_date', '');
        $object      = $request->input('object', '');
        $notes       = $request->input('notes', '');

        $companyName    = $request->input('company_name');
        $companyAddress = $request->input('company_address', '');
        $companyPhone   = $request->input('company_phone', '');
        $companyEmail   = $request->input('company_email', '');
        $companyRccm    = $request->input('company_rccm', '');
        $companyCc      = $request->input('company_cc', '');
        $companyBank    = $request->input('company_bank', '');
        $companyIban    = $request->input('company_iban', '');

        $clientName    = $request->input('client_name');
        $clientAddress = $request->input('client_address', '');
        $clientPhone   = $request->input('client_phone', '');
        $clientEmail   = $request->input('client_email', '');
        $clientRccm    = $request->input('client_rccm', '');

        $prompt = <<<PROMPT
Tu es un expert en génération de documents commerciaux conformes aux normes OHADA (Organisation pour l'Harmonisation en Afrique du Droit des Affaires).

Génère un document HTML complet et autonome de type "{$docType}" avec les informations suivantes.

RÈGLES IMPÉRATIVES :
1. Retourne UNIQUEMENT le code HTML complet, sans aucun commentaire ni explication.
2. Le document doit être autonome (tout le CSS est inline dans une balise <style>).
3. Design professionnel avec gradient bleu marine #1E3A5F → #2563EB pour l'en-tête.
4. Tableaux élégants avec alternance de couleurs de lignes.
5. Zone de signature en bas du document.
6. Pied de page mentionnant "IBIG FactPro" et la conformité OHADA.
7. CSS @media print pour une impression parfaite (marges, sauts de page, suppression des éléments non imprimables).
8. Typographie professionnelle (font-family: 'Segoe UI', Arial, sans-serif).
9. Le document doit être visuellement professionnel, prêt à être imprimé ou envoyé par e-mail.

INFORMATIONS DU DOCUMENT :
- Type de document : {$docType}
- Numéro : {$docNumber}
- Date : {$docDate}
- Date d'échéance : {$dueDate}
- Objet : {$object}

ÉMETTEUR (Entreprise) :
- Nom : {$companyName}
- Adresse : {$companyAddress}
- Téléphone : {$companyPhone}
- Email : {$companyEmail}
- RCCM : {$companyRccm}
- Compte contribuable : {$companyCc}
- Banque : {$companyBank}
- IBAN/RIB : {$companyIban}

DESTINATAIRE (Client) :
- Nom : {$clientName}
- Adresse : {$clientAddress}
- Téléphone : {$clientPhone}
- Email : {$clientEmail}
- RCCM : {$clientRccm}

LIGNES DU DOCUMENT :
| Description | Quantité | Prix Unitaire | Montant |
|-------------|----------|---------------|---------|
{$linesText}

TOTAUX :
- Sous-total HT : {$subtotalHT} {$currency}
- TVA ({$tvaRate}%) : {$tvaAmount} {$currency}
- TOTAL TTC : {$totalTTC} {$currency}

NOTES / CONDITIONS :
{$notes}

STRUCTURE HTML ATTENDUE :
- En-tête avec gradient bleu marine (#1E3A5F → #2563EB) contenant le nom de l'entreprise et le type de document
- Bloc informations émetteur et destinataire côte à côte
- Tableau des prestations/produits avec style professionnel
- Bloc totaux aligné à droite
- Zone de notes/conditions si présentes
- Zone de signature (émetteur et destinataire)
- Pied de page : "Document généré par IBIG FactPro — Conforme OHADA"
- @media print optimisé
PROMPT;

        try {
            $apiKey = config('services.anthropic.key');

            if (empty($apiKey)) {
                return response()->json(['error' => 'Clé API Anthropic non configurée.'], 500);
            }

            $response = Http::timeout(90)
                ->withHeaders([
                    'x-api-key'         => $apiKey,
                    'anthropic-version' => '2023-06-01',
                    'content-type'      => 'application/json',
                ])
                ->post('https://api.anthropic.com/v1/messages', [
                    'model'      => 'claude-sonnet-4-6',
                    'max_tokens' => 4096,
                    'messages'   => [
                        [
                            'role'    => 'user',
                            'content' => $prompt,
                        ],
                    ],
                ]);

            if ($response->failed()) {
                $errorBody = $response->json();
                $errorMsg  = $errorBody['error']['message'] ?? 'Erreur API Anthropic inconnue.';
                Log::error('AiDocumentController API error', ['status' => $response->status(), 'body' => $errorBody]);
                return response()->json(['error' => $errorMsg], 502);
            }

            $data    = $response->json();
            $html    = $data['content'][0]['text'] ?? '';

            // Nettoyage des balises markdown éventuelles
            $html = preg_replace('/^```html\s*/i', '', trim($html));
            $html = preg_replace('/\s*```$/', '', $html);
            $html = trim($html);

            return response()->json(['html' => $html]);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('AiDocumentController connection error', ['message' => $e->getMessage()]);
            return response()->json(['error' => 'Impossible de joindre l\'API Anthropic : ' . $e->getMessage()], 503);
        } catch (\Exception $e) {
            Log::error('AiDocumentController unexpected error', ['message' => $e->getMessage()]);
            return response()->json(['error' => 'Erreur inattendue : ' . $e->getMessage()], 500);
        }
    }
}

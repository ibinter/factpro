<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;

class DocumentCatalogController extends Controller
{
    /**
     * Map statique des 13 catégories de documents.
     */
    private array $categories = [
        'commercial'     => ['label' => 'Commercial & Ventes',          'icon' => '🤝'],
        'rh'             => ['label' => 'Ressources Humaines',          'icon' => '👥'],
        'juridique'      => ['label' => 'Juridique & Contrats',         'icon' => '⚖️'],
        'financier'      => ['label' => 'Financier & Comptable',        'icon' => '💰'],
        'administratif'  => ['label' => 'Administratif & Gestion',      'icon' => '📋'],
        'marketing'      => ['label' => 'Marketing & Communication',    'icon' => '📢'],
        'logistique'     => ['label' => 'Logistique & Supply Chain',    'icon' => '🚚'],
        'qualite'        => ['label' => 'Qualité & Conformité',         'icon' => '✅'],
        'informatique'   => ['label' => 'Informatique & Digital',       'icon' => '💻'],
        'fiscal'         => ['label' => 'Fiscal & Douanier',            'icon' => '🏛️'],
        'immobilier'     => ['label' => 'Immobilier & Patrimoine',      'icon' => '🏢'],
        'sante'          => ['label' => 'Santé & Sécurité au Travail',  'icon' => '🏥'],
        'formation'      => ['label' => 'Formation & Développement',    'icon' => '📚'],
    ];

    /**
     * Affiche le catalogue de documents.
     * Toutes les données sont hardcodées côté Vue.
     */
    public function index()
    {
        return Inertia::render('Documents/Catalog', []);
    }

    /**
     * Affiche le formulaire de génération IA pour un type/catégorie donné.
     */
    public function aiGenerateForm(Request $request)
    {
        $type  = $request->query('type', '');
        $catId = $request->query('cat', '');

        $company = Auth::user()->currentCompany;

        $catLabel = $this->categories[$catId]['label'] ?? 'Document';
        $catIcon  = $this->categories[$catId]['icon']  ?? '📄';

        return Inertia::render('Documents/AiGenerate', [
            'company'  => $company,
            'docType'  => $type,
            'catLabel' => $catLabel,
            'catIcon'  => $catIcon,
        ]);
    }

    /**
     * Génère un document via l'API Claude (Anthropic).
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'docType'     => 'required|string|max:255',
            'catLabel'    => 'required|string|max:255',
            'companyName' => 'nullable|string|max:255',
            'details'     => 'nullable|string|max:5000',
            'language'    => 'nullable|string|max:10',
        ]);

        $docType     = $validated['docType'];
        $catLabel    = $validated['catLabel'];
        $companyName = $validated['companyName'] ?? (Auth::user()->currentCompany?->name ?? 'Mon Entreprise');
        $details     = $validated['details'] ?? '';
        $language    = $validated['language'] ?? 'fr';

        $detailsSection = $details
            ? "\n\nInformations complémentaires fournies par l'utilisateur :\n{$details}"
            : '';

        $prompt = <<<PROMPT
Tu es expert OHADA et droit des affaires en Afrique francophone.

Génère un document HTML COMPLET de type "{$docType}" (catégorie : {$catLabel}) pour l'entreprise "{$companyName}".

Exigences :
- Le document doit être conforme aux normes OHADA et aux pratiques professionnelles en vigueur.
- Produis uniquement le HTML complet (balise <!DOCTYPE html> incluse), prêt à être affiché ou imprimé.
- Utilise un style CSS intégré (balise <style>) professionnel, avec logo de l'entreprise en haut, en-têtes, pieds de page, et mise en page soignée.
- Remplis toutes les sections avec des données réalistes mais fictives (placeholders clairs entre crochets [VALEUR] pour les champs à personnaliser).
- Le document doit être rédigé en {$language}.
- Ne fournis aucune explication, uniquement le HTML.{$detailsSection}
PROMPT;

        $apiKey = config('services.anthropic.key');

        if (empty($apiKey)) {
            return response()->json([
                'error' => 'Clé API Anthropic non configurée. Veuillez ajouter ANTHROPIC_API_KEY dans votre fichier .env.',
            ], 500);
        }

        try {
            $response = Http::withHeaders([
                'x-api-key'         => $apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type'      => 'application/json',
            ])->timeout(120)->post('https://api.anthropic.com/v1/messages', [
                'model'      => 'claude-opus-4-5',
                'max_tokens' => 4096,
                'messages'   => [
                    [
                        'role'    => 'user',
                        'content' => $prompt,
                    ],
                ],
            ]);

            if ($response->failed()) {
                $body = $response->json();
                $errorMessage = $body['error']['message'] ?? 'Erreur inconnue de l\'API Anthropic.';

                return response()->json([
                    'error' => 'Erreur API Anthropic : ' . $errorMessage,
                ], $response->status());
            }

            $body    = $response->json();
            $content = $body['content'][0]['text'] ?? '';

            if (empty($content)) {
                return response()->json([
                    'error' => 'L\'API n\'a retourné aucun contenu.',
                ], 500);
            }

            return response()->json([
                'html'         => $content,
                'inputTokens'  => $body['usage']['input_tokens']  ?? 0,
                'outputTokens' => $body['usage']['output_tokens'] ?? 0,
            ]);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return response()->json([
                'error' => 'Impossible de joindre l\'API Anthropic. Vérifiez votre connexion internet.',
            ], 503);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur inattendue : ' . $e->getMessage(),
            ], 500);
        }
    }
}

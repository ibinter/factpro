<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SaraController extends Controller
{
    private string $apiKey;
    private string $model = 'llama-3.3-70b-versatile';
    private string $baseUrl = 'https://api.groq.com/openai/v1/chat/completions';

    public function __construct()
    {
        $this->apiKey = config('services.groq.api_key', '');
    }

    private function systemPrompt(): string
    {
        return <<<PROMPT
Tu es SARA, l'assistante IA de IBIG FactPro — le logiciel de facturation professionnel conçu pour les entrepreneurs et PME d'Afrique et du monde.

Tu réponds avec bienveillance, concision et professionnalisme. Tu parles en français par défaut, en anglais si l'utilisateur te parle en anglais.

Ce que tu sais sur IBIG FactPro :
- Logiciel SaaS de facturation : devis, factures, bons de commande, reçus, avoirs
- QR anti-falsification sur chaque document
- Impression thermique (58mm et 80mm)
- Caisse POS tactile
- Mobile Money (Orange Money, Wave, MTN, Moov)
- Multi-devises et multi-sociétés
- Portail client self-service
- Relances automatiques
- Comptabilité et export FEC/Sage/QuickBooks
- API REST publique
- PWA installable sur mobile
- Conformité OHADA, TVA multi-pays, Factur-X France 2026
- Plans: Starter (gratuit/limité), Pro, Business, Enterprise
- Essai gratuit 7 jours sans carte bancaire
- Support: noreply@ibigsoft.com

Si on te pose une question hors sujet, réponds poliment que tu es spécialisée dans IBIG FactPro et redirige vers le sujet.
PROMPT;
    }

    public function chat(Request $request)
    {
        $request->validate([
            'messages' => 'required|array|max:20',
            'messages.*.role' => 'required|in:user,assistant',
            'messages.*.content' => 'required|string|max:2000',
        ]);

        if (empty($this->apiKey)) {
            return response()->json(['error' => 'SARA non configurée.'], 503);
        }

        $messages = array_merge(
            [['role' => 'system', 'content' => $this->systemPrompt()]],
            $request->input('messages')
        );

        $response = Http::withToken($this->apiKey)
            ->timeout(30)
            ->post($this->baseUrl, [
                'model' => $this->model,
                'messages' => $messages,
                'max_tokens' => 600,
                'temperature' => 0.7,
            ]);

        if ($response->failed()) {
            return response()->json(['error' => 'Erreur de communication avec SARA.'], 502);
        }

        $content = $response->json('choices.0.message.content', '');

        return response()->json(['reply' => $content]);
    }
}

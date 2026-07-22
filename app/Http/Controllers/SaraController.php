<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SaraController extends Controller
{
    private string $model   = 'claude-haiku-4-5-20251001';
    private string $baseUrl = 'https://api.anthropic.com/v1/messages';

    private function apiKey(): string
    {
        return config('services.anthropic.api_key', env('ANTHROPIC_API_KEY', ''));
    }

    private function systemPrompt(): string
    {
        return <<<PROMPT
Tu es SARA, l'assistante commerciale IA d'IBIG FactPro — logiciel de facturation professionnelle SaaS pour entrepreneurs et PME d'Afrique et du monde, édité par IBIG SARL (Côte d'Ivoire).

Règles :
- Réponds toujours en français sauf si l'utilisateur écrit en anglais.
- Sois concise (max 3-4 phrases), bienveillante et professionnelle.
- Dirige vers l'action : essai gratuit sur /register, démo sur /demo-login, tarifs sur /pricing.
- Ne réponds pas aux questions hors sujet IBIG FactPro.

Connaissances produit :
• Documents : devis, factures, avoirs, bons de commande, bons de livraison, reçus, proformas, bulletins de paie
• QR anti-falsification sur chaque document + vérification publique
• Impression thermique 58mm et 80mm (tickets caisse)
• Caisse POS tactile multi-caissier avec rapport X/Z
• Mobile Money : Wave, Orange Money, MTN MoMo, Moov, CinetPay, FedaPay, Flutterwave
• Multi-devises (FCFA, EUR, USD, GHS, NGN…) et multi-sociétés
• Portail client self-service (paiement en ligne, téléchargement)
• Relances automatiques par email et WhatsApp
• Stocks avancés avec alertes de réapprovisionnement
• Comptabilité et export FEC, Sage, QuickBooks
• API REST publique 100% documentée
• PWA installable sur Android et iPhone (mode hors-ligne)
• Conformité OHADA, TVA multi-pays (CI, SN, CM, BF, BJ, TG, MA…), Factur-X France 2026
• Signature électronique des devis
• Journal d'audit RGPD complet
• Assistant IA (saisie intelligente)
• White-label pour revendeurs
• Programme IBIG Partners : 20% N1, 10% N2, 5% N3

Forfaits (FCFA/mois) :
- STARTER : 4 900 FCFA — 10 docs/mois, 1 utilisateur
- PRO : 12 900 FCFA — illimité, 3 utilisateurs (le plus populaire)
- BUSINESS : 24 900 FCFA — POS + stocks + thermique, 10 utilisateurs
- ENTERPRISE : 59 900 FCFA — tout illimité + White-Label + Factur-X

Essai : 7 jours gratuits, sans carte bancaire.
Contact : factpro@ibigsoft.com | +225 05 55 05 99 01
PROMPT;
    }

    public function chat(Request $request)
    {
        $request->validate([
            'messages'           => 'required|array|min:1|max:20',
            'messages.*.role'    => 'required|in:user,assistant',
            'messages.*.content' => 'required|string|max:2000',
        ]);

        $key = $this->apiKey();
        if (empty($key)) {
            return response()->json([
                'reply' => "Je suis temporairement indisponible. Contactez-nous : factpro@ibigsoft.com ou +225 05 55 05 99 01",
            ]);
        }

        $response = Http::withHeaders([
            'x-api-key'         => $key,
            'anthropic-version' => '2023-06-01',
            'content-type'      => 'application/json',
        ])->timeout(30)->post($this->baseUrl, [
            'model'      => $this->model,
            'max_tokens' => 500,
            'system'     => $this->systemPrompt(),
            'messages'   => $request->input('messages'),
        ]);

        if ($response->failed()) {
            return response()->json([
                'reply' => "Désolée, je rencontre un problème technique. Réessayez ou écrivez-nous à factpro@ibigsoft.com",
            ]);
        }

        $content = $response->json('content.0.text', '');

        return response()->json(['reply' => $content]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Document;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiReminderController extends Controller
{
    public function generate(Request $request, Customer $customer): JsonResponse
    {
        $company = $request->user()->currentCompany;

        if ($customer->company_id !== $company->id) {
            abort(403);
        }

        $overdueInvoices = Document::where('company_id', $company->id)
            ->where('customer_id', $customer->id)
            ->where('status', 'overdue')
            ->orderBy('due_date')
            ->get(['number', 'total_ttc', 'due_date', 'currency']);

        $score        = $customer->score;
        $totalOverdue = $overdueInvoices->sum('total_ttc');
        $currency     = $overdueInvoices->first()?->currency ?? 'XOF';

        $context  = "Société émettrice : {$company->name}\n";
        $context .= "Client : {$customer->name}\n";
        $context .= "Nombre de factures impayées : {$overdueInvoices->count()}\n";
        $context .= "Total dû : " . number_format($totalOverdue, 0, ',', ' ') . " {$currency}\n";
        if ($score) {
            $context .= "Score risque paiement : {$score->risk_label} ({$score->payment_risk_score}/100)\n";
            $context .= "Délai moyen de paiement : {$score->avg_payment_days} jours\n";
        }
        $context .= "Factures : " . $overdueInvoices->map(
            fn ($i) => "{$i->number} — " . number_format($i->total_ttc, 0, ',', ' ') . " {$i->currency} (échue le {$i->due_date})"
        )->join(', ');

        $prompt = "Tu es un assistant de relance commerciale professionnel pour une PME africaine.\n\n{$context}\n\nRédige une relance email professionnelle, courtoise mais ferme, en français. Adapte le ton au profil de risque. Donne uniquement le corps du message (sans objet), en 3-4 paragraphes courts. Signe au nom de {$company->name}.";

        try {
            $client   = new \Anthropic\Client(config('services.anthropic.key'));
            $response = $client->messages()->create([
                'model'      => 'claude-haiku-4-5-20251001',
                'max_tokens' => 500,
                'messages'   => [['role' => 'user', 'content' => $prompt]],
            ]);
            $text = $response->content[0]->text ?? 'Erreur de génération.';
        } catch (\Exception $e) {
            $text = "Madame, Monsieur,\n\nNous nous permettons de vous rappeler que la facture {$overdueInvoices->first()?->number} d'un montant de " . number_format($totalOverdue, 0, ',', ' ') . " {$currency} est arrivée à échéance.\n\nNous vous prions de bien vouloir régulariser cette situation dans les meilleurs délais.\n\nCordialement,\n{$company->name}";
        }

        return response()->json(['message' => $text]);
    }

    public function suggestPrice(Request $request): JsonResponse
    {
        $request->validate(['product_id' => 'required|integer']);
        $company = $request->user()->currentCompany;

        $lines = \App\Models\DocumentLine::whereHas('document', fn ($q) => $q
                ->where('company_id', $company->id)
                ->whereIn('status', ['paid', 'sent', 'viewed'])
            )
            ->where('product_id', $request->product_id)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get(['unit_price', 'quantity', 'total_ht']);

        if ($lines->isEmpty()) {
            return response()->json(['suggested_price' => null, 'message' => 'Pas assez de données.']);
        }

        $avgPrice  = $lines->avg('unit_price');
        $maxPrice  = $lines->max('unit_price');
        $minPrice  = $lines->min('unit_price');
        $suggested = round($avgPrice * 1.05, 0);

        return response()->json([
            'suggested_price' => $suggested,
            'avg_price'       => round($avgPrice, 0),
            'min_price'       => $minPrice,
            'max_price'       => $maxPrice,
            'sample_count'    => $lines->count(),
            'message'         => "Prix suggéré basé sur {$lines->count()} ventes récentes.",
        ]);
    }
}

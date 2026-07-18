<?php

namespace App\Services;

use App\Models\PosSession;
use Illuminate\Database\Eloquent\Collection;

class PosReportService
{
    /**
     * Rapport X — Intermédiaire (sans clôture, peut être imprimé plusieurs fois).
     * Retourne les chiffres depuis l'ouverture de session jusqu'à maintenant.
     */
    public function generateXReport(PosSession $session): array
    {
        $session->loadMissing('user:id,name');

        $byMethod = $session->totals_by_method ?? [];
        $cashSales = (float) ($byMethod['cash'] ?? 0);
        $theoreticalCash = round((float) $session->opening_float + $cashSales, 2);

        $totalSales = (float) ($session->total_sales ?? 0);
        $tickets = (int) ($session->tickets_count ?? 0);
        $averageBasket = $tickets > 0 ? round($totalSales / $tickets, 2) : 0;

        return [
            'type' => 'X',
            'session' => $session,
            'sales_by_payment_method' => $this->formatByMethod($byMethod),
            'total_sales' => $totalSales,
            'total_tickets' => $tickets,
            'average_basket' => $averageBasket,
            'vat_collected' => 0.0,   // TVA intégrée dans les prix — extensible
            'discounts' => 0.0,
            'refunds' => 0.0,
            'opening_float' => (float) $session->opening_float,
            'theoretical_cash' => $theoreticalCash,
            'cashier' => $session->cashier_name ?? $session->user?->name,
            'opened_at' => $session->opened_at,
        ];
    }

    /**
     * Rapport Z — Clôture journalière (irréversible après génération).
     *
     * @throws \Exception si session déjà clôturée ou rapport Z déjà généré
     */
    public function generateZReport(PosSession $session, float $actualCash, string $notes = ''): array
    {
        if ($session->z_report_generated_at) {
            throw new \Exception('Rapport Z déjà généré pour cette session.');
        }

        $data = $this->generateXReport($session);
        $expectedCash = $data['theoretical_cash'];
        $difference = round($actualCash - $expectedCash, 2);

        $zNumber = $this->getNextZNumber((int) $session->company_id);

        $session->update([
            'status' => 'closed',
            'closed_at' => now(),
            'z_report_generated_at' => now(),
            'z_report_number' => $zNumber,
            'expected_cash' => $expectedCash,
            'counted_cash' => $actualCash,
            'difference' => $difference,
            'notes' => $notes ?: null,
        ]);

        $session->refresh();

        return array_merge($data, [
            'type' => 'Z',
            'z_number' => $zNumber,
            'actual_cash' => $actualCash,
            'expected_cash' => $expectedCash,
            'cash_difference' => $difference,
            'closing_notes' => $notes,
            'z_report_generated_at' => $session->z_report_generated_at,
        ]);
    }

    /**
     * Retourne le N° Z suivant au format Z-{AAAA}-{CID3}-{SEQ4}
     * La séquence est par company et par année civile.
     */
    private function getNextZNumber(int $companyId): string
    {
        $year = now()->year;

        $count = PosSession::where('company_id', $companyId)
            ->whereNotNull('z_report_number')
            ->whereYear('z_report_generated_at', $year)
            ->count();

        $seq = $count + 1;

        return sprintf('Z-%04d-%03d-%04d', $year, $companyId % 1000, $seq);
    }

    /**
     * Formate les totaux par mode de paiement.
     */
    private function formatByMethod(array $byMethod): array
    {
        $result = [];
        foreach ($byMethod as $method => $amount) {
            $result[] = [
                'method' => $method,
                'label' => match ($method) {
                    'cash' => 'Espèces',
                    'card' => 'Carte bancaire',
                    'mobile_money' => 'Mobile Money',
                    'credit' => 'Crédit client',
                    default => ucfirst($method),
                },
                'amount' => (float) $amount,
            ];
        }
        return $result;
    }

    /**
     * Historique des rapports Z d'une company.
     */
    public function getZHistory(int $companyId, int $limit = 30): Collection
    {
        return PosSession::where('company_id', $companyId)
            ->whereNotNull('z_report_number')
            ->with('user:id,name')
            ->orderByDesc('z_report_generated_at')
            ->limit($limit)
            ->get();
    }
}

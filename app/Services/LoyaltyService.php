<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Document;
use App\Models\LoyaltyPoint;
use App\Models\LoyaltyProgram;
use App\Models\LoyaltyReward;
use Illuminate\Support\Str;

class LoyaltyService
{
    /**
     * Calcule et attribue les points pour un paiement.
     * @return int points attribués
     */
    public function awardPoints(Document $document, float $amountPaid): int
    {
        $program = LoyaltyProgram::where('company_id', $document->company_id)
            ->where('is_active', true)->first();

        if (! $program || ! $document->customer_id) {
            return 0;
        }

        $points = (int) floor($amountPaid / 1000 * $program->points_per_1000);
        if ($points <= 0) {
            return 0;
        }

        $balance = $this->getBalance($document->customer_id, $document->company_id);

        LoyaltyPoint::create([
            'customer_id' => $document->customer_id,
            'company_id' => $document->company_id,
            'document_id' => $document->id,
            'type' => 'earned',
            'points' => $points,
            'balance_after' => $balance + $points,
            'description' => "Facture {$document->number}",
            'expires_at' => $program->expiry_months
                ? now()->addMonths($program->expiry_months)->toDateString()
                : null,
        ]);

        return $points;
    }

    /**
     * Retourne le solde de points d'un client.
     */
    public function getBalance(int $customerId, int $companyId): int
    {
        return (int) LoyaltyPoint::where('customer_id', $customerId)
            ->where('company_id', $companyId)
            ->sum('points');
    }

    /**
     * Retourne le niveau du client (Bronze/Argent/Or).
     */
    public function getLevel(int $balance, LoyaltyProgram $program): array
    {
        if ($balance >= $program->gold_threshold) {
            return ['name' => 'Or', 'color' => '#F0C040', 'icon' => '🥇'];
        }
        if ($balance >= $program->silver_threshold) {
            return ['name' => 'Argent', 'color' => '#9CA3AF', 'icon' => '🥈'];
        }

        return ['name' => 'Bronze', 'color' => '#B45309', 'icon' => '🥉'];
    }

    /**
     * Échange des points contre une récompense, crée un coupon.
     *
     * @throws \Exception
     */
    public function redeemReward(int $customerId, int $companyId, LoyaltyReward $reward): string
    {
        $balance = $this->getBalance($customerId, $companyId);

        if ($balance < $reward->points_cost) {
            throw new \Exception('Solde de points insuffisant.');
        }

        $newBalance = $balance - $reward->points_cost;

        LoyaltyPoint::create([
            'customer_id' => $customerId,
            'company_id' => $companyId,
            'document_id' => null,
            'type' => 'redeemed',
            'points' => -$reward->points_cost,
            'balance_after' => $newBalance,
            'description' => "Échange: {$reward->name}",
            'expires_at' => null,
        ]);

        $code = 'FID-' . strtoupper(Str::random(8));

        Coupon::create([
            'code' => $code,
            'description' => "Fidélité : {$reward->name}",
            'type' => $reward->reward_type === 'discount_percent' ? 'percent' : 'fixed',
            'value' => $reward->reward_value,
            'applies_to' => 'invoice',
            'max_redemptions' => 1,
            'redemptions_count' => 0,
            'is_active' => true,
            'expires_at' => now()->addDays(90)->toDateString(),
        ]);

        $reward->increment('redemptions_count');

        if ($reward->stock !== null) {
            $reward->decrement('stock');
        }

        return $code;
    }

    /**
     * Expire les points périmés (appelé par scheduler).
     */
    public function expirePoints(): int
    {
        $expired = LoyaltyPoint::where('type', 'earned')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now()->toDateString())
            ->get();

        $count = 0;

        foreach ($expired as $point) {
            $balance = $this->getBalance($point->customer_id, $point->company_id);

            LoyaltyPoint::create([
                'customer_id' => $point->customer_id,
                'company_id' => $point->company_id,
                'document_id' => null,
                'type' => 'expired',
                'points' => -$point->points,
                'balance_after' => max(0, $balance - $point->points),
                'description' => 'Points expirés',
                'expires_at' => null,
            ]);

            // Marquer comme expiré pour ne pas retraiter
            $point->update(['type' => 'expired']);
            $count++;
        }

        return $count;
    }

    /**
     * Retourne le top N des clients par points cumulés.
     */
    public function topCustomers(int $companyId, int $limit = 10): \Illuminate\Support\Collection
    {
        return LoyaltyPoint::where('company_id', $companyId)
            ->selectRaw('customer_id, SUM(points) as total_points')
            ->groupBy('customer_id')
            ->orderByDesc('total_points')
            ->limit($limit)
            ->with('customer:id,name,email')
            ->get();
    }
}

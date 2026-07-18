<?php

namespace App\Services\MobileMoney;

interface MobileMoneyDriver
{
    /**
     * Initie un paiement Mobile Money.
     *
     * @return array{checkout_url?: string, reference: string, status: string, instructions?: string}
     */
    public function initiate(string $phone, float $amount, string $currency, string $reference, string $description): array;

    /**
     * Vérifie le statut d'un paiement.
     *
     * @return array{status: string, paid: bool, amount?: float}
     */
    public function checkStatus(string $reference): array;

    /**
     * Valide la signature d'un webhook entrant.
     */
    public function validateWebhook(array $payload, string $signature): bool;
}

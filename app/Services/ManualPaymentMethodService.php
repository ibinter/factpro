<?php

namespace App\Services;

use App\Models\CryptoWallet;
use App\Models\ManualPaymentMethod;

class ManualPaymentMethodService
{
    /**
     * Retourne les méthodes actives pour un pays et un montant donné.
     * Groupées par type (mobile_money, bank_national, bank_international, transfer_service, cash).
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function getAvailableMethods(string $country, float $amount, string $currency): array
    {
        return ManualPaymentMethod::active()
            ->where(function ($q) use ($country) {
                $q->where('country', $country)->orWhereNull('country');
            })
            ->where(function ($q) use ($amount) {
                $q->where('min_amount', '<=', $amount)->orWhereNull('min_amount');
            })
            ->where(function ($q) use ($amount) {
                $q->where('max_amount', '>=', $amount)->orWhereNull('max_amount');
            })
            ->where(function ($q) use ($currency) {
                $q->where('currency', $currency)->orWhereNull('currency');
            })
            ->orderBy('sort_order')
            ->orderBy('display_order')
            ->get()
            ->groupBy('type')
            ->map(fn ($group) => $group->values()->toArray())
            ->toArray();
    }

    /**
     * Crée ou met à jour une méthode de paiement manuel.
     */
    public function upsert(array $data): ManualPaymentMethod
    {
        if (isset($data['id'])) {
            $method = ManualPaymentMethod::findOrFail($data['id']);
            $method->update($data);
            return $method;
        }

        return ManualPaymentMethod::create($data);
    }

    /**
     * Seeders par défaut : opérateurs Mobile Money Afrique + services de transfert.
     * Crée uniquement si absent (firstOrCreate), is_active = false par défaut
     * pour que le superadmin active ce qui est pertinent.
     */
    public function seedCryptoWallets(): void
    {
        $defaults = [
            ['currency' => 'USDT', 'network' => 'TRC20',    'label' => 'USDT TRC20 (Tron)',       'wallet_address' => 'À_CONFIGURER', 'confirmations_required' => 1, 'is_active' => false],
            ['currency' => 'USDT', 'network' => 'ERC20',    'label' => 'USDT ERC20 (Ethereum)',   'wallet_address' => 'À_CONFIGURER', 'confirmations_required' => 6, 'is_active' => false],
            ['currency' => 'BTC',  'network' => 'Bitcoin',  'label' => 'Bitcoin (BTC)',            'wallet_address' => 'À_CONFIGURER', 'confirmations_required' => 3, 'is_active' => false],
            ['currency' => 'ETH',  'network' => 'Ethereum', 'label' => 'Ethereum (ETH)',           'wallet_address' => 'À_CONFIGURER', 'confirmations_required' => 6, 'is_active' => false],
            ['currency' => 'BNB',  'network' => 'BEP20',    'label' => 'BNB (BSC)',               'wallet_address' => 'À_CONFIGURER', 'confirmations_required' => 1, 'is_active' => false],
        ];

        foreach ($defaults as $d) {
            CryptoWallet::firstOrCreate(
                ['currency' => $d['currency'], 'network' => $d['network']],
                $d,
            );
        }
    }

    public function seedDefaults(): void
    {
        $defaults = [
            // Mobile Money — Sénégal
            ['type' => 'mobile_money', 'operator' => 'Wave',           'country' => 'SN', 'currency' => 'XOF', 'label' => 'Wave Sénégal',          'processing_time' => '24-48h'],
            ['type' => 'mobile_money', 'operator' => 'Orange Money',   'country' => 'SN', 'currency' => 'XOF', 'label' => 'Orange Money Sénégal',   'processing_time' => '24-48h'],
            ['type' => 'mobile_money', 'operator' => 'Free Money',     'country' => 'SN', 'currency' => 'XOF', 'label' => 'Free Money Sénégal',     'processing_time' => '24-48h'],

            // Mobile Money — Côte d'Ivoire
            ['type' => 'mobile_money', 'operator' => 'Wave',           'country' => 'CI', 'currency' => 'XOF', 'label' => "Wave Côte d'Ivoire",    'processing_time' => '24-48h'],
            ['type' => 'mobile_money', 'operator' => 'Orange Money',   'country' => 'CI', 'currency' => 'XOF', 'label' => 'Orange Money CI',        'processing_time' => '24-48h'],
            ['type' => 'mobile_money', 'operator' => 'MTN Mobile Money','country' => 'CI', 'currency' => 'XOF', 'label' => 'MTN MoMo CI',           'processing_time' => '24-48h'],
            ['type' => 'mobile_money', 'operator' => 'Moov Money',     'country' => 'CI', 'currency' => 'XOF', 'label' => 'Moov Money CI',          'processing_time' => '24-48h'],

            // Mobile Money — Burkina Faso
            ['type' => 'mobile_money', 'operator' => 'Orange Money',   'country' => 'BF', 'currency' => 'XOF', 'label' => 'Orange Money BF',        'processing_time' => '24-48h'],
            ['type' => 'mobile_money', 'operator' => 'Moov Money',     'country' => 'BF', 'currency' => 'XOF', 'label' => 'Moov Money BF',          'processing_time' => '24-48h'],

            // Mobile Money — Mali
            ['type' => 'mobile_money', 'operator' => 'Orange Money',   'country' => 'ML', 'currency' => 'XOF', 'label' => 'Orange Money Mali',      'processing_time' => '24-48h'],
            ['type' => 'mobile_money', 'operator' => 'Moov Money',     'country' => 'ML', 'currency' => 'XOF', 'label' => 'Moov Money Mali',        'processing_time' => '24-48h'],

            // Transferts internationaux
            ['type' => 'transfer_service', 'label' => 'Western Union',  'processing_time' => '1-3 jours'],
            ['type' => 'transfer_service', 'label' => 'MoneyGram',      'processing_time' => '1-3 jours'],
            ['type' => 'transfer_service', 'label' => 'Sendwave',       'processing_time' => '24h'],
            ['type' => 'transfer_service', 'label' => 'Ria Money Transfer', 'processing_time' => '1-2 jours'],

            // Chèque bancaire
            ['type' => 'cheque', 'label' => 'Chèque bancaire', 'processing_time' => '3-5 jours ouvrables'],
        ];

        foreach ($defaults as $d) {
            ManualPaymentMethod::firstOrCreate(
                ['label' => $d['label']],
                array_merge($d, ['is_active' => false])
            );
        }
    }
}

<?php

namespace App\Services;

use App\Mail\LicenseExpired;
use App\Mail\LicenseExpiringSoon;
use App\Mail\PaymentProofReceived;
use App\Mail\PaymentRejected;
use App\Mail\PaymentValidated;
use App\Mail\ProofComplementRequested;
use App\Mail\ProvisionalLicenseActivated;
use App\Models\License;
use App\Models\Order;
use App\Models\PaymentTransaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PaymentNotificationService
{
    /**
     * Accusé de réception : preuve de paiement soumise.
     */
    public function sendProofReceived(Order $order, PaymentTransaction $transaction): void
    {
        $email = $this->resolveEmail($order);
        if (! $email) {
            return;
        }

        try {
            Mail::to($email)->queue(new PaymentProofReceived($order, $transaction));
        } catch (\Throwable $e) {
            Log::error('PaymentNotification: sendProofReceived failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Paiement validé — envoie email + reçu PDF en pièce jointe.
     */
    public function sendPaymentValidated(Order $order, PaymentTransaction $transaction, License $license): void
    {
        $email = $this->resolveEmail($order);
        if (! $email) {
            return;
        }

        try {
            $receiptPath = $this->generateReceipt($order, $transaction, $license);
            Mail::to($email)->queue(new PaymentValidated($order, $license, $transaction, $receiptPath));
        } catch (\Throwable $e) {
            Log::error('PaymentNotification: sendPaymentValidated failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Paiement rejeté.
     */
    public function sendPaymentRejected(Order $order, PaymentTransaction $transaction, string $reason): void
    {
        $email = $this->resolveEmail($order);
        if (! $email) {
            return;
        }

        try {
            Mail::to($email)->queue(new PaymentRejected($order, $transaction, $reason));
        } catch (\Throwable $e) {
            Log::error('PaymentNotification: sendPaymentRejected failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Complément d'information requis.
     */
    public function sendComplementRequested(Order $order, string $complementNote): void
    {
        $email = $this->resolveEmail($order);
        if (! $email) {
            return;
        }

        try {
            Mail::to($email)->queue(new ProofComplementRequested($order, $complementNote));
        } catch (\Throwable $e) {
            Log::error('PaymentNotification: sendComplementRequested failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Licence provisoire activée.
     */
    public function sendProvisionalActivated(License $license): void
    {
        $email = $license->user?->email;
        if (! $email) {
            return;
        }

        try {
            Mail::to($email)->queue(new ProvisionalLicenseActivated($license));
        } catch (\Throwable $e) {
            Log::error('PaymentNotification: sendProvisionalActivated failed', [
                'license_id' => $license->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Alerte expiration imminente (J-7, J-3, J-1).
     */
    public function sendExpiringSoon(License $license, int $daysLeft): void
    {
        $email = $license->user?->email;
        if (! $email) {
            return;
        }

        try {
            Mail::to($email)->queue(new LicenseExpiringSoon($license, $daysLeft));
        } catch (\Throwable $e) {
            Log::error('PaymentNotification: sendExpiringSoon failed', [
                'license_id' => $license->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Licence expirée.
     */
    public function sendExpired(License $license): void
    {
        $email = $license->user?->email;
        if (! $email) {
            return;
        }

        try {
            Mail::to($email)->queue(new LicenseExpired($license));
        } catch (\Throwable $e) {
            Log::error('PaymentNotification: sendExpired failed', [
                'license_id' => $license->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Génère le reçu PDF et retourne le chemin du fichier.
     * Retourne null si la génération échoue.
     */
    public function generateReceipt(Order $order, PaymentTransaction $transaction, License $license): ?string
    {
        try {
            $order->loadMissing(['plan', 'user']);
            $license->loadMissing('plan');

            $pdf = Pdf::loadView('pdf.payment-receipt', compact('order', 'transaction', 'license'));
            $pdf->setPaper('A4', 'portrait');

            $dir = storage_path('app/receipts');
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $path = $dir . '/receipt-' . $order->order_number . '.pdf';
            file_put_contents($path, $pdf->output());

            return $path;
        } catch (\Throwable $e) {
            Log::error('PaymentNotification: generateReceipt failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Résout l'adresse email du titulaire de la commande.
     */
    private function resolveEmail(Order $order): ?string
    {
        return $order->user?->email;
    }
}

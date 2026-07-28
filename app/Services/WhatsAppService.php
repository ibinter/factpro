<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Document;
use Illuminate\Support\Facades\Http;

class WhatsAppService
{
    public function __construct(private Company $company) {}

    private function apiUrl(): string
    {
        return "https://graph.facebook.com/v18.0/{$this->company->wa_phone_number_id}/messages";
    }

    private function headers(): array
    {
        return [
            'Authorization' => "Bearer {$this->company->wa_access_token}",
            'Content-Type'  => 'application/json',
        ];
    }

    public function sendText(string $to, string $message): array
    {
        $phone = $this->formatPhone($to);
        $payload = [
            'messaging_product' => 'whatsapp',
            'to'                => $phone,
            'type'              => 'text',
            'text'              => ['body' => $message],
        ];

        $response = Http::withHeaders($this->headers())->post($this->apiUrl(), $payload);

        if (! $response->successful()) {
            throw new \Exception($response->body());
        }

        return $response->json();
    }

    public function sendTemplate(string $to, string $templateName, array $components = []): array
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'to'                => $this->formatPhone($to),
            'type'              => 'template',
            'template'          => [
                'name'       => $templateName,
                'language'   => ['code' => 'fr'],
                'components' => $components,
            ],
        ];

        $response = Http::withHeaders($this->headers())->post($this->apiUrl(), $payload);

        if (! $response->successful()) {
            throw new \Exception($response->body());
        }

        return $response->json();
    }

    public function sendInvoiceNotification(Document $document, Customer $customer): array
    {
        $amount  = number_format($document->total, 0, ',', ' ');
        $payLink = route('pay.public', $document->public_token);

        $message = "Bonjour {$customer->name},\n\n"
            . "Votre facture n° {$document->number} d'un montant de {$amount} {$document->currency} est disponible.\n\n"
            . "Payer en ligne : {$payLink}\n\n"
            . "Merci.";

        return $this->sendText($customer->phone, $message);
    }

    public function formatPhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        if (str_starts_with($phone, '+')) {
            return substr($phone, 1);
        }

        if (str_starts_with($phone, '00')) {
            return substr($phone, 2);
        }

        // Ajouter indicatif par défaut si numéro local (8-10 chiffres)
        if (strlen($phone) <= 10) {
            return '225' . $phone; // Côte d'Ivoire par défaut
        }

        return $phone;
    }
}

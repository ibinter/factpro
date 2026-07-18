<?php

namespace App\Services;

use App\Jobs\DispatchWebhookJob;
use App\Models\Company;
use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OutgoingWebhookService
{
    /**
     * Dispatcher les webhooks pour un événement donné à tous les endpoints actifs de la société.
     */
    public function dispatch(Company $company, string $event, array $payload): void
    {
        $endpoints = WebhookEndpoint::where('company_id', $company->id)
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->get()
            ->filter(fn (WebhookEndpoint $ep) => in_array($event, $ep->events ?? [], true));

        foreach ($endpoints as $endpoint) {
            $delivery = WebhookDelivery::create([
                'webhook_endpoint_id' => $endpoint->id,
                'event' => $event,
                'payload' => $payload,
                'attempt' => 1,
            ]);

            DispatchWebhookJob::dispatch($delivery);
        }
    }

    /**
     * Effectuer l'envoi HTTP d'une livraison webhook.
     * Appelé par le Job (et les retries).
     */
    public function sendDelivery(WebhookDelivery $delivery): void
    {
        $delivery->refresh();
        $endpoint = $delivery->endpoint;

        if (! $endpoint || ! $endpoint->is_active) {
            return;
        }

        $body = json_encode($delivery->payload);
        $signature = 'sha256='.hash_hmac('sha256', $body, $endpoint->secret);

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-FactPro-Signature' => $signature,
                'X-FactPro-Event' => $delivery->event,
            ])
                ->timeout(5)
                ->post($endpoint->url, $delivery->payload);

            $status = $response->status();
            $responseBody = $response->body();

            if ($response->successful()) {
                $delivery->update([
                    'response_status' => $status,
                    'response_body' => substr($responseBody, 0, 2000),
                    'delivered_at' => now(),
                    'attempt' => $delivery->attempt,
                ]);
            } else {
                $delivery->update([
                    'response_status' => $status,
                    'response_body' => substr($responseBody, 0, 2000),
                    'failed_at' => now(),
                    'attempt' => $delivery->attempt,
                ]);

                throw new \RuntimeException("Webhook delivery failed with status {$status}");
            }
        } catch (\Throwable $e) {
            $delivery->update([
                'failed_at' => now(),
                'attempt' => $delivery->attempt,
            ]);

            Log::warning('Webhook delivery failed', [
                'delivery_id' => $delivery->id,
                'endpoint_url' => $endpoint->url,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Calcule la signature HMAC pour un payload donné et un secret.
     */
    public function signature(string $secret, string $body): string
    {
        return 'sha256='.hash_hmac('sha256', $body, $secret);
    }
}

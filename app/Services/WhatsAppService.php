<?php

namespace App\Services;

use App\Models\NotificationChannel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Envoi WhatsApp via Twilio WhatsApp Business API.
 */
class WhatsAppService
{
    public function send(string $to, string $message, NotificationChannel $channel): bool
    {
        try {
            $config = $channel->config;
            $sid = $config['account_sid'];
            $token = $config['auth_token'];
            $from = $config['from_number'];

            $response = Http::withBasicAuth($sid, $token)
                ->asForm()
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                    'From' => "whatsapp:+{$from}",
                    'To' => "whatsapp:+{$to}",
                    'Body' => $message,
                ]);

            if ($response->successful()) {
                return true;
            }

            Log::warning('WhatsAppService: Twilio returned non-success', [
                'status' => $response->status(),
                'body' => $response->body(),
                'to' => $to,
            ]);

            return false;
        } catch (\Throwable $e) {
            Log::error('WhatsAppService: exception lors de l\'envoi WhatsApp', [
                'message' => $e->getMessage(),
                'to' => $to,
            ]);

            return false;
        }
    }
}

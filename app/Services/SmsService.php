<?php

namespace App\Services;

use App\Models\NotificationChannel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Envoi SMS via Africa's Talking API.
 */
class SmsService
{
    public function send(string $to, string $message, NotificationChannel $channel): bool
    {
        try {
            $config = $channel->config;

            $response = Http::withHeaders([
                'apiKey' => $config['api_key'],
                'Accept' => 'application/json',
            ])->asForm()->post('https://api.africastalking.com/version1/messaging', [
                'username' => $config['username'],
                'to' => $to,
                'message' => $message,
            ]);

            if ($response->successful() || $response->status() === 201) {
                return true;
            }

            Log::warning('SmsService: Africa\'s Talking returned non-success', [
                'status' => $response->status(),
                'body' => $response->body(),
                'to' => $to,
            ]);

            return false;
        } catch (\Throwable $e) {
            Log::error('SmsService: exception lors de l\'envoi SMS', [
                'message' => $e->getMessage(),
                'to' => $to,
            ]);

            return false;
        }
    }
}

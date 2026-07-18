<?php

namespace App\Services;

use App\Models\PushSubscription;
use App\Models\User;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\VAPID;
use Minishlink\WebPush\WebPush;

class PushNotificationService
{
    private WebPush $webPush;

    public function __construct()
    {
        $auth = [
            'VAPID' => [
                'subject'    => config('app.url'),
                'publicKey'  => config('services.vapid.public_key'),
                'privateKey' => config('services.vapid.private_key'),
            ],
        ];

        $this->webPush = new WebPush($auth);
    }

    /**
     * Envoie une notification push à tous les appareils actifs d'un utilisateur.
     * Retourne le nombre de subscriptions traitées.
     */
    public function sendToUser(User $user, string $title, string $body, array $data = []): int
    {
        $subscriptions = PushSubscription::where('user_id', $user->id)
            ->where('is_active', true)
            ->get();

        if ($subscriptions->isEmpty()) {
            return 0;
        }

        $payload = json_encode([
            'title'  => $title,
            'body'   => $body,
            'icon'   => '/icons/icon-192.png',
            'badge'  => '/icons/badge-72x72.png',
            'data'   => $data,
            'tag'    => $data['tag'] ?? 'factpro-notification',
        ]);

        $sent = 0;
        foreach ($subscriptions as $sub) {
            $subscription = Subscription::create([
                'endpoint'  => $sub->endpoint,
                'publicKey' => $sub->public_key,
                'authToken' => $sub->auth_token,
            ]);
            $this->webPush->queueNotification($subscription, $payload);
            $sent++;
        }

        foreach ($this->webPush->flush() as $report) {
            if (! $report->isSuccess()) {
                $statusCode = $report->getResponse()?->getStatusCode();
                // 410 Gone → endpoint invalide, on désactive
                if ($statusCode === 410) {
                    PushSubscription::where('endpoint', $report->getEndpoint())
                        ->update(['is_active' => false]);
                }
            } else {
                // Mettre à jour last_used_at
                PushSubscription::where('endpoint', $report->getEndpoint())
                    ->update(['last_used_at' => now()]);
            }
        }

        return $sent;
    }

    /**
     * Envoie une notification à tous les utilisateurs d'une société.
     */
    public function sendToCompany(int $companyId, string $title, string $body, array $data = []): void
    {
        $users = User::whereHas('companies', function ($q) use ($companyId) {
            $q->where('companies.id', $companyId);
        })->get();

        foreach ($users as $user) {
            $this->sendToUser($user, $title, $body, $data);
        }
    }

    /**
     * Génère une paire de clés VAPID.
     * Retourne ['public' => '...', 'private' => '...'].
     */
    public static function generateVapidKeys(): array
    {
        $keys = VAPID::createVapidKeys();

        return [
            'public'  => $keys['publicKey'],
            'private' => $keys['privateKey'],
        ];
    }
}

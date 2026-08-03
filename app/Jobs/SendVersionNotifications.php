<?php

namespace App\Jobs;

use App\Models\AppVersion;
use App\Models\License;
use App\Models\User;
use App\Notifications\NewVersionReleased;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendVersionNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public readonly AppVersion $version) {}

    public function handle(): void
    {
        // Utilisateurs avec une licence active (non expirée, non suspendue)
        $query = User::query()
            ->whereHas('licenses', function ($q) {
                $q->whereIn('status', ['active', 'trial'])
                  ->where(function ($q2) {
                      $q2->whereNull('ends_at')
                         ->orWhere('ends_at', '>', now());
                  });
            });

        // Filtre par plan si target_plans est renseigné
        if (! is_null($this->version->target_plans) && count($this->version->target_plans) > 0) {
            $planIds = $this->version->target_plans;
            $query->whereHas('licenses', function ($q) use ($planIds) {
                $q->whereIn('plan_id', $planIds)
                  ->whereIn('status', ['active', 'trial']);
            });
        }

        // Envoi par lot de 50
        $query->select(['id', 'name', 'email'])
              ->chunk(50, function ($users) {
                  foreach ($users as $user) {
                      $user->notify(new NewVersionReleased($this->version));
                  }
              });

        // Mise à jour du timestamp d'envoi
        $this->version->update(['notification_sent_at' => now()]);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('[SendVersionNotifications] Échec des notifications de version', [
            'version_id' => $this->version->id,
            'version'    => $this->version->version,
            'error'      => $exception->getMessage(),
        ]);
    }
}

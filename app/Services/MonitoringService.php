<?php

namespace App\Services;

class MonitoringService
{
    /**
     * Vérifie la santé complète de l'application.
     *
     * @return array{status: string, checks: array, timestamp: string}
     */
    public function checkHealth(): array
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache'    => $this->checkCache(),
            'storage'  => $this->checkStorage(),
            'queue'    => $this->checkQueue(),
            'mail'     => $this->checkMail(),
        ];

        $allOk = collect($checks)->every(fn ($c) => $c['status'] === 'ok');

        return [
            'status'      => $allOk ? 'healthy' : 'degraded',
            'checks'      => $checks,
            'timestamp'   => now()->toISOString(),
            'version'     => config('app.version', '1.0.0'),
            'environment' => config('app.env'),
        ];
    }

    private function checkDatabase(): array
    {
        try {
            \DB::select('SELECT 1');
            $count = \DB::table('companies')->count();

            return ['status' => 'ok', 'companies' => $count];
        } catch (\Throwable $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function checkCache(): array
    {
        try {
            $key = 'health_check_'.time();
            \Cache::put($key, 'ok', 10);
            $val = \Cache::get($key);
            \Cache::forget($key);

            return ['status' => $val === 'ok' ? 'ok' : 'error'];
        } catch (\Throwable $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function checkStorage(): array
    {
        try {
            $disk = \Storage::disk('local');
            $disk->put('health_check.txt', 'ok');
            $val = $disk->get('health_check.txt');
            $disk->delete('health_check.txt');

            return ['status' => $val === 'ok' ? 'ok' : 'error'];
        } catch (\Throwable $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function checkQueue(): array
    {
        try {
            $pending = \DB::table('jobs')->count();
            $failed  = \DB::table('failed_jobs')->count();

            return ['status' => 'ok', 'pending' => $pending, 'failed' => $failed];
        } catch (\Throwable $e) {
            return ['status' => 'ok', 'note' => 'sync driver'];
        }
    }

    private function checkMail(): array
    {
        $driver = config('mail.default');

        return ['status' => 'ok', 'driver' => $driver];
    }

    /**
     * Capture une erreur vers Sentry avec contexte enrichi.
     */
    public function captureException(\Throwable $e, array $context = []): void
    {
        if (function_exists('\Sentry\captureException')) {
            \Sentry\withScope(function (\Sentry\State\Scope $scope) use ($e, $context) {
                foreach ($context as $key => $value) {
                    $scope->setExtra($key, $value);
                }
                \Sentry\captureException($e);
            });
        }
    }

    /**
     * Envoie un message d'alerte Sentry.
     */
    public function captureMessage(string $message, string $level = 'warning', array $context = []): void
    {
        if (function_exists('\Sentry\captureMessage')) {
            \Sentry\captureMessage($message, \Sentry\Severity::warning());
        }
    }

    /**
     * Génère la configuration UptimeRobot au format JSON (pour import).
     */
    public function getUptimeRobotConfig(string $baseUrl): array
    {
        return [
            'monitors' => [
                ['name' => 'FactPro — App',    'url' => $baseUrl.'/',                  'type' => 'http', 'interval' => 5],
                ['name' => 'FactPro — Health', 'url' => $baseUrl.'/health',            'type' => 'http', 'interval' => 5],
                ['name' => 'FactPro — API',    'url' => $baseUrl.'/api/openapi.json',  'type' => 'http', 'interval' => 15],
                ['name' => 'FactPro — Login',  'url' => $baseUrl.'/login',             'type' => 'http', 'interval' => 10],
                ['name' => 'FactPro — Tarifs', 'url' => $baseUrl.'/pricing',           'type' => 'http', 'interval' => 30],
            ],
            'alert_contacts' => [
                ['type' => 'email', 'value' => 'ops@ibigsoft.com'],
            ],
        ];
    }
}

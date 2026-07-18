<?php

namespace App\Http\Controllers;

use App\Services\MonitoringService;
use Illuminate\Http\JsonResponse;

class HealthController extends Controller
{
    /**
     * GET /health — statut simplifié (pour load balancer, UptimeRobot).
     */
    public function simple(): JsonResponse
    {
        return response()->json([
            'status'    => 'ok',
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * GET /health/detailed — statut complet (auth requise).
     */
    public function detailed(): JsonResponse
    {
        $health     = app(MonitoringService::class)->checkHealth();
        $statusCode = $health['status'] === 'healthy' ? 200 : 503;

        return response()->json($health, $statusCode);
    }

    /**
     * GET /health/uptimerobot — config UptimeRobot (auth superadmin).
     */
    public function uptimeRobotConfig(): JsonResponse
    {
        $config = app(MonitoringService::class)->getUptimeRobotConfig(config('app.url'));

        return response()->json($config);
    }
}

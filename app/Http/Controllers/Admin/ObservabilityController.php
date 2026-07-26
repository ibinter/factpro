<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppVersion;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ObservabilityController extends Controller
{
    public function index(): Response
    {
        // 1. Queue stats
        try {
            $failedJobs  = DB::table('failed_jobs')->count();
            $pendingJobs = DB::table('jobs')->count();
            $queueHealth = $failedJobs < 5 ? 'ok' : 'warning';
        } catch (\Exception $e) {
            $failedJobs = 0;
            $pendingJobs = 0;
            $queueHealth = 'unknown';
        }

        // 2. DB check
        try {
            DB::connection()->getPdo();
            $dbHealth = 'ok';
        } catch (\Exception $e) {
            $dbHealth = 'error';
        }

        // 3. Disk space
        $diskFree         = @disk_free_space('/') ?: @disk_free_space('C:\\') ?: 0;
        $diskTotal        = @disk_total_space('/') ?: @disk_total_space('C:\\') ?: 1;
        $diskUsedPercent  = $diskTotal > 0 ? round((1 - $diskFree / $diskTotal) * 100) : 0;
        $diskHealth       = $diskUsedPercent > 85 ? 'warning' : 'ok';

        // 4. Recent errors (from logs)
        $logFile      = storage_path('logs/laravel.log');
        $recentErrors = [];
        if (file_exists($logFile)) {
            $lines = array_slice(file($logFile), -200);
            foreach ($lines as $line) {
                if (str_contains($line, '.ERROR')) {
                    $recentErrors[] = trim(substr($line, 0, 200));
                }
            }
            $recentErrors = array_slice(array_reverse($recentErrors), 0, 10);
        }

        // 5. Stats utilisateurs
        $totalUsers    = User::count();
        $activeUsers   = User::whereHas('company', fn ($q) => $q->where('status', 'active'))->count();
        $trialUsers    = User::whereHas('company', fn ($q) => $q->where('status', 'trial'))->count();
        $newUsersToday = User::whereDate('created_at', today())->count();

        // 6. App version
        $appVersion = AppVersion::published()
            ->latest('published_at')
            ->value('version') ?? '1.0.0';

        return Inertia::render('Admin/Observability', [
            'stats' => [
                'db'               => $dbHealth,
                'queue'            => $queueHealth,
                'disk'             => $diskHealth,
                'disk_used_percent' => $diskUsedPercent,
                'failed_jobs'      => $failedJobs,
                'pending_jobs'     => $pendingJobs,
                'disk_free_gb'     => round($diskFree / 1024 / 1024 / 1024, 1),
            ],
            'users' => [
                'total'     => $totalUsers,
                'active'    => $activeUsers,
                'trial'     => $trialUsers,
                'new_today' => $newUsersToday,
            ],
            'recent_errors' => $recentErrors,
            'app_version'   => $appVersion,
            'checked_at'    => now()->toIso8601String(),
        ]);
    }

    public function clearCache()
    {
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');

        return back()->with('success', 'Cache vidé avec succès.');
    }

    public function clearConfigCache()
    {
        Artisan::call('config:clear');
        return back()->with('success', 'Cache de configuration vidé.');
    }

    public function clearRouteCache()
    {
        Artisan::call('route:clear');
        return back()->with('success', 'Cache des routes vidé.');
    }

    public function clearViewCache()
    {
        Artisan::call('view:clear');
        return back()->with('success', 'Cache des vues vidé.');
    }
}

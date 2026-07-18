<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CacheService
{
    // TTLs par type de données (en secondes)
    const TTL_DASHBOARD = 300;      // 5 min — données temps quasi-réel
    const TTL_REPORTS = 3600;       // 1h — rapports agrégés
    const TTL_PLANS = 86400;        // 24h — plans tarifaires
    const TTL_TEMPLATES = 86400;    // 24h — liste templates PDF
    const TTL_EXCHANGE_RATES = 3600; // 1h — taux de change

    /**
     * Get or cache with company-scoped key.
     */
    public static function rememberForCompany(int $companyId, string $key, int $ttl, \Closure $callback): mixed
    {
        $cacheKey = "company_{$companyId}_{$key}";

        return Cache::remember($cacheKey, $ttl, $callback);
    }

    /**
     * Invalidate cache for a company (after mutations).
     */
    public static function forgetCompany(int $companyId, string $key = '*'): void
    {
        if ($key === '*') {
            if (Cache::getStore() instanceof \Illuminate\Cache\RedisStore) {
                Cache::tags(["company_{$companyId}"])->flush();
            } else {
                // File driver: pas de tags — clés prévisibles
                $keys = ['dashboard_stats', 'monthly_revenue', 'top_customers', 'top_products', 'active_alerts', 'conversion_rate'];
                foreach ($keys as $k) {
                    Cache::forget("company_{$companyId}_{$k}");
                }
            }
        } else {
            Cache::forget("company_{$companyId}_{$key}");
        }
    }

    /**
     * Cache global (non lié à une company).
     */
    public static function rememberGlobal(string $key, int $ttl, \Closure $callback): mixed
    {
        return Cache::remember("global_{$key}", $ttl, $callback);
    }

    /**
     * Forget a global cache key.
     */
    public static function forgetGlobal(string $key): void
    {
        Cache::forget("global_{$key}");
    }
}

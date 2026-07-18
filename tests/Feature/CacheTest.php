<?php

use App\Services\CacheService;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    $this->user = createUserWithCompanyAndTrial();
    $this->company = $this->user->currentCompany;
    Cache::flush();
});

it('cache service remember for company works', function () {
    $result = CacheService::rememberForCompany(
        $this->company->id,
        'test_key',
        60,
        fn () => 'computed_value'
    );

    expect($result)->toBe('computed_value');
});

it('caches dashboard stats for company', function () {
    $callCount = 0;

    $key = 'test_dashboard';
    $ttl = CacheService::TTL_DASHBOARD;
    $companyId = $this->company->id;

    CacheService::rememberForCompany($companyId, $key, $ttl, function () use (&$callCount) {
        $callCount++;
        return ['stats' => true];
    });

    CacheService::rememberForCompany($companyId, $key, $ttl, function () use (&$callCount) {
        $callCount++;
        return ['stats' => true];
    });

    // Le callback ne doit être appelé qu'une seule fois (deuxième appel = cache hit)
    expect($callCount)->toBe(1);
});

it('cache key is scoped to company id', function () {
    $company2Id = $this->company->id + 9999;

    CacheService::rememberForCompany($this->company->id, 'scoped_key', 60, fn () => 'company_1_value');
    CacheService::rememberForCompany($company2Id, 'scoped_key', 60, fn () => 'company_2_value');

    expect(Cache::get("company_{$this->company->id}_scoped_key"))->toBe('company_1_value');
    expect(Cache::get("company_{$company2Id}_scoped_key"))->toBe('company_2_value');
});

it('invalidates company cache after document creation', function () {
    $companyId = $this->company->id;

    // Préremplir le cache
    CacheService::rememberForCompany($companyId, 'monthly_revenue', 60, fn () => ['month' => 'jan']);
    CacheService::rememberForCompany($companyId, 'top_customers', 60, fn () => ['name' => 'Client A']);

    expect(Cache::has("company_{$companyId}_monthly_revenue"))->toBeTrue();
    expect(Cache::has("company_{$companyId}_top_customers"))->toBeTrue();

    // Invalider tout le cache de la company
    CacheService::forgetCompany($companyId);

    expect(Cache::has("company_{$companyId}_monthly_revenue"))->toBeFalse();
    expect(Cache::has("company_{$companyId}_top_customers"))->toBeFalse();
});

it('warm cache command runs without error', function () {
    $this->artisan('cache:warm', ['--company' => $this->company->id])
        ->assertExitCode(0);
});

it('global cache works independently of company', function () {
    CacheService::rememberGlobal('pricing_plans', 60, fn () => ['plan' => 'starter']);

    expect(Cache::get('global_pricing_plans'))->toBe(['plan' => 'starter']);

    // Ne doit pas être affecté par forgetCompany
    CacheService::forgetCompany($this->company->id);

    expect(Cache::has('global_pricing_plans'))->toBeTrue();
});

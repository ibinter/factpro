<?php

use App\Models\User;
use App\Services\MonitoringService;

/*
|--------------------------------------------------------------------------
| Phase 17 — Monitoring Sentry & UptimeRobot
|--------------------------------------------------------------------------
*/

it('health endpoint returns 200', function () {
    $this->getJson('/health')->assertStatus(200);
});

it('health endpoint returns ok status', function () {
    $this->getJson('/health')
        ->assertStatus(200)
        ->assertJson(['status' => 'ok']);
});

it('detailed health checks database', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->getJson('/health/detailed')
        ->assertJsonPath('checks.database.status', 'ok');
});

it('detailed health checks cache', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->getJson('/health/detailed')
        ->assertJsonPath('checks.cache.status', 'ok');
});

it('detailed health checks storage', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->getJson('/health/detailed')
        ->assertJsonPath('checks.storage.status', 'ok');
});

it('detailed health returns healthy when all ok', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->getJson('/health/detailed');

    $response->assertStatus(200)
        ->assertJsonPath('status', 'healthy');
});

it('uptimerobot config has 5 monitors', function () {
    $user = User::factory()->create(['is_superadmin' => true]);

    $response = $this->actingAs($user)
        ->getJson('/health/uptimerobot');

    $response->assertStatus(200);
    $data = $response->json();
    expect($data['monitors'])->toHaveCount(5);
});

it('health check command returns 0 on success', function () {
    $this->artisan('app:health-check')->assertExitCode(0);
});

it('monitoring service captures exception silently', function () {
    $service = app(MonitoringService::class);

    // Should not throw even without Sentry DSN configured
    $service->captureException(new \RuntimeException('test error'), ['foo' => 'bar']);

    expect(true)->toBeTrue();
});

it('sentry config file exists', function () {
    expect(file_exists(config_path('sentry.php')))->toBeTrue();
});

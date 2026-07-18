<?php

use Illuminate\Support\Facades\Route;

it('ci workflow file exists', function () {
    expect(file_exists(base_path('.github/workflows/ci.yml')))->toBeTrue();
});

it('deploy workflow file exists', function () {
    expect(file_exists(base_path('.github/workflows/deploy.yml')))->toBeTrue();
});

it('security workflow file exists', function () {
    expect(file_exists(base_path('.github/workflows/security.yml')))->toBeTrue();
});

it('env ci file exists with required keys', function () {
    $path = base_path('.env.ci');
    expect(file_exists($path))->toBeTrue();

    $content = file_get_contents($path);
    expect($content)
        ->toContain('APP_ENV=testing')
        ->toContain('DB_CONNECTION=mysql')
        ->toContain('DB_DATABASE=factpro_test')
        ->toContain('CACHE_DRIVER=array')
        ->toContain('QUEUE_CONNECTION=sync');
});

it('health check route returns 200', function () {
    $response = $this->get('/health');
    $response->assertStatus(200);
});

it('health check response has status ok', function () {
    $response = $this->getJson('/health');
    $response->assertJson(['status' => 'ok']);
});

it('health check includes app version info', function () {
    $response = $this->getJson('/health');
    $response->assertJsonStructure([
        'status',
        'app',
        'env',
        'timestamp',
        'php',
        'laravel',
    ]);
});

it('scripts directory has deploy script', function () {
    expect(file_exists(base_path('scripts/deploy.sh'))
        || file_exists(base_path('scripts/ci-setup.sh'))
    )->toBeTrue();
});

it('scripts directory has health check script', function () {
    expect(file_exists(base_path('scripts/health-check.sh')))->toBeTrue();
});

it('github secrets documented in ci-cd guide', function () {
    $path = base_path('docs/CI-CD.md');
    expect(file_exists($path))->toBeTrue();

    $content = file_get_contents($path);
    expect($content)
        ->toContain('LWS_HOST')
        ->toContain('LWS_SSH_KEY')
        ->toContain('LWS_USERNAME')
        ->toContain('DEPLOY_SECRET');
});

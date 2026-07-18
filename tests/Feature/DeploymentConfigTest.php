<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

describe('DeploymentConfig', function () {

    it('has env production example file', function () {
        $path = base_path('.env.production.example');
        expect(File::exists($path))->toBeTrue();
    });

    it('env production example contains required variables', function () {
        $content = File::get(base_path('.env.production.example'));

        $required = [
            'APP_NAME',
            'APP_ENV',
            'APP_KEY',
            'APP_DEBUG',
            'APP_URL',
            'DB_CONNECTION',
            'DB_HOST',
            'DB_DATABASE',
            'DB_USERNAME',
            'DB_PASSWORD',
            'QUEUE_CONNECTION',
            'CACHE_STORE',
            'SESSION_DRIVER',
            'MAIL_MAILER',
            'MAIL_FROM_ADDRESS',
            'VERIFY_BASE_URL',
            'MONEROO_PUBLIC_KEY',
            'MONEROO_SECRET_KEY',
            'MONEROO_WEBHOOK_SECRET',
        ];

        foreach ($required as $var) {
            expect($content)->toContain($var);
        }
    });

    it('has deployment documentation files', function () {
        $files = [
            'docs/deployment/LWS-MUTUALIZE.md',
            'docs/deployment/LWS-VPS.md',
            'docs/deployment/PRODUCTION-CHECKLIST.md',
            'docs/deployment/ENV-REFERENCE.md',
        ];

        foreach ($files as $file) {
            expect(File::exists(base_path($file)))->toBeTrue();
        }
    });

    it('has deploy and backup scripts', function () {
        expect(File::exists(base_path('scripts/deploy.sh')))->toBeTrue();
        expect(File::exists(base_path('scripts/backup.sh')))->toBeTrue();
        expect(strlen(File::get(base_path('scripts/deploy.sh'))))->toBeGreaterThan(100);
        expect(strlen(File::get(base_path('scripts/backup.sh'))))->toBeGreaterThan(100);
    });

    it('has queue connection configured', function () {
        $connection = config('queue.default');
        expect($connection)->toBeString()->not->toBeEmpty();

        $validConnections = ['sync', 'database', 'redis', 'sqs', 'beanstalkd'];
        expect(in_array($connection, $validConnections))->toBeTrue();
    });

    it('has cache store configured', function () {
        $store = config('cache.default');
        expect($store)->toBeString()->not->toBeEmpty();

        $validStores = ['file', 'database', 'redis', 'array', 'memcached', 'dynamodb', 'octane', 'null'];
        expect(in_array($store, $validStores))->toBeTrue();
    });

    it('has session driver configured', function () {
        $driver = config('session.driver');
        expect($driver)->toBeString()->not->toBeEmpty();

        $validDrivers = ['file', 'cookie', 'database', 'apc', 'memcached', 'redis', 'array', 'dynamodb'];
        expect(in_array($driver, $validDrivers))->toBeTrue();
    });

    it('has mail configuration', function () {
        $mailer = config('mail.default');
        expect($mailer)->toBeString()->not->toBeEmpty();

        $fromAddress = config('mail.from.address');
        expect($fromAddress)->not->toBeEmpty();
        expect($fromAddress)->toContain('@');
    });

    it('has app url configured', function () {
        $url = config('app.url');
        expect($url)->not->toBeEmpty();
        expect($url)->toStartWith('http');
    });

    it('has database connection configured', function () {
        $connection = config('database.default');
        expect($connection)->not->toBeEmpty();

        $connectionConfig = config("database.connections.$connection");
        expect($connectionConfig)->toBeArray();
        expect($connectionConfig['database'] ?? '')->not->toBeEmpty();
    });

    it('artisan optimize runs without error', function () {
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');

        $exitCode = Artisan::call('config:cache');
        expect($exitCode)->toBe(0);

        // cleanup pour ne pas affecter les autres tests
        Artisan::call('config:clear');
    });

    it('artisan route cache runs without error', function () {
        $exitCode = Artisan::call('route:cache');
        expect($exitCode)->toBe(0);
        // Clear immédiatement pour ne pas affecter les tests suivants
        Artisan::call('route:clear');
        // Forcer le rechargement de la config (le RouteServiceProvider peut
        // avoir mémorisé le chemin du cache — on purge tout)
        Artisan::call('optimize:clear');
    });

    it('artisan view cache runs without error', function () {
        $exitCode = Artisan::call('view:cache');
        expect($exitCode)->toBe(0);
        Artisan::call('view:clear');
    });

    it('storage directory is writable', function () {
        expect(is_writable(storage_path()))->toBeTrue();
        expect(is_writable(storage_path('logs')))->toBeTrue();
        expect(is_writable(storage_path('app')))->toBeTrue();
    });

    it('bootstrap cache directory is writable', function () {
        expect(is_writable(base_path('bootstrap/cache')))->toBeTrue();
    });

    it('env production example has correct production values', function () {
        $content = File::get(base_path('.env.production.example'));

        // APP_DEBUG=false (ligne non commentée)
        expect($content)->toContain('APP_DEBUG=false');

        // APP_ENV=production
        expect($content)->toContain('APP_ENV=production');

        // LOG_LEVEL=error
        expect($content)->toContain('LOG_LEVEL=error');

        // MAIL_MAILER=smtp (pas log)
        expect($content)->toContain('MAIL_MAILER=smtp');
    });

    it('verify base url is documented', function () {
        $content = File::get(base_path('.env.production.example'));
        expect($content)->toContain('VERIFY_BASE_URL');
    });

    it('env production example has payment gateways documented', function () {
        $content = File::get(base_path('.env.production.example'));

        $gateways = [
            'MONEROO_PUBLIC_KEY',
            'MONEROO_SECRET_KEY',
            'CINETPAY_API_KEY',
            'FEDAPAY_SECRET_KEY',
            'FLUTTERWAVE_SECRET_KEY',
            'STRIPE_KEY',
        ];

        foreach ($gateways as $gateway) {
            expect($content)->toContain($gateway);
        }
    });

});

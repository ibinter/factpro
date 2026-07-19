<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        // Détecter les N+1 en dev : log au lieu de bloquer
        if (app()->environment('local')) {
            Model::preventLazyLoading();
            Model::handleLazyLoadingViolationUsing(function ($model, $relation) {
                $class = get_class($model);
                info("Lazy loading detected: {$class}::{$relation}");
            });
        }
    }
}

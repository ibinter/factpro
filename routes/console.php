<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Planification IBIG FactPro — cycle de vie paiements & licences
|--------------------------------------------------------------------------
*/

// Expiration des commandes en attente de paiement
Schedule::command('payments:expire-orders')->everyFiveMinutes();

// Expiration des essais gratuits + rappels J-7 / J-3 / J-1
Schedule::command('trials:check-expiration')->dailyAt('08:00');

// Alertes d'expiration J-7 / J-3 / J-1 des licences payantes
Schedule::command('licenses:send-expiry-alerts')->dailyAt('09:00');

// Passage en période de tolérance des licences échues
Schedule::command('licenses:apply-grace-period')->dailyAt('00:00');

// Suspension des licences dont la période de tolérance est terminée
Schedule::command('licenses:auto-suspend')->dailyAt('00:30');

// Suspension des licences provisoires échues sans validation du paiement
Schedule::command('licenses:expire-provisional')->hourly();

/*
|--------------------------------------------------------------------------
| Relances intelligentes (cahier des charges §13)
|--------------------------------------------------------------------------
*/

// Passage automatique en retard (overdue) des factures échues impayées
Schedule::command('invoices:mark-overdue')->dailyAt('07:30');

// Relances email automatiques : escalade J+3 / J+7 / J+15
Schedule::command('reminders:send')->dailyAt('08:30');

/*
|--------------------------------------------------------------------------
| Factures récurrentes — abonnements automatiques (cahier §3)
|--------------------------------------------------------------------------
*/

// Génération des factures récurrentes arrivées à échéance
Schedule::command('invoices:generate-recurring')->dailyAt('06:00');

/*
|--------------------------------------------------------------------------
| Multi-devises — taux de change (cahier §3 DEV / §14)
|--------------------------------------------------------------------------
*/

// Rafraîchissement quotidien des taux de change (API publique, repli taux fixes)
Schedule::command('rates:refresh')->dailyAt('05:30');

/*
|--------------------------------------------------------------------------
| Optimisation performances — Phase 12
|--------------------------------------------------------------------------
*/

// Pré-chauffage cache dashboard toutes les 10 minutes
Schedule::command('cache:warm --all')->everyTenMinutes();

// Analyse des requêtes chaque nuit
Schedule::command('perf:analyze-queries')->dailyAt('03:00');

/*
|--------------------------------------------------------------------------
| Forecasting & Objectifs — Phase 13
|--------------------------------------------------------------------------
*/

// Snapshot forecast quotidien pour toutes les companies actives
Schedule::call(function () {
    $service = app(\App\Services\ForecastingService::class);
    \App\Models\Company::query()->each(fn ($company) => $service->saveSnapshot($company->id));
})->dailyAt('23:55')->name('forecasting:daily-snapshot');

// Vérification sous-performance à mi-mois (alerte log)
Schedule::call(function () {
    $service = app(\App\Services\ForecastingService::class);
    \App\Models\Company::query()->each(function ($company) use ($service) {
        $underperformers = $service->checkUnderperformance($company->id);
        if (! empty($underperformers)) {
            \Illuminate\Support\Facades\Log::info('Forecasting: sous-performance détectée', [
                'company_id' => $company->id,
                'underperformers' => $underperformers,
            ]);
        }
    });
})->monthlyOn(15, '09:00')->name('forecasting:underperformance-check');

/*
|--------------------------------------------------------------------------
| Email Tracking — alertes non ouvert après 3 jours (Phase 13)
|--------------------------------------------------------------------------
*/
Schedule::call(function () {
    app(\App\Services\EmailTrackingService::class)->checkUnopenedAndAlert(3);
})->dailyAt('09:00')->name('email-tracking.alert');

/*
|--------------------------------------------------------------------------
| Réapprovisionnement automatique — Phase 16
|--------------------------------------------------------------------------
*/

// Vérification quotidienne des seuils de stock et génération des BOC
Schedule::call(function () {
    $service = app(\App\Services\AutoReorderService::class);
    \App\Models\Company::query()->each(
        fn ($company) => $service->checkAndTrigger($company->id)
    );
})->dailyAt('07:00')->name('stock.auto-reorder');

/*
|--------------------------------------------------------------------------
| Cycle de vie e-mails — essais & licences
|--------------------------------------------------------------------------
*/

// E-mails de cycle de vie : fin d'essai J-3/J-1, expiration licence J-7/J-1, licence expirée hier
Schedule::command('emails:lifecycle')->dailyAt('09:00');

// Séquence d'onboarding : J+1, J+3, J+7, J+14 après inscription
Schedule::command('emails:onboarding')->dailyAt('10:00');

// Rappel fin d'essai gratuit J-3 (template HTML brandé)
Schedule::command('emails:trial-ending')->dailyAt('09:00');

/*
|--------------------------------------------------------------------------
| Monitoring santé applicative — Phase 17
|--------------------------------------------------------------------------
*/

Schedule::command('app:health-check --alert')->dailyAt('06:00')->name('health.check');

/*
|--------------------------------------------------------------------------
| Scoring clients — Phase 14
|--------------------------------------------------------------------------
*/
Schedule::command('scoring:customers')->dailyAt('02:00');

/*
|--------------------------------------------------------------------------
| Coffre-fort numérique — Phase 16B
|--------------------------------------------------------------------------
*/

// Archive automatique des documents finalisés des dernières 24h
Schedule::command('vault:auto-archive')->dailyAt('01:00')->name('vault.auto-archive');

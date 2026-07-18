<?php

namespace App\Services;

use App\Models\Document;
use App\Models\EmailTracking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EmailTrackingService
{
    /**
     * Crée un tracking record et retourne l'instance.
     */
    public function createTracking(Document $document, string $recipientEmail): EmailTracking
    {
        return EmailTracking::create([
            'document_id'    => $document->id,
            'company_id'     => $document->company_id,
            'recipient_email'=> $recipientEmail,
            'tracking_token' => Str::random(48),
            'sent_at'        => now(),
        ]);
    }

    /**
     * Génère le HTML du pixel de tracking (image 1x1 transparente).
     */
    public function getPixelHtml(string $token): string
    {
        $url = route('tracking.open', $token);
        return '<img src="'.$url.'" width="1" height="1" alt="" style="display:none;">';
    }

    /**
     * Génère un lien tracké vers le PDF.
     */
    public function getTrackedPdfUrl(string $token, string $originalUrl): string
    {
        return route('tracking.click', ['token' => $token, 'url' => urlencode($originalUrl)]);
    }

    /**
     * Enregistre une ouverture d'email.
     */
    public function recordOpen(EmailTracking $tracking, Request $request): void
    {
        $tracking->increment('opens_count');
        $tracking->update([
            'opened_at'      => $tracking->opened_at ?? now(),
            'last_opened_at' => now(),
            'client_ip'      => $request->ip(),
            'user_agent'     => $request->userAgent(),
        ]);
    }

    /**
     * Enregistre un clic sur le PDF.
     */
    public function recordClick(EmailTracking $tracking): void
    {
        $tracking->increment('clicks_count');
        $tracking->update([
            'clicked_at'      => $tracking->clicked_at ?? now(),
            'last_clicked_at' => now(),
        ]);
    }

    /**
     * Retourne les stats d'engagement pour une company.
     */
    public function getStats(int $companyId, int $days = 30): array
    {
        $since = now()->subDays($days);

        $records = EmailTracking::where('company_id', $companyId)
            ->where('sent_at', '>=', $since)
            ->get();

        $total   = $records->count();
        $opened  = $records->whereNotNull('opened_at')->count();
        $clicked = $records->whereNotNull('clicked_at')->count();

        // Meilleure heure d'envoi (heure avec le plus d'ouvertures)
        $opensByHour = $records
            ->whereNotNull('opened_at')
            ->groupBy(fn ($r) => $r->opened_at->format('H'))
            ->map->count()
            ->sortDesc();

        $bestHour = $opensByHour->isEmpty() ? null : (int) $opensByHour->keys()->first();

        // Non ouverts depuis > 3 jours
        $unopened = EmailTracking::where('company_id', $companyId)
            ->whereNull('opened_at')
            ->where('sent_at', '<=', now()->subDays(3))
            ->count();

        // Ouvertures par jour
        $opensByDay = EmailTracking::where('company_id', $companyId)
            ->whereNotNull('opened_at')
            ->where('opened_at', '>=', $since)
            ->get()
            ->groupBy(fn ($r) => $r->opened_at->format('Y-m-d'))
            ->map->count()
            ->toArray();

        return [
            'total_sent'      => $total,
            'total_opened'    => $opened,
            'total_clicked'   => $clicked,
            'open_rate'       => $total > 0 ? round($opened / $total * 100, 1) : 0,
            'click_rate'      => $total > 0 ? round($clicked / $total * 100, 1) : 0,
            'unopened_3days'  => $unopened,
            'best_send_hour'  => $bestHour,
            'opens_by_day'    => $opensByDay,
            'period_days'     => $days,
        ];
    }

    /**
     * Détecte les factures non ouvertes depuis N jours et envoie une alerte au vendeur.
     */
    public function checkUnopenedAndAlert(int $afterDays = 3): int
    {
        $cutoff = now()->subDays($afterDays);

        $records = EmailTracking::whereNull('opened_at')
            ->whereNull('alert_sent_at')
            ->where('sent_at', '<=', $cutoff)
            ->with(['document.company'])
            ->get();

        $count = 0;
        foreach ($records as $tracking) {
            $company = $tracking->document->company ?? null;
            if (! $company) {
                continue;
            }

            // Envoyer un email d'alerte au propriétaire de la company
            $ownerEmail = $company->owner_email ?? null;

            if ($ownerEmail) {
                Mail::raw(
                    "Bonjour,\n\nLe document {$tracking->document->number} envoyé à {$tracking->recipient_email} le "
                    .$tracking->sent_at->format('d/m/Y')." n'a pas encore été ouvert.\n\nConnectez-vous à FactPro pour relancer votre client.",
                    function ($m) use ($ownerEmail, $tracking) {
                        $m->to($ownerEmail)
                          ->subject("[FactPro] Document non ouvert : {$tracking->document->number}");
                    }
                );
            }

            $tracking->update(['alert_sent_at' => now()]);
            $count++;
        }

        return $count;
    }
}

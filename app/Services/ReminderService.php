<?php

namespace App\Services;

use App\Mail\InvoiceReminderMail;
use App\Models\Company;
use App\Models\Document;
use App\Models\NotificationChannel;
use App\Models\ReminderLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Mail;

/**
 * Relances intelligentes (cahier des charges §13).
 *
 * Scénario d'escalade en 3 niveaux : J+3 courtois → J+7 ferme → J+15 mise en demeure.
 * Les seuils (jours) sont personnalisables par société via settings['reminders']['levels'].
 */
class ReminderService
{
    /** Statuts d'une facture impayée "active" (relançable) */
    public const ACTIVE_STATUSES = ['sent', 'viewed', 'partial', 'overdue'];

    public const MAX_LEVEL = 3;

    /** Configuration par défaut des 3 niveaux d'escalade */
    public const DEFAULT_LEVELS = [
        1 => ['days' => 3,  'tone' => 'courtois',         'subject' => 'Rappel amical — Facture {number}'],
        2 => ['days' => 7,  'tone' => 'ferme',            'subject' => 'Relance — Facture {number} échue'],
        3 => ['days' => 15, 'tone' => 'mise_en_demeure',  'subject' => 'Mise en demeure — Facture {number}'],
    ];

    /**
     * Niveaux effectifs de la société : défauts fusionnés avec
     * settings['reminders']['levels'] (jours personnalisables).
     *
     * @return array<int, array{days:int, tone:string, subject:string}>
     */
    public function levels(Company $company): array
    {
        $custom = data_get($company->settings, 'reminders.levels', []);

        $levels = [];
        foreach (self::DEFAULT_LEVELS as $n => $default) {
            $override = is_array($custom[$n] ?? null) ? $custom[$n] : [];
            $levels[$n] = [
                'days' => max(1, (int) ($override['days'] ?? $default['days'])),
                'tone' => $default['tone'],
                'subject' => (string) ($override['subject'] ?? $default['subject']),
            ];
        }

        return $levels;
    }

    /** Les relances automatiques sont-elles activées ? (oui par défaut) */
    public function isEnabled(Company $company): bool
    {
        return data_get($company->settings, 'reminders.enabled') !== false;
    }

    /**
     * Factures en retard relançables : type facture, statut actif impayé,
     * échéance dépassée, solde restant dû, client avec adresse email.
     *
     * @return Collection<int, Document>
     */
    public function overdueInvoices(Company $company): Collection
    {
        return $this->overdueQuery($company)
            ->whereHas('customer', fn ($q) => $q->whereNotNull('email')->where('email', '!=', ''))
            ->with('customer:id,name,email')
            ->orderBy('due_date')
            ->get();
    }

    /**
     * Prochain niveau de relance : dernier niveau envoyé + 1, plafonné à 3.
     * Null si la mise en demeure (niveau 3) a déjà été envoyée.
     */
    public function nextLevelFor(Document $document): ?int
    {
        $last = (int) ReminderLog::where('document_id', $document->id)->max('level');

        return $last >= self::MAX_LEVEL ? null : $last + 1;
    }

    /**
     * Envoie la relance de niveau $level pour la facture, si les garde-fous passent :
     * email client présent, pas de doublon (document, niveau) — idempotence —,
     * solde restant dû > 0. Passe la facture en `overdue` si besoin.
     */
    public function send(Document $document, int $level, string $triggeredBy = 'auto', ?int $sentBy = null): ?ReminderLog
    {
        $document->loadMissing(['customer', 'company']);

        $email = $document->customer?->email;

        if (empty($email) || $document->balance_due <= 0) {
            return null;
        }

        // Idempotence : un seul envoi par (facture, niveau)
        if (ReminderLog::where('document_id', $document->id)->where('level', $level)->exists()) {
            return null;
        }

        $config = $this->levels($document->company)[$level] ?? null;
        if ($config === null) {
            return null;
        }

        $config['subject'] = str_replace('{number}', $document->number, $config['subject']);

        Mail::to($email)->send(new InvoiceReminderMail($document, $level, $config));

        // SMS / WhatsApp en complément de l'email (canaux actifs de la société)
        $this->sendViaNotificationChannels($document);

        $log = ReminderLog::create([
            'company_id' => $document->company_id,
            'document_id' => $document->id,
            'level' => $level,
            'channel' => 'email',
            'sent_to' => $email,
            'subject' => $config['subject'],
            'triggered_by' => $triggeredBy,
            'sent_by' => $sentBy,
            'sent_at' => now(),
        ]);

        if ($document->status !== 'overdue') {
            $document->update(['status' => 'overdue']);
        }

        return $log;
    }

    /**
     * Relance automatique de toutes les factures en retard de la société.
     *
     * Choix assumé (anti-spam de rattrapage) : on envoie UNE seule relance par
     * facture et par exécution, au niveau le plus élevé atteint par le retard réel.
     * Exemple : facture à J+10 jamais relancée → on envoie directement le niveau 2
     * (ferme) sans envoyer d'abord le niveau 1 — le client ne reçoit pas une rafale
     * de rappels obsolètes. La progression reste monotone via nextLevelFor().
     *
     * @return int Nombre de relances envoyées
     */
    public function runAuto(Company $company): int
    {
        $levels = $this->levels($company);
        $sent = 0;

        foreach ($this->overdueInvoices($company) as $document) {
            $next = $this->nextLevelFor($document);
            if ($next === null) {
                continue; // mise en demeure déjà envoyée
            }

            $daysLate = (int) $document->due_date->startOfDay()->diffInDays(today());

            // Niveau le plus élevé dont le seuil est atteint par le retard réel
            $target = null;
            foreach ($levels as $n => $config) {
                if ($daysLate >= $config['days']) {
                    $target = $n;
                }
            }

            if ($target === null || $target < $next) {
                continue; // seuil pas encore atteint pour le prochain niveau
            }

            if ($this->send($document, $target, 'auto') !== null) {
                $sent++;
            }
        }

        return $sent;
    }

    /**
     * Envoie le message de rappel via tous les canaux SMS/WhatsApp actifs de la société.
     * N'interrompt jamais le flux principal — les erreurs sont loggées uniquement.
     */
    protected function sendViaNotificationChannels(Document $document): void
    {
        $phone = $document->customer?->phone ?? null;
        if (empty($phone)) {
            return;
        }

        $channels = NotificationChannel::where('company_id', $document->company_id)
            ->where('is_active', true)
            ->get();

        if ($channels->isEmpty()) {
            return;
        }

        $message = sprintf(
            'Rappel : votre facture %s de %s %s est en attente. Merci de régler rapidement. - %s',
            $document->number,
            number_format((float) $document->balance_due, 0, ',', ' '),
            $document->currency,
            $document->company?->name ?? ''
        );

        foreach ($channels as $channel) {
            try {
                if ($channel->type === 'sms') {
                    app(SmsService::class)->send($phone, $message, $channel);
                } elseif ($channel->type === 'whatsapp') {
                    app(WhatsAppService::class)->send($phone, $message, $channel);
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('ReminderService: erreur canal notification', [
                    'channel_id' => $channel->id,
                    'type' => $channel->type,
                    'message' => $e->getMessage(),
                ]);
            }
        }
    }

    /** Requête de base des factures en retard (sans le filtre email client). */
    public function overdueQuery(Company $company): Builder
    {
        return Document::query()
            ->where('company_id', $company->id)
            ->where('type', 'invoice')
            ->whereIn('status', self::ACTIVE_STATUSES)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', today())
            ->whereColumn('total', '>', 'amount_paid');
    }
}

<?php

namespace App\Services;

use App\Mail\DocumentMail;
use App\Models\Document;
use App\Models\RecurringTemplate;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Moteur des factures récurrentes (cahier §3 — abonnements automatiques) :
 * matérialise les gabarits arrivés à échéance en factures, avec finalisation
 * (scellement) et envoi email optionnels.
 */
class RecurringService
{
    public function __construct(private DocumentService $documents)
    {
    }

    /**
     * Génère toutes les factures récurrentes dues à la date donnée (défaut :
     * aujourd'hui). Idempotent : chaque gabarit est verrouillé (lockForUpdate)
     * et sa date de prochaine émission re-vérifiée dans la transaction — un
     * second passage le même jour ne produit rien.
     *
     * @return int Nombre de factures générées.
     */
    public function runDue(?Carbon $today = null): int
    {
        $today = ($today ?? Carbon::today())->copy()->startOfDay();
        $generated = 0;

        $ids = RecurringTemplate::query()
            ->where('is_active', true)
            ->whereDate('next_run_date', '<=', $today)
            ->orderBy('id')
            ->pluck('id');

        foreach ($ids as $id) {
            DB::transaction(function () use ($id, $today, &$generated) {
                /** @var RecurringTemplate|null $template */
                $template = RecurringTemplate::query()->lockForUpdate()->find($id);

                // Re-vérification sous verrou (anti double génération)
                if (! $template
                    || ! $template->is_active
                    || $template->next_run_date->copy()->startOfDay()->gt($today)) {
                    return;
                }

                // Bornes atteintes avant génération → simple désactivation
                if ($this->isExhausted($template)) {
                    $template->update(['is_active' => false]);

                    return;
                }

                $this->generateAndAdvance($template, $template->next_run_date->copy());
                $generated++;
            });
        }

        return $generated;
    }

    /** Génération manuelle immédiate (« Générer maintenant »). */
    public function generateNow(RecurringTemplate $template): Document
    {
        return DB::transaction(function () use ($template) {
            $locked = RecurringTemplate::query()->lockForUpdate()->findOrFail($template->id);

            return $this->generateAndAdvance($locked, Carbon::today(), advanceFrom: Carbon::today());
        });
    }

    /**
     * Crée la facture depuis le gabarit puis avance le compteur d'occurrences
     * et la prochaine échéance ($advanceFrom permet, en génération manuelle,
     * de recalculer depuis la date du jour).
     */
    private function generateAndAdvance(RecurringTemplate $template, Carbon $issueDate, ?Carbon $advanceFrom = null): Document
    {
        $user = $this->actingUserFor($template);

        $document = $this->documents->create($template->company, $user, [
            'type' => 'invoice',
            'customer_id' => $template->customer_id,
            'issue_date' => $issueDate->toDateString(),
            'due_date' => $issueDate->copy()->addDays((int) $template->due_days)->toDateString(),
            'currency' => $template->currency,
            'notes' => $template->notes,
            'terms' => $template->terms,
            'reference' => 'REC-'.$template->id,
        ], $template->lines ?? []);

        if ($template->auto_finalize) {
            $this->documents->finalize($document);
        }

        // Envoi email optionnel — un échec d'envoi n'invalide jamais la génération
        if ($template->auto_send && $template->customer?->email) {
            try {
                if (! $document->isFinalized()) {
                    $this->documents->finalize($document); // le PDF envoyé doit être scellé
                }

                $document->load(['lines', 'customer', 'company']);
                Mail::to($template->customer->email)->send(new DocumentMail($document));

                $updates = ['sent_at' => now()];
                if ($document->status === 'draft') {
                    $updates['status'] = 'sent';
                }
                $document->update($updates);
            } catch (\Throwable $e) {
                Log::warning('Facture récurrente : échec de l\'envoi email.', [
                    'template_id' => $template->id,
                    'document_id' => $document->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Avance du gabarit : occurrence consommée + prochaine échéance
        $from = ($advanceFrom ?? $issueDate)->copy()->startOfDay();
        $template->forceFill([
            'occurrences_done' => $template->occurrences_done + 1,
            'last_run_date' => $issueDate->toDateString(),
            'next_run_date' => $template->computeNextRunDate($from)->toDateString(),
        ])->save();

        if ($this->isExhausted($template)) {
            $template->update(['is_active' => false]);
        }

        return $document->fresh(['lines', 'customer']);
    }

    /** Le gabarit a-t-il épuisé ses bornes (date de fin ou nombre d'occurrences) ? */
    private function isExhausted(RecurringTemplate $template): bool
    {
        if ($template->occurrences_limit !== null
            && $template->occurrences_done >= $template->occurrences_limit) {
            return true;
        }

        return $template->end_date !== null
            && $template->next_run_date->copy()->startOfDay()->gt($template->end_date->copy()->startOfDay());
    }

    /** Auteur des factures générées : créateur du gabarit, sinon propriétaire de la société. */
    private function actingUserFor(RecurringTemplate $template): User
    {
        return $template->creator ?? $template->company->owner;
    }
}

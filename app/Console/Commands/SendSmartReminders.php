<?php

namespace App\Console\Commands;

use App\Models\Document;
use App\Models\ReminderRule;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Rappels de paiement intelligents configurables (cahier des charges §9).
 *
 * Parcourt les règles actives de chaque société et envoie un email (ou
 * logue un message WhatsApp) pour chaque facture en retard correspondant
 * au nombre de jours configuré.
 */
class SendSmartReminders extends Command
{
    protected $signature = 'reminders:send-smart
                            {--dry-run : Simule sans envoyer}';

    protected $description = 'Envoie les rappels de paiement configurables par règles (email / WhatsApp)';

    /** Statuts de factures éligibles aux relances */
    private const OVERDUE_STATUSES = ['sent', 'overdue', 'partial'];

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $sent   = 0;
        $errors = 0;

        // Charger toutes les règles actives avec leur société et les factures dues
        $rules = ReminderRule::with('company')
            ->where('is_active', true)
            ->orderBy('days_after_due')
            ->get();

        if ($rules->isEmpty()) {
            $this->info('Aucune règle active — rien à faire.');
            return self::SUCCESS;
        }

        foreach ($rules as $rule) {
            $company = $rule->company;
            if (! $company) {
                continue;
            }

            // Trouver les factures dues depuis exactement `days_after_due` jours
            $targetDate = now()->subDays($rule->days_after_due)->toDateString();

            $invoices = Document::where('company_id', $company->id)
                ->where('type', 'invoice')
                ->whereIn('status', self::OVERDUE_STATUSES)
                ->whereNotNull('due_date')
                ->whereDate('due_date', $targetDate)
                ->whereColumn('total', '>', 'amount_paid')
                ->with('customer')
                ->get();

            foreach ($invoices as $invoice) {
                try {
                    $message = $this->buildMessage($rule, $invoice, $company);

                    if (! $dryRun) {
                        if ($rule->channel === 'email') {
                            $this->sendEmail($invoice, $company, $message);
                        } else {
                            // Canal WhatsApp : loggué pour intégration future
                            $this->logWhatsApp($invoice, $company, $message);
                        }
                    }

                    $this->line(sprintf(
                        '[%s] Règle J+%d · %s · %s · %s',
                        $dryRun ? 'DRY' : 'OK',
                        $rule->days_after_due,
                        strtoupper($rule->channel),
                        $company->name,
                        $invoice->number ?? "#{$invoice->id}"
                    ));

                    $sent++;
                } catch (\Throwable $e) {
                    $errors++;
                    Log::error('SendSmartReminders: échec envoi', [
                        'rule_id'     => $rule->id,
                        'invoice_id'  => $invoice->id,
                        'channel'     => $rule->channel,
                        'error'       => $e->getMessage(),
                    ]);
                    $this->error("Erreur facture #{$invoice->id} : {$e->getMessage()}");
                }
            }
        }

        $this->info($dryRun
            ? "Simulation terminée : {$sent} rappel(s) détecté(s)."
            : "{$sent} rappel(s) envoyé(s), {$errors} erreur(s)."
        );

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function buildMessage(ReminderRule $rule, Document $invoice, $company): string
    {
        $template = $rule->message_template ?: $this->defaultTemplate($rule->days_after_due);

        $daysOverdue = now()->diffInDays($invoice->due_date);
        $amount      = number_format((float) ($invoice->total - $invoice->amount_paid), 2, ',', ' ')
                     . ' ' . ($company->currency ?? 'XOF');

        return str_replace(
            ['{client_name}', '{invoice_number}', '{amount}', '{days_overdue}', '{company_name}'],
            [
                $invoice->customer?->name ?? 'Client',
                $invoice->number ?? "#{$invoice->id}",
                $amount,
                $daysOverdue,
                $company->name,
            ],
            $template
        );
    }

    private function defaultTemplate(int $days): string
    {
        if ($days <= 3) {
            return "Bonjour {client_name},\n\nNous vous rappelons que la facture {invoice_number} ({amount}) est en retard de {days_overdue} jour(s).\n\nMerci de procéder au règlement.\n\n{company_name}";
        }

        if ($days <= 7) {
            return "Bonjour {client_name},\n\nMalgré notre rappel, la facture {invoice_number} ({amount}) demeure impayée depuis {days_overdue} jours. Merci de régulariser sous 48 h.\n\n{company_name}";
        }

        return "Bonjour {client_name},\n\nNous vous mettons en demeure de régler la facture {invoice_number} ({amount}) — {days_overdue} jours de retard.\n\n{company_name}";
    }

    private function sendEmail(Document $invoice, $company, string $message): void
    {
        $recipient = $invoice->customer?->email ?? null;

        if (! $recipient) {
            Log::warning('SendSmartReminders: pas d\'email client', ['invoice_id' => $invoice->id]);
            return;
        }

        Mail::raw($message, function ($mail) use ($recipient, $company, $invoice) {
            $mail->to($recipient)
                 ->subject("Rappel de paiement — Facture {$invoice->number} [{$company->name}]");
        });
    }

    private function logWhatsApp(Document $invoice, $company, string $message): void
    {
        $phone = $invoice->customer?->phone ?? 'N/A';

        Log::info('SendSmartReminders: WhatsApp (non encore intégré)', [
            'company'    => $company->name,
            'invoice'    => $invoice->number ?? $invoice->id,
            'phone'      => $phone,
            'message'    => $message,
        ]);
    }
}

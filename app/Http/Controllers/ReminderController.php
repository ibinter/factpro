<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\ReminderLog;
use App\Services\ReminderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Relances intelligentes (cahier des charges §13) : tableau de bord des factures
 * en retard, relance manuelle et paramétrage des seuils d'escalade.
 */
class ReminderController extends Controller
{
    public function __construct(private ReminderService $reminders)
    {
    }

    public function index(Request $request): Response
    {
        $company = $request->user()->currentCompany;
        $levels = $this->reminders->levels($company);

        $invoices = $this->reminders->overdueInvoices($company);

        // Dernier niveau relancé + date, par facture (une requête)
        $lastLogs = ReminderLog::query()
            ->whereIn('document_id', $invoices->pluck('id'))
            ->orderBy('level')
            ->get(['document_id', 'level', 'sent_at'])
            ->groupBy('document_id')
            ->map(fn ($logs) => $logs->last());

        $overdue = $invoices->map(function (Document $document) use ($lastLogs) {
            $last = $lastLogs->get($document->id);
            $lastLevel = $last?->level ?? 0;

            return [
                'id' => $document->id,
                'number' => $document->number,
                'customer_name' => $document->customer?->name,
                'due_date' => $document->due_date?->toDateString(),
                'days_late' => max(0, (int) $document->due_date->startOfDay()->diffInDays(today())),
                'balance_due' => $document->balance_due,
                'currency' => $document->currency,
                'last_level' => $lastLevel ?: null,
                'last_sent_at' => $last?->sent_at?->toDateTimeString(),
                'next_level' => $lastLevel >= ReminderService::MAX_LEVEL ? null : $lastLevel + 1,
            ];
        })->values();

        $history = ReminderLog::query()
            ->where('company_id', $company->id)
            ->with(['document:id,number', 'sender:id,name'])
            ->orderByDesc('sent_at')
            ->orderByDesc('id')
            ->limit(50)
            ->get()
            ->map(fn (ReminderLog $log) => [
                'id' => $log->id,
                'document_id' => $log->document_id,
                'number' => $log->document?->number,
                'level' => $log->level,
                'level_label' => $log->level_label,
                'sent_to' => $log->sent_to,
                'sent_at' => $log->sent_at?->toDateTimeString(),
                'triggered_by' => $log->triggered_by,
                'sender_name' => $log->sender?->name,
            ]);

        return Inertia::render('Reminders/Index', [
            'overdue' => $overdue,
            'history' => $history,
            'stats' => [
                'overdue_count' => $invoices->count(),
                'overdue_total' => round($invoices->sum(fn ($d) => $d->balance_due), 2),
                'sent_this_month' => ReminderLog::where('company_id', $company->id)
                    ->where('sent_at', '>=', now()->startOfMonth())
                    ->count(),
            ],
            'settings' => [
                'enabled' => $this->reminders->isEnabled($company),
                'levels' => collect($levels)->map(fn ($l, $n) => ['level' => $n, 'days' => $l['days']])->values(),
            ],
            'currency' => $company->currency,
        ]);
    }

    /** Relance manuelle d'une facture depuis l'interface. */
    public function send(Request $request, Document $document): RedirectResponse
    {
        abort_unless($document->company_id === $request->user()->current_company_id, 403);

        $level = $this->reminders->nextLevelFor($document);

        if ($level === null) {
            abort(422, 'La mise en demeure (niveau 3) a déjà été envoyée pour cette facture.');
        }

        $log = $this->reminders->send($document, $level, 'manual', $request->user()->id);

        if ($log === null) {
            return back()->with('error', 'Relance impossible : vérifiez que le client a un email et que la facture n\'est pas soldée.');
        }

        return redirect()->route('reminders.index')->with('success', "Relance niveau {$level} envoyée à {$log->sent_to} pour la facture {$document->number}.");
    }

    /** Paramétrage : activation + seuils (jours) des 3 niveaux. */
    public function updateSettings(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'enabled' => 'required|boolean',
            'levels' => 'required|array|size:3',
            'levels.1' => 'required|integer|min:1|max:90',
            'levels.2' => 'required|integer|min:1|max:90',
            'levels.3' => 'required|integer|min:1|max:90',
        ]);

        if (! ($data['levels'][1] < $data['levels'][2] && $data['levels'][2] < $data['levels'][3])) {
            throw ValidationException::withMessages([
                'levels' => 'Les seuils doivent être strictement croissants (niveau 1 < niveau 2 < niveau 3).',
            ]);
        }

        $company = $request->user()->currentCompany;

        $settings = $company->settings ?? [];
        $settings['reminders'] = [
            'enabled' => (bool) $data['enabled'],
            'levels' => [
                1 => ['days' => (int) $data['levels'][1]],
                2 => ['days' => (int) $data['levels'][2]],
                3 => ['days' => (int) $data['levels'][3]],
            ],
        ];
        $company->update(['settings' => $settings]);

        return redirect()->route('reminders.index')->with('success', 'Paramètres de relance enregistrés.');
    }
}

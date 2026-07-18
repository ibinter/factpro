<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\TimeEntry;
use App\Services\LicenseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

/**
 * Entrées de temps (cahier §9) — saisie manuelle ou chronomètre.
 * Réservé BUSINESS/ENTERPRISE (§22.1), comme le module projets.
 */
class TimeEntryController extends Controller
{
    private const ALLOWED_PLANS = ['business', 'enterprise'];

    public function __construct(private LicenseService $licenses)
    {
    }

    private function ensureAccess(Request $request): void
    {
        $license = $this->licenses->currentFor($request->user());

        abort_unless(
            $license !== null && in_array($license->plan?->code, self::ALLOWED_PLANS, true),
            403,
            'Le suivi du temps est réservé aux forfaits BUSINESS et ENTERPRISE.'
        );
    }

    /** Crée une entrée de temps (durée en minutes ou couple started_at/ended_at). */
    public function store(Request $request, Project $project): RedirectResponse
    {
        $this->ensureAccess($request);
        abort_unless($project->company_id === $request->user()->current_company_id, 403);

        $data = $this->validateData($request);

        TimeEntry::create([
            'company_id' => $project->company_id,
            'project_id' => $project->id,
            'user_id' => $request->user()->id,
            'description' => $data['description'],
            'entry_date' => $data['entry_date'],
            'started_at' => $data['started_at'] ?? null,
            'ended_at' => $data['ended_at'] ?? null,
            'duration_minutes' => $data['duration_minutes'],
            // Copie du taux au moment de la saisie (sinon celui du projet).
            'hourly_rate' => $data['hourly_rate'] ?? $project->hourly_rate,
            'is_billable' => $data['is_billable'] ?? true,
        ]);

        return back()->with('success', 'Temps enregistré.');
    }

    public function update(Request $request, TimeEntry $entry): RedirectResponse
    {
        $this->ensureAccess($request);
        abort_unless($entry->company_id === $request->user()->current_company_id, 403);

        if ($entry->is_billed) {
            throw ValidationException::withMessages([
                'entry' => 'Entrée déjà facturée : modification impossible.',
            ]);
        }

        $data = $this->validateData($request);

        $entry->update([
            'description' => $data['description'],
            'entry_date' => $data['entry_date'],
            'started_at' => $data['started_at'] ?? null,
            'ended_at' => $data['ended_at'] ?? null,
            'duration_minutes' => $data['duration_minutes'],
            'hourly_rate' => $data['hourly_rate'] ?? $entry->hourly_rate,
            'is_billable' => $data['is_billable'] ?? $entry->is_billable,
        ]);

        return back()->with('success', 'Entrée mise à jour.');
    }

    public function destroy(Request $request, TimeEntry $entry): RedirectResponse
    {
        $this->ensureAccess($request);
        abort_unless($entry->company_id === $request->user()->current_company_id, 403);

        if ($entry->is_billed) {
            throw ValidationException::withMessages([
                'entry' => 'Entrée déjà facturée : suppression impossible.',
            ]);
        }

        $entry->delete();

        return back()->with('success', 'Entrée supprimée.');
    }

    /**
     * Valide la saisie : duration_minutes explicite OU couple started_at/ended_at
     * (la durée est alors calculée). duration_minutes reste la source de vérité.
     */
    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'description' => ['required', 'string', 'max:255'],
            'entry_date' => ['required', 'date'],
            'duration_minutes' => ['required_without_all:started_at,ended_at', 'nullable', 'integer', 'min:1', 'max:1440'],
            'started_at' => ['nullable', 'required_with:ended_at', 'date'],
            'ended_at' => ['nullable', 'required_with:started_at', 'date', 'after:started_at'],
            'hourly_rate' => ['nullable', 'numeric', 'min:0'],
            'is_billable' => ['sometimes', 'boolean'],
        ]);

        if (empty($data['duration_minutes'])) {
            $start = Carbon::parse($data['started_at']);
            $end = Carbon::parse($data['ended_at']);
            $data['duration_minutes'] = max(1, (int) round($start->diffInMinutes($end)));
        }

        return $data;
    }
}

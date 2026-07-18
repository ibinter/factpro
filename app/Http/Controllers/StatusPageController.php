<?php

namespace App\Http\Controllers;

use App\Models\StatusIncident;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class StatusPageController extends Controller
{
    private function buildComponents(iterable $incidents): array
    {
        $components = [
            ['name' => 'API REST',          'key' => 'api',      'status' => 'operational'],
            ['name' => 'Application Web',   'key' => 'web',      'status' => 'operational'],
            ['name' => 'Facturation & PDF', 'key' => 'billing',  'status' => 'operational'],
            ['name' => 'Emails & Relances', 'key' => 'email',    'status' => 'operational'],
            ['name' => 'POS & Caisse',      'key' => 'pos',      'status' => 'operational'],
            ['name' => 'Paiements',         'key' => 'payments', 'status' => 'operational'],
            ['name' => 'Portail Client',    'key' => 'portal',   'status' => 'operational'],
        ];

        foreach ($incidents as $incident) {
            foreach ($incident->affected_components ?? [] as $key) {
                foreach ($components as &$comp) {
                    if ($comp['key'] === $key) {
                        $comp['status'] = match ($incident->severity) {
                            'critical' => 'major_outage',
                            'major'    => 'partial_outage',
                            default    => 'degraded',
                        };
                    }
                }
            }
        }

        return $components;
    }

    public function public(): InertiaResponse
    {
        $incidents = StatusIncident::public()->active()
            ->orderBy('started_at', 'desc')->limit(5)->get();

        $resolved = StatusIncident::public()->where('status', 'resolved')
            ->orderBy('resolved_at', 'desc')->limit(10)->get();

        $components = $this->buildComponents($incidents);

        return inertia('Status/Public', compact('incidents', 'resolved', 'components'));
    }

    public function opsBoard(): InertiaResponse
    {
        $activeIncidents = StatusIncident::active()->orderBy('started_at', 'desc')->get();

        $allIncidents = StatusIncident::orderBy('created_at', 'desc')->limit(50)->get();

        // MTTR: average minutes to resolve
        $resolved = StatusIncident::where('status', 'resolved')
            ->whereNotNull('resolved_at')
            ->whereMonth('created_at', now()->month)
            ->get();

        $mttr = $resolved->count() > 0
            ? $resolved->avg(fn ($i) => $i->started_at->diffInMinutes($i->resolved_at))
            : 0;

        $monthlyCount = StatusIncident::whereMonth('created_at', now()->month)->count();

        // Weekly incidents for last 12 weeks
        $weeklyData = [];
        for ($i = 11; $i >= 0; $i--) {
            $start = now()->startOfWeek()->subWeeks($i);
            $end   = (clone $start)->endOfWeek();
            $weeklyData[] = [
                'week'  => $start->format('d/m'),
                'count' => StatusIncident::whereBetween('created_at', [$start, $end])->count(),
            ];
        }

        $components = $this->buildComponents($activeIncidents);

        return inertia('Admin/OpsBoard', compact(
            'activeIncidents',
            'allIncidents',
            'mttr',
            'monthlyCount',
            'weeklyData',
            'components'
        ));
    }

    public function storeIncident(Request $r): JsonResponse
    {
        $data = $r->validate([
            'title'               => 'required|string|max:200',
            'description'         => 'required|string',
            'status'              => 'in:investigating,identified,monitoring,resolved',
            'severity'            => 'in:minor,major,critical',
            'affected_components' => 'array',
            'started_at'          => 'nullable|date',
            'is_public'           => 'boolean',
        ]);

        $incident = StatusIncident::create([
            ...$data,
            'started_at'    => $data['started_at'] ?? now(),
            'created_by_id' => $r->user()?->id,
        ]);

        return response()->json($incident, 201);
    }

    public function updateIncident(StatusIncident $incident, Request $r): JsonResponse
    {
        $data = $r->validate([
            'title'               => 'string|max:200',
            'description'         => 'string',
            'status'              => 'in:investigating,identified,monitoring,resolved',
            'severity'            => 'in:minor,major,critical',
            'affected_components' => 'array',
            'is_public'           => 'boolean',
        ]);

        $incident->update($data);

        return response()->json($incident);
    }

    public function resolveIncident(StatusIncident $incident): JsonResponse
    {
        $incident->update([
            'status'      => 'resolved',
            'resolved_at' => now(),
        ]);

        return response()->json($incident);
    }

    public function api(): JsonResponse
    {
        $incidents = StatusIncident::public()->active()
            ->orderBy('started_at', 'desc')->limit(5)->get();

        $components = $this->buildComponents($incidents);

        $overallStatus = collect($components)->contains(fn ($c) => $c['status'] !== 'operational')
            ? 'incident'
            : 'operational';

        return response()->json([
            'status'     => $overallStatus,
            'updated_at' => now()->toISOString(),
            'components' => $components,
            'incidents'  => $incidents,
        ]);
    }
}

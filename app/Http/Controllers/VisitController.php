<?php
namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerVisit;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VisitController extends Controller
{
    public function index(Request $request): Response
    {
        $company = $request->user()->currentCompany;
        $visits = CustomerVisit::where('company_id', $company->id)
            ->with(['customer:id,name','user:id,name'])
            ->orderByDesc('planned_at')
            ->paginate(30)
            ->through(fn($v) => [
                'id'           => $v->id,
                'customer'     => $v->customer?->name ?? $v->customer_name ?? '—',
                'user'         => $v->user?->name,
                'visit_type'   => $v->visit_type,
                'status'       => $v->status,
                'outcome'      => $v->outcome,
                'planned_at'   => $v->planned_at?->format('Y-m-d H:i'),
                'duration'     => $v->duration_formatted,
                'has_report'   => !empty($v->report),
            ]);

        $stats = [
            'total'     => CustomerVisit::where('company_id', $company->id)->count(),
            'this_week' => CustomerVisit::where('company_id', $company->id)
                ->where('planned_at', '>=', now()->startOfWeek())->count(),
            'completed' => CustomerVisit::where('company_id', $company->id)
                ->where('status', 'completed')->count(),
            'positif'   => CustomerVisit::where('company_id', $company->id)
                ->where('outcome', 'positif')->count(),
        ];

        $customers = Customer::where('company_id', $company->id)
            ->select('id','name')->orderBy('name')->get();

        return Inertia::render('Visits/Index', compact('visits','stats','customers'));
    }

    public function store(Request $request)
    {
        $company = $request->user()->currentCompany;
        $data = $request->validate([
            'customer_id'     => 'nullable|integer',
            'customer_name'   => 'nullable|string|max:255',
            'visit_type'      => 'required|in:commercial,livraison,sav,prospection',
            'planned_at'      => 'nullable|date',
            'objective'       => 'nullable|string|max:1000',
            'address_visited' => 'nullable|string|max:500',
        ]);

        CustomerVisit::create([
            'company_id' => $company->id,
            'user_id'    => $request->user()->id,
        ] + $data);

        return back()->with('success', 'Visite planifiée.');
    }

    public function update(Request $request, CustomerVisit $visit)
    {
        if ($visit->company_id !== $request->user()->currentCompany->id) abort(403);

        $data = $request->validate([
            'status'          => 'sometimes|in:planned,in_progress,completed,cancelled',
            'lat_start'       => 'nullable|numeric',
            'lng_start'       => 'nullable|numeric',
            'lat_end'         => 'nullable|numeric',
            'lng_end'         => 'nullable|numeric',
            'address_visited' => 'nullable|string|max:500',
            'started_at'      => 'nullable|date',
            'ended_at'        => 'nullable|date',
            'report'          => 'nullable|string|max:5000',
            'outcome'         => 'nullable|in:positif,neutre,negatif,relance',
        ]);

        // Calculer durée si start+end connus
        if (!empty($data['started_at']) && !empty($data['ended_at'])) {
            $data['duration_minutes'] = (int) round(
                (strtotime($data['ended_at']) - strtotime($data['started_at'])) / 60
            );
        }

        $visit->update($data);
        return back()->with('success', 'Visite mise à jour.');
    }

    public function destroy(Request $request, CustomerVisit $visit)
    {
        if ($visit->company_id !== $request->user()->currentCompany->id) abort(403);
        $visit->delete();
        return back()->with('success', 'Visite supprimée.');
    }

    /** API — check-in GPS */
    public function checkin(Request $request, CustomerVisit $visit)
    {
        if ($visit->company_id !== $request->user()->currentCompany->id) abort(403);
        $data = $request->validate(['lat' => 'required|numeric', 'lng' => 'required|numeric']);
        $visit->update([
            'lat_start'  => $data['lat'],
            'lng_start'  => $data['lng'],
            'started_at' => now(),
            'status'     => 'in_progress',
        ]);
        return response()->json(['ok' => true, 'message' => 'Check-in enregistré.']);
    }

    /** API — check-out GPS */
    public function checkout(Request $request, CustomerVisit $visit)
    {
        if ($visit->company_id !== $request->user()->currentCompany->id) abort(403);
        $data = $request->validate([
            'lat'     => 'nullable|numeric',
            'lng'     => 'nullable|numeric',
            'report'  => 'nullable|string|max:5000',
            'outcome' => 'nullable|in:positif,neutre,negatif,relance',
        ]);
        $duration = $visit->started_at
            ? (int) round($visit->started_at->diffInMinutes(now()))
            : null;

        $visit->update([
            'lat_end'          => $data['lat'] ?? null,
            'lng_end'          => $data['lng'] ?? null,
            'ended_at'         => now(),
            'status'           => 'completed',
            'duration_minutes' => $duration,
            'report'           => $data['report'] ?? null,
            'outcome'          => $data['outcome'] ?? null,
        ]);
        return response()->json(['ok' => true, 'duration_minutes' => $duration]);
    }
}

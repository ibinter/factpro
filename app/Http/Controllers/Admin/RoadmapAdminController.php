<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RoadmapFeature;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RoadmapAdminController extends Controller {
    public function index(): Response {
        $features = RoadmapFeature::orderBy('sort_order')->orderByDesc('votes_count')->get();
        return Inertia::render('Admin/Roadmap', ['features' => $features]);
    }

    public function store(Request $request): RedirectResponse {
        $data = $request->validate([
            'title'       => 'required|string|max:150',
            'description' => 'required|string|max:500',
            'category'    => 'required|in:general,pos,facturation,stocks,api,mobile',
            'status'      => 'required|in:planned,in_progress,delivered,cancelled',
            'sort_order'  => 'nullable|integer',
        ]);
        if ($data['status'] === 'delivered') $data['delivered_at'] = now();
        RoadmapFeature::create($data);
        return back()->with('success', 'Fonctionnalité ajoutée.');
    }

    public function update(Request $request, RoadmapFeature $feature): RedirectResponse {
        $data = $request->validate([
            'title'       => 'required|string|max:150',
            'description' => 'required|string|max:500',
            'category'    => 'required|in:general,pos,facturation,stocks,api,mobile',
            'status'      => 'required|in:planned,in_progress,delivered,cancelled',
            'sort_order'  => 'nullable|integer',
        ]);
        if ($data['status'] === 'delivered' && !$feature->delivered_at) $data['delivered_at'] = now();
        $feature->update($data);
        return back()->with('success', 'Fonctionnalité mise à jour.');
    }

    public function destroy(RoadmapFeature $feature): RedirectResponse {
        $feature->delete();
        return back()->with('success', 'Fonctionnalité supprimée.');
    }
}

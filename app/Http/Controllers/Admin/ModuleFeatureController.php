<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ModuleFeature;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ModuleFeatureController extends Controller
{
    public function index(Request $request): Response
    {
        $query = ModuleFeature::query()->orderBy('sort_order');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return Inertia::render('Admin/ModuleFeatures', [
            'items'   => $query->paginate(25)->withQueryString(),
            'filters' => $request->only(['category', 'status']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'slug'               => 'required|string|max:100|unique:module_features,slug',
            'category'           => 'required|in:module,feature,integration',
            'name_fr'            => 'required|string|max:255',
            'name_en'            => 'nullable|string|max:255',
            'description_fr'     => 'nullable|string',
            'description_en'     => 'nullable|string',
            'icon'               => 'nullable|string|max:50',
            'available_in_plans' => 'nullable|array',
            'available_in_plans.*' => 'string|in:starter,pro,business,enterprise',
            'status'             => 'required|in:available,coming_soon,beta,removed',
            'sort_order'         => 'integer|min:0',
            'is_active'          => 'boolean',
        ]);

        $item = ModuleFeature::create($data);

        return back()->with('success', 'Module créé avec succès.');
    }

    public function update(Request $request, int $id)
    {
        $item = ModuleFeature::findOrFail($id);

        $data = $request->validate([
            'slug'               => 'required|string|max:100|unique:module_features,slug,' . $id,
            'category'           => 'required|in:module,feature,integration',
            'name_fr'            => 'required|string|max:255',
            'name_en'            => 'nullable|string|max:255',
            'description_fr'     => 'nullable|string',
            'description_en'     => 'nullable|string',
            'icon'               => 'nullable|string|max:50',
            'available_in_plans' => 'nullable|array',
            'available_in_plans.*' => 'string|in:starter,pro,business,enterprise',
            'status'             => 'required|in:available,coming_soon,beta,removed',
            'sort_order'         => 'integer|min:0',
            'is_active'          => 'boolean',
        ]);

        $item->update($data);

        return back()->with('success', 'Module mis à jour.');
    }

    public function destroy(int $id)
    {
        ModuleFeature::findOrFail($id)->delete();

        return back()->with('success', 'Module supprimé.');
    }
}
